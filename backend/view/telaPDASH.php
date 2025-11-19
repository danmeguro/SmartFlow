<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard 1.0</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
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
      max-width: 800px;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      overflow: hidden;
      animation: fadeIn 0.5s ease;
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
    }

    header svg {
      transition: transform 0.2s ease;
    }

    header svg:hover {
      transform: translateX(-5px);
    }

    .perfil-mini {
      position: absolute;
      right: 25px;
      top: 22px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .perfil-mini img {
      width: 26px;
      height: 26px;
      border-radius: 50%;
    }

    .perfil-mini span {
      font-size: 14px;
      color: #2c2c2c;
      font-weight: 500;
    }

    /* Dias da semana */
    .dias {
      display: flex;
      justify-content: space-between;
      margin: 25px 60px 10px;
      font-weight: 600;
      color: #4A2D9C;
    }

    .dias span {
      background: #f2e9ff;
      padding: 10px 14px;
      border-radius: 10px;
      transition: 0.3s;
      cursor: pointer;
      user-select: none;
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

    hr {
      border: none;
      height: 3px;
      background: #CDAFFA;
      width: 80%;
      margin: 10px auto 30px;
      border-radius: 2px;
    }

    /* Área dos dashboards */
    .dashboard-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
      padding: 0 40px 40px;
      max-height: 400px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #CDAFFA #f0f0f0;
    }

    .dashboard-list::-webkit-scrollbar {
      width: 8px;
    }

    .dashboard-list::-webkit-scrollbar-thumb {
      background: #CDAFFA;
      border-radius: 4px;
    }

    .dashboard-item {
      background: #F7F4FF;
      border-left: 6px solid #CDAFFA;
      border-radius: 12px;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: 0.3s;
    }

    .dashboard-item:hover {
      transform: scale(1.02);
      background: #f2e9ff;
    }

    .dashboard-title {
      font-size: 20px;
      font-family: "Abyssinica SIL", serif;
    }

    .info-btn {
      background: #CDAFFA;
      color: #000;
      text-decoration: none;
      padding: 10px 25px;
      border-radius: 10px;
      font-weight: 500;
      transition: 0.3s;
    }

    .info-btn:hover {
      background: #b79ce8;
    }

    @media (max-width: 600px) {
      .dashboard-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .info-btn {
        width: 100%;
        text-align: center;
      }

      .perfil-mini span {
        display: none;
      }

      .dias {
        margin: 20px 25px;
      }
    }
  </style>
</head>
<body>
  <?php session_start(); ?>
  <div class="dashboard-container">
    <header>
      <a href="http://localhost/meusucos/menu.php" class="back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="37" height="36" fill="none" viewBox="0 0 37 36">
          <path d="M6.55206 18.4115L29.6771 18.4115" stroke="#200E32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M15.8788 27.4481L6.55176 18.4121L15.8788 9.37456" stroke="#200E32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>

      <h1>Dashboard</h1>

      <div class="perfil-mini">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Perfil">
        <span>
          <?php 
            echo isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Usuário';
          ?>
        </span>
      </div>
    </header>

    <!-- Dias da Semana -->
    <div class="dias">
      <span>S</span>
      <span>T</span>
      <span>Q</span>
      <span>Q</span>
      <span>S</span>
      <span>S</span>
      <span>D</span>
    </div>
    <hr>

    <!-- Lista de dashboards -->
    <div class="dashboard-list">
      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 1:</div>
        <a href="dashboard1.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 2:</div>
        <a href="dashboard2.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 3:</div>
        <a href="dashboard3.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 4:</div>
        <a href="dashboard4.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 5:</div>
        <a href="dashboard5.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 6:</div>
        <a href="dashboard6.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 7:</div>
        <a href="dashboard7.php" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Dashboard 8:</div>
        <a href="dashboard8.php" class="info-btn">Informações</a>
      </div>
    </div>
  </div>

  <script>
    const dias = document.querySelectorAll('.dias span');
    const hoje = new Date().getDay(); // 0 = Domingo, 1 = Segunda, etc.
    const indice = hoje === 0 ? 6 : hoje - 1; // Ajuste para alinhar com S a D
    dias[indice].classList.add('ativo');

    dias.forEach(dia => {
      dia.addEventListener('click', () => {
        dias.forEach(d => d.classList.remove('ativo'));
        dia.classList.add('ativo');
      });
    });
  </script>
</body>
</html>
