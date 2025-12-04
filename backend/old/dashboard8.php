<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
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

    .dashboard-container {
      background: #fff;
      width: 100%;
      max-width: 700px;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      overflow: hidden;
      padding-bottom: 30px;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
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
      color: #090A0A;
    }

    .conteudo {
      width: 90%;
      margin: 30px auto;
    }

    .dias {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-weight: 600;
      color: #4A2D9C;
    }

    .dias span {
      background: #f2e9ff;
      padding: 10px 12px;
      border-radius: 10px;
      transition: 0.3s;
      cursor: pointer;
    }

    .dias span:hover {
      background: #e3d1ff;
      transform: scale(1.05);
    }

    .dias .ativo {
      background: linear-gradient(90deg, #CDAFFA, #a77bff);
      color: white;
      font-weight: bold;
      transform: scale(1.05);
    }

    .data-container,
    .nome-dashboard,
    .resumo {
      margin-bottom: 20px;
    }

    label {
      display: block;
      color: #4A2D9C;
      font-weight: 500;
      margin-bottom: 8px;
    }

    input[type="date"],
    select,
    textarea {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #CDAFFA;
      outline: none;
      font-size: 15px;
      color: #4A2D9C;
      background: #F7F4FF;
      transition: 0.3s;
    }

    input[type="date"]:focus,
    select:focus,
    textarea:focus {
      border-color: #a77bff;
      background: #f2e9ff;
    }

    .grafico {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 25px 0;
    }

    .grafico img {
      width: 80%;
      max-width: 400px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    textarea {
      height: 120px;
      resize: none;
    }

    .salvar-btn {
      background: linear-gradient(90deg, #CDAFFA, #a77bff);
      border: none;
      border-radius: 10px;
      padding: 12px 25px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      width: 100%;
      font-size: 16px;
    }

    .salvar-btn:hover {
      transform: scale(1.05);
      background: linear-gradient(90deg, #b892ff, #8e60ff);
    }

  </style>
</head>
<body>

  <div class="dashboard-container">
    <header>
      <button class="back-btn" onclick="window.location.href='http://localhost/meusucos/telaPDASH.php'">←</button>

      <h1>Dashboard</h1>
    </header>

    <div class="conteudo">
      <div class="dias">
        <span>S</span>
        <span>T</span>
        <span>Q</span>
        <span>Q</span>
        <span>S</span>
        <span>S</span>
        <span>D</span>
      </div>

      <div class="data-container">
        <label for="data">Selecionar data:</label>
        <input type="date" id="data">
      </div>

      <div class="nome-dashboard">
        <label for="dash">Nome do Dashboard:</label>
        <select id="dash">
          <option>Dashboard 1</option>
          <option>Dashboard 2</option>
          <option>Dashboard 3</option>
        </select>
      </div>

      <div class="grafico">
        <img src="grafico.png" alt="Gráfico">
      </div>

      <div class="resumo">
        <label for="resumo">Resumo do Dashboard:</label>
        <textarea id="resumo" placeholder="Descreva aqui o que está acontecendo no dashboard..."></textarea>
      </div>

      <button class="salvar-btn" onclick="alert('Resumo salvo com sucesso!')">Salvar Alterações</button>
    </div>
  </div>

  <script>
    // Marca automaticamente o dia atual da semana
    const dias = document.querySelectorAll('.dias span');
    const hoje = new Date().getDay(); // 0=Dom, 1=Seg, 2=Ter, ...
    const indice = hoje === 0 ? 6 : hoje - 1; // Ajuste para alinhar S a Dom
    dias[indice].classList.add('ativo');

    // Permite selecionar outro dia manualmente
    dias.forEach(dia => {
      dia.addEventListener('click', () => {
        dias.forEach(d => d.classList.remove('ativo'));
        dia.classList.add('ativo');
      });
    });
  </script>

</body>
</html>
