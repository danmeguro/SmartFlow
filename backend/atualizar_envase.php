<?php
// backend/atualizar_envase.php
require_once 'conexao.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $d = json_decode(file_get_contents("php://input"), true);
    if (empty($d)) { echo json_encode(['success'=>false]); exit; }

    $sql = "INSERT INTO telemetria_maquina (pedido_id, rotuladora_presenca, estoque_contagem, carrossel_posicao, tampinhas_disponiveis, tempo_envase_ms) VALUES (?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // Preenche com 0 se não vier dado
        $pid = $d['pedido_id'] ?? NULL;
        $rot = $d['rotuladora_presenca'] ?? 0;
        $est = $d['estoque_contagem'] ?? 0;
        $car = $d['carrossel_posicao'] ?? 0;
        $tam = $d['tampinhas_disponiveis'] ?? 0;
        $tem = $d['tempo_envase_ms'] ?? 0;

        $stmt->bind_param("iiiiii", $pid, $rot, $est, $car, $tam, $tem);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
    }
    $conn->close();
}
?>