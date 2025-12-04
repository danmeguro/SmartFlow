import tensorflow as tf
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv2D, MaxPooling2D, Flatten, Dense, Dropout
import os

# --- Configurações ---
IMAGE_SIZE = (150, 150) # O tamanho das imagens de entrada para o modelo
BATCH_SIZE = 32         # Número de amostras por lote de gradiente
EPOCHS = 10             # Número de vezes que o algoritmo de treinamento trabalhará em todo o dataset
DATA_DIR = 'data_suco'  # Diretório raiz dos dados

# Cria o diretório de dados se não existir (apenas para simulação de estrutura)
if not os.path.exists(DATA_DIR):
    print(f"ATENÇÃO: Diretório '{DATA_DIR}' não encontrado. Certifique-se de criar a estrutura de pastas.")
    # Exemplo: data_suco/treino/laranja, data_suco/treino/uva
    # Se você está pronto para treinar, comente esta parte e crie as pastas.

# --- 1. Entrada de Dados: ImageDataGenerator ---

# Gerador para dados de TREINAMENTO
# A única transformação é a NORMALIZAÇÃO: divide os valores dos pixels por 255
# A normalização garante que os valores de entrada fiquem entre 0 e 1, o que é crucial
# para a estabilidade e velocidade do treinamento da rede neural.
train_datagen = ImageDataGenerator(rescale=1./255)

# O fluxo de dados do diretório
# Ele automaticamente infere as classes dos nomes das subpastas (laranja, uva).
train_generator = train_datagen.flow_from_directory(
    os.path.join(DATA_DIR, 'treino'), # Subdiretório de treino
    target_size=IMAGE_SIZE,           # Redimensiona todas as imagens para IMAGE_SIZE
    batch_size=BATCH_SIZE,
    class_mode='binary'               # 'binary' para classificação de 2 classes
)

# --- 2. Modelo: Arquitetura CNN Simples ---

# Sequential: A maneira mais fácil de construir um modelo Keras, empilhando camadas.
model = Sequential([
    # Camada 1: CONV2D e MaxPooling2D
    # Conv2D: Aplica 32 filtros (kernels 3x3) para extrair características (bordas, texturas).
    # MaxPooling2D: Reduz a dimensionalidade (tamanho 2x2), mantendo as características mais importantes e reduzindo o risco de overfitting.
    Conv2D(32, (3, 3), activation='relu', input_shape=(IMAGE_SIZE[0], IMAGE_SIZE[1], 3)),
    MaxPooling2D((2, 2)),

    # Camada 2: CONV2D e MaxPooling2D
    Conv2D(64, (3, 3), activation='relu'),
    MaxPooling2D((2, 2)),
    
    # Camada 3: CONV2D e MaxPooling2D (Opcional, para aumentar a profundidade)
    Conv2D(128, (3, 3), activation='relu'),
    MaxPooling2D((2, 2)),
    
    # Camada 4: Flatten e Dense (Classificador)
    # Flatten: Transforma o tensor 3D de saída (feature maps) em um vetor 1D para ser alimentado nas camadas densas.
    Flatten(),
    
    # Dense (Oculta): Uma camada totalmente conectada com 512 neurônios para processamento de alto nível.
    Dense(512, activation='relu'),
    
    # Dropout: Desliga aleatoriamente 50% dos neurônios durante o treinamento para evitar overfitting.
    Dropout(0.5),

    # Dense (Saída): A camada final de saída.
    # Sigmoid: Produz uma probabilidade entre 0 e 1, ideal para classificação binária.
    Dense(1, activation='sigmoid')
])

# Compila o modelo
# Optimizer: 'adam' é uma escolha robusta para a maioria dos problemas.
# Loss: 'binary_crossentropy' é a função de perda padrão para classificação binária.
# Metrics: 'accuracy' (acurácia) para monitorar o desempenho.
model.compile(optimizer='adam',
              loss='binary_crossentropy',
              metrics=['accuracy'])

# Imprime o resumo da arquitetura do modelo
model.summary()

# --- Treinamento ---
print("\n--- Iniciando Treinamento ---")
history = model.fit(
    train_generator,
    steps_per_epoch=train_generator.samples // BATCH_SIZE,
    epochs=EPOCHS
)

# --- 3. Saída: Salvamento e Mapeamento de Classes ---

MODEL_FILENAME = 'suco_classifier.keras'
model.save(MODEL_FILENAME)
print(f"\n✅ Modelo treinado salvo como: **{MODEL_FILENAME}**")

# Mapeamento de Classes (crucial para o cliente saber o que 0 e 1 significam)
class_indices = train_generator.class_indices
# Inverte para o formato: Índice: Nome da Classe
class_map = {v: k for k, v in class_indices.items()}

print("\n--- Mapeamento de Classes ---")
print(class_map)

# Salva o mapeamento para uso no script de classificação
import json
with open('class_map.json', 'w') as f:
    json.dump(class_map, f)
print("Mapeamento de classes salvo em 'class_map.json'.")