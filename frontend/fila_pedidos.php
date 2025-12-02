<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fila de Pedidos</title>
  <style>
    body {
      background: linear-gradient(135deg, #CDAFFA, #E7D4FF);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      font-family: 'Poppins', sans-serif;
      margin: 0;
    }

    .container {
      background: #fff;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      text-align: center;
      width: 90%;
      max-width: 500px;
    }

    h1 {
      color: #4A2D9C;
      margin-bottom: 15px;
    }

    p {
      font-size: 18px;
      color: #333;
      margin: 10px 0;
    }

    .emoji {
      font-size: 50px;
      margin: 15px 0;
    }

    .btn-voltar {
      background-color: #CDAFFA;
      color: #fff;
      border: none;
      padding: 12px 25px;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 25px;
      transition: 0.3s;
    }

    .btn-voltar:hover {
      background-color: #B689F2;
      transform: scale(1.05);
    }
  </style>
</head>
<body>

  <div class="container">
    <?php
      $sabor = htmlspecialchars($_GET['sabor'] ?? 'Não informado');
      $quantidade = htmlspecialchars($_GET['quantidade'] ?? '1');
      $nome = htmlspecialchars($_GET['nome'] ?? '');
      $fila = htmlspecialchars($_GET['fila'] ?? '0');
    ?>

    <h1>Pedido Confirmado! 🍹</h1>
    <p><strong>Suco:</strong> <?php echo $sabor; ?> (<?php echo $quantidade; ?>)</p>
    <?php if ($nome): ?>
      <p><strong>Nome na garrafa:</strong> <?php echo $nome; ?></p>
    <?php endif; ?>

    <div class="emoji">⏳</div>
    <p>Há <strong><?php echo $fila; ?></strong> pessoa(s) na sua frente.</p>
    <p>Aguarde enquanto preparamos seu suco!</p>

    <button class="btn-voltar" onclick="window.location.href='menu.php'">Voltar ao Menu</button>
  </div>

</body>
</html>
