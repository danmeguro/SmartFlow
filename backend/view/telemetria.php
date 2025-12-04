<?php
// backend/view/telemetria.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['papel'] !== 'administrador') {
    header("location: index.php");
    exit;
}
require_once '../conexao.php';

$sql = "SELECT * FROM telemetria_maquina ORDER BY id DESC LIMIT 1";
$res = $conn->query($sql);
$tel = $res->fetch_assoc();
if (!$tel) $tel = ['rotuladora_presenca'=>0, 'estoque_contagem'=>0, 'carrossel_posicao'=>0, 'tampinhas_disponiveis'=>0, 'tempo_envase_ms'=>0, 'data_registro'=>'---'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Telemetria</title>
  <meta http-equiv="refresh" content="3">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); min-height: 100vh; padding: 20px; }
    .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    header { display: flex; justify-content: space-between; margin-bottom: 30px; align-items: center; }
    h1 { color: #5E3B76; }
    .btn-voltar { text-decoration: none; color: #5E3B76; font-weight: bold; border: 1px solid #5E3B76; padding: 8px 15px; border-radius: 8px; }
    .btn-voltar:hover { background: #5E3B76; color: white; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .card { background: #f9f9f9; padding: 20px; border-radius: 15px; text-align: center; border: 1px solid #eee; }
    .valor { font-size: 30px; font-weight: bold; color: #4A2D9C; }
    .ok { color: #32CD32; } .err { color: #ff6b6b; }
  </style>
</head>
<body>
  <div class="container">
    <header><h1>Telemetria</h1><a href="home_admin.php" class="btn-voltar">Voltar</a></header>
    <div class="grid">
        <div class="card"><h3>Estoque</h3><div class="valor"><?php echo $tel['estoque_contagem']; ?></div></div>
        <div class="card"><h3>Carrossel</h3><div class="valor"><?php echo $tel['carrossel_posicao']; ?>°</div></div>
        <div class="card"><h3>Rotuladora</h3><div class="valor <?php echo $tel['rotuladora_presenca']?'ok':'err'; ?>"><?php echo $tel['rotuladora_presenca']?'Detectado':'Vazio'; ?></div></div>
        <div class="card"><h3>Tampinhas</h3><div class="valor <?php echo $tel['tampinhas_disponiveis']?'ok':'err'; ?>"><?php echo $tel['tampinhas_disponiveis']?'OK':'Faltando'; ?></div></div>
        <div class="card" style="grid-column:1/-1;"><h3>Último Envase</h3><div class="valor"><?php echo $tel['tempo_envase_ms']; ?> ms</div><small><?php echo $tel['data_registro']; ?></small></div>
    </div>
  </div>
</body>
</html>