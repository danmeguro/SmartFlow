<?php
// backend/view/dashboard_comercial.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['papel'] !== 'administrador') {
    header("location: index.php");
    exit;
}

require_once '../conexao.php';

// --- CONFIGURAÇÃO DE PREÇOS (Simulação) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['novo_preco'])) {
    $novo_preco = floatval(str_replace(',', '.', $_POST['novo_preco']));
    if ($novo_preco > 0) {
        $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('preco_suco', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $valor_str = number_format($novo_preco, 2, '.', '');
        $stmt->bind_param("s", $valor_str);
        $stmt->execute();
        $stmt->close();
        $msg_sucesso = "Preço atualizado para R$ " . number_format($novo_preco, 2, ',', '.');
    }
}

// --- BUSCA PREÇO ATUAL ---
$sql_conf = "SELECT valor FROM configuracoes WHERE chave = 'preco_suco'";
$res_conf = $conn->query($sql_conf);
$preco_suco = ($res_conf && $res_conf->num_rows > 0) ? floatval($res_conf->fetch_assoc()['valor']) : 10.00;

// --- 1. LÓGICA DO FILTRO DE DATAS ---
// Pega as datas do GET ou define um período padrão (últimos 15 dias)
$data_inicio_str = $_GET['data_inicio'] ?? (new DateTime())->sub(new DateInterval("P14D"))->format('Y-m-d');
$data_fim_str = $_GET['data_fim'] ?? (new DateTime())->format('Y-m-d');

// Validação básica e formatação de objetos DateTime
try {
    $data_inicio = new DateTime($data_inicio_str);
    $data_fim = new DateTime($data_fim_str);
    if ($data_inicio > $data_fim) {
        $data_inicio = new DateTime($data_fim_str); // Garante que inicio não seja maior que fim
    }
} catch (Exception $e) {
    // Fallback em caso de datas inválidas
    $data_inicio = (new DateTime())->sub(new DateInterval("P14D"));
    $data_fim = new DateTime();
}
$data_inicio_sql = $data_inicio->format('Y-m-d');
$data_fim_sql = $data_fim->format('Y-m-d');


// --- CÁLCULOS PRINCIPAIS (KPIs - Afetados pela data) ---
// Calcula o faturamento e quantidade apenas no período selecionado
$sql_vendas_periodo = "
    SELECT 
        SUM(i.quantidade) as total_sucos, 
        SUM(i.quantidade * {$preco_suco}) as faturamento_total,
        COUNT(DISTINCT p.id) as total_pedidos
    FROM pedidos p
    JOIN itens_pedido i ON p.id = i.pedido_id
    WHERE DATE(p.data_pedido) BETWEEN '{$data_inicio_sql}' AND '{$data_fim_sql}'
";
$res_kpi = $conn->query($sql_vendas_periodo)->fetch_assoc();

$total_sucos = $res_kpi['total_sucos'] ?? 0;
$faturamento_total = $res_kpi['faturamento_total'] ?? 0;
$total_pedidos = $res_kpi['total_pedidos'] ?? 0;
$ticket_medio = ($total_pedidos > 0) ? ($faturamento_total / $total_pedidos) : 0;

$sql_ranking = "SELECT sabor, SUM(quantidade) as qtd FROM itens_pedido GROUP BY sabor ORDER BY qtd DESC";
$res_ranking = $conn->query($sql_ranking);

// --- 2. LÓGICA DE GERAÇÃO DE DADOS PARA GRÁFICO ---
$datas_grafico_qtd = [];
$datas_grafico_rec = [];

// A. Gerar Array de Datas para o período selecionado (Gap Filling)
$interval = new DateInterval('P1D');
$periodo = new DatePeriod($data_inicio, $interval, $data_fim->modify('+1 day'));
$data_fim->modify('-1 day'); // Volta para o dia final correto

foreach ($periodo as $data) {
    $datas_grafico_qtd[$data->format('Y-m-d')] = 0; 
    $datas_grafico_rec[$data->format('Y-m-d')] = 0; 
}

