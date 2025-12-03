<?php
// SmartFlow/backend/salvar_pedido.php

// 1. Configurações Iniciais
require_once 'conexao.php';
header('Content-Type: application/json'); // Avisa que a resposta será JSON

// 2. Verifica se é um POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // 3. Recebe o JSON enviado pelo Javascript
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    // 4. Validação Básica
    if (empty($dados['itens']) || !is_array($dados['itens'])) {
        echo json_encode(['success' => false, 'message' => 'Nenhum item selecionado.']);
        exit;
    }

    // Limpa o nome (se vier vazio, usa "Sem Nome")
    $nome_garrafa = isset($dados['nome']) && trim($dados['nome']) !== '' 
                    ? $conn->real_escape_string(substr($dados['nome'], 0, 15)) 
                    : 'Sem Nome';

    $itens = $dados['itens']; // Array de {sabor, quantidade}

    // 5. Inicia Transação (Para garantir que salva Pedido E Itens, ou nenhum dos dois)
    $conn->begin_transaction();

    try {
        // --- PASSO A: Inserir na tabela 'pedidos' ---
        $sql_pedido = "INSERT INTO pedidos (nome_garrafa, status) VALUES (?, 'pendente')";
        $stmt = $conn->prepare($sql_pedido);
        $stmt->bind_param("s", $nome_garrafa);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar pedido: " . $stmt->error);
        }
        
        // Pega o ID que acabou de ser criado (ex: Pedido #50)
        $pedido_id = $conn->insert_id;
        $stmt->close();

        // --- PASSO B: Inserir na tabela 'itens_pedido' ---
        $sql_item = "INSERT INTO itens_pedido (pedido_id, sabor, quantidade) VALUES (?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        foreach ($itens as $item) {
            $sabor = $item['sabor'];
            $qtd = intval($item['quantidade']);

            // Só salva se quantidade for maior que 0
            if ($qtd > 0) {
                // "isi" = Integer (ID), String (Sabor), Integer (Qtd)
                $stmt_item->bind_param("isi", $pedido_id, $sabor, $qtd);
                
                if (!$stmt_item->execute()) {
                    throw new Exception("Erro ao inserir item ($sabor): " . $stmt_item->error);
                }
            }
        }
        $stmt_item->close();

        // --- PASSO C: Confirmar (Commit) ---
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Pedido realizado com sucesso!',
            'pedido_id' => $pedido_id
        ]);

    } catch (Exception $e) {
        // Se deu erro em qualquer parte, desfaz tudo
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Erro no servidor: ' . $e->getMessage()
        ]);
    }

    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>