<?php
// SmartFlow/backend/view/home_admin.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['papel'] !== 'administrador') {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Admin - SmartFlow</title>
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { 
            background: linear-gradient(135deg, #CDAFFA, #E7D4FF); 
            height: 100vh; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
        }
        
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 90%;
        }

        h1 { color: #5E3B76; margin-bottom: 30px; }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .menu-btn {
            background: #F7F4FF;
            border: 2px solid #CDAFFA;
            border-radius: 15px;
            padding: 30px 20px;
            text-decoration: none;
            color: #4A2D9C;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 150px;
        }

        .menu-btn:hover {
            background: #CDAFFA;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .menu-btn span { font-size: 30px; }

        .btn-sair {
            margin-top: 30px;
            display: inline-block;
            color: #ff6b6b;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Área Administrativa</h1>
        
        <div class="menu-grid">
            <!-- Botão 1: Gestão de Pedidos -->
            <a href="gestao_pedidos.php" class="menu-btn">
                <span>📋</span>
                Gestão de Pedidos
            </a>

            <!-- Botão 2: Telemetria -->
            <a href="telemetria.php" class="menu-btn">
                <span>📡</span>
                Painel Sensores
            </a>

            <!-- Botão 3: Comercial (NOVO) -->
            <a href="dashboard_comercial.php" class="menu-btn">
                <span>💰</span>
                Dash. Comercial
            </a>
        </div>

        <a href="../logout.php" class="btn-sair">Sair do Sistema</a>
    </div>

</body>
</html>