// B. Consulta SQL para buscar vendas por dia
$sql_grafico = "
    SELECT 
        DATE(p.data_pedido) as dia, 
        SUM(i.quantidade) as sucos_vendidos,
        SUM(i.quantidade * {$preco_suco}) as receita
    FROM pedidos p
    JOIN itens_pedido i ON p.id = i.pedido_id
    WHERE DATE(p.data_pedido) BETWEEN '{$data_inicio_sql}' AND '{$data_fim_sql}'
    GROUP BY dia
    ORDER BY dia ASC
";
$res_grafico = $conn->query($sql_grafico);

// C. Popular os Arrays de Datas
if ($res_grafico) {
    while ($row = $res_grafico->fetch_assoc()) {
        $data_sql = $row['dia'];
        if (isset($datas_grafico_qtd[$data_sql])) {
            $datas_grafico_qtd[$data_sql] = intval($row['sucos_vendidos']);
            $datas_grafico_rec[$data_sql] = floatval($row['receita']);
        }
    }
}

// D. Formatação para JSON/JavaScript
$dados_js_qtd = [['Dia', 'Sucos Vendidos']];
foreach ($datas_grafico_qtd as $dia_full => $qtd) { $dados_js_qtd[] = [(new DateTime($dia_full))->format('d/m'), $qtd]; }
$json_grafico_qtd = json_encode($dados_js_qtd);

