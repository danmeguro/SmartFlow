<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Usuário</title>
</head>
<body>
    <h1>Login Usuário</h1>
    <form action="menu.php" method="post">
        <input type="text" name="usuario" placeholder="Usuário" required><br>
        <input type="password" name="senha" placeholder="Senha" required><br>
        <button type="submit">Entrar</button>
    </form>
    <br>
    <a href="login_adm.php">
        <button>Login ADM</button>
    </a>
</body>
</html>