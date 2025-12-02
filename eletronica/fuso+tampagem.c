#include <Arduino.h>

// ====== CONFIGURAÇÃO MOTOR PASSO FUSO (DESCIDA/SUBIDA) ======
#define PUL_FUSO     14    // Pino PUL/STEP do TB6600 do fuso
#define DIR_FUSO     27    // Pino DIR do TB6600 do fuso
#define ENABLE_FUSO  25    // Pino ENABLE do TB6600 do fuso (ativo em LOW)

// ====== CONFIGURAÇÃO MOTOR PASSO BOCAL (ROSCA) ======
#define PUL_BOCAL     26    // Pino PUL/STEP do TB6600 do bocal
#define DIR_BOCAL     23    // Pino DIR do TB6600 do bocal
#define ENABLE_BOCAL  32    // Pino ENABLE do TB6600 do bocal (ativo em LOW)

// ====== BOTÃO FIM DE CURSO SUPERIOR ======
#define FIM_CURSO   33    // Fim de curso superior (LOW quando pressionado)

// ====== PARÂMETROS DO FUSO ======
const float PASSO_FUSO = 5.0;           // 5mm por volta
const int PASSOS_POR_VOLTA_FUSO = 200;  // 200 passos/volta do motor do fuso
const float MICROSTEPPING_FUSO = 1.0;   // 1 = full step

// Cálculo de passos por mm do fuso
const float PASSOS_POR_MM = (PASSOS_POR_VOLTA_FUSO * MICROSTEPPING_FUSO) / PASSO_FUSO;

// ====== PARÂMETROS DO BOCAL ======
const int PASSOS_POR_VOLTA_BOCAL = 200; // 200 passos para 360 graus
const float MICROSTEPPING_BOCAL = 1.0;  // 1 = full step (mais torque)
const int PASSOS_POR_VOLTA_COMPLETA = PASSOS_POR_VOLTA_BOCAL * MICROSTEPPING_BOCAL;

// ====== AJUSTES DE TORQUE ======
const int VOLTAS_BOCAL = 1;             // ALTERADO: Número de voltas completas para rosquear (de 2 para 1)
const float TEMPO_POR_VOLTA = 3.0;      // Tempo em segundos para cada volta (3s para mais torque)

// ====== DISTÂNCIAS DE DESCIDA ======
const float distanciaInicioRosqueamento = 40.0;  // Inicia rosqueamento aos 40mm
const float distanciaTotalDescida = 43.0;        // Distância total de descida
const float distanciaAposInicio = 3.0;           // Distância restante após início do rosqueamento (43-40)

// ====== VARIÁVEIS DE CONTROLE ======
bool emergencia = false;
int delayMicrosFuso = 1500;             // Controle de velocidade fuso (1500us = 1.5ms)
int delayMicrosBocal = 18000;           // Controle de velocidade bocal (18000us = 18ms) - MAIS LENTO PARA MAIS TORQUE
float posicaoAtualMM = 0.0;
unsigned long passosParaInicioRosqueamento = 0;
unsigned long passosAposInicioRosqueamento = 0;
unsigned long passosTotaisDescida = 0;

// ====== MÁQUINA DE ESTADOS ======
enum EstadoSistema {
  PARADO,
  DESCENDO_FUSO_ATE_40MM,
  INICIANDO_ROSCAMENTO,
  DESCENDO_E_ROSCANDO,
  TERMINANDO_ROSCAMENTO,
  SUBINDO_FUSO,
  RETORNANDO_BOCAL,
  EM_EMERGENCIA,
  FINALIZADO
};
EstadoSistema estadoAtual = PARADO;
unsigned long tempoEspera = 0;
int voltasExecutadas = 0;
bool roscaIniciada = false;

