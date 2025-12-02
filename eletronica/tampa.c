#include <Arduino.h>

// ====== CONFIGURAÇÃO MOTOR PASSO (TB6600) ======
#define PUL_PIN     14    // Pino PUL/STEP do TB6600
#define DIR_PIN     27    // Pino DIR do TB6600
#define ENABLE_PIN  25    // Pino ENABLE do TB6600 (ativo em LOW)

// ====== BOTÃO FIM DE CURSO ======
#define FIM_CURSO   33    // Fim de curso superior (LOW quando pressionado)

// ====== PARÂMETROS DO FUSO ======
// Ajuste estes valores conforme suas especificações
const float PASSO_FUSO = 5.0;           // 5mm por volta (exemplo: fuso T8-5)
const int PASSOS_POR_VOLTA = 200;       // 200 passos/volta do motor (full step)
const float MICROSTEPPING = 1.0;        // 1 = full step, 0.5 = 1/2 step, etc.

// Cálculo de passos por mm
const float PASSOS_POR_MM = (PASSOS_POR_VOLTA * MICROSTEPPING) / PASSO_FUSO;

// ====== VARIÁVEIS DE CONTROLE ======
bool sistemaAtivo = false;
bool emergencia = false;
unsigned long ultimoPulso = 0;
int delayMicros = 1000;                 // Controle de velocidade (1000us = 1ms)
float posicaoAtualMM = 0.0;
float distanciaTotalMM = 50.0;        // ALTERADO: de 100mm para 50mm
unsigned long passosNecessarios = 0;
unsigned long passosExecutados = 0;

// ====== MÁQUINA DE ESTADOS ======
enum EstadoSistema {
  PARADO,
  DESCENDO,
  SUBINDO,
  EM_EMERGENCIA
};
EstadoSistema estadoAtual = PARADO;

void setup() {
  Serial.begin(115200);
  
  // Configuração dos pinos do TB6600
  pinMode(PUL_PIN, OUTPUT);
  pinMode(DIR_PIN, OUTPUT);
  pinMode(ENABLE_PIN, OUTPUT);
  
  // Configuração do fim de curso
  pinMode(FIM_CURSO, INPUT_PULLUP);  // LOW quando pressionado
  
  // Inicialização: desabilitar motor (ENABLE em HIGH)
  digitalWrite(ENABLE_PIN, HIGH);
  digitalWrite(PUL_PIN, LOW);
  digitalWrite(DIR_PIN, LOW);
  
  // Cálculo de passos necessários
  passosNecessarios = (unsigned long)(distanciaTotalMM * PASSOS_POR_MM);
  
  Serial.println("=== SISTEMA DE CONTROLE FUSO NEMA/TB6600 ===");
  Serial.println("Configuração do sistema:");
  Serial.print("  Passo do fuso: "); Serial.print(PASSO_FUSO); Serial.println(" mm/volta");
  Serial.print("  Passos/volta: "); Serial.println(PASSOS_POR_VOLTA);
  Serial.print("  Microstepping: 1/"); Serial.println(1.0/MICROSTEPPING);
  Serial.print("  Passos/mm: "); Serial.println(PASSOS_POR_MM, 2);
  Serial.print("  Distância total: "); Serial.print(distanciaTotalMM); Serial.println(" mm");
  Serial.print("  Passos necessários: "); Serial.println(passosNecessarios);
  Serial.println("\nComandos disponíveis:");
  Serial.println("  1 - Iniciar descida (50mm)");  // ALTERADO: de 100mm para 50mm
  Serial.println("  2 - Subir até fim de curso");
  Serial.println("  3 - Mover para posição específica (em mm)");
  Serial.println("  4 - Configurar velocidade (delay em microssegundos)");
  Serial.println("  5 - Mostrar posição atual");
  Serial.println("  0 - Parada de emergência");
  Serial.println("  r - Resetar sistema após emergência");
  Serial.println("  h - Homing (voltar para fim de curso)");
  
  // Verificar estado inicial do fim de curso
  bool estadoFimCurso = digitalRead(FIM_CURSO);
  if (estadoFimCurso == LOW) {
    Serial.println("Sistema na posição inicial (fim de curso pressionado)");
    posicaoAtualMM = 0.0;
  } else {
    Serial.println("ATENÇÃO: Sistema não está no fim de curso!");
    Serial.println("Use 'h' para fazer homing.");
  }
}

// ====== FUNÇÃO PARA GERAR UM PASSO ======
void gerarPasso() {
  digitalWrite(PUL_PIN, HIGH);
  delayMicroseconds(10);  // Pulse width mínimo (ajuste conforme necessário)
  digitalWrite(PUL_PIN, LOW);
}

