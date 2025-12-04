import tensorflow as tf
from tensorflow.keras.preprocessing import image
import numpy as np
import requests
from io import BytesIO
import time
import json
from PIL import Image

# --- Configurações ---
MODEL_FILENAME = 'suco_classifier.keras'
IMAGE_URL = 'http://192.168.1.100/capture' # <-- ALTERAR para o IP da sua ESP-CAM ou servidor Flask
IMAGE_SIZE = (150, 150)                    # Deve ser o mesmo tamanho usado no treinamento
CONFIDENCE_THRESHOLD = 0.95                # Limite de confiança para emitir o comando de envase

# --- 1. Configuração Inicial ---
try:
    # Carrega o modelo treinado (.keras)
    model = tf.keras.models.load_model(MODEL_FILENAME)
    print(f"✅ Modelo '{MODEL_FILENAME}' carregado com sucesso.")

    # Carrega o mapeamento de classes
    with open('class_map.json', 'r') as f:
        class_map = json.load(f)
    # Converte chaves de string para inteiro
    class_map = {int(k): v for k, v in class_map.items()}
    print(f"✅ Mapeamento de classes carregado: {class_map}")
    
except FileNotFoundError as e:
    print(f"❌ ERRO: Arquivo não encontrado - {e.filename}")
    print("Execute o 'train_model.py' primeiro para gerar o modelo e o mapeamento de classes.")
    exit()
except Exception as e:
    print(f"❌ ERRO ao carregar modelo ou mapeamento: {e}")
    exit()


def capture_and_preprocess(url, target_size):
    """
    Função para capturar a imagem via HTTP, pré-processar e normalizar.
    """
    try:
        # Faz a requisição HTTP GET para a URL da câmera
        # O timeout evita que o script trave indefinidamente
        response = requests.get(url, timeout=5) 
        response.raise_for_status() # Lança exceção para códigos de status ruins (4xx ou 5xx)

        # Carrega a imagem a partir dos bytes da resposta HTTP
        img_bytes = BytesIO(response.content)
        img = Image.open(img_bytes).convert('RGB') # Garante que a imagem é RGB

        # Pré-processamento: Redimensionamento e Conversão para Array
        # Redimensiona a imagem para o tamanho de entrada do modelo (ex: 150x150)
        img = img.resize(target_size) 
        
        # Converte a imagem PIL para um array NumPy
        x = image.img_to_array(img)
        
        # Adiciona uma dimensão de 'batch' no início (o Keras espera um tensor [1, altura, largura, canais])
        x = np.expand_dims(x, axis=0) 

        # Normalização: Divide os valores dos pixels por 255
        # CRUCIAL: Deve ser a mesma normalização aplicada no treinamento!
        x = x / 255.0 

        return x

    except requests.exceptions.RequestException as e:
        print(f"❌ ERRO de Conexão/HTTP: Não foi possível acessar a URL {url}. {e}")
        return None
    except Exception as e:
        print(f"❌ ERRO de Pré-processamento: {e}")
        return None

# --- 2. Loop de Previsão em Tempo Real ---
print("\n--- Iniciando Classificação em Tempo Real (Loop) ---")
print(f"Acessando Câmera em: {IMAGE_URL}")

while True:
    start_time = time.time()
    
    # 2.1. Captura e Pré-processamento
    processed_image = capture_and_preprocess(IMAGE_URL, IMAGE_SIZE)

    if processed_image is not None:
        
        # 2.2. Previsão
        # model.predict(): Roda a inferência. Retorna um array de probabilidades (ex: [[0.98]]).
        prediction = model.predict(processed_image, verbose=0)[0][0]
        
        # Como o modelo de saída usa 'sigmoid', a previsão é uma probabilidade entre 0 e 1.
        # Se prediction for próximo de 1 (p.ex., 0.98), é a classe 1 (uva, assumindo o mapeamento).
        # Se for próximo de 0 (p.ex., 0.02), é a classe 0 (laranja, assumindo o mapeamento).

        # Determina a classe e confiança
        if prediction >= 0.5:
            # Classe 1 (maior probabilidade)
            predicted_index = 1
            confidence = prediction
        else:
            # Classe 0 (menor probabilidade)
            predicted_index = 0
            confidence = 1.0 - prediction
        
        predicted_sabor = class_map.get(predicted_index, 'DESCONHECIDO')
        confidence_percent = confidence * 100

        # Tempo de inferência
        end_time = time.time()
        inference_time = end_time - start_time

        # 2.3. Saída e Controle
        print(f"\n--- Resultado da Classificação ---")
        print(f"Sabor Previsto: **{predicted_sabor.upper()}** (Índice: {predicted_index})")
        print(f"Confiança: **{confidence_percent:.2f}%**")
        print(f"Tempo de Inferência: {inference_time:.3f}s")
        
        # Lógica de Comando de Envase
        if confidence >= CONFIDENCE_THRESHOLD:
            print(f"*** 🟢 COMANDO DE ENVASE: {predicted_sabor.upper()} (Confiança > 95%) ***")
            # Aqui você adicionaria o código para enviar o sinal para o atuador/bomba (via serial, MQTT, etc.)
        else:
            print(f"*** 🟡 ESPERA: Confiança abaixo do limite ({CONFIDENCE_THRESHOLD*100:.0f}%) ***")
        
    # Intervalo para o próximo ciclo de classificação
    time.sleep(1) # Classifica a cada 1 segundo (ajuste conforme necessário)