import cv2
import numpy as np
import tensorflow as tf
import requests
import time

# --- 1. CONFIGURAÇÕES GERAIS ---
MODEL_PATH = 'modelo_suco.h5'
CONFIDENCE_THRESHOLD = 0.70 

# Classes (Ordem alfabética do treinamento)
CLASSES = ['Laranja', 'Uva', 'Vazio']
COLORS = [(0, 165, 255), (128, 0, 128), (200, 200, 200)] # Laranja, Roxo, Cinza

# URL da API (Ajustada para rodar no Localhost/XAMPP)
# Certifique-se que o Apache e MySQL do XAMPP estão ligados (botão Start)
API_URL = 'http://localhost/SMARTFLOW/backend/atualizar_envase.php'

# --- 2. VARIÁVEIS DE CONTROLE ---
estado_atual = "Vazio"
estado_anterior = "Vazio"
contador_garrafas = 0

print("--- INICIANDO SISTEMA SMARTFLOW (TCC) ---")

# --- 3. CARREGAMENTO DA IA ---
print("Carregando modelo inteligente...")
try:
    model = tf.keras.models.load_model(MODEL_PATH)
    print("✅ Cérebro da IA carregado com sucesso.")
except Exception as e:
    print(f"❌ ERRO CRÍTICO: Modelo não encontrado. Detalhes: {e}")
    exit()

# --- 4. FUNÇÃO DE ENVIO PARA O BANCO DE DADOS ---
def enviar_telemetria(sabor, contagem):
    print(f"📡 Atualizando Dashboard... Sabor: {sabor} | Estoque: {contagem}")
    
    # payload: O pacote de dados que o PHP espera receber
    # CORREÇÃO FEITA: Valores fixos para sensores que não existem fisicamente
    payload = {
        "pedido_id": 1,             # ID do pedido ativo
        "rotuladora_presenca": 1,   # 1 = Sensor OK
        "estoque_contagem": contagem, # <--- DADO REAL (Vindo da Câmera)
        "carrossel_posicao": 0,     # FIXO em 0 (Para não oscilar na tela)
        "tampinhas_disponiveis": 1, # 1 = Tem tampinhas (Status OK)
        "tempo_envase_ms": 3000     # Tempo fixo de 3 segundos
    }

    try:
        response = requests.post(API_URL, json=payload, timeout=2)
        if response.status_code == 200:
            print(f"✅ Banco de Dados Atualizado!")
        else:
            print(f"⚠️ Erro no PHP (Status {response.status_code})")
    except requests.exceptions.ConnectionError:
        print(f"❌ Erro de Conexão: O XAMPP/Servidor está ligado?")
    except Exception as e:
        print(f"❌ Erro genérico: {e}")

# --- 5. INICIALIZAÇÃO DA CÂMERA ---
print("Abrindo Webcam...")
# Tenta usar DirectShow (melhor para Windows)
cap = cv2.VideoCapture(0, cv2.CAP_DSHOW)
if not cap.isOpened():
    cap = cv2.VideoCapture(0)

# Loop Principal (Roda a cada frame de vídeo)
while True:
    ret, frame = cap.read()
    if not ret:
        print("Falha na captura de vídeo.")
        break

    # --- A. PRÉ-PROCESSAMENTO (Visão) ---
    # 1. Redimensiona para 224x224 (Padrão MobileNet)
    img_small = cv2.resize(frame, (224, 224))
    
    # 2. Converte BGR para RGB (CORREÇÃO CRUCIAL PARA A LARANJA)
    img_rgb = cv2.cvtColor(img_small, cv2.COLOR_BGR2RGB)
    
    # 3. Prepara array para o TensorFlow
    img_array = np.array(img_rgb, dtype=np.float32)
    img_array = np.expand_dims(img_array, axis=0)
    img_input = tf.keras.applications.mobilenet_v2.preprocess_input(img_array)

    # --- B. PREDIÇÃO (O Cérebro pensa) ---
    preds = model.predict(img_input, verbose=0)
    idx = np.argmax(preds)      # Qual índice ganhou?
    confidence = preds[0][idx]  # Qual a certeza?
    label = CLASSES[idx]        # Nome da classe

    # --- C. LÓGICA DE NEGÓCIO (Máquina de Estados) ---
    
    # Filtra incertezas
    if confidence >= CONFIDENCE_THRESHOLD:
        novo_estado = label
    else:
        novo_estado = estado_atual # Mantém o anterior na dúvida

    # DETECÇÃO DE BORDA DE SUBIDA (O momento que a garrafa entra)
    # Regra: Se estava "Vazio" e mudou para "Suco", conta +1
    if estado_anterior == "Vazio" and novo_estado in ["Laranja", "Uva"]:
        contador_garrafas += 1
        # Envia para o site
        enviar_telemetria(novo_estado, contador_garrafas)
    
    # Atualiza memória para o próximo frame
    estado_atual = novo_estado
    estado_anterior = estado_atual

    # --- D. EXIBIÇÃO NA TELA (Interface) ---
    
    if confidence >= CONFIDENCE_THRESHOLD:
        if label == 'Vazio':
            # Visual discreto para quando não tem nada
            msg = "Aguardando garrafa..."
            cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), (200, 200, 200), -1) # Cinza
            cv2.putText(frame, msg, (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (50, 50, 50), 2)
        else:
            # Visual colorido para detecção
            msg = f"{label.upper()}: {confidence*100:.1f}% | Total: {contador_garrafas}"
            cor = COLORS[idx]
            cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), cor, -1)
            cv2.putText(frame, msg, (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255, 255, 255), 2)
    else:
        # Visual de Incerteza
        cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), (50, 50, 50), -1)
        cv2.putText(frame, "Analisando...", (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255, 255, 255), 2)

    # Mostra a janela
    cv2.imshow('SmartFlow - Monitoramento TCC', frame)

    # --- E. SAÍDA ---
    # Fecha com a tecla 'q' ou clicando no 'X' da janela
    if (cv2.waitKey(1) & 0xFF == ord('q')) or (cv2.getWindowProperty('SmartFlow - Monitoramento TCC', cv2.WND_PROP_VISIBLE) < 1):
        break

cap.release()
cv2.destroyAllWindows()