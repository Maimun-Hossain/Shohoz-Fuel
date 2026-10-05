<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];


$tokenResult = $conn->query("SELECT t.*, s.name as station_name FROM tokens t JOIN stations s ON t.station_id = s.id WHERE t.user_id=$user_id AND t.status IN ('Pending', 'Called') LIMIT 1");
$activeToken = $tokenResult->fetch_assoc();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fuel Crisis</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="../profile-menu.css" />
    <style>
      body{ 
        width: 100%; 
        overflow-x: hidden; 
        background-color: #0f051a;
        font-family: "Segoe UI";
        color: #ffffff;
        min-height: 100vh;
      }
      
      *{
        margin: 0;
        padding: 0;
        box-sizing: border-box; 
      }
      .navbar{
        background-color: #302b3f;
        padding: 14px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        width: 100%;
        box-sizing: border-box;
      }

      .navbar .logo{
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 2px;
        color: #ffffff;
        max-width: 200px;
      }

      .hamburger{
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
        background: none;
        border: none;
        padding: 5px;
      }

      .hamburger span{
        width: 25px;
        height: 3px;
        background-color: white;
        border-radius: 2px;
      }

      #user-avatar{
        width: 50px;
        height: 50px;
        border-radius: 50%;
      }

      .avatar-container{
        position: relative;
        display: inline-block;
        cursor: pointer;
      }

      .dropdown-menu{
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background-color: #302b3f;
        min-width: 150px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 10px;
        border: 1px solid #4a435d;
      }

      .dropdown-menu.show{
        display: block;
      }

      .dropdown-menu a{
        color: #ffffff;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        transition: background-color 0.3s;
      }

      .dropdown-menu a:hover{
        background-color: #4a435d;
      }

      .navbar .nav-links{
        display: flex;
        align-items: center;
        gap: 24px;
        list-style: none;
      }

      .navbar .nav-links a{
        text-decoration: none;
        color: #cccccc;
        font-size: 14px;
      }

      .navbar .nav-links a:hover{
        color: #ffffff;
      }

      .btn-request{
        background-color: #e8e8e8;
        color: #1a1a2e;
        border: none;
        border-radius: 8px;
        padding: 12px 28px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
      }

      .btn-request:hover{
        background-color: #ffffff;
      }

      footer{
        margin-top: 40px;
        background-color: #302b3f;
        color: #d2cee0;
        border-top: 1px solid #4a435d;
      }

      .footer-wrap{
        width: 96%;
        margin: 0 auto;
        padding: 34px 8px 18px;
      }

      .footer-top{
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 22px;
      }

      .footer-brand h3{
        letter-spacing: 1px;
        margin-bottom: 10px;
      }

      .footer-brand p{
        color: #c8c3d7;
        max-width: 380px;
        font-size: 0.95rem;
      }

      .social-row{
        margin-top: 14px;
        display: flex;
        gap: 10px;
      }

      .social-btn{
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid #5a526c;
        color: #ffffff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 700;
        background-color: #1a1328;
      }

      .social-btn:hover{
        background-color: #e8e8e8;
        color: #1a1a2e;
      }

      .footer-col h4{
        margin-bottom: 10px;
        color: #ffffff;
      }

      .footer-links{
        list-style: none;
      }

      .footer-links li{
        margin-bottom: 8px;
      }

      .footer-links a{
        color: #d2cee0;
        text-decoration: none;
        font-size: 0.95rem;
      }

      .footer-links a:hover{
        color: #ffffff;
      }

      .footer-bottom{
        margin-top: 22px;
        border-top: 1px solid #4a435d;
        padding-top: 14px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 0.9rem;
        color: #bcb7cb;
      }

      @media (max-width: 850px){
        .navbar{
          flex-direction: row;
          justify-content: space-between;
          padding: 15px 30px;
          text-align: left;
        }

        .hamburger{
          display: flex;
        }

        .navbar .nav-links{
          display: none;
          flex-direction: column;
          position: absolute;
          top: 100%;
          left: 0;
          width: 100%;
          background-color: #302b3f;
          padding: 20px 0;
          gap: 15px;
          z-index: 1000;
          border-bottom: 1px solid #4a435d;
          flex-wrap: nowrap;
          justify-content: flex-start;
        }

        .navbar .nav-links.active{
          display: flex;
        }

        .banner-1{
          padding: 20px 0;
        }

        .title-1{
          width: 100%;
          padding-left: 0;
          text-align: center;
          height: auto;
          margin-bottom: 20px;
        }

        .card-container{
          flex-direction: column;
          align-items: center;
        }

        .card-1, .card-2{
          width: 90% !important;
          margin-left: 0 !important;
          margin-right: 0 !important;
          margin-top: 20px !important;
          padding: 30px 20px !important;
          height: auto !important;
        }

        .footer-top{
          grid-template-columns: 1fr;
        }

        .footer-bottom{
          flex-direction: column;
        }

        .dropdown-menu{
          position: static;
          width: 100%;
          box-shadow: none;
          border: none;
          margin-top: 0;
          background-color: transparent;
        }

        .dropdown-menu a{
          padding-left: 0;
          color: #cccccc;
        }
      }
    </style>
  </head>
  <body>
    <nav class="navbar">
      <span class="logo">FUEL CRISIS</span>
      <button class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="../../home.php">Home</a></li>
        <li><a href="./index.php">Dashboard</a></li>
        <li><a href="../USER STATION/index.php">Stations</a></li>
        <li><a href="../USER TOKEN/user-token-page.php">Token</a></li>
        <li>
          <?php $profileReturnPath = 'USER HOME/index.php'; include '../profile-menu.php'; ?>
        </li>
      </ul>
    </nav>
    <section class="banner-1">
      <h2 class="title-1">Welcome, <?php echo ($user_name); ?></h2>
      <div class="card-container">
        <div class="card-1">
          <h2>Your Active Tokens</h2>
          <?php if($activeToken): ?>
            <p class="card-text1" style="margin: 10px 0;">
                Station: <strong><?php echo ($activeToken['station_name']); ?></strong><br>
                Serial: <strong>#<?php echo $activeToken['serial_number']; ?></strong><br>
                Status: <strong style="color: <?php 

                  if($activeToken['status'] == 'Called'){
                    echo '#10B981';
                  }
                  else{
                    echo '#FBBF24';
                  }
                ?>"><?php echo $activeToken['status']; ?></strong>
            </p>
          <?php else: ?>
            <p class="card-text1">No active tokens.</p>
          <?php endif; ?>
          <a href="../USER TOKEN/user-token-page.php"><button class="btn-request">Manage Tokens</button></a>
        </div>

        <div class="card-2">
          <h2>Fuel Stations</h2>
          <p class="card-text2">
            Check real-time availability and<br />
            nearby stations.
          </p>
          <a href="../USER STATION/index.php"><button class="btn-request">View Stations</button></a>
        </div>
      </div>
    </section>

    <footer>
      <div class="footer-wrap">
        <div class="footer-top">
          <div class="footer-brand">
            <h3>FUEL CRISIS</h3>
            <p>
              A smarter way to find stations, reduce waiting time, and track
              your fuel token in real time.
            </p>
            <div class="social-row">
              <a class="social-btn" href="#" aria-label="Facebook">FB</a>
              <a class="social-btn" href="#" aria-label="Instagram">IG</a>
              <a class="social-btn" href="#" aria-label="Twitter">TW</a>
            </div>
          </div>

          <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links">
              <li><a href="../../home.php">Dashboard</a></li>
              <li><a href="../USER STATION/index.php">Stations</a></li>
              <li><a href="../USER TOKEN/user-token-page.php">Token</a></li>
              <li>
                <a href="../../Registration_Page/SignIn/index.php">Sign In</a>
              </li>
            </ul>
          </div>

          <div class="footer-col">
            <h4>Legal & Support</h4>
            <ul class="footer-links">
              <li><a href="#">Terms and Conditions</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Cookie Policy</a></li>
              <li><a href="#">Help Center</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; 2026 Fuel Crisis. All rights reserved.</p>
          <p>Built for faster and fairer fuel access.</p>
        </div>
      </div>
    </footer>
    <script>
      const hamburger = document.getElementById('hamburger');
      const navLinks = document.getElementById('nav-links');
      if(hamburger && navLinks){
        hamburger.addEventListener('click', function(){
          navLinks.classList.toggle('active');
        });
      }

    </script>
    <script src="../profile-menu.js" defer></script>
  </body>
</html>