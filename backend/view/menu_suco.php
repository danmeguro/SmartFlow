<?php
session_start();
// Se não estiver logado, volta para o login
if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fazer Pedido</title>
  <style>
    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); display: flex; justify-content: center; min-height: 100vh; padding: 40px 20px; }
    .suco-container-principal { background: #fff; width: 100%; max-width: 800px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding-bottom: 30px; }
    header { background-color: #CDAFFA; color: #090A0A; text-align: center; padding: 20px; position: relative; }
    .back-btn { position: absolute; left: 25px; top: 25px; background: none; border: none; cursor: pointer; font-size: 16px; font-weight: bold; color: #333; }
    .suco-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; width: 90%; margin: 40px auto 0 auto; }
    .suco-card { background: #F7F4FF; border-radius: 20px; border-left: 6px solid #CDAFFA; padding: 20px; text-align: center; }
    .suco-card img { width: 70px; height: 70px; object-fit: cover; border-radius: 50%; }
    .quantidade-container { display: flex; justify-content: center; gap: 10px; margin-top: 10px; }
    .quantidade-container button { background: #CDAFFA; border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; }
    .pedido-selecionado { background: #F7F4FF; width: 90%; max-width: 600px; border-radius: 15px; margin: 40px auto 0 auto; padding: 20px; text-align: center; }
    .finalizar-btn { background: linear-gradient(90deg, #CDAFFA, #a77bff); border: none; border-radius: 10px; padding: 12px 25px; color: white; cursor: pointer; margin-top: 20px; }
    .input-nome { margin-top: 20px; padding: 10px; width: 70%; border-radius: 10px; border: 1px solid #CDAFFA; text-align: center; }
  </style>
</head>
<body>
  <div class="suco-container-principal">
    <header>
      <button class="back-btn" onclick="window.location.href='../logout.php'">Sair</button>
      <h1>Escolha seus Sucos</h1>
    </header>

    <div class="suco-container">
      <div class="suco-card"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxMGsVTFjjXXAnydIjHsqiM7acSEOraTPysw&s" alt="Laranja"><h3>Laranja</h3>
        <div class="quantidade-container"><button onclick="alt('Laranja', -1)">−</button><span id="qtd-Laranja">0</span><button onclick="alt('Laranja', 1)">+</button></div>
      </div>
      <div class="suco-card"><img src="https://media.istockphoto.com/id/2161126958/vector/2107_grape_purple.jpg?s=612x612&w=0&k=20&c=BgTjkoGeaHuMxomQeZM45VRvJxjB4xbyzRRiUWKOCBA=" alt="Uva"><h3>Uva</h3>
        <div class="quantidade-container"><button onclick="alt('Uva', -1)">−</button><span id="qtd-Uva">0</span><button onclick="alt('Uva', 1)">+</button></div>
      </div>
    </div>

    <div class="pedido-selecionado">
      <h2>Seu Pedido</h2>
      <div id="listaPedido">Nenhum suco selecionado</div>
      <input type="text" id="nomeGarrafa" class="input-nome" placeholder="Nome na garrafa (máx. 15)" maxlength="15">
      <button class="finalizar-btn" onclick="alert('Funcionalidade de pedido em breve!')">Finalizar Pedido</button>
    </div>
  </div>

  <script>
    const pedido = { Laranja: 0, Uva: 0 };
    function alt(s, v) { 
        pedido[s] = Math.max(0, Math.min(4, pedido[s] + v)); 
        document.getElementById(`qtd-${s}`).textContent = pedido[s]; 
        atualizar(); 
    }
    function atualizar() {
        const itens = Object.entries(pedido).filter(([_, q]) => q > 0);
        document.getElementById('listaPedido').innerHTML = itens.length ? itens.map(([s, q]) => `<p>${q}x ${s}</p>`).join('') : "Nenhum suco selecionado";
    }
  </script>
</body>
</html>