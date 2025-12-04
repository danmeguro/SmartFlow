import cv2
import numpy as np
import tensorflow as tf

# Configurações
MODEL_PATH = 'modelo_suco.h5'
CONFIDENCE_THRESHOLD = 0.70 # 70% de certeza mínima

# Definir as classes manualmente (deve bater com o print do script de treino)
# Geralmente é ordem alfabética: 0: laranja, 1: uva
CLASSES = ['Laranja', 'Uva'] 
COLORS = [(0, 165, 255), (128, 0, 128)] # Laranja (BGR) e Roxo (BGR)

print("Carregando modelo...")
model = tf.keras.models.load_model(MODEL_PATH)
print("Modelo carregado. Iniciando Webcam...")

cap = cv2.VideoCapture(0) # 0 geralmente é a webcam padrão

while True:
    ret, frame = cap.read()
    if not ret:
        break

    # --- Pré-processamento para a IA ---
    # 1. Redimensionar para 224x224
    img_small = cv2.resize(frame, (224, 224))
    
    # 2. Converter para array e expandir dimensões (batch de 1)
    img_array =  np.array(img_small, dtype=np.float32)
    img_array = np.expand_dims(img_array, axis=0)
    
    # 3. Pré-processamento específico da MobileNetV2 (mesmo do treino)
    # Isso coloca os pixels entre -1 e 1
    img_input = tf.keras.applications.mobilenet_v2.preprocess_input(img_array)

    # --- Predição ---
    preds = model.predict(img_input, verbose=0)
    idx = np.argmax(preds)      # Qual índice ganhou?
    confidence = preds[0][idx]  # Qual a certeza?

    # --- Lógica de Exibição ---
    if confidence >= CONFIDENCE_THRESHOLD:
        label = CLASSES[idx]
        color = COLORS[idx]
        text_display = f"{label}: {confidence*100:.1f}%"
    else:
        label = "Aguardando..."
        color = (0, 0, 255) # Vermelho
        text_display = f"Aguardando... ({confidence*100:.1f}%)"

    # Desenhar na tela
    # Retângulo no topo
    cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), (0, 0, 0), -1)
    cv2.putText(frame, text_display, (20, 40), 
                cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2)

    cv2.imshow('Detector de Sucos - TCC', frame)

    # Pressione 'q' para sair
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()