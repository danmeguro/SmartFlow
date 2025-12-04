<?php
// backend/salvar_pedido.php
require_once 'conexao.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    if (empty($dados['itens'])) {
        echo json_encode(['success' => false, 'message' => 'Sem itens.']);
        exit;
    }

    $nome = isset($dados['nome']) ? $conn->real_escape_string(substr($dados['nome'], 0, 15)) : 'Sem Nome';
    $itens = $dados['itens'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO pedidos (nome_garrafa) VALUES (?)");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $pedido_id = $conn->insert_id;
        $stmt->close();

        $stmt_item = $conn->prepare("INSERT INTO itens_pedido (pedido_id, sabor, quantidade) VALUES (?, ?, ?)");
        foreach ($itens as $item) {
            $sabor = $item['sabor'];
            $qtd = intval($item['quantidade']);
            if ($qtd > 0) {
                $stmt_item->bind_param("isi", $pedido_id, $sabor, $qtd);
                $stmt_item->execute();
            }
        }
        $stmt_item->close();
        $conn->commit();
        echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    $conn->close();
}
?>