// ====== FUNÇÃO PARA MOVER MOTOR ======
void moverMotor(bool direcao, unsigned long passos) {
  // direcao: true = subir (retrai fuso), false = descer (avança fuso)
  digitalWrite(DIR_PIN, direcao ? HIGH : LOW);
  delayMicroseconds(100);  // Tempo de setup para direção
  
  // Habilitar motor
  digitalWrite(ENABLE_PIN, LOW);
  delayMicroseconds(100);
  
  unsigned long tempoAtual;
  for (unsigned long i = 0; i < passos && !emergencia; i++) {
    tempoAtual = micros();
    
    // Verificar fim de curso durante subida
    if (direcao && digitalRead(FIM_CURSO) == LOW) {
      Serial.println("Fim de curso atingido durante subida!");
      break;
    }
    
    // Gerar pulso com timing controlado
    gerarPasso();
    
    // Atualizar posição
    if (direcao) {
      posicaoAtualMM -= (1.0 / PASSOS_POR_MM);
      if (posicaoAtualMM < 0) posicaoAtualMM = 0;
    } else {
      posicaoAtualMM += (1.0 / PASSOS_POR_MM);
    }
    
    // Delay para controle de velocidade
    while (micros() - tempoAtual < delayMicros) {
      // Verificar comandos serial durante o delay
      if (Serial.available()) {
        char comando = Serial.read();
        if (comando == '0') {
          emergencia = true;
          estadoAtual = EM_EMERGENCIA;
          break;
        }
      }
    }
    
    passosExecutados++;
  }
  
  // Desabilitar motor após movimento (opcional, reduz consumo)
  digitalWrite(ENABLE_PIN, HIGH);
}

// ====== FUNÇÃO PARA DESCER 50mm ======
void descer50mm() {  // ALTERADO: de descer100mm para descer50mm
  if (emergencia) {
    Serial.println("Sistema em emergência! Use 'r' para resetar.");
    return;
  }
  
  // Verificar se está no fim de curso
  if (digitalRead(FIM_CURSO) == HIGH) {
    Serial.println("ERRO: Sistema não está no fim de curso!");
    Serial.println("Execute homing ('h') primeiro.");
    return;
  }
  
  Serial.println("Iniciando descida de 50mm...");  // ALTERADO: de 100mm para 50mm
  Serial.print("Passos necessários: "); Serial.println(passosNecessarios);
  Serial.print("Velocidade: delay="); Serial.print(delayMicros); Serial.println("us");
  
  estadoAtual = DESCENDO;
  passosExecutados = 0;
  posicaoAtualMM = 0.0;
  
  // Executar movimento
  moverMotor(false, passosNecessarios);
  
  if (emergencia) {
    Serial.println("Movimento interrompido por emergência!");
    Serial.print("Posição alcançada: "); 
    Serial.print(posicaoAtualMM); 
    Serial.println(" mm");
  } else {
    Serial.println("Descida concluída com sucesso!");
    Serial.print("Posição final: "); 
    Serial.print(posicaoAtualMM); 
    Serial.println(" mm");
    estadoAtual = PARADO;
  }
}

// ====== FUNÇÃO PARA SUBIR ATÉ FIM DE CURSO ======
void subirAteFimCurso() {
  if (emergencia) {
    Serial.println("Sistema em emergência! Use 'r' para resetar.");
    return;
  }
  
  Serial.println("Subindo até fim de curso...");
  Serial.println("Pressione 0 para parada de emergência.");
  
  estadoAtual = SUBINDO;
  
  // Habilitar motor
  digitalWrite(ENABLE_PIN, LOW);
  digitalWrite(DIR_PIN, HIGH);  // Direção para subir
  delayMicroseconds(100);
  
  // Subir até pressionar fim de curso
  while (digitalRead(FIM_CURSO) == HIGH && !emergencia) {
    unsigned long tempoAtual = micros();
    
    // Gerar pulso
    gerarPasso();
    
    // Atualizar posição (aproximada)
    posicaoAtualMM -= (1.0 / PASSOS_POR_MM);
    if (posicaoAtualMM < 0) posicaoAtualMM = 0;
    
    // Delay para controle de velocidade
    while (micros() - tempoAtual < delayMicros) {
      // Verificar comandos serial durante o delay
      if (Serial.available()) {
        char comando = Serial.read();
        if (comando == '0') {
          emergencia = true;
          estadoAtual = EM_EMERGENCIA;
          break;
        }
      }
    }
  }
  
  // Desabilitar motor
  digitalWrite(ENABLE_PIN, HIGH);
  
  if (emergencia) {
    Serial.println("Subida interrompida por emergência!");
  } else {
    Serial.println("Fim de curso atingido!");
    posicaoAtualMM = 0.0;  // Resetar posição
    estadoAtual = PARADO;
  }
}

