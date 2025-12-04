<?php
// backend/view/dashboard_comercial.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['papel'] !== 'administrador') {
    header("location: index.php");
    exit;
}

require_once '../conexao.php';

// --- LÓGICA DE ATUALIZAÇÃO DE PREÇO ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['novo_preco'])) {
    $novo_preco = floatval(str_replace(',', '.', $_POST['novo_preco']));
    if ($novo_preco > 0) {
        // Usa INSERT ON DUPLICATE KEY UPDATE para garantir que a linha de configuração existe
        $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('preco_suco', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $valor_str = number_format($novo_preco, 2, '.', '');
        $stmt->bind_param("s", $valor_str);
        $stmt->execute();
        $stmt->close();
        $msg_sucesso = "Preço atualizado para R$ " . number_format($novo_preco, 2, ',', '.');
    }
}

// --- BUSCA PREÇO ATUAL ---
// ATENÇÃO: Se a tabela não existir, a query irá falhar.
$sql_conf = "SELECT valor FROM configuracoes WHERE chave = 'preco_suco'";
$res_conf = $conn->query($sql_conf);
// Se não achar no banco, usa 10.00 como fallback
$preco_suco = ($res_conf && $res_conf->num_rows > 0) ? floatval($res_conf->fetch_assoc()['valor']) : 10.00;

// --- CÁLCULOS PRINCIPAIS ---
$sql_vendas = "SELECT SUM(quantidade) as total_sucos FROM itens_pedido";
$res_vendas = $conn->query($sql_vendas);
$total_sucos = $res_vendas->fetch_assoc()['total_sucos'] ?? 0;
$faturamento_total = $total_sucos * $preco_suco;

$sql_pedidos = "SELECT COUNT(*) as total FROM pedidos";
$total_pedidos = $conn->query($sql_pedidos)->fetch_assoc()['total'] ?? 0;
$ticket_medio = ($total_pedidos > 0) ? ($faturamento_total / $total_pedidos) : 0;

$sql_ranking = "SELECT sabor, SUM(quantidade) as qtd FROM itens_pedido GROUP BY sabor ORDER BY qtd DESC";
$res_ranking = $conn->query($sql_ranking);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Comercial</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); min-height: 100vh; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; }
    h1 { color: #5E3B76; font-size: 24px; }
    .btn-voltar { text-decoration: none; color: #5E3B76; font-weight: bold; border: 1px solid #5E3B76; padding: 8px 15px; border-radius: 8px; }
    .btn-voltar:hover { background: #5E3B76; color: white; }

    /* GRIDS */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
    
    .card-kpi { background: #F7F4FF; padding: 25px; border-radius: 15px; text-align: center; border-bottom: 5px solid #CDAFFA; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .card-kpi h3 { color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
    .card-kpi .valor { font-size: 32px; font-weight: bold; color: #4A2D9C; }
    .card-kpi .sub { font-size: 12px; color: #999; }

    /* FORMULÁRIO DE PREÇO */
    .config-section { background: #fff3cd; padding: 15px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #ffeeba; display: flex; justify-content: space-between; align-items: center; }
    .config-form { display: flex; gap: 10px; align-items: center; }
    .config-form input { padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100px; }
    .btn-salvar { background: #4A2D9C; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
    
    /* TABELA RANKING */
    .ranking-section h2 { color: #5E3B76; margin-bottom: 15px; font-size: 18px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f9f9f9; color: #5E3B76; }
    
    .barra-container { width: 100%; background: #eee; height: 8px; border-radius: 4px; margin-top: 5px; }
    .barra-fill { height: 100%; background: #CDAFFA; border-radius: 4px; }

  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Dashboard Comercial</h1>
      <a href="home_admin.php" class="btn-voltar">Voltar</a>
    </header>

    <!-- Área de Configuração de Preço -->
    <div class="config-section">
        <div>
            <strong>Configuração de Preço:</strong> O valor atual por unidade é <b>R$ <?php echo number_format($preco_suco, 2, ',', '.'); ?></b>
            <?php if(isset($msg_sucesso)) echo "<span style='color:green; margin-left:10px;'>$msg_sucesso</span>"; ?>
        </div>
        <form method="POST" class="config-form">
            <!-- Coloca o valor atual no input para facilitar a edição -->
            <input type="number" name="novo_preco" step="0.01" min="0.01" value="<?php echo number_format($preco_suco, 2, '.', ''); ?>" required>
            <button type="submit" class="btn-salvar">Alterar</button>
        </form>
    </div>

    <!-- KPIs Financeiros -->
    <div class="kpi-grid">
        <div class="card-kpi">
            <h3>Faturamento Total</h3>
            <div class="valor">R$ <?php echo number_format($faturamento_total, 2, ',', '.'); ?></div>
            <p class="sub">Baseado no preço atual</p>
        </div>

        <div class="card-kpi">
            <h3>Sucos Vendidos</h3>
            <div class="valor"><?php echo $total_sucos; ?></div>
            <p class="sub">Unidades totais</p>
        </div>

        <div class="card-kpi">
            <h3>Total de Pedidos</h3>
            <div class="valor"><?php echo $total_pedidos; ?></div>
            <p class="sub">Clientes atendidos</p>
        </div>

        <div class="card-kpi">
            <h3>Ticket Médio</h3>
            <div class="valor">R$ <?php echo number_format($ticket_medio, 2, ',', '.'); ?></div>
            <p class="sub">Média por pedido</p>
        </div>
    </div>

    <!-- Ranking de Sabores -->
    <div class="ranking-section">
        <h2>🏆 Ranking de Sabores Mais Vendidos</h2>
        <table>
            <thead>
                <tr>
                    <th>Sabor</th>
                    <th>Quantidade Vendida</th>
                    <th>Popularidade</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($res_ranking && $res_ranking->num_rows > 0): 
                    $dados_ranking = [];
                    $primeiro = true;
                    $max_val = 1;
                    
                    // 1. Coleta e encontra o valor máximo para a barra de popularidade
                    while($r = $res_ranking->fetch_assoc()) {
                        $dados_ranking[] = $r;
                        if ($primeiro) { $max_val = $r['qtd']; $primeiro = false; }
                    }

                    // 2. Itera e exibe os dados
                    foreach($dados_ranking as $row):
                        $porcentagem = ($row['qtd'] / $max_val) * 100;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['sabor']); ?></td>
                        <td><?php echo $row['qtd']; ?> unid.</td>
                        <td style="width: 40%;">
                            <div class="barra-container">
                                <div class="barra-fill" style="width: <?php echo $porcentagem; ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center; padding:20px;">Nenhuma venda registrada ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

  </div>
</body>
</html>