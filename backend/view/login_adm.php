<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Perfil ADM</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg-grad-start:#CDAFFA;
      --bg-grad-mid:#DCC6FE;
      --bg-grad-end:#E5D7FA;
      --accent:#200E32;
      --card-border: #A9A6A6;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family:Poppins, Arial, sans-serif;background:#fff}

    .canvas{width:411px;height:731px;max-width:100%;margin:18px auto;position:relative;border-radius:8px;overflow:hidden;background:#fff;box-shadow:0 6px 20px rgba(0,0,0,0.06)}

    .top-blob{position:absolute;left:-8px;top:-9px;width:430px;height:120px;background:linear-gradient(180deg,var(--bg-grad-start) 5%,var(--bg-grad-mid) 21%,#ECE4F9 67%,#E5D7FA 95%);border-radius:14px}

    .back{position:absolute;left:33px;top:32px;width:37px;height:36px;display:flex;align-items:center;justify-content:center;border:none;background:none;cursor:pointer}
    .back svg{display:block}

    .title{position:absolute;left:0;right:0;top:41px;text-align:center;color:#090A0A;font-weight:500;font-size:18px}

    .avatar{position:absolute;left:129px;top:122px;width:152px;height:152px;border-radius:50%;background:linear-gradient(180deg,#fff,#f2f2f2);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(0,0,0,0.08);border:6px solid white}
    .avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%}

    .name-email{position:absolute;left:0;right:0;top:290px;text-align:center;font-family:'Abyssinica SIL', serif;color:#000;font-size:25px}

    .search-wrap{position:absolute;left:29px;top:414px;width:234.54px;height:36.82px;border-radius:10px;border:2px solid rgba(161,173,173,1);box-shadow:0 4px 4px rgba(0,0,0,0.25);display:flex;align-items:center;padding:8px 12px;background:rgba(255,255,255,0)}
    .search-wrap .icon{width:16px;height:18px;margin-right:10px}
    .search-wrap input{border:0;outline:0;font-size:15px;color:#333;background:transparent}

    .edit{position:absolute;left:298px;top:423px;font-size:16px;color:rgba(28,36,57,0.5)}
    .edit-icon{position:absolute;left:359px;top:418px;width:24px;height:24px}

    /* Container rolável dos dashboards */
    .dash-container{
      position:absolute;
      left:0;
      right:0;
      top:470px;
      bottom:20px;
      overflow-y:auto;
      padding:0 34px;
      scroll-behavior:smooth;
    }

    .dash-container::-webkit-scrollbar{
      width:6px;
    }
    .dash-container::-webkit-scrollbar-thumb{
      background:#A9A6A6;
      border-radius:4px;
    }

    .card{height:63px;border-radius:10px;border:2px solid var(--card-border);box-shadow:0 4px 4px rgba(0,0,0,0.25);display:flex;align-items:center;padding:12px 16px;margin-bottom:14px;background:transparent;cursor:pointer;transition:transform 0.2s}
    .card:hover{transform:scale(1.03)}
    .card .texts{flex:1}
    .card .title-lg{font-family:'Abyssinica SIL', serif;font-size:20px}
    .card .title-sm{font-family:'Abyssinica SIL', serif;font-size:16px;display:block}
    .chev{width:11px;height:16px}

    @media (max-width:440px){
      .canvas{transform:scale(0.95);transform-origin:top center}
    }
  </style>
</head>
<body>
  <div class="canvas" role="application" aria-label="Perfil ADM">

    <div class="top-blob" aria-hidden="true"></div>

    <button class="back" aria-label="Voltar" onclick="window.history.back()">
      <svg width="37" height="36" viewBox="0 0 37 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6.55208 18.4115L29.6771 18.4115" stroke="#200E32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M15.8789 27.4481L6.55178 18.4121L15.8789 9.37455" stroke="#200E32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <div class="title">ADM</div>

    <div class="avatar">
      <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="60" cy="40" r="28" fill="#F3E8FF" />
        <path d="M16 100c0-24 22-40 44-40s44 16 44 40" fill="#EDE6FB" />
      </svg>
    </div>

    <div class="name-email">Nome<br/>Email</div>

    <div class="search-wrap" role="search" aria-label="Pesquisar">
      <div class="icon" aria-hidden="true">
        <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.0761 0.157074C5.75654 0.157575 4.46674 0.627585 3.36978 1.50768C2.27281 2.38777 1.41794 3.63841 0.913259 5.10149C0.408577 6.56456 0.276745 8.17436 0.534433 9.72734C0.792121 11.2803 1.42776 12.7067 2.36097 13.8262C3.29418 14.9457 4.48307 15.708 5.7773 16.0168C7.07153 16.3255 8.41298 16.1668 9.63205 15.5607C10.8511 14.9546 11.8931 13.9284 12.6261 12.6118C13.3592 11.2951 13.7504 9.74722 13.7504 8.16376C13.7496 6.04012 13.0461 4.00379 11.7945 2.50239C10.543 1.001 8.84579 0.15741 7.0761 0.157074ZM15.2498 16.7756L11.9376 12.8011C11.889 12.7406 11.8307 12.6923 11.7663 12.6591C11.702 12.626 11.6327 12.6085 11.5626 12.6078C11.4926 12.607 11.4231 12.623 11.3582 12.6549C11.2934 12.6867 11.2344 12.7338 11.1849 12.7932C11.1353 12.8527 11.0961 12.9234 11.0696 13.0012C11.0431 13.079 11.0297 13.1624 11.0303 13.2465C11.0309 13.3306 11.0455 13.4137 11.0732 13.491C11.1008 13.5682 11.141 13.6381 11.1914 13.6965L14.5035 17.6692C14.5522 17.7296 14.6104 17.7779 14.6748 17.8111C14.7392 17.8443 14.8085 17.8617 14.8785 17.8625C14.9486 17.8632 15.0181 17.8472 15.083 17.8153C15.1478 17.7835 15.2067 17.7365 15.2563 17.677C15.3058 17.6176 15.345 17.5468 15.3716 17.469C15.3981 17.3912 15.4115 17.3078 15.4108 17.2237C15.4102 17.1396 15.3957 17.0565 15.368 16.9793C15.3404 16.902 15.3002 16.8321 15.2498 16.7737V16.7756Z" fill="#A8A8A8"/>
        </svg>
      </div>
      <input type="search" placeholder="Pesquisar" aria-label="campo pesquisar">
    </div>

    <div class="edit">Editar</div>
    <div class="edit-icon" aria-hidden="true">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" fill="#A9A6A6"/>
        <path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="#A9A6A6"/>
      </svg>
    </div>

    <!-- DASHBOARDS ROLÁVEIS -->
    <div class="dash-container">
      <div class="card" onclick="window.location.href='dashboards/dashboard1.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 1</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard2.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 2</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard3.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 3</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard4.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 4</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard5.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 5</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard6.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 6</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard7.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 7</span></div>
        <div class="chev">▶</div>
      </div>
      <div class="card" onclick="window.location.href='dashboards/dashboard8.html'">
        <div class="texts"><span class="title-lg">Dashboard</span><span class="title-sm">Sensor 8</span></div>
        <div class="chev">▶</div>
      </div>
    </div>

  </div>
</body>
</html>