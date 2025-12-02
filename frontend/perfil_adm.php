<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil ADM</title>
</head>
<body>
    <h1>Painel do Administrador</h1>
    <p>Bem-vindo, ADM!</p>

    <h3>Dashboards</h3>
    <?php for ($i = 1; $i <= 8; $i++): ?>
        <a href="dashboards/dashboard<?php echo $i; ?>.php" target="_blank">
            <button>Dashboard <?php echo $i; ?></button>
        </a>
    <?php endfor; ?>
</body>
</html>