<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}


$stations = $conn->query("SELECT * FROM stations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Token</title>

  <style>
    *{
      margin: 0;
      padding: 0;
    }

    body{
      background-color: #0F051A;
      font-family: 'Segoe UI';
      color: #ffffff;
      min-height: 100vh;
    }

    .navbar{
      background-color: #302B3F;
      padding: 14px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar .logo{
      font-weight: 700;
      font-size: 14px;
      letter-spacing: 2px;
      color: #ffffff;
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
      transition: 0.3s;
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


    .main-content{
      padding: 40px 30px;
    }

    .page-title{
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 30px;
      color: #ffffff;
    }

    .stations-grid{
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 40px;
    }

    .station-card{
      background-color: #302B3F;
      border: 1px solid #A28EAB;
      border-radius: 16px;
      padding: 24px;
    }

    .station-card h2{
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 18px;
      color: #ffffff;
    }


    .stats-row{
      display: flex;
      justify-content: space-between;
      margin-bottom: 4px;
    }

    .stats-row .label{
      font-size: 10px;
      letter-spacing: 1px;
      color: #888888;
      text-transform: uppercase;
    }


    .numbers-row{
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 18px;
    }

    .numbers-row .waiting-count{
      font-size: 38px;
      font-weight: 700;
      color: #ffffff;
    }

    .numbers-row .next-up{
      font-size: 38px;
      font-weight: 700;
      color: #ffffff;
    }


    .btn-call{
      width: 100%;
      background-color: #cccccc;
      color: black;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 1px;
      cursor: pointer;
      text-decoration: none;
      display: block;
      text-align: center;
      margin-bottom: 10px;
    }

    .btn-call:hover{
      background-color: #ffffff;
    }

    .btn-complete{
        background-color: #10B981;
        color: white;
    }

    .btn-complete:hover{
        background-color: #059669;
    }


    .btn-call.disabled{
      opacity: 0.5;
      cursor: not-allowed;
    }

    @media (max-width: 850px){
      .navbar{
        flex-direction: row;
        justify-content: space-between;
        position: relative;
        padding: 14px 30px;
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
        background-color: #302B3F;
        padding: 20px 0;
        gap: 15px;
        z-index: 1000;
        border-bottom: 1px solid #4A435D;
      }
      .navbar .nav-links.active{
        display: flex;
      }
      .stations-grid{
        grid-template-columns: 1fr;
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
      <li><a href="../ADMIN HOME/index.php">Dashboard</a></li>
      <li><a href="../ADMIN STATION/index.php">Stations</a></li>
      <li><a href="./admin-token-page.php">Token</a></li>
      <li><a href="../ADMIN ANALYTICS/index.php">Analytics</a></li>
      <li>
        <div class="avatar-container" id="avatar-container">
          <img id="user-avatar" src="../../Assets/image.png" alt="User Avatar">
          <div class="dropdown-menu" id="avatar-dropdown">
            <a href="../../logout.php">LogOut</a>
          </div>
        </div>
      </li>
    </ul>
  </nav>

  <div class="main-content">
    <h1 class="page-title">Token Management</h1>

    <div class="stations-grid">
      <?php while($s = $stations->fetch_assoc()): ?>
        <?php
            $s_id = $s['id'];
            $waitingCount = $conn->query("SELECT COUNT(*) as count FROM tokens WHERE station_id=$s_id AND status='Pending' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
            

            $nextUpResult = $conn->query("SELECT serial_number FROM tokens WHERE station_id=$s_id AND status='Pending' AND DATE(created_at) = CURDATE() ORDER BY serial_number ASC LIMIT 1");
            

            if($nextUpResult->num_rows > 0){
              $nextUpData = $nextUpResult->fetch_assoc();
              $nextUp = "#" . $nextUpData['serial_number'];
            }
            else{
              $nextUp = "-";
            }


            $beingServedResult = $conn->query("SELECT id, serial_number, vehicle_type FROM tokens WHERE station_id=$s_id AND status='Called' AND DATE(created_at) = CURDATE() ORDER BY serial_number ASC LIMIT 1");
            $beingServed = $beingServedResult->fetch_assoc();
        ?>
        <div class="station-card">
            <h2><?php echo htmlspecialchars($s['name']); ?></h2>

            <div class="stats-row">
            <span class="label">Waiting</span>
            <span class="label">Next Up</span>
            </div>

            <div class="numbers-row">
            <span class="waiting-count"><?php echo $waitingCount; ?></span>
            <span class="next-up"><?php echo $nextUp; ?></span>
            </div>

            <?php if($beingServed): ?>
                <p style="margin-bottom: 10px; color: #10B981; font-weight: bold;">Currently Serving: #<?php echo $beingServed['serial_number']; ?> (<?php echo ucfirst($beingServed['vehicle_type'] ?? 'car'); ?>)</p>
                <a href="../../Staff_Page/STAFF TOKEN/token_handler.php?action=complete&token_id=<?php echo $beingServed['id']; ?>" class="btn-call btn-complete">COMPLETE ORDER</a>
            <?php endif; ?>

            <?php if($waitingCount > 0): ?>
                <a href="../../Staff_Page/STAFF TOKEN/token_handler.php?action=call&station_id=<?php echo $s_id; ?>" class="btn-call">CALL NEXT USER</a>
            <?php else: ?>
                <button class="btn-call disabled" disabled>NO ONE WAITING</button>
            <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <script>
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');
    if(hamburger && navLinks){
      hamburger.addEventListener('click', function(){
        navLinks.classList.toggle('active');
      });
    }


    const avatarContainer = document.getElementById('avatar-container');
    const avatarDropdown = document.getElementById('avatar-dropdown');

    if(avatarContainer && avatarDropdown){
      avatarContainer.addEventListener('click', function(e){
        e.stopPropagation();
        avatarDropdown.classList.toggle('show');
      });


      document.addEventListener('click', function(e){
        if(!avatarContainer.contains(e.target)){
          avatarDropdown.classList.remove('show');
        }
      });
    }
  </script>
</body>
</html>