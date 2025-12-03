<?php
// SmartFlow/backend/view/telaPDASH.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['papel'] !== 'administrador') {
    header("location: index.php");
    exit;
}

require_once '../conexao.php';

// Lógica de Atualização de Status
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $novo_status = $_GET['acao'] == 'concluir' ? 'finalizado' : 'cancelado';
    $stmt = $conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_status, $id);
    $stmt->execute();
    $stmt->close();
    header("location: telaPDASH.php");
    exit;
}

// Busca Pedidos
$sql_lista = "
    SELECT p.id, p.nome_garrafa, p.status, p.data_pedido,
           GROUP_CONCAT(CONCAT(i.quantidade, 'x ', i.sabor) SEPARATOR ', ') as resumo_itens
    FROM pedidos p
    LEFT JOIN itens_pedido i ON p.id = i.pedido_id
    GROUP BY p.id
    ORDER BY p.id DESC LIMIT 20
";
$res_lista = $conn->query($sql_lista);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Pedidos</title>
  <meta http-equiv="refresh" content="10">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); min-height: 100vh; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; }
    h1 { color: #5E3B76; }
    .btn-voltar { text-decoration: none; color: #5E3B76; font-weight: bold; border: 1px solid #5E3B76; padding: 8px 15px; border-radius: 8px; margin-right: 10px; }
    .btn-voltar:hover { background: #5E3B76; color: white; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background-color: #f9f9f9; color: #5E3B76; }
    .status-badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
    .status-pendente { background: #fff3cd; color: #856404; }
    .status-finalizado { background: #d4edda; color: #155724; }
    .status-cancelado { background: #f8d7da; color: #721c24; }
    .acao-btn { text-decoration: none; font-size: 12px; padding: 5px 10px; border-radius: 5px; margin-right: 5px; color: white; }
    .btn-ok { background: #32CD32; } .btn-del { background: #ff6b6b; }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Gestão de Pedidos</h1>
      <div>
        <a href="home_admin.php" class="btn-voltar">Voltar ao Menu</a>
      </div>
    </header>

    <table>
        <thead>
            <tr><th>#ID</th><th>Cliente</th><th>Itens</th><th>Status</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php if ($res_lista && $res_lista->num_rows > 0): ?>
                <?php while($ped = $res_lista->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $ped['id']; ?></td>
                        <td><?php echo htmlspecialchars($ped['nome_garrafa']); ?></td>
                        <td><?php echo htmlspecialchars($ped['resumo_itens']); ?></td>
                        <td><span class="status-badge status-<?php echo $ped['status']; ?>"><?php echo $ped['status']; ?></span></td>
                        <td>
                            <?php if ($ped['status'] == 'pendente'): ?>
                                <a href="?acao=concluir&id=<?php echo $ped['id']; ?>" class="acao-btn btn-ok">✔</a>
                                <a href="?acao=cancelar&id=<?php echo $ped['id']; ?>" class="acao-btn btn-del">✖</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">Nenhum pedido.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
  </div>
</body>
</html>