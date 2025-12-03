<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #CDAFFA, #E9D5FF);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }

        h2 {
            color: #5E3B76;
            margin-bottom: 25px;
            font-size: 26px;
        }

        .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        label {
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-top: 5px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #CDAFFA;
            box-shadow: 0 0 5px rgba(205, 175, 250, 0.7);
        }

        .btn-login {
            width: 100%;
            background-color: #CDAFFA;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #B689F2;
        }

        .adm-link {
            margin-top: 40px;
            font-size: 15px;
            color: #5E3B76;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: color 0.3s ease;
        }

        .adm-link:hover {
            color: #B689F2;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
            }

            h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Bem-vindo(a)</h2>

        <!-- Botão de login comum -->
        <form action="http://localhost/meusucos/menu_suco.php" method="POST">
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>
            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <!-- Link do ADM -->
        <a href="http://localhost/meusucos/telaPDASH.php" class="adm-link">Entrar como ADM</a>
    </div>

</body>
</html>
