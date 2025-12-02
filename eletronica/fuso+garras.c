#include <Arduino.h>
#include <ESP32Servo.h>

// ====== MOTOR FUSO GARRA ======
#define FUSO_GARRA_PUL   26
#define FUSO_GARRA_DIR   23
#define FUSO_GARRA_ENBL  32

// ====== SERVO MOTOR MG996R ======
#define SERVO_PIN        13  // Altere para o pino correto
Servo meuServo;

// ====== BOTÃO FIM DE CURSO GARRA ======
#define FIM_CURSO_GARRA  33  // Sistema fechado (LOW quando pressionado)

// ====== VARIÁVEIS ======
bool testeAtivo = false;
unsigned long tempoInicio = 0;
int estadoTeste = 0;  // 0=parado, 1=descendo, 2=pausa, 3=servo abre, 4=servo fecha, 5=sobe

void setup() {
  Serial.begin(115200);
  
  // Configuração motor passo (garra)
  pinMode(FUSO_GARRA_PUL, OUTPUT);
  pinMode(FUSO_GARRA_DIR, OUTPUT);
  pinMode(FUSO_GARRA_ENBL, OUTPUT);
  
  // Configuração servo motor com ESP32Servo
  meuServo.attach(SERVO_PIN, 1000, 2000);  // Min 1000µs, Max 2000µs
  
  // Posição inicial do servo (0 graus)
  meuServo.write(0);
  delay(1000);
  
  // Configuração fim de curso
  pinMode(FIM_CURSO_GARRA, INPUT_PULLUP);  // Sistema fechado (LOW quando pressionado)
  
  // Inicialização: desabilitar motor
  digitalWrite(FUSO_GARRA_ENBL, HIGH);
  
  Serial.println("=== SISTEMA DE TESTE DA GARRA ===");
  Serial.println("1. Digite '1' para iniciar teste");
  Serial.println("2. Digite '2' para testar servo (abrir/fechar)");
  Serial.println("3. Digite '3' para testar motor (descer/subir)");
  Serial.println("4. Digite '4' para testar fim de curso");
  Serial.println("5. Digite '0' para parar tudo");
  Serial.println("6. Digite 'a' para abrir servo manual");
  Serial.println("7. Digite 'f' para fechar servo manual");
}

void testarFimDeCurso() {
  Serial.println("Testando fim de curso...");
  Serial.println("Pressione o botão fim de curso");
  
  for(int i = 0; i < 20; i++) {
    bool estado = digitalRead(FIM_CURSO_GARRA);
    Serial.print("Estado fim de curso: ");
    Serial.println(estado ? "LIVRE (HIGH)" : "PRESSIONADO (LOW)");
    delay(500);
  }
}

void testarServo() {
  Serial.println("Testando servo...");
  Serial.println("Abrindo para 180°");
  meuServo.write(180);
  delay(3000);
  Serial.println("Fechando para 0°");
  meuServo.write(0);
  delay(3000);
  Serial.println("Teste servo completo");
}

void controlarMotorPasso(bool direcao, int passos, int velocidade) {
  // direcao: true = subir, false = descer
  digitalWrite(FUSO_GARRA_ENBL, LOW);
  digitalWrite(FUSO_GARRA_DIR, direcao ? HIGH : LOW);
  
  for(int i = 0; i < passos; i++) {
    digitalWrite(FUSO_GARRA_PUL, HIGH);
    delayMicroseconds(velocidade);
    digitalWrite(FUSO_GARRA_PUL, LOW);
    delayMicroseconds(velocidade);
  }
  
  digitalWrite(FUSO_GARRA_ENBL, HIGH);
}

void testarMotor() {
  Serial.println("Testando motor...");
  Serial.println("Descendo por 2 segundos");
  
  unsigned long inicio = millis();
  while(millis() - inicio < 2000) {
    controlarMotorPasso(false, 10, 1000);  // Descer
    delay(1);
  }
  
  Serial.println("Parado por 1 segundo");
  delay(1000);
  
  Serial.println("Subindo por 2 segundos");
  inicio = millis();
  while(millis() - inicio < 2000) {
    controlarMotorPasso(true, 10, 1000);  // Subir
    delay(1);
  }
  
  Serial.println("Teste motor completo");
}

void executarSequenciaCompleta() {
  Serial.println("=== INICIANDO SEQUÊNCIA COMPLETA ===");
  
  // 1. Descida por 5 segundos
  Serial.println("1. Descendo por 5 segundos...");
  unsigned long inicio = millis();
  while(millis() - inicio < 5000) {
    controlarMotorPasso(false, 10, 1000);  // Descer
    delay(1);
  }
  
  // 2. Pausa de 5 segundos
  Serial.println("2. Pausa de 5 segundos...");
  delay(5000);
  
  // 3. Servo abre 180° por 5 segundos
  Serial.println("3. Servo abre 180° por 5 segundos...");
  meuServo.write(180);
  delay(3000);
  
  // 4. Servo fecha 0°
  Serial.println("4. Servo fecha para 0°...");
  meuServo.write(0);
  delay(3000);
  
  // 5. Subida até fim de curso
  Serial.println("5. Subindo até fim de curso...");
  Serial.println("   (Pressione o botão fim de curso para parar)");
  
  bool fimDeCursoPressionado = false;
  while(!fimDeCursoPressionado) {
    // Verificar fim de curso
    if(digitalRead(FIM_CURSO_GARRA) == LOW) {
      fimDeCursoPressionado = true;
      Serial.println("   Fim de curso pressionado!");
    } else {
      controlarMotorPasso(true, 10, 1000);  // Subir
      delay(1);
    }
  }
  
  Serial.println("=== SEQUÊNCIA COMPLETA CONCLUÍDA ===");
}

void loop() {
  // Verificar entrada serial
  if (Serial.available()) {
    char comando = Serial.read();
    
    switch(comando) {
      case '1':
        executarSequenciaCompleta();
        break;
        
      case '2':
        testarServo();
        break;
        
      case '3':
        testarMotor();
        break;
        
      case '4':
        testarFimDeCurso();
        break;
        
      case '0':
        Serial.println("PARANDO TUDO");
        digitalWrite(FUSO_GARRA_ENBL, HIGH);  // Desabilita motor
        break;
        
      case 'a':
        Serial.println("Abrindo servo manual (180°)");
        meuServo.write(180);
        delay(3000);
        break;
        
      case 'f':
        Serial.println("Fechando servo manual (0°)");
        meuServo.write(0);
        break;
        
      default:
        Serial.println("Comando não reconhecido");
        break;
    }
  }
  
  delay(10);
}