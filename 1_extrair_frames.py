import cv2
import os

# --- CONFIGURAÇÕES ---
# Certifique-se de ter os 3 vídeos na pasta raiz do projeto
VIDEOS = {
    'laranja': 'video_laranja.mp4',
    'uva': 'video_uva.mp4',
    'vazio': 'video_vazio.mp4'  # Nova classe para o fundo
}
OUTPUT_ROOT = 'machinelearning/data_suco/treino'
FRAME_INTERVAL = 5  # Salva 1 frame a cada 5
IMG_SIZE = (224, 224)

def extrair_frames():
    for classe, video_file in VIDEOS.items():
        output_folder = os.path.join(OUTPUT_ROOT, classe)
        os.makedirs(output_folder, exist_ok=True)
        
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
            
            if count % FRAME_INTERVAL == 0:
                frame_resized = cv2.resize(frame, IMG_SIZE)
                filename = os.path.join(output_folder, f"{classe}_frame_{saved_count}.jpg")
                cv2.imwrite(filename, frame_resized)
                saved_count += 1
            
            count += 1
            
        cap.release()
        print(f"Concluído: {saved_count} imagens salvas em {output_folder}")

if __name__ == "__main__":
    extrair_frames()