void setup() {
  Serial.begin(115200);
  
  // Configuração dos pinos do TB6600 do FUSO
  pinMode(PUL_FUSO, OUTPUT);
  pinMode(DIR_FUSO, OUTPUT);
  pinMode(ENABLE_FUSO, OUTPUT);
  
  // Configuração dos pinos do TB6600 do BOCAL
  pinMode(PUL_BOCAL, OUTPUT);
  pinMode(DIR_BOCAL, OUTPUT);
  pinMode(ENABLE_BOCAL, OUTPUT);
  
  // Configuração do fim de curso
  pinMode(FIM_CURSO, INPUT_PULLUP);  // LOW quando pressionado
  
  // Inicialização: desabilitar motores
  digitalWrite(ENABLE_FUSO, HIGH);
  digitalWrite(ENABLE_BOCAL, HIGH);
  digitalWrite(PUL_FUSO, LOW);
  digitalWrite(DIR_FUSO, LOW);
  digitalWrite(PUL_BOCAL, LOW);
  digitalWrite(DIR_BOCAL, LOW);
  
  // Cálculo de passos necessários
  passosParaInicioRosqueamento = (unsigned long)(distanciaInicioRosqueamento * PASSOS_POR_MM);
  passosAposInicioRosqueamento = (unsigned long)(distanciaAposInicio * PASSOS_POR_MM);
  passosTotaisDescida = (unsigned long)(distanciaTotalDescida * PASSOS_POR_MM);
  
  Serial.println("=== SISTEMA DE ROSQUEAMENTO AUTOMÁTICO ===");
  Serial.println("CONFIGURAÇÃO OTIMIZADA PARA MAIOR EFICIÊNCIA:");
  Serial.println("  1. Rosqueamento inicia aos 40mm (antes dos 43mm finais)");
  Serial.println("  2. 1 volta para tampagem eficiente");
  Serial.println("  3. Maior torque com fita isolante no bocal");
  Serial.println("\nConfiguração para ALTO TORQUE no bocal:");
  Serial.print("  Passo do fuso: "); Serial.print(PASSO_FUSO); Serial.println(" mm/volta");
  Serial.print("  Passos/mm fuso: "); Serial.println(PASSOS_POR_MM, 2);
  Serial.print("  Distância total de descida: "); Serial.print(distanciaTotalDescida); Serial.println(" mm");
  Serial.print("  Início do rosqueamento: "); Serial.print(distanciaInicioRosqueamento); Serial.println(" mm");
  Serial.print("  Distância com rosqueamento ativo: "); Serial.print(distanciaAposInicio); Serial.println(" mm");
  Serial.print("  Passos totais descida: "); Serial.println(passosTotaisDescida);
  Serial.println("\nConfiguração do BOCAL (rosqueamento):");
  Serial.print("  Passos/volta bocal: "); Serial.println(PASSOS_POR_VOLTA_BOCAL);
  Serial.print("  Microstepping: 1/"); Serial.println(1.0/MICROSTEPPING_BOCAL);
  Serial.print("  Voltas para rosquear: "); Serial.println(VOLTAS_BOCAL);
  Serial.print("  Velocidade bocal: delay="); Serial.print(delayMicrosBocal); Serial.println(" us (ALTO TORQUE)");
  Serial.print("  Tempo estimado por volta: "); Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA) / 1000000.0); Serial.println(" segundos");
  Serial.print("  Tempo total estimado de rosqueamento: "); Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA * VOLTAS_BOCAL) / 1000000.0); Serial.println(" segundos");
  Serial.println("\nComandos disponíveis:");
  Serial.println("  1 - Iniciar sequência completa de rosqueamento");
  Serial.println("  2 - Testar descida do fuso (40mm sem rosqueamento)");
  Serial.println("  3 - Testar rotação do bocal (1 volta LENTA com ALTO TORQUE)");
  Serial.println("  4 - Subir até fim de curso");
  Serial.println("  5 - Retornar bocal para posição inicial");
  Serial.println("  6 - Configurar velocidade fuso");
  Serial.println("  7 - Configurar velocidade bocal (torque)");
  Serial.println("  8 - Configurar número de voltas do bocal");
  Serial.println("  9 - Mostrar status");
  Serial.println("  0 - Parada de emergência");
  Serial.println("  r - Resetar sistema");
  Serial.println("  h - Homing (voltar para fim de curso)");
  
  // Verificar estado inicial do fim de curso
  bool estadoFimCurso = digitalRead(FIM_CURSO);
  if (estadoFimCurso == LOW) {
    Serial.println("Sistema na posição inicial (fim de curso pressionado)");
    posicaoAtualMM = 0.0;
  } else {
    Serial.println("ATENÇÃO: Sistema não está no fim de curso!");
    Serial.println("Execute homing ('h') antes de iniciar a sequência.");
  }
}

// ====== FUNÇÕES PARA GERAR PASSOS ======
void gerarPassoFuso() {
  digitalWrite(PUL_FUSO, HIGH);
  delayMicroseconds(10);
  digitalWrite(PUL_FUSO, LOW);
}

