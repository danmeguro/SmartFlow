import tensorflow as tf
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.layers import Dense, GlobalAveragePooling2D, Dropout
from tensorflow.keras.models import Model
from tensorflow.keras.optimizers import Adam

# Configurações
DATA_DIR = 'machinelearning/data_suco/treino'
IMG_SHAPE = (224, 224, 3)
BATCH_SIZE = 32
EPOCHS = 8  # Poucas épocas pois é transfer learning

print("Configurando Data Augmentation...")

# O preprocess_input da MobileNetV2 converte pixels para o intervalo [-1, 1]
# O validation_split separa 20% das imagens para teste interno
train_datagen = ImageDataGenerator(
    preprocessing_function=tf.keras.applications.mobilenet_v2.preprocess_input,
    rotation_range=20,      # Rotaciona levemente
    width_shift_range=0.1,  # Move horizontalmente
    height_shift_range=0.1, # Move verticalmente
    brightness_range=[0.8, 1.2], # Varia brilho (essencial para esteira)
    zoom_range=0.2,         # Zoom in/out
    horizontal_flip=True,
    fill_mode='nearest',
    validation_split=0.2    # 20% para validação
)

# Gerador de Treino
train_generator = train_datagen.flow_from_directory(
    DATA_DIR,
    target_size=(224, 224),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    subset='training',
    shuffle=True
)

# Gerador de Validação
validation_generator = train_datagen.flow_from_directory(
    DATA_DIR,
    target_size=(224, 224),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    subset='validation'
)

# Mostra quais índices correspondem a quais sucos (ex: 0=laranja, 1=uva)
print("Índices das Classes:", train_generator.class_indices)

# --- Construção do Modelo ---
print("Baixando MobileNetV2 (pesos ImageNet)...")
base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=IMG_SHAPE)

# Congela a base (não treinamos os pesos da MobileNet, só os nossos)
base_model.trainable = False

# Adiciona o cabeçalho personalizado
x = base_model.output
x = GlobalAveragePooling2D()(x)
x = Dense(128, activation='relu')(x)
x = Dropout(0.2)(x) # Evita overfitting
predictions = Dense(2, activation='softmax')(x) # 2 classes: Laranja e Uva

model = Model(inputs=base_model.input, outputs=predictions)

# Compilação
model.compile(optimizer=Adam(learning_rate=0.0001),
              loss='categorical_crossentropy',
              metrics=['accuracy'])

# Treinamento
print("Iniciando treinamento...")
history = model.fit(
    train_generator,
    epochs=EPOCHS,
    validation_data=validation_generator
)

# Salvar
model.save('modelo_suco.h5')
print("Modelo salvo como 'modelo_suco.h5'!")