<?php
session_start();
// Se não estiver logado, redireciona. 
if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

require_once '../conexao.php';

// 1. BUSCA O PREÇO DO SUCO NO BANCO DE DADOS
$sql_conf = "SELECT valor FROM configuracoes WHERE chave = 'preco_suco'";
$res_conf = $conn->query($sql_conf);
// Define o preço, usando 10.00 como fallback
$preco_suco = 10.00; 
if ($res_conf && $res_conf->num_rows > 0) {
    $preco_suco = floatval($res_conf->fetch_assoc()['valor']);
}

// O preço formatado para exibição
$preco_formatado = number_format($preco_suco, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tela de Sucos</title>
  <style>
    /* ESTILOS ORIGINAIS MANTIDOS */
    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
    body { background: linear-gradient(135deg, #CDAFFA, #E7D4FF); display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; padding: 40px 20px; }
    .suco-container-principal { background: #fff; width: 100%; max-width: 800px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding-bottom: 30px; }
    header { background-color: #CDAFFA; color: #090A0A; text-align: center; padding: 20px 40px; position: relative; }
    header h1 { font-size: 24px; font-weight: 600; }
    .back-btn { position: absolute; left: 25px; top: 25px; background: none; border: none; cursor: pointer; font-size: 16px; font-weight: bold; color: #333; }
    
    /* CENTRALIZA APENAS DOIS CARDS */
    .suco-container { display: flex; justify-content: center; gap: 40px; width: 90%; margin: 40px auto 0 auto; flex-wrap: wrap; }
    
    .suco-card { background: #F7F4FF; border-radius: 20px; border-left: 6px solid #CDAFFA; padding: 20px; text-align: center; width: 180px; transition: transform 0.3s ease; }
    .suco-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    .suco-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
    
    .quantidade-container { display: flex; justify-content: center; gap: 10px; margin-top: 10px; align-items: center; }
    .quantidade-container button { background: #CDAFFA; border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-weight: bold; font-size: 18px; line-height: 1; }
    .quantidade-container span { font-weight: bold; font-size: 18px; color: #333; min-width: 20px; }
    .pedido-selecionado { background: #F7F4FF; width: 90%; max-width: 600px; border-radius: 15px; margin: 40px auto 0 auto; padding: 20px; text-align: center; }
    .finalizar-btn { background: linear-gradient(90deg, #CDAFFA, #a77bff); border: none; border-radius: 10px; padding: 12px 25px; color: white; cursor: pointer; margin-top: 20px; font-weight: bold; font-size: 16px; }
    .input-nome { margin-top: 20px; padding: 10px; width: 70%; border-radius: 10px; border: 1px solid #CDAFFA; text-align: center; font-size: 16px; }
    .feedback-message { margin-top: 15px; padding: 10px; border-radius: 8px; font-weight: bold; display: none; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
    
    /* ESTILO PARA EXIBIR O VALOR TOTAL */
    .total-display { margin-top: 15px; padding-top: 10px; border-top: 2px dashed #CDAFFA; font-size: 22px; font-weight: bold; color: #4A2D9C; }
    .preco-unitario-display { font-size: 16px; margin-top: 5px; font-weight: 500; color: #5E3B76; }

  </style>
</head>
<body>

  <div class="suco-container-principal">
    <header>
      <button class="back-btn" onclick="window.location.href='../logout.php'">Sair</button>
      <h1>Escolha seu Suco</h1>
      <!-- EXIBIÇÃO DO PREÇO UNITÁRIO -->
      <p class="preco-unitario-display">Preço Unitário: R$ <?php echo $preco_formatado; ?></p>
    </header>

    <!-- CONTAINER COM APENAS LARANJA E UVA -->
    <div class="suco-container">
      
      <div class="suco-card">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxMGsVTFjjXXAnydIjHsqiM7acSEOraTPysw&s" alt="Laranja">
        <h3>Laranja</h3>
        <div class="quantidade-container"><button onclick="alt('Laranja', -1)">−</button><span id="qtd-Laranja">0</span><button onclick="alt('Laranja', 1)">+</button></div>
      </div>

      <div class="suco-card">
        <img src="https://media.istockphoto.com/id/2161126958/vector/2107_grape_purple.jpg?s=612x612&w=0&k=20&c=BgTjkoGeaHuMxomQeZM45VRvJxjB4xbyzRRiUWKOCBA=" alt="Uva">
        <h3>Uva</h3>
        <div class="quantidade-container"><button onclick="alt('Uva', -1)">−</button><span id="qtd-Uva">0</span><button onclick="alt('Uva', 1)">+</button></div>
      </div>
    </div>

    <div class="pedido-selecionado">
      <h2>Seu Pedido</h2>
      <div id="listaPedido" style="margin: 15px 0; font-size: 18px; color: #555;">Nenhum suco selecionado</div>
      
      <!-- DISPLAY DO VALOR TOTAL ADICIONADO -->
      <div id="totalPedido" class="total-display">Total: R$ 0,00</div>

      <input type="text" id="nomeGarrafa" class="input-nome" placeholder="Nome na garrafa (máx. 15)" maxlength="15">
      <button class="finalizar-btn" onclick="finalizarPedido()">Finalizar Pedido</button>
      <div id="feedbackMessage" class="feedback-message"></div>
    </div>
  </div>

  <script>
    // 2. VARIÁVEL GLOBAL JAVASCRIPT COM O PREÇO DO BANCO
    const PRECO_UNITARIO = <?php echo $preco_suco; ?>;

    // Pedido agora só contém Laranja e Uva
    const pedido = { Laranja: 0, Uva: 0 };

    function calcularTotal() {
      let totalItens = 0;
      // Soma a quantidade de todos os sucos
      Object.values(pedido).forEach(qtd => {
        totalItens += qtd;
      });
      // Multiplica a quantidade total pelo preço unitário
      return totalItens * PRECO_UNITARIO;
    }

    function atualizarTotal() {
      const total = calcularTotal();
      // Formata para R$ X,XX
      document.getElementById('totalPedido').textContent = `Total: R$ ${total.toFixed(2).replace('.', ',')}`;
    }

    function alt(s, v) { 
        pedido[s] = Math.max(0, Math.min(4, pedido[s] + v)); 
        document.getElementById(`qtd-${s}`).textContent = pedido[s]; 
        atualizarLista(); 
        atualizarTotal(); // <--- CHAMA O CÁLCULO DE TOTAL
    }

    function atualizarLista() {
        const lista = document.getElementById('listaPedido');
        const pedidosFeitos = Object.entries(pedido).filter(([_, q]) => q > 0);
        if (pedidosFeitos.length === 0) { document.getElementById('listaPedido').innerHTML = "Nenhum suco selecionado"; } 
        else { document.getElementById('listaPedido').innerHTML = pedidosFeitos.map(([s, q]) => `<p><b>${q}x</b> ${s}</p>`).join(''); }
    }

    async function finalizarPedido(btn) {
        const nome = document.getElementById('nomeGarrafa').value.trim();
        const itens = Object.entries(pedido).filter(([_, q]) => q > 0).map(([s, q]) => ({sabor: s, quantidade: q}));
        const totalPagar = calcularTotal(); 

        if (!itens.length) return feedback('Selecione pelo menos um suco', 'error');
        if (!nome) return feedback('Por favor, digite seu nome.', 'error');

        const confirmacao = confirm(`Confirma o pedido em nome de ${nome} no valor total de R$ ${totalPagar.toFixed(2).replace('.', ',')}?`);
        if (!confirmacao) { return; }

        btn.disabled = true; btn.textContent = 'Enviando...';
        
        try {
            // Envio AJAX para salvar pedido
            const res = await fetch('../salvar_pedido.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nome: nome || 'Sem Nome', itens: itens})
            });
            const textResponse = await res.text();
            var json = JSON.parse(textResponse);
            
            if (json.success) {
                feedback('✅ Pedido #' + json.pedido_id + ' enviado com sucesso!', 'success');
                setTimeout(() => { alert("Pedido #" + json.pedido_id + " registrado!"); location.reload(); }, 1500);
            } else {
                feedback('Erro: ' + json.message, 'error');
                btn.disabled = false; btn.textContent = 'Finalizar Pedido';
            }
        } catch (e) {
            console.error(e); 
            feedback('Erro de conexão: Verifique o console.', 'error');
            btn.disabled = false; btn.textContent = 'Finalizar Pedido';
        }
        atualizarTotal();
    }
    
    function feedback(msg, tipo) {
        const el = document.getElementById('feedbackMessage');
        el.textContent = msg; el.className = `feedback-message ${tipo}`; el.style.display = 'block';
    }

    // Inicializa o total ao carregar a página
    window.onload = atualizarTotal; 
  </script>
</body>
</html>