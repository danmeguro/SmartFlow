<?php
session_start();
// Proteção: Se não estiver logado, volta pro login
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
  <title>Fazer Pedido - SmartFlow</title>
  <style>
    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
    
    body { 
        background: linear-gradient(135deg, #CDAFFA, #E7D4FF); 
        display: flex; 
        justify-content: center; 
        min-height: 100vh; 
        padding: 40px 20px; 
    }
    
    .suco-container-principal { 
        background: #fff; 
        width: 100%; 
        max-width: 800px; 
        border-radius: 20px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
        padding-bottom: 30px; 
    }
    
    header { 
        background-color: #CDAFFA; 
        color: #090A0A; 
        text-align: center; 
        padding: 20px; 
        position: relative; 
    }
    
    .back-btn { 
        position: absolute; 
        left: 25px; 
        top: 25px; 
        background: none; 
        border: none; 
        cursor: pointer; 
        font-size: 16px; 
        font-weight: bold; 
        color: #333; 
    }
    
    .suco-container { 
        display: flex; 
        justify-content: center;
        gap: 40px; 
        width: 90%; 
        margin: 40px auto 0 auto; 
        flex-wrap: wrap;
    }
    
    .suco-card { 
        background: #F7F4FF; 
        border-radius: 20px; 
        border-left: 6px solid #CDAFFA; 
        padding: 20px; 
        text-align: center; 
        width: 180px; 
        transition: transform 0.3s ease;
    }

    .suco-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    }
    
    .suco-card img { 
        width: 100px; 
        height: 100px; 
        object-fit: cover; 
        border-radius: 50%; 
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 10px;
    }
    
    .quantidade-container { display: flex; justify-content: center; gap: 10px; margin-top: 10px; align-items: center; }
    
    .quantidade-container button { 
        background: #CDAFFA; 
        border: none; 
        color: white; 
        width: 30px; 
        height: 30px; 
        border-radius: 50%; 
        cursor: pointer; 
        font-weight: bold;
        font-size: 18px;
        line-height: 1;
    }

    .quantidade-container span {
        font-weight: bold;
        font-size: 18px;
        color: #333;
        min-width: 20px;
    }
    
    .pedido-selecionado { 
        background: #F7F4FF; 
        width: 90%; 
        max-width: 600px; 
        border-radius: 15px; 
        margin: 40px auto 0 auto; 
        padding: 20px; 
        text-align: center; 
    }
    
    .finalizar-btn { 
        background: linear-gradient(90deg, #CDAFFA, #a77bff); 
        border: none; 
        border-radius: 10px; 
        padding: 12px 25px; 
        color: white; 
        cursor: pointer; 
        margin-top: 20px; 
        font-weight: bold;
        font-size: 16px;
    }
    
    .input-nome { 
        margin-top: 20px; 
        padding: 10px; 
        width: 70%; 
        border-radius: 10px; 
        border: 1px solid #CDAFFA; 
        text-align: center; 
        font-size: 16px;
    }

    /* Feedback visual */
    .feedback-message { margin-top: 15px; padding: 10px; border-radius: 8px; font-weight: bold; display: none; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }

  </style>
</head>
<body>
  <div class="suco-container-principal">
    <header>
      <button class="back-btn" onclick="window.location.href='../logout.php'">Sair</button>
      <h1>Escolha seu Suco</h1>
    </header>

    <div class="suco-container">
      
      <!-- Card LARANJA -->
      <div class="suco-card">
        <!-- Imagem Local -->
        <img src="img/laranja.jpg" alt="Laranja" onerror="this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxMGsVTFjjXXAnydIjHsqiM7acSEOraTPysw&s'">
        <h3>Laranja</h3>
        <div class="quantidade-container">
            <button onclick="alt('Laranja', -1)">−</button>
            <span id="qtd-Laranja">0</span>
            <button onclick="alt('Laranja', 1)">+</button>
        </div>
      </div>

      <!-- Card UVA -->
      <div class="suco-card">
        <!-- Imagem Local -->
        <img src="img/uva.jpg" alt="Uva" onerror="this.src='https://media.istockphoto.com/id/2161126958/vector/2107_grape_purple.jpg?s=612x612&w=0&k=20&c=BgTjkoGeaHuMxomQeZM45VRvJxjB4xbyzRRiUWKOCBA='">
        <h3>Uva</h3>
        <div class="quantidade-container">
            <button onclick="alt('Uva', -1)">−</button>
            <span id="qtd-Uva">0</span>
            <button onclick="alt('Uva', 1)">+</button>
        </div>
      </div>

    </div>

    <div class="pedido-selecionado">
      <h2>Seu Pedido</h2>
      <div id="listaPedido" style="margin: 15px 0; font-size: 18px; color: #555;">
        Nenhum suco selecionado
      </div>
      
      <input type="text" id="nomeGarrafa" class="input-nome" placeholder="Nome na garrafa (máx. 15)" maxlength="15">
      
      <button class="finalizar-btn" onclick="finalizar(this)">Finalizar Pedido</button>
      
      <div id="feedbackMessage" class="feedback-message"></div>
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
        
        if (itens.length === 0) {
            document.getElementById('listaPedido').innerHTML = "Nenhum suco selecionado";
        } else {
            document.getElementById('listaPedido').innerHTML = itens.map(([s, q]) => `<p><b>${q}x</b> ${s}</p>`).join('');
        }
    }

    async function finalizar(btn) {
        const nome = document.getElementById('nomeGarrafa').value.trim();
        
        // Cria um array com os itens que têm quantidade > 0
        const itensParaEnviar = Object.entries(pedido)
            .filter(([sabor, quantidade]) => quantidade > 0)
            .map(([sabor, quantidade]) => ({ sabor: sabor, quantidade: quantidade }));
        
        // Validação no frontend
        if (itensParaEnviar.length === 0) {
            return feedback('Selecione pelo menos um suco!', 'error');
        }
        
        // Bloqueia botão para evitar duplo clique
        btn.disabled = true; 
        btn.textContent = 'Enviando...';
        
        try {
            // Envia para o backend (AJAX)
            // Note o caminho '../salvar_pedido.php' pois estamos em view/
            const res = await fetch('../salvar_pedido.php', {
                method: 'POST', 
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    nome: nome, 
                    itens: itensParaEnviar
                })
            });
            
            // Tenta ler a resposta como JSON
            const textResponse = await res.text(); // Pega texto primeiro para debug se precisar
            try {
                var json = JSON.parse(textResponse);
            } catch (e) {
                throw new Error("Erro ao ler JSON: " + textResponse);
            }
            
            if (json.success) {
                feedback('✅ Pedido #' + json.pedido_id + ' enviado com sucesso!', 'success');
                
                // Reseta tudo após 2 segundos
                setTimeout(() => {
                    location.reload(); 
                }, 2000);
            } else {
                feedback('Erro: ' + json.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Finalizar Pedido';
            }
        } catch (e) {
            console.error(e);
            feedback('Erro de conexão: Verifique o console.', 'error');
            btn.disabled = false;
            btn.textContent = 'Finalizar Pedido';
        }
    }

    function feedback(msg, tipo) {
        const el = document.getElementById('feedbackMessage');
        el.textContent = msg; 
        el.className = `feedback-message ${tipo}`; 
        el.style.display = 'block';
    }
  </script>
</body>
</html>