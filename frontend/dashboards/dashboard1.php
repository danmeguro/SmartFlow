<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard 1.0</title>
  <style>
    /* RESET */
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

    header a {
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

    .week {
      text-align: center;
      font-family: "Abyssinica SIL", serif;
      font-size: 26px;
      margin: 25px 0 10px;
    }

    hr {
      border: none;
      height: 3px;
      background: #CDAFFA;
      width: 80%;
      margin: 0 auto 30px;
      border-radius: 2px;
    }

    .dashboard-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
      padding: 0 40px 40px;
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

    /* Responsivo */
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
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <header>
      <a href="perfil-adm.html">
        <svg xmlns="http://www.w3.org/2000/svg" width="37" height="36" fill="none" viewBox="0 0 37 36">
          <path d="M6.55206 18.4115L29.6771 18.4115" stroke="#200E32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M15.8788 27.4481L6.55176 18.4121L15.8788 9.37456" stroke="#200E32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
      <h1>Dashboard</h1>
    </header>

    <div class="week">S&nbsp; T&nbsp; Q&nbsp; Q&nbsp; S&nbsp; S&nbsp; D</div>
    <hr>

    <div class="dashboard-list">
      <div class="dashboard-item">
        <div class="dashboard-title">Nome do Dashboard 1:</div>
        <a href="dashboard1.html" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Nome do Dashboard 2:</div>
        <a href="dashboard2.html" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Nome do Dashboard 3:</div>
        <a href="dashboard3.html" class="info-btn">Informações</a>
      </div>

      <div class="dashboard-item">
        <div class="dashboard-title">Nome do Dashboard 4:</div>
        <a href="#" class="info-btn">Informações</a>
      </div>
    </div>
  </div>
</body>
</html>