// ====== FUNÇÃO HOMING ======
void fazerHoming() {
  if (emergencia) {
    Serial.println("Sistema em emergência! Use 'r' para resetar.");
    return;
  }
  
  Serial.println("Iniciando homing...");
  
  // Primeiro, subir um pouco para liberar o fim de curso (se estiver pressionado)
  if (digitalRead(FIM_CURSO) == LOW) {
    Serial.println("Liberando fim de curso...");
    digitalWrite(ENABLE_PIN, LOW);
    digitalWrite(DIR_PIN, false);  // Descer um pouco
    delayMicroseconds(100);
    
    for (int i = 0; i < 500; i++) {  // Descer 500 passos
      gerarPasso();
      delayMicroseconds(delayMicros);
    }
    
    digitalWrite(ENABLE_PIN, HIGH);
    delay(500);
  }
  
  // Agora subir até pressionar fim de curso
  subirAteFimCurso();
}

// ====== FUNÇÃO PARA MOVER PARA POSIÇÃO ESPECÍFICA ======
void moverParaPosicao(float destinoMM) {
  if (emergencia) {
    Serial.println("Sistema em emergência! Use 'r' para resetar.");
    return;
  }
  
  if (destinoMM < 0 || destinoMM > distanciaTotalMM) {
    Serial.print("Posição inválida! Deve estar entre 0 e ");
    Serial.print(distanciaTotalMM);
    Serial.println(" mm");
    return;
  }
  
  float diferenca = destinoMM - posicaoAtualMM;
  unsigned long passos = (unsigned long)(abs(diferenca) * PASSOS_POR_MM);
  bool direcao = (diferenca > 0) ? false : true;  // false=descer, true=subir
  
  Serial.print("Movendo para: "); Serial.print(destinoMM); Serial.println(" mm");
  Serial.print("Passos necessários: "); Serial.println(passos);
  
  estadoAtual = (direcao) ? SUBINDO : DESCENDO;
  moverMotor(direcao, passos);
  
  if (!emergencia) {
    estadoAtual = PARADO;
    Serial.print("Posição alcançada: "); Serial.print(posicaoAtualMM); Serial.println(" mm");
  }
}

void loop() {
  // Verificar entrada serial
  if (Serial.available()) {
    char comando = Serial.read();
    
    switch(comando) {
      case '1':  // Iniciar descida de 50mm
        if (estadoAtual == PARADO) {
          descer50mm();  // ALTERADO: de descer100mm para descer50mm
        } else {
          Serial.println("Sistema já em movimento!");
        }
        break;
        
      case '2':  // Subir até fim de curso
        if (estadoAtual == PARADO) {
          subirAteFimCurso();
        } else {
          Serial.println("Sistema já em movimento!");
        }
        break;
        
      case '3':  // Mover para posição específica
        {
          if (estadoAtual == PARADO) {
            Serial.println("Digite a posição em mm:");
            while (!Serial.available()) delay(10);
            String input = Serial.readStringUntil('\n');
            float posicao = input.toFloat();
            moverParaPosicao(posicao);
          } else {
            Serial.println("Sistema já em movimento!");
          }
        }
        break;
        
      case '4':  // Configurar velocidade
        {
          Serial.println("Digite o delay em microssegundos (ex: 1000 = 1ms):");
          while (!Serial.available()) delay(10);
          String input = Serial.readStringUntil('\n');
          delayMicros = input.toInt();
          Serial.print("Novo delay: "); Serial.print(delayMicros); Serial.println(" us");
        }
        break;
        
      case '5':  // Mostrar posição atual
        Serial.print("Posição atual: "); 
        Serial.print(posicaoAtualMM); 
        Serial.println(" mm");
        Serial.print("Estado: ");
        switch(estadoAtual) {
          case PARADO: Serial.println("PARADO"); break;
          case DESCENDO: Serial.println("DESCENDO"); break;
          case SUBINDO: Serial.println("SUBINDO"); break;
          case EM_EMERGENCIA: Serial.println("EMERGÊNCIA"); break;
        }
        break;
        
      case '0':  // Parada de emergência
        emergencia = true;
        estadoAtual = EM_EMERGENCIA;
        digitalWrite(ENABLE_PIN, HIGH);  // Desabilitar motor imediatamente
        Serial.println("!!! PARADA DE EMERGÊNCIA ATIVADA !!!");
        break;
        
      case 'r':  // Resetar após emergência
        emergencia = false;
        estadoAtual = PARADO;
        Serial.println("Sistema resetado. Pronto para operar.");
        break;
        
      case 'h':  // Homing
        if (estadoAtual == PARADO) {
          fazerHoming();
        } else {
          Serial.println("Sistema já em movimento!");
        }
        break;
        
      default:
        Serial.println("Comando não reconhecido");
        break;
    }
  }
  
  delay(10);
}