$dados_js_rec = [['Dia', 'Faturamento (R$)']];
foreach ($datas_grafico_rec as $dia_full => $rec) { $dados_js_rec[] = [(new DateTime($dia_full))->format('d/m'), $rec]; }
$json_grafico_rec = json_encode($dados_js_rec);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Comercial</title>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); min-height: 100vh; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; }
    h1 { color: #5E3B76; font-size: 24px; }
    .btn-voltar { text-decoration: none; color: #5E3B76; font-weight: bold; border: 1px solid #5E3B76; padding: 8px 15px; border-radius: 8px; }
    .btn-voltar:hover { background: #5E3B76; color: white; }

    /* FILTRO DE DATA */
    .filter-container { background: #f7f4ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #CDAFFA; }
    .filter-container form { display: flex; align-items: center; gap: 15px; }
    .filter-container label { font-weight: 600; color: #4A2D9C; }
    .filter-container input[type="date"] { padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
    .filter-container button { background: #4A2D9C; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }

    /* GRIDS */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .card-kpi { background: #F7F4FF; padding: 25px; border-radius: 15px; text-align: center; border-bottom: 5px solid #CDAFFA; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .card-kpi h3 { color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
    .card-kpi .valor { font-size: 32px; font-weight: bold; color: #4A2D9C; }
    
    .config-section { background: #fff3cd; padding: 15px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #ffeeba; display: flex; justify-content: space-between; align-items: center; }
    .config-form { display: flex; gap: 10px; align-items: center; }
    .config-form input { padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100px; }
    .btn-salvar { background: #4A2D9C; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
    
    .grafico-card { background: #F7F4FF; padding: 20px; border-radius: 15px; margin-bottom: 30px; }
    #chart_div, #chart_receita { height: 350px; } 

    .chart-controls { margin-bottom: 20px; display: flex; align-items: center; gap: 15px; }
    .chart-controls label { font-weight: bold; color: #5E3B76; }
    .chart-controls select { padding: 8px; border-radius: 5px; border: 1px solid #CDAFFA; }

    .ranking-section { margin-top: 30px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f9f9f9; color: #5E3B76; }
    .barra-container { width: 100%; background: #eee; height: 8px; border-radius: 4px; margin-top: 5px; }
    .barra-fill { height: 100%; background: #CDAFFA; border-radius: 4px; }

  </style>

  <!-- Lógica do Gráfico em JavaScript -->
  <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(initializeChartDisplay); 

    const JSON_VENDAS = <?php echo $json_grafico_qtd; ?>;
    const JSON_RECEITA = <?php echo $json_grafico_rec; ?>;

    function initializeChartDisplay() {
        // Inicializa o primeiro gráfico (Vendas) e o filtro
        drawChart(JSON_VENDAS, 'chart_div', 'Vendas (Unidades)', 'Qtd. Sucos', '#9661D2');
        
        // Esconde o gráfico de Receita no início
        document.getElementById('chart_receita_container').style.display = 'none';
        
        // Garante que o valor do filtro seja mantido após o refresh
        document.getElementById('dataInicio').value = '<?php echo $data_inicio_str; ?>';
        document.getElementById('dataFim').value = '<?php echo $data_fim_str; ?>';
    }
    
    function drawChart(jsonData, elementId, title, vAxisTitle, color) {
      var data = google.visualization.arrayToDataTable(jsonData);

      var options = {
        title: title,
        areaOpacity: 0.2, 
        lineWidth: 3, 
        pointSize: 5,
        legend: { position: 'bottom' },
        colors: [color],
        hAxis: { title: 'Data', titleTextStyle: { color: '#333' } },
        vAxis: { title: vAxisTitle, minValue: 0, format: '0' }
      };
      
      if (vAxisTitle === 'Receita (R$)') {
         options.vAxis.format = 'currency';
      }

      var chart = new google.visualization.LineChart(document.getElementById(elementId));
      chart.draw(data, options);
    }

    function toggleChart() {
        const selector = document.getElementById('chartSelector');
        const chartType = selector.value;
        
        const vendasContainer = document.getElementById('chart_vendas_container');
        const receitaContainer = document.getElementById('chart_receita_container');
        
        if (chartType === 'quantidade') {
            vendasContainer.style.display = 'block';
            receitaContainer.style.display = 'none';
        } else if (chartType === 'receita') {
            vendasContainer.style.display = 'none';
            receitaContainer.style.display = 'block';
            
            // Desenha o gráfico de receita se estiver sendo mostrado
            drawChart(JSON_RECEITA, 'chart_receita', 'Faturamento Bruto', 'Receita (R$)', '#32CD32');
        }
    }
  </script>
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
            <input type="number" name="novo_preco" step="0.01" min="0.01" value="<?php echo number_format($preco_suco, 2, '.', ''); ?>" required>
            <button type="submit" class="btn-salvar">Alterar</button>
        </form>
    </div>

    <!-- Filtro de Datas -->
    <div class="filter-container">
        <form method="GET" action="dashboard_comercial.php">
            <label for="dataInicio">De:</label>
            <input type="date" id="dataInicio" name="data_inicio" value="<?php echo $data_inicio_str; ?>" required>

            <label for="dataFim">Até:</label>
            <input type="date" id="dataFim" name="data_fim" value="<?php echo $data_fim_str; ?>" required>

            <button type="submit">Filtrar</button>
        </form>
        <p style="margin-top: 10px; font-size: 14px; color: #777;">Análise de: <?php echo $data_inicio->format('d/m/Y'); ?> a <?php echo $data_fim->format('d/m/Y'); ?></p>
    </div>


    <!-- KPIs Financeiros -->
    <div class="kpi-grid">
        <div class="card-kpi">
            <h3>Faturamento Total</h3>
            <div class="valor">R$ <?php echo number_format($faturamento_total, 2, ',', '.'); ?></div>
        </div>

        <div class="card-kpi">
            <h3>Sucos Vendidos</h3>
            <div class="valor"><?php echo $total_sucos; ?></div>
        </div>

        <div class="card-kpi">
            <h3>Total de Pedidos</h3>
            <div class="valor"><?php echo $total_pedidos; ?></div>
        </div>

        <div class="card-kpi">
            <h3>Ticket Médio</h3>
            <div class="valor">R$ <?php echo number_format($ticket_medio, 2, ',', '.'); ?></div>
        </div>
    </div>
    
    <!-- CARD DE GRÁFICOS E CONTROLE -->
    <div class="grafico-card">
        <h2>Análise de Tendência</h2>

        <!-- CONTROLE DROPDOWN -->
        <div class="chart-controls">
            <label for="chartSelector">Visualizar:</label>
            <select id="chartSelector" onchange="toggleChart()">
                <option value="quantidade">Vendas (Quantidade)</option>
                <option value="receita">Receita (Faturamento)</option>
            </select>
        </div>

        <!-- Container 1: Vendas por Quantidade (Inicialmente visível) -->
        <div id="chart_vendas_container">
            <div id="chart_div"></div>
        </div>
        
        <!-- Container 2: Vendas por Receita (Inicialmente oculto) -->
        <div id="chart_receita_container">
            <div id="chart_receita"></div>
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
                    
                    while($r = $res_ranking->fetch_assoc()) {
                        $dados_ranking[] = $r;
                        if ($primeiro) { $max_val = $r['qtd']; $primeiro = false; }
                    }

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