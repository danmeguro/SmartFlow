<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFlow - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #CDAFFA, #E9D5FF); display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-container { background-color: #fff; padding: 40px 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #5E3B76; margin-bottom: 25px; font-size: 26px; }
        .input-group { margin-bottom: 18px; text-align: left; }
        label { color: #555; font-weight: 500; font-size: 14px; }
        input { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; margin-top: 5px; }
        .btn-login { width: 100%; background-color: #CDAFFA; color: #fff; border: none; padding: 12px; border-radius: 10px; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .btn-login:hover { background-color: #B689F2; }
        .erro-msg { color: #a00; margin-bottom: 15px; font-weight: bold; font-size: 14px; }
        .teste-info { margin-top:20px; font-size:12px; color:#666; text-align: left; background: #f9f9f9; padding: 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>SmartFlow Login</h2>
        
        <?php if (isset($_GET['erro'])): ?>
            <p class="erro-msg">
                <?php 
                if ($_GET['erro'] == 'usuario') echo "Usuário não encontrado.";
                if ($_GET['erro'] == 'senha') echo "Senha incorreta.";
                if ($_GET['erro'] == '1') echo "Erro ao processar login.";
                ?>
            </p>
        <?php endif; ?>

        <form action="../login.php" method="POST">
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="Digite seu e-mail">
            </div>
            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required placeholder="Digite sua senha">
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
        <div class="teste-info">
            <p><strong>Teste:</strong></p>
            <p>Cliente: cliente@smartflow.com / cliente123</p>
            <p>Admin: admin@smartflow.com / admin123</p>
        </div>
    </div>
</body>
</html>