void gerarPassoBocal() {
  digitalWrite(PUL_BOCAL, HIGH);
  delayMicroseconds(35);  // AUMENTADO: Pulse width maior para mais torque com fita isolante
  digitalWrite(PUL_BOCAL, LOW);
}

// ====== FUNÇÕES PARA MOVER MOTORES ======
void moverFuso(bool direcao, unsigned long passos, int velocidade) {
  // direcao: true = subir, false = descer
  digitalWrite(DIR_FUSO, direcao ? HIGH : LOW);
  delayMicroseconds(100);
  
  digitalWrite(ENABLE_FUSO, LOW);
  delayMicroseconds(100);
  
  unsigned long tempoAtual;
  for (unsigned long i = 0; i < passos && !emergencia && estadoAtual != EM_EMERGENCIA; i++) {
    tempoAtual = micros();
    
    // Verificar fim de curso durante subida
    if (direcao && digitalRead(FIM_CURSO) == LOW) {
      Serial.println("Fim de curso atingido!");
      break;
    }
    
    gerarPassoFuso();
    
    // Atualizar posição
    if (direcao) {
      posicaoAtualMM -= (1.0 / PASSOS_POR_MM);
      if (posicaoAtualMM < 0) posicaoAtualMM = 0;
    } else {
      posicaoAtualMM += (1.0 / PASSOS_POR_MM);
    }
    
    // Delay para controle de velocidade
    while (micros() - tempoAtual < velocidade) {
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
  
  digitalWrite(ENABLE_FUSO, HIGH);
}

void moverBocalComTorque(bool direcao, unsigned long passos, int velocidade) {
  // direcao: true = horário (fechar), false = anti-horário (abrir)
  digitalWrite(DIR_BOCAL, direcao ? HIGH : LOW);
  delayMicroseconds(250);  // AUMENTADO: Mais tempo para setup de direção
  
  digitalWrite(ENABLE_BOCAL, LOW);
  delayMicroseconds(250);  // AUMENTADO: Mais tempo para estabilização
  
  unsigned long tempoAtual;
  unsigned long passosCompletos = 0;
  
  for (unsigned long i = 0; i < passos && !emergencia && estadoAtual != EM_EMERGENCIA; i++) {
    tempoAtual = micros();
    
    gerarPassoBocal();
    passosCompletos++;
    
    // A cada 50 passos, mostrar progresso
    if (passosCompletos % 50 == 0) {
      float progresso = (passosCompletos * 100.0) / passos;
      Serial.print("Progresso bocal: ");
      Serial.print(progresso, 1);
      Serial.println("%");
    }
    
    // Delay para controle de velocidade (MAIS LENTO para MAIS TORQUE)
    while (micros() - tempoAtual < velocidade) {
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
  
  digitalWrite(ENABLE_BOCAL, HIGH);
}

// ====== FUNÇÃO PARA MOVER AMBOS MOTORES SIMULTANEAMENTE ======
void moverFusoEBocalSimultaneamente(unsigned long passosFusoRestantes, unsigned long passosBocalTotais, int velocidadeFuso, int velocidadeBocal) {
  digitalWrite(DIR_FUSO, LOW);  // Descer
  digitalWrite(DIR_BOCAL, HIGH); // Horário (fechar)
  
  delayMicroseconds(150);
  
  digitalWrite(ENABLE_FUSO, LOW);
  digitalWrite(ENABLE_BOCAL, LOW);
  
  delayMicroseconds(150);
  
  unsigned long ultimoPassoFuso = micros();
  unsigned long ultimoPassoBocal = micros();
  unsigned long passosBocalCompletos = 0;
  unsigned long passosFusoCompletos = 0;
  
  while ((passosFusoCompletos < passosFusoRestantes || passosBocalCompletos < passosBocalTotais) && 
         !emergencia && estadoAtual != EM_EMERGENCIA) {
    
    unsigned long tempoAtual = micros();
    
    // Mover fuso se ainda houver passos restantes
    if (passosFusoCompletos < passosFusoRestantes && tempoAtual - ultimoPassoFuso >= velocidadeFuso) {
      gerarPassoFuso();
      ultimoPassoFuso = tempoAtual;
      passosFusoCompletos++;
      
      // Atualizar posição
      posicaoAtualMM += (1.0 / PASSOS_POR_MM);
      
      // Mostrar progresso a cada 20 passos do fuso
      if (passosFusoCompletos % 20 == 0) {
        float progressoFuso = (passosFusoCompletos * 100.0) / passosFusoRestantes;
        Serial.print("Descida: ");
        Serial.print(progressoFuso, 0);
        Serial.print("%, Posição: ");
        Serial.print(posicaoAtualMM, 1);
        Serial.println(" mm");
      }
    }
    
    // Mover bocal se ainda houver passos restantes
    if (passosBocalCompletos < passosBocalTotais && tempoAtual - ultimoPassoBocal >= velocidadeBocal) {
      gerarPassoBocal();
      ultimoPassoBocal = tempoAtual;
      passosBocalCompletos++;
      
      // Mostrar progresso a cada 50 passos do bocal
      if (passosBocalCompletos % 50 == 0) {
        float progressoBocal = (passosBocalCompletos * 100.0) / passosBocalTotais;
        Serial.print("Rosqueamento: ");
        Serial.print(progressoBocal, 1);
        Serial.println("%");
      }
    }
    
    // Verificar comandos de emergência
    if (Serial.available()) {
      char comando = Serial.read();
      if (comando == '0') {
        emergencia = true;
        estadoAtual = EM_EMERGENCIA;
        break;
      }
    }
    
    // Pequena pausa para evitar sobrecarga
    delayMicroseconds(10);
  }
  
  digitalWrite(ENABLE_FUSO, HIGH);
  digitalWrite(ENABLE_BOCAL, HIGH);
}

// ====== FUNÇÕES DE SEQUÊNCIA ======
void iniciarSequenciaRosqueamento() {
  if (emergencia) {
    Serial.println("Sistema em emergência! Use 'r' para resetar.");
    return;
  }
  
  if (digitalRead(FIM_CURSO) == HIGH) {
    Serial.println("ERRO: Sistema não está no fim de curso!");
    Serial.println("Execute homing ('h') primeiro.");
    return;
  }
  
  Serial.println("=== INICIANDO SEQUÊNCIA DE ROSQUEAMENTO OTIMIZADA ===");
  Serial.println("ESTRATÉGIA: Início antecipado do rosqueamento (40mm)");
  Serial.print("Configuração: ");
  Serial.print(VOLTAS_BOCAL);
  Serial.println(" volta com ALTO TORQUE");
  Serial.print("Tempo estimado total: ");
  Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA * VOLTAS_BOCAL) / 1000000.0);
  Serial.println(" segundos");
  Serial.print("Distância total: "); Serial.print(distanciaTotalDescida); Serial.println(" mm");
  Serial.print("Início do rosqueamento: "); Serial.print(distanciaInicioRosqueamento); Serial.println(" mm");
  
  estadoAtual = DESCENDO_FUSO_ATE_40MM;
  posicaoAtualMM = 0.0;
  voltasExecutadas = 0;
  roscaIniciada = false;
}

void executarSequencia() {
  switch (estadoAtual) {
    case DESCENDO_FUSO_ATE_40MM:
      Serial.println("1. Descendo fuso até 40mm (sem rosqueamento)...");
      moverFuso(false, passosParaInicioRosqueamento, delayMicrosFuso);
      if (!emergencia && estadoAtual != EM_EMERGENCIA) {
        Serial.print("Posição alcançada: "); Serial.print(posicaoAtualMM); Serial.println(" mm");
        estadoAtual = INICIANDO_ROSCAMENTO;
        tempoEspera = millis();
        Serial.println("2. Aguardando 0.5 segundos para estabilização...");
      }
      break;
      
    case INICIANDO_ROSCAMENTO:
      if (millis() - tempoEspera >= 500) {
        estadoAtual = DESCENDO_E_ROSCANDO;
        Serial.println("3. Iniciando rosqueamento aos 40mm e continuando descida...");
        Serial.print("   Executando "); Serial.print(VOLTAS_BOCAL); Serial.println(" volta");
        Serial.print("   Distância restante: "); Serial.print(distanciaAposInicio); Serial.println(" mm");
        Serial.print("   Tempo estimado: ");
        Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA * VOLTAS_BOCAL) / 1000000.0);
        Serial.println(" segundos");
        roscaIniciada = true;
      }
      break;
      
    case DESCENDO_E_ROSCANDO:
      {
        unsigned long passosBocalTotais = VOLTAS_BOCAL * PASSOS_POR_VOLTA_COMPLETA;
        moverFusoEBocalSimultaneamente(passosAposInicioRosqueamento, passosBocalTotais, delayMicrosFuso, delayMicrosBocal);
        if (!emergencia && estadoAtual != EM_EMERGENCIA) {
          Serial.print("Descida concluída (43mm) e ");
          Serial.print(VOLTAS_BOCAL);
          Serial.println(" volta aplicada com ALTO TORQUE.");
          estadoAtual = TERMINANDO_ROSCAMENTO;
          tempoEspera = millis();
          Serial.println("4. Aguardando 1 segundo para acomodação final...");
        }
      }
      break;
      
    case TERMINANDO_ROSCAMENTO:
      if (millis() - tempoEspera >= 1000) {
        estadoAtual = SUBINDO_FUSO;
        Serial.println("5. Subindo fuso até fim de curso...");
      }
      break;
      
    case SUBINDO_FUSO:
      // Subir até pressionar o fim de curso
      digitalWrite(ENABLE_FUSO, LOW);
      digitalWrite(DIR_FUSO, HIGH);
      delayMicroseconds(100);
      
      while (digitalRead(FIM_CURSO) == HIGH && !emergencia && estadoAtual != EM_EMERGENCIA) {
        unsigned long tempoAtual = micros();
        gerarPassoFuso();
        
        // Atualizar posição
        posicaoAtualMM -= (1.0 / PASSOS_POR_MM);
        if (posicaoAtualMM < 0) posicaoAtualMM = 0;
        
        while (micros() - tempoAtual < delayMicrosFuso) {
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
      
      digitalWrite(ENABLE_FUSO, HIGH);
      
      if (!emergencia && estadoAtual != EM_EMERGENCIA) {
        Serial.println("Fim de curso atingido!");
        posicaoAtualMM = 0.0;
        estadoAtual = RETORNANDO_BOCAL;
        Serial.println("6. Retornando bocal para posição inicial...");
      }
      break;
      
    case RETORNANDO_BOCAL:
      // Retornar o bocal para posição inicial
      {
        unsigned long passosTotais = VOLTAS_BOCAL * PASSOS_POR_VOLTA_COMPLETA;
        int velocidadeRetorno = 6000; // Velocidade fixa para retorno
        moverBocalComTorque(false, passosTotais, velocidadeRetorno);
        if (!emergencia && estadoAtual != EM_EMERGENCIA) {
          Serial.println("Bocal retornado para posição inicial.");
          estadoAtual = FINALIZADO;
          Serial.println("=== SEQUÊNCIA CONCLUÍDA COM SUCESSO ===");
          Serial.println("Dica: A fita isolante no bocal aumenta o atrito para melhor tampagem.");
        }
      }
      break;
      
    case FINALIZADO:
      // Aguardar comando para reiniciar
      break;
      
    case EM_EMERGENCIA:
      digitalWrite(ENABLE_FUSO, HIGH);
      digitalWrite(ENABLE_BOCAL, HIGH);
      break;
      
    case PARADO:
      // Nada a fazer
      break;
  }
}

// ====== FUNÇÕES DE TESTE ======
void testarDescidaFuso() {
  if (estadoAtual != PARADO) {
    Serial.println("Sistema em movimento!");
    return;
  }
  
  if (digitalRead(FIM_CURSO) == HIGH) {
    Serial.println("ERRO: Execute homing primeiro!");
    return;
  }
  
  Serial.println("Testando descida do fuso até 40mm (sem rosqueamento)...");
  estadoAtual = DESCENDO_FUSO_ATE_40MM;
  posicaoAtualMM = 0.0;
  moverFuso(false, passosParaInicioRosqueamento, delayMicrosFuso);
  estadoAtual = PARADO;
  Serial.print("Teste concluído. Posição: "); 
  Serial.print(posicaoAtualMM); 
  Serial.println(" mm");
}

void testarRotacaoBocal() {
  if (estadoAtual != PARADO) {
    Serial.println("Sistema em movimento!");
    return;
  }
  
  Serial.println("Testando rotação do bocal (1 volta LENTA com ALTO TORQUE)...");
  Serial.print("Delay atual: "); Serial.print(delayMicrosBocal); Serial.println(" us");
  Serial.print("Tempo estimado: ");
  Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA * VOLTAS_BOCAL) / 1000000.0);
  Serial.println(" segundos");
  moverBocalComTorque(true, VOLTAS_BOCAL * PASSOS_POR_VOLTA_COMPLETA, delayMicrosBocal);
  Serial.println("Teste concluído.");
}

