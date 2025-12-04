import cv2
import numpy as np
import tensorflow as tf

# Configurações
MODEL_PATH = 'modelo_suco.h5'
CONFIDENCE_THRESHOLD = 0.70 

# Mapeamento (Deve seguir a ordem alfabética das pastas: L, U, V)
# Se o seu treino mostrou ordem diferente, ajuste aqui!
CLASSES = ['laranja', 'uva', 'vazio']
COLORS = [(0, 165, 255), (128, 0, 128), (200, 200, 200)] # Laranja, Roxo, Cinza

print("Carregando modelo...")
model = tf.keras.models.load_model(MODEL_PATH)
print("Modelo carregado. Iniciando Webcam...")

# Tenta abrir com DirectShow (melhor para Windows)
cap = cv2.VideoCapture(0, cv2.CAP_DSHOW)

# Se der erro ou não abrir, tenta o padrão
if not cap.isOpened():
    cap = cv2.VideoCapture(0)

while True:
    ret, frame = cap.read()
    if not ret:
        print("Falha ao capturar imagem da webcam.")
        break

    # Pré-processamento
    img_small = cv2.resize(frame, (224, 224))
    img_array = np.array(img_small, dtype=np.float32)
    img_array = np.expand_dims(img_array, axis=0)
    img_input = tf.keras.applications.mobilenet_v2.preprocess_input(img_array)

    # Predição
    preds = model.predict(img_input, verbose=0)
    idx = np.argmax(preds)      
    confidence = preds[0][idx]  

    # Lógica de Exibição
    label = CLASSES[idx]
    
    if confidence >= CONFIDENCE_THRESHOLD:
        if label == 'vazio':
            # Se detectou VAZIO com certeza
            text_display = "Aguardando garrafa..."
            color = (200, 200, 200) # Cinza
            
            # (Opcional) Desenha retângulo cinza discreto
            cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), (50, 50, 50), -1)
        else:
            # Se detectou SUCO (Laranja ou Uva)
            text_display = f"{label.upper()}: {confidence*100:.1f}%"
            color = COLORS[idx]
            
            # Desenha barra colorida no topo
            cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), color, -1)
            # Texto branco para contraste
            cv2.putText(frame, text_display, (20, 40), 
                        cv2.FONT_HERSHEY_SIMPLEX, 1, (255, 255, 255), 2)
    else:
        # Se não tem certeza de nada
        text_display = f"Analisando... ({confidence*100:.0f}%)"
        cv2.rectangle(frame, (0, 0), (frame.shape[1], 60), (0, 0, 0), -1)
        cv2.putText(frame, text_display, (20, 40), 
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

    # Se for "vazio", desenha o texto fora do if/else de suco para manter padrão
    if label == 'vazio' and confidence >= CONFIDENCE_THRESHOLD:
         cv2.putText(frame, text_display, (20, 40), 
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (255, 255, 255), 2)

    cv2.imshow('Detector de Sucos - TCC', frame)

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()