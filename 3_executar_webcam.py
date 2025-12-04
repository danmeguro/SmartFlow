import cv2
import numpy as np
import tensorflow as tf
import requests
import time
import json
import random

# --- CONFIGURAÇÕES DO MODELO ---
MODEL_PATH = 'modelo_suco.h5'
CONFIDENCE_THRESHOLD = 0.70 
CLASSES = ['Laranja', 'Uva', 'Vazio']
COLORS = [(0, 165, 255), (128, 0, 128), (200, 200, 200)] # Laranja, Roxo, Cinza

# --- CONFIGURAÇÃO DA API LOCAL (INTEGRAÇÃO PHP) ---
# IMPORTANTE: Ajuste esta URL conforme a pasta do seu projeto no XAMPP/WAMP
# Baseado na sua imagem, a estrutura é SMARTFLOW/backend/atualizar_envase.php
API_URL = 'http://localhost/SmartFlow/backend/atualizar_envase.php'

# --- VARIÁVEIS DE CONTROLE ---
estado_atual = "Vazio"
estado_anterior = "Vazio"
contador_garrafas = 0

print("--- INICIANDO SISTEMA SMARTFLOW ---")
print("1. Carregando modelo IA...")
try:
    model = tf.keras.models.load_model(MODEL_PATH)
    print("✅ Modelo carregado com sucesso.")
except Exception as e:
    print(f"❌ ERRO CRÍTICO: Não foi possível carregar 'modelo_suco.h5'.\nErro: {e}")
    exit()

# Função para enviar dados ao seu backend PHP
def enviar_para_banco(sabor, contagem):
    print(f"📡 Enviando dados para: {API_URL}")
    
    # Prepara o JSON exatamente como o 'atualizar_envase.php' espera
    # Baseado no seu arquivo PHP enviado:
    payload = {
        "pedido_id": 1,             # Pode ser dinâmico no futuro
        "rotuladora_presenca": 1,   # 1 = Detectou garrafa
        "estoque_contagem": contagem,
        "carrossel_posicao": random.randint(1, 360), # Simulação de sensor
        "tampinhas_disponiveis": 1, # 1 = OK
        "tempo_envase_ms": random.randint(2800, 3500) # Simulação de tempo (ms)
    }

    try:
        # Envia a requisição POST para o servidor local
        response = requests.post(API_URL, json=payload, timeout=2)
        
        if response.status_code == 200:
            # O PHP retorna JSON com { success: true/false }
            print(f"✅ SUCESSO! Banco Atualizado. Retorno do PHP: {response.text}")
        else:
            print(f"⚠️ ERRO HTTP {response.status_code}: Verifique se o caminho do arquivo PHP está correto.")
            
    except requests.exceptions.ConnectionError:
        print(f"❌ ERRO DE CONEXÃO: O servidor local (Apache/XAMPP) está ligado? Não consegui acessar {API_URL}")
    except Exception as e:
        print(f"❌ Erro genérico ao enviar: {e}")

# --- INÍCIO DA WEBCAM ---
print("2. Abrindo Webcam...")
cap = cv2.VideoCapture(0, cv2.CAP_DSHOW) # Tenta DirectShow para Windows
if not cap.isOpened():
    cap = cv2.VideoCapture(0)

while True:
    ret, frame = cap.read()
    if not ret:
        print("Erro na captura da câmera.")
        break

    # --- 1. PROCESSAMENTO DE IMAGEM (Visão) ---
    # Redimensiona
    img_small = cv2.resize(frame, (224, 224))
    
    # CORREÇÃO DE COR (Fundamental para Laranja funcionar)
    img_rgb = cv2.cvtColor(img_small, cv2.COLOR_BGR2RGB)
    
    # Prepara para a IA
    img_array = np.array(img_rgb, dtype=np.float32)
    img_array = np.expand_dims(img_array, axis=0)
    img_input = tf.keras.applications.mobilenet_v2.preprocess_input(img_array)

    # Predição
    preds = model.predict(img_input, verbose=0)
    idx = np.argmax(preds)
    confidence = preds[0][idx]
    label = CLASSES[idx]

    # --- 2. LÓGICA DE NEGÓCIO (Máquina de Estados) ---
    
    # Filtro de confiança para evitar "piscar"
    if confidence >= CONFIDENCE_THRESHOLD:
        novo_estado = label
    else:
        novo_estado = estado_atual # Mantém o anterior se tiver dúvida
        
    # DETECÇÃO DE BORDA DE SUBIDA (O momento exato que a garrafa entra)
    # Lógica: Estava "Vazio" -> Agora é "Laranja" ou "Uva"
    if estado_anterior == "Vazio" and novo_estado in ["Laranja", "Uva"]:
        contador_garrafas += 1
        print(f"\n--- 🍾 NOVA GARRAFA DETECTADA: {novo_estado.upper()} (#{contador_garrafas}) ---")
        
        # Envia para o PHP/MySQL
        enviar_para_banco(novo_estado, contador_garrafas)
        
        # Pequeno delay para evitar envio duplicado no mesmo segundo
        # time.sleep(0.5) 

    estado_atual = novo_estado
    estado_anterior = estado_atual

    # --- 3. INTERFACE GRÁFICA (Display) ---
    
    if confidence >= CONFIDENCE_THRESHOLD:
        if label == 'Vazio':
            msg = "Aguardando garrafa..."
            cor_box = (200, 200, 200) # Cinza
            cor_txt = (50, 50, 50)
        else:
            msg = f"{label.upper()}: {confidence*100:.1f}% | Total: {contador_garrafas}"
            cor_box = COLORS[idx]
            cor_txt = (255, 255, 255)
            
        # Desenha barra e texto
        cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), cor_box, -1)
        cv2.putText(frame, msg, (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 0.8, cor_txt, 2)
    
    else:
        # Modo Incerteza
        cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), (50, 50, 50), -1)
        cv2.putText(frame, "Analisando...", (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255,255,255), 2)

    cv2.imshow('SmartFlow - Monitoramento', frame)

    # Fechar com 'q' ou botão X
    if (cv2.waitKey(1) & 0xFF == ord('q')) or (cv2.getWindowProperty('SmartFlow - Monitoramento', cv2.WND_PROP_VISIBLE) < 1):
        break

cap.release()
cv2.destroyAllWindows()