void subirAteFimCurso() {
  if (estadoAtual != PARADO) {
    Serial.println("Sistema em movimento!");
    return;
  }
  
  Serial.println("Subindo até fim de curso...");
  
  digitalWrite(ENABLE_FUSO, LOW);
  digitalWrite(DIR_FUSO, HIGH);
  delayMicroseconds(100);
  
  while (digitalRead(FIM_CURSO) == HIGH && !emergencia) {
    unsigned long tempoAtual = micros();
    gerarPassoFuso();
    
    // Atualizar posição
    posicaoAtualMM -= (1.0 / PASSOS_POR_MM);
    if (posicaoAtualMM < 0) posicaoAtualMM = 0;
    
    while (micros() - tempoAtual < delayMicrosFuso) {
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
  
  digitalWrite(ENABLE_FUSO, HIGH);
  
  if (!emergencia) {
    Serial.println("Fim de curso atingido!");
    posicaoAtualMM = 0.0;
  }
}

void retornarBocalInicial() {
  if (estadoAtual != PARADO) {
    Serial.println("Sistema em movimento!");
    return;
  }
  
  Serial.println("Retornando bocal para posição inicial...");
  moverBocalComTorque(false, VOLTAS_BOCAL * PASSOS_POR_VOLTA_COMPLETA, 6000);
  Serial.println("Bocal retornado para posição inicial.");
}

void fazerHoming() {
  if (emergencia) {
    Serial.println("Sistema em emergência! Use 'r' para resetar.");
    return;
  }
  
  if (estadoAtual != PARADO) {
    Serial.println("Sistema em movimento!");
    return;
  }
  
  Serial.println("Iniciando homing...");
  
  // Primeiro, subir um pouco para liberar o fim de curso (se estiver pressionado)
  if (digitalRead(FIM_CURSO) == LOW) {
    Serial.println("Liberando fim de curso...");
    digitalWrite(ENABLE_FUSO, LOW);
    digitalWrite(DIR_FUSO, false);  // Descer um pouco
    delayMicroseconds(100);
    
    for (int i = 0; i < 500; i++) {
      gerarPassoFuso();
      delayMicroseconds(delayMicrosFuso);
    }
    
    digitalWrite(ENABLE_FUSO, HIGH);
    delay(500);
  }
  
  // Agora subir até pressionar fim de curso
  subirAteFimCurso();
}

void mostrarStatus() {
  Serial.println("=== STATUS DO SISTEMA ===");
  Serial.print("Posição atual: "); Serial.print(posicaoAtualMM); Serial.println(" mm");
  Serial.print("Estado: ");
  switch(estadoAtual) {
    case PARADO: Serial.println("PARADO"); break;
    case DESCENDO_FUSO_ATE_40MM: Serial.println("DESCENDO ATÉ 40MM"); break;
    case INICIANDO_ROSCAMENTO: Serial.println("INICIANDO ROSCAMENTO"); break;
    case DESCENDO_E_ROSCANDO: Serial.println("DESCENDO E ROSCANDO"); break;
    case TERMINANDO_ROSCAMENTO: Serial.println("TERMINANDO ROSCAMENTO"); break;
    case SUBINDO_FUSO: Serial.println("SUBINDO FUSO"); break;
    case RETORNANDO_BOCAL: Serial.println("RETORNANDO BOCAL"); break;
    case EM_EMERGENCIA: Serial.println("EMERGÊNCIA"); break;
    case FINALIZADO: Serial.println("FINALIZADO"); break;
  }
  Serial.print("Fim de curso: "); Serial.println(digitalRead(FIM_CURSO) ? "LIVRE" : "PRESSIONADO");
  Serial.println("\nConfigurações atuais (OTIMIZADAS):");
  Serial.print("  Velocidade fuso: delay="); Serial.print(delayMicrosFuso); Serial.print(" us ("); 
  Serial.print((1000000.0/delayMicrosFuso), 0); Serial.println(" passos/segundo)");
  Serial.print("  Velocidade bocal: delay="); Serial.print(delayMicrosBocal); Serial.print(" us ("); 
  Serial.print((1000000.0/delayMicrosBocal), 0); Serial.println(" passos/segundo)");
  Serial.print("  Tempo por volta do bocal: "); 
  Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA) / 1000000.0, 1); 
  Serial.println(" segundos (ALTO TORQUE)");
  Serial.print("  Voltas configuradas: "); Serial.println(VOLTAS_BOCAL);
  Serial.print("  Tempo total de rosqueamento: "); 
  Serial.print((delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA * VOLTAS_BOCAL) / 1000000.0, 1); 
  Serial.println(" segundos");
  Serial.print("  Distância total: "); Serial.print(distanciaTotalDescida); Serial.println(" mm");
  Serial.print("  Início do rosqueamento: "); Serial.print(distanciaInicioRosqueamento); Serial.println(" mm");
  Serial.println("\nDica: Use fita isolante no bocal para aumentar o atrito.");
}

