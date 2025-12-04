<?php
// backend/view/gestao_pedidos.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['papel'] !== 'administrador') {
    header("location: index.php");
    exit;
}
require_once '../conexao.php';

if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $novo_status = $_GET['acao'] == 'concluir' ? 'finalizado' : 'cancelado';
    $conn->query("UPDATE pedidos SET status = '$novo_status' WHERE id = $id");
    header("location: gestao_pedidos.php");
    exit;
}

$sql = "SELECT p.id, p.nome_garrafa, p.status, p.data_pedido, 
        GROUP_CONCAT(CONCAT(i.quantidade, 'x ', i.sabor) SEPARATOR ', ') as resumo 
        FROM pedidos p LEFT JOIN itens_pedido i ON p.id = i.pedido_id 
        GROUP BY p.id ORDER BY p.id DESC LIMIT 20";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Pedidos</title>
  <meta http-equiv="refresh" content="10">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); min-height: 100vh; padding: 20px; }
    .container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; }
    h1 { color: #5E3B76; }
    .btn-voltar { text-decoration: none; color: #5E3B76; font-weight: bold; border: 1px solid #5E3B76; padding: 8px 15px; border-radius: 8px; }
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
    <header><h1>Gestão de Pedidos</h1><a href="home_admin.php" class="btn-voltar">Voltar</a></header>
    <table>
        <thead><tr><th>#ID</th><th>Cliente</th><th>Itens</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
            <?php if ($res && $res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nome_garrafa']); ?></td>
                <td><?php echo htmlspecialchars($row['resumo']); ?></td>
                <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                <td>
                    <?php if ($row['status'] == 'pendente'): ?>
                        <a href="?acao=concluir&id=<?php echo $row['id']; ?>" class="acao-btn btn-ok">✔</a>
                        <a href="?acao=cancelar&id=<?php echo $row['id']; ?>" class="acao-btn btn-del">✖</a>
                    <?php else: echo "-"; endif; ?>
                </td>
            </tr>
            <?php endwhile; else: echo "<tr><td colspan='5'>Nenhum pedido.</td></tr>"; endif; ?>
        </tbody>
    </table>
  </div>
</body>
</html>