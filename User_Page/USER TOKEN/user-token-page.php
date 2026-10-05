<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$tokenResult = $conn->query("SELECT t.*, s.name as station_name, s.location as station_location FROM tokens t JOIN stations s ON t.station_id = s.id WHERE t.user_id=$user_id AND t.status IN ('Pending', 'Called') LIMIT 1");
$activeToken = $tokenResult->fetch_assoc();

$peopleAhead = 0;
$currentlyServing = "-";

if($activeToken){
    $s_id = $activeToken['station_id'];
    $my_serial = $activeToken['serial_number'];
    
    $aheadResult = $conn->query("SELECT COUNT(*) as count FROM tokens WHERE station_id=$s_id AND status='Pending' AND serial_number < $my_serial AND DATE(created_at) = CURDATE()");
    $peopleAhead = $aheadResult->fetch_assoc()['count'];

    $servingResult = $conn->query("SELECT serial_number FROM tokens WHERE station_id=$s_id AND status='Called' AND DATE(created_at) = CURDATE() ORDER BY serial_number ASC LIMIT 1");
    if($servingRow = $servingResult->fetch_assoc()){
        $currentlyServing = "#" . $servingRow['serial_number'];
    }
}

$historyResult = $conn->query("SELECT t.*, s.name as station_name, c.car_id AS registered_car_id FROM tokens t JOIN stations s ON t.station_id = s.id LEFT JOIN cars c ON c.user_id = t.user_id AND c.car_id = t.car_id WHERE t.user_id=$user_id AND t.status = 'Completed' ORDER BY t.created_at DESC");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Token</title>
    <link rel="stylesheet" href="../profile-menu.css" />

    <style>
      *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body{
        background-color: #0f051a;
        font-family: "Segoe UI";
        color: #ffffff;
        min-height: 100vh;
        overflow-x: hidden;
      }

      .navbar{
        background-color: #302b3f;
        padding: 14px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
      }

      .navbar .logo{
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 2px;
        color: #ffffff;
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

      .main-content{
        padding: 40px 30px;
        padding-bottom: 110px;
      }

      .page-title{
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #ffffff;
      }

      .token-card{
        background-color: #302b3f;
        border: 1px solid #a28eab;
        border-radius: 16px;
        padding: 40px 30px;
        width: 100%;
        max-width: 600px;
        text-align: left;
        margin-bottom: 30px;
      }

      .token-card h2{
        font-size: 20px;
        margin-bottom: 15px;
        color: #A28EAB;
      }

      .token-card p{
        color: #eeeeee;
        font-size: 16px;
        margin-bottom: 10px;
      }

      .token-card .status-badge{
        display: inline-block;
        padding: 5px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 14px;
        margin-top: 10px;
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
        text-decoration: none;
        display: inline-block;
      }

      .btn-request:hover{
        background-color: #ffffff;
      }

      .history-table{
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: #1a1328;
        border-radius: 12px;
        overflow: hidden;
      }

      .history-table th, .history-table td{
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #4a435d;
      }

      .history-table th{
        background: #302b3f;
        color: #A28EAB;
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
        }

        .navbar .nav-links.active{
          display: flex;
        }

        .main-content{
          padding: 20px 15px;
          padding-bottom: 40px;
        }

        .token-card{
          width: 100%;
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
    <?php if(isset($_GET['msg'])): ?>
        <script>alert("<?php echo htmlspecialchars($_GET['msg']); ?>");</script>
    <?php endif; ?>

    <nav class="navbar">
      <span class="logo">FUEL CRISIS</span>
      <button class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="../../home.php">Home</a></li>
        <li><a href="../USER HOME/index.php">Dashboard</a></li>
        <li><a href="../USER STATION/index.php">Stations</a></li>
        <li><a href="./user-token-page.php">Token</a></li>
        <li>
          <?php $profileReturnPath = 'USER TOKEN/user-token-page.php'; include '../profile-menu.php'; ?>
        </li>
      </ul>
    </nav>

    <div class="main-content">
      <h1 class="page-title">Token Management</h1>

      <?php if($activeToken): ?>
        <div class="token-card">
          <h2>Active Token: #<?php echo $activeToken['serial_number']; ?></h2>
          <p>Station: <strong><?php echo htmlspecialchars($activeToken['station_name']); ?></strong></p>
          <p>Location: <strong><?php echo htmlspecialchars($activeToken['station_location']); ?></strong></p>
          <p>Vehicle Type: <strong><?php echo ucfirst($activeToken['vehicle_type'] ?? 'car'); ?></strong></p>
          <p>Fuel Type: <strong><?php echo ucfirst($activeToken['fuel_type']); ?></strong></p>
          <p>Liters: <strong><?php echo $activeToken['liters']; ?> L</strong></p>
          <p>Requested On: <strong><?php echo date('M d, Y h:i A', strtotime($activeToken['created_at'])); ?></strong></p>
          
          <div style="background-color: #1a1328; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px solid #4a435d;">
            <p style="margin-bottom: 5px;">Currently Serving: <strong style="color: #10B981;"><?php echo $currentlyServing; ?></strong></p>
            <p>People in front of me: <strong style="color: #FBBF24;"><?php echo $peopleAhead; ?></strong></p>
          </div>

          <div class="status-badge" style="background-color: <?php 

            if($activeToken['status'] == 'Called'){
              echo '#10B981';
            }
            else{
              echo '#FBBF24';
            }
          ?>; color: black;">
            Status: <?php echo strtoupper($activeToken['status']); ?>
          </div>
          <?php if($activeToken['status'] == 'Called'): ?>
            <p style="margin-top: 15px; color: #10B981; font-weight: bold;">It's your turn! Please proceed to the station.</p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="token-card" style="text-align: center;">
          <p>You don't have any active tokens at the moment.</p>
          <a class="btn-request" href="../USER STATION/index.php">Request a Token</a>
        </div>
      <?php endif; ?>

      <h2 class="page-title" style="margin-top: 50px;">Token History</h2>
      <div style="overflow-x: auto;">
        <table class="history-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Station</th>
            <th>Vehicle</th>
            <th>Car ID</th>
            <th>Fuel</th>
            <th>Liters</th>
            <th>Serial</th>
          </tr>
        </thead>
        <tbody>
          <?php if($historyResult->num_rows > 0): ?>
            <?php while($h = $historyResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo date('M d, Y', strtotime($h['created_at'])); ?></td>
                <td><?php echo htmlspecialchars($h['station_name']); ?></td>
                <td><?php echo ucfirst($h['vehicle_type'] ?? 'car'); ?></td>
                <td><?php echo $h['registered_car_id'] !== null ? htmlspecialchars((string)$h['registered_car_id']) : '-'; ?></td>
                <td><?php echo ucfirst($h['fuel_type']); ?></td>
                <td><?php echo $h['liters']; ?> L</td>
                <td>#<?php echo $h['serial_number']; ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align: center;">No history available.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    </div>

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
              <li><a href="./user-token-page.php">Token</a></li>
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