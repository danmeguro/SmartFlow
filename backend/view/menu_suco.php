<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tela de Sucos</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      background: linear-gradient(135deg, #CDAFFA, #E7D4FF);
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 40px 20px;
    }

    .suco-container-principal {
      background: #fff;
      width: 100%;
      max-width: 800px;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      overflow: hidden;
      padding-bottom: 30px;
    }

    header {
      background-color: #CDAFFA;
      color: #090A0A;
      text-align: center;
      padding: 20px 40px;
      position: relative;
    }

    header h1 {
      font-size: 24px;
      font-weight: 600;
    }

    .back-btn {
      position: absolute;
      left: 25px;
      top: 25px;
      text-decoration: none;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 20px;
    }

    .suco-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 20px;
      width: 90%;
      margin: 40px auto 0 auto;
    }

    .suco-card {
      background: #F7F4FF;
      border-radius: 20px;
      border-left: 6px solid #CDAFFA;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 20px;
      text-align: center;
      transition: 0.3s;
    }

    .suco-card:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 14px rgba(0,0,0,0.15);
      background: #f2e9ff;
    }

    .suco-card img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    .suco-card h3 {
      color: #4A2D9C;
      font-size: 1rem;
      margin-top: 5px;
    }

    .quantidade-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 10px 0;
      gap: 10px;
    }

    .quantidade-container button {
      background: #CDAFFA;
      border: none;
      color: white;
      font-size: 18px;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      cursor: pointer;
      transition: 0.3s;
    }

    .quantidade-container button:hover {
      background: #B689F2;
    }

    .quantidade-container span {
      font-size: 18px;
      color: #4A2D9C;
      font-weight: 600;
      min-width: 20px;
      text-align: center;
    }

    .pedido-selecionado {
      background: #F7F4FF;
      width: 90%;
      max-width: 600px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin: 40px auto 0 auto;
      padding: 20px;
      text-align: center;
    }

    .pedido-selecionado h2 {
      color: #4A2D9C;
      margin-bottom: 10px;
    }

    .pedido-lista {
      text-align: left;
      color: #4A2D9C;
      margin-top: 10px;
    }

    .input-nome {
      margin-top: 20px;
      padding: 10px;
      width: 70%;
      border-radius: 10px;
      border: 1px solid #CDAFFA;
      outline: none;
      text-align: center;
    }

    .finalizar-btn {
      background: linear-gradient(90deg, #CDAFFA, #a77bff);
      border: none;
      border-radius: 10px;
      padding: 12px 25px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 20px;
    }

    .finalizar-btn:hover {
      transform: scale(1.05);
      background: linear-gradient(90deg, #b892ff, #8e60ff);
    }
  </style>
</head>
<body>

  <div class="suco-container-principal">
    <header>
      <button class="back-btn" onclick="window.location.href='menu.php'">←</button>
      <h1>Escolha seus Sucos</h1>
    </header>

    <div class="suco-container">
      <div class="suco-card" id="Laranja">
        <img src="https://cdn-icons-png.flaticon.com/512/415/415733.png" alt="Suco de Laranja">
        <h3>Laranja</h3>
        <div class="quantidade-container">
          <button onclick="alterarQuantidade('Laranja', -1)">−</button>
          <span id="qtd-Laranja">0</span>
          <button onclick="alterarQuantidade('Laranja', 1)">+</button>
        </div>
      </div>

      <div class="suco-card" id="Abacaxi">
        <img src="img/suco/abacaxi.png" alt="Suco de Abacaxi">
        <h3>Abacaxi</h3>
        <div class="quantidade-container">
          <button onclick="alterarQuantidade('Abacaxi', -1)">−</button>
          <span id="qtd-Abacaxi">0</span>
          <button onclick="alterarQuantidade('Abacaxi', 1)">+</button>
        </div>
      </div>

      <div class="suco-card" id="Uva">
        <img src="https://cdn-icons-png.flaticon.com/512/415/415732.png" alt="Suco de Uva">
        <h3>Uva</h3>
        <div class="quantidade-container">
          <button onclick="alterarQuantidade('Uva', -1)">−</button>
          <span id="qtd-Uva">0</span>
          <button onclick="alterarQuantidade('Uva', 1)">+</button>
        </div>
      </div>

      <div class="suco-card" id="Mix">
        <img src="https://cdn-icons-png.flaticon.com/512/415/415735.png" alt="Suco Mix">
        <h3>Mix</h3>
        <div class="quantidade-container">
          <button onclick="alterarQuantidade('Mix', -1)">−</button>
          <span id="qtd-Mix">0</span>
          <button onclick="alterarQuantidade('Mix', 1)">+</button>
        </div>
      </div>
    </div>

    <div class="pedido-selecionado">
      <h2>Seu Pedido</h2>
      <div id="listaPedido" class="pedido-lista">Nenhum suco selecionado</div>

      <input type="text" id="nomeGarrafa" class="input-nome" placeholder="Nome na garrafa (máx. 15)" maxlength="15">

      <button class="finalizar-btn" onclick="finalizarPedido()">Finalizar Pedido</button>
    </div>
  </div>

  <script>
    const pedido = {
      Laranja: 0,
      Abacaxi: 0,
      Uva: 0,
      Mix: 0
    };

    function alterarQuantidade(sabor, valor) {
      pedido[sabor] += valor;
      if (pedido[sabor] < 0) pedido[sabor] = 0;
      if (pedido[sabor] > 4) pedido[sabor] = 4;
      document.getElementById(`qtd-${sabor}`).textContent = pedido[sabor];
      atualizarLista();
    }

    function atualizarLista() {
      const lista = document.getElementById('listaPedido');
      const pedidosFeitos = Object.entries(pedido).filter(([_, qtd]) => qtd > 0);
      if (pedidosFeitos.length === 0) {
        lista.textContent = "Nenhum suco selecionado";
        return;
      }
      lista.innerHTML = pedidosFeitos
        .map(([sabor, qtd]) => `<p>${qtd}x ${sabor}</p>`)
        .join('');
    }

    function finalizarPedido() {
      const nome = document.getElementById('nomeGarrafa').value.trim();
      const pedidosFeitos = Object.entries(pedido).filter(([_, qtd]) => qtd > 0);

      if (pedidosFeitos.length === 0) {
        alert("Selecione pelo menos um suco antes de finalizar.");
        return;
      }

      let resumo = pedidosFeitos.map(([sabor, qtd]) => `${qtd}x ${sabor}`).join(', ');
      alert(`Pedido Finalizado:\n${resumo}\nNome na garrafa: ${nome || 'sem nome'}`);

      window.location.href = 'fila_pedidos.php';
    }
  </script>

</body>
</html>
