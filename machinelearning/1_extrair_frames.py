import cv2
import os

# Configurações
VIDEOS = {
    'laranja': 'video_laranja.mp4',
    'uva': 'video_uva.mp4'
}
OUTPUT_ROOT = 'machinelearning/data_suco/treino'
FRAME_INTERVAL = 5  # Salva 1 frame a cada 5 (ajuste se o vídeo for muito longo)
IMG_SIZE = (224, 224)

def extrair_frames():
    for classe, video_file in VIDEOS.items():
        # Caminho completo da pasta de saída
        output_folder = os.path.join(OUTPUT_ROOT, classe)
        
        # Garante que a pasta existe
        os.makedirs(output_folder, exist_ok=True)
        
        # Abre o vídeo
        if not os.path.exists(video_file):
            print(f"[ERRO] Vídeo não encontrado: {video_file}")
            continue
            
        cap = cv2.VideoCapture(video_file)
        count = 0
        saved_count = 0
        
        print(f"--- Processando: {classe} ---")
        
        while True:
            success, frame = cap.read()
            if not success:
                break
            
            # Pega apenas frames no intervalo definido
            if count % FRAME_INTERVAL == 0:
                # Redimensiona para o input da MobileNetV2
                frame_resized = cv2.resize(frame, IMG_SIZE)
                
                # Nome do arquivo único
                filename = os.path.join(output_folder, f"{classe}_frame_{saved_count}.jpg")
                cv2.imwrite(filename, frame_resized)
                saved_count += 1
            
            count += 1
            
        cap.release()
        print(f"Concluído: {saved_count} imagens salvas em {output_folder}")

if __name__ == "__main__":
    extrair_frames()