void loop() {
  // Executar sequência se estiver em andamento
  if (estadoAtual != PARADO && estadoAtual != FINALIZADO && estadoAtual != EM_EMERGENCIA) {
    executarSequencia();
  }
  
  // Verificar entrada serial
  if (Serial.available()) {
    char comando = Serial.read();
    
    switch(comando) {
      case '1':  // Iniciar sequência completa
        if (estadoAtual == PARADO || estadoAtual == FINALIZADO) {
          iniciarSequenciaRosqueamento();
        } else {
          Serial.println("Sistema já em movimento!");
        }
        break;
        
      case '2':  // Testar descida do fuso
        testarDescidaFuso();
        break;
        
      case '3':  // Testar rotação do bocal
        testarRotacaoBocal();
        break;
        
      case '4':  // Subir até fim de curso
        subirAteFimCurso();
        break;
        
      case '5':  // Retornar bocal para posição inicial
        retornarBocalInicial();
        break;
        
      case '6':  // Configurar velocidade fuso
        {
          Serial.println("Digite o delay do fuso em microssegundos (maior = mais lento):");
          Serial.println("Recomendado: 1000-3000 us");
          while (!Serial.available()) delay(10);
          String input = Serial.readStringUntil('\n');
          delayMicrosFuso = input.toInt();
          if (delayMicrosFuso < 500) delayMicrosFuso = 500; // Limite mínimo
          Serial.print("Novo delay fuso: "); Serial.print(delayMicrosFuso); Serial.println(" us");
        }
        break;
        
      case '7':  // Configurar velocidade bocal (torque)
        {
          Serial.println("Digite o delay do bocal em microssegundos (maior = mais lento = mais torque):");
          Serial.println("Recomendado para ROSQUEAMENTO COM ALTO TORQUE: 15000-25000 us");
          Serial.println("Para teste: 2000-5000 us");
          Serial.print("Valor atual: "); Serial.print(delayMicrosBocal); Serial.println(" us");
          while (!Serial.available()) delay(10);
          String input = Serial.readStringUntil('\n');
          delayMicrosBocal = input.toInt();
          if (delayMicrosBocal < 1000) delayMicrosBocal = 1000; // Limite mínimo
          Serial.print("Novo delay bocal: "); Serial.print(delayMicrosBocal); Serial.println(" us");
          float tempoVolta = (delayMicrosBocal * PASSOS_POR_VOLTA_COMPLETA) / 1000000.0;
          Serial.print("Tempo estimado por volta: "); Serial.print(tempoVolta, 1); Serial.println(" segundos");
        }
        break;
        
      case '8':  // Configurar número de voltas do bocal
        {
          Serial.println("Digite o número de voltas para rosqueamento (ex: 1, 1.5, 2):");
          Serial.println("ATENÇÃO: A alteração deste valor requer recompilação do código!");
          Serial.println("Para ajuste dinâmico, altere a constante VOLTAS_BOCAL no código.");
          Serial.print("Valor atual: "); Serial.println(VOLTAS_BOCAL);
        }
        break;
        
      case '9':  // Mostrar status
        mostrarStatus();
        break;
        
      case '0':  // Parada de emergência
        emergencia = true;
        estadoAtual = EM_EMERGENCIA;
        digitalWrite(ENABLE_FUSO, HIGH);
        digitalWrite(ENABLE_BOCAL, HIGH);
        Serial.println("!!! PARADA DE EMERGÊNCIA ATIVADA !!!");
        break;
        
      case 'r':  // Resetar sistema
        emergencia = false;
        estadoAtual = PARADO;
        Serial.println("Sistema resetado. Pronto para operar.");
        break;
        
      case 'h':  // Homing
        fazerHoming();
        break;
        
      default:
        Serial.println("Comando não reconhecido");
        break;
    }
  }
  
  delay(10);
}