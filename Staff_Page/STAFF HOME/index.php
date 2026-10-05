<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}

$logs = $conn->query("SELECT l.*, u.full_name FROM logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY timestamp DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./style.css">
    <style>
      body{ 
        width: 100%; 
        overflow-x: hidden; 
        margin: 0; 
        padding: 0; 
      }
      
      *{ 
        box-sizing: border-box; 
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


      table{
        width: 100%;
        border-collapse: collapse;
        display: block;
        overflow-x: auto;
      }
      

      th, 
      td{
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #4a435d;
        color: white;
      }
      

      th{
        color: #A28EAB;
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
      <li><a href="./index.php">Dashboard</a></li>
      <li><a href="../STAFF STATION/index.php">Stations</a></li>
      <li><a href="../STAFF TOKEN/staff-token-page.php">Token</a></li>
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

     <section class="banner-1">
        <h2 class="title-1">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
        <div class="card-container">
            <div class="card-1">
                <h3 class="title-2">Recent System Activity</h3>
                <div style="max-height: 200px; overflow-y: auto; overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($logs->num_rows > 0): ?>
                                <?php while($l = $logs->fetch_assoc()): ?>
                                    <tr>
                                        <td style="font-size: 12px;"><?php echo date('h:i A', strtotime($l['timestamp'])); ?></td>
                                        <td style="font-size: 12px; font-weight: bold;"><?php echo ($l['action']); ?></td>
                                        <td style="font-size: 12px;"><?php echo ($l['details']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No logs available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-2">
                <h3 class="title-3">Quick Action</h3>
                <a href="../STAFF STATION/index.php"><button>Manage Stations</button></a>
                <a href="../STAFF TOKEN/staff-token-page.php"><button>Manage Queue</button></a>
            </div>
        </div>
     </section>
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