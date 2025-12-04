import tensorflow as tf
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.layers import Dense, GlobalAveragePooling2D, Dropout
from tensorflow.keras.models import Model
from tensorflow.keras.optimizers import Adam
import os

# Configurações
DATA_DIR = 'machinelearning/data_suco/treino'
IMG_SHAPE = (224, 224, 3)
BATCH_SIZE = 32
EPOCHS = 8 

print("Configurando Data Augmentation...")

train_datagen = ImageDataGenerator(
    preprocessing_function=tf.keras.applications.mobilenet_v2.preprocess_input,
    rotation_range=20,
    width_shift_range=0.1,
    height_shift_range=0.1,
    brightness_range=[0.8, 1.2],
    zoom_range=0.2,
    horizontal_flip=True,
    fill_mode='nearest',
    validation_split=0.2
)

# Geradores
train_generator = train_datagen.flow_from_directory(
    DATA_DIR,
    target_size=(224, 224),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    subset='training',
    shuffle=True
)

validation_generator = train_datagen.flow_from_directory(
    DATA_DIR,
    target_size=(224, 224),
    batch_size=BATCH_SIZE,
    class_mode='categorical',
    subset='validation'
)

# IMPORTANTE: O script vai imprimir a ordem (ex: 0:laranja, 1:uva, 2:vazio)
# Anote essa ordem se for diferente da alfabética!
print("--- ÍNDICES DAS CLASSES ---")
print(train_generator.class_indices)
print("---------------------------")

# Modelo
print("Baixando MobileNetV2...")
base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=IMG_SHAPE)
base_model.trainable = False

x = base_model.output
x = GlobalAveragePooling2D()(x)
x = Dense(128, activation='relu')(x)
x = Dropout(0.2)(x)
# MUDANÇA AQUI: 3 classes agora
predictions = Dense(3, activation='softmax')(x) 

model = Model(inputs=base_model.input, outputs=predictions)

model.compile(optimizer=Adam(learning_rate=0.0001),
              loss='categorical_crossentropy',
              metrics=['accuracy'])

print("Iniciando treinamento...")
history = model.fit(
    train_generator,
    epochs=EPOCHS,
    validation_data=validation_generator
)

model.save('modelo_suco.h5')
print("Modelo salvo como 'modelo_suco.h5'!")