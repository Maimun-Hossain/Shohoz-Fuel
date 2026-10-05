<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}


$totalStations = $conn->query("SELECT COUNT(*) as count FROM stations")->fetch_assoc()['count'];


$activeTokens = $conn->query("SELECT COUNT(*) as count FROM tokens WHERE status IN ('Pending', 'Called')")->fetch_assoc()['count'];


$tokensToday = $conn->query("SELECT COUNT(*) as count FROM tokens WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];


$busiestResult = $conn->query("SELECT s.name, COUNT(t.id) as count FROM tokens t JOIN stations s ON t.station_id = s.id GROUP BY t.station_id ORDER BY count DESC LIMIT 1");


if($busiestResult->num_rows > 0){
    $busiestData = $busiestResult->fetch_assoc();
    $busiestStation = $busiestData['name'];
}
else{
    $busiestStation = "None";
}


$fuelDistResult = $conn->query("SELECT fuel_type, COUNT(*) as count FROM tokens GROUP BY fuel_type");
$fuelLabels = [];
$fuelCounts = [];
while($row = $fuelDistResult->fetch_assoc()){
    $fuelLabels[] = ucfirst($row['fuel_type']);
    $fuelCounts[] = $row['count'];
}


$hourlyResult = $conn->query("SELECT HOUR(created_at) as hr, COUNT(*) as count FROM tokens WHERE DATE(created_at) = CURDATE() GROUP BY hr ORDER BY hr ASC");
$hours = [];
$hourCounts = [];
for($i=0; $i<24; $i++){
    $hours[] = $i . ":00";
    $hourCounts[$i] = 0;
}
while($row = $hourlyResult->fetch_assoc()){
    $hourCounts[$row['hr']] = $row['count'];
}


$transferredResult = $conn->query("SELECT s.name, IFNULL(SUM(t.liters), 0) as total FROM stations s LEFT JOIN tokens t ON s.id = t.station_id AND t.status='Completed' GROUP BY s.id ORDER BY total DESC LIMIT 5");
$stationNames = [];
$stationTotals = [];
while($row = $transferredResult->fetch_assoc()){
    $stationNames[] = $row['name'];
    $stationTotals[] = $row['total'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>System Analytics</title>
    <style>
    body{ 
      width: 100%; 
      overflow-x: hidden; 
      margin: 0; 
      padding: 0; 
    }

    *{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body{
      background-color: #0F051A;
      font-family: 'Segoe UI';
      color: #ffffff;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .navbar{
      background-color: #302B3F;
      padding: 14px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
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
      width: 100%;
    }

    .page-title{
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 30px;
      color: #ffffff;
    }
    .card{
        background-color: #302B3F;
        padding: 20px;
        border-radius: 20px;
        width: 100%;
        text-align: center;
        border: 1px solid #A28EAB;
    }
    .cards{
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
        width: 100%;
    }
    .chart-box{
        background-color: #302B3F;
        padding: 20px;
        border-radius: 20px;
        border: 1px solid #A28EAB;
        height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        width: 100%;
        min-width: 0;
    }
    .chart-container{
        position: relative;
        flex: 1;
        width: 100% !important;
        min-height: 0;
    }
    .charts-grid{
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        width: 100%;
    }
    .chart-full{
        grid-column: span 2;
    }

      @media (max-width: 850px){
        .navbar{
          flex-direction: row;
          justify-content: space-between;
          position: relative;
          padding: 14px 20px;
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
        .chart-full{
          grid-column: span 1;
        }
        .main-content{ 
          padding: 20px 15px; 
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

      @media (max-width: 600px){
        .cards{
          display: block;
        }
        .card{
          width: 100%;
          margin: 10px 0;
        }
        .charts-grid{
          display: block;
        }
        .chart-box{
          height: auto;
          min-height: 350px;
          padding: 10px;
          width: 100%;
          margin: 10px 0;
        }
        .page-title{
          font-size: 18px;
          text-align: center;
        }
        .navbar{ 
          padding: 14px 10px; 
        }
        .main-content{ 
          padding: 20px 10px; 
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
        <li><a href="../ADMIN HOME/index.php">Dashboard</a></li>
        <li><a href="../ADMIN STATION/index.php">Stations</a></li>
        <li><a href="../ADMIN TOKEN/admin-token-page.php">Token</a></li>
        <li><a href="./index.php">Analytics</a></li>
        <li>
          <div class="avatar-container" id="avatar-container">
            <img id="user-avatar" src="../../Assets/image.png" alt="User Avatar"/>
            <div class="dropdown-menu" id="avatar-dropdown">
              <a href="../../logout.php">LogOut</a>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <div class="main-content">
    <h1 class="page-title">System Analytics Overview</h1>
    <div class="cards">
        <div class="card">
            <h2 style="color: #A28EAB;">Total Stations</h2>
            <p class="font-bold text-2xl"><?php echo $totalStations; ?></p>
        </div>
        <div class="card">
            <h2 style="color: #A28EAB;">Active Tokens</h2>
            <p class="font-bold text-2xl"><?php echo $activeTokens; ?></p>
        </div>
        <div class="card">
            <h2 style="color: #A28EAB;">Tokens Today</h2>
            <p class="font-bold text-2xl"><?php echo $tokensToday; ?></p>
        </div>
        <div class="card">
            <h2 style="color: #A28EAB;">Busiest Station</h2>
            <p class="font-bold text-lg" style="color: #10B981;"><?php echo ($busiestStation); ?></p>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-box">
            <h2 class="mb-4">Hourly Token Requests (Today)</h2>
            <div class="chart-container"><canvas id="hourlyChart"></canvas></div>
        </div>
        <div class="chart-box">
            <h2 class="mb-4">Fuel Type Distribution</h2>
            <div class="chart-container"><canvas id="fuelPieChart"></canvas></div>
        </div>
        <div class="chart-box" style="grid-column: span 2;">
            <h2 class="mb-4">Fuel Transferred (Total Liters by Station)</h2>
            <div class="chart-container"><canvas id="stationBarChart"></canvas></div>
        </div>
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
        

        window.addEventListener('click', function(){
          if(avatarDropdown.classList.contains('show')){
            avatarDropdown.classList.remove('show');
          }
        });
      }


      const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: 'white' } }
        },
        scales: {
            y: { 
              beginAtZero: true,
              ticks: { color: 'white' }, 
              grid: { color: '#4a435d' } 
            },
            x: { 
              ticks: { color: 'white' }, 
              grid: { color: '#4a435d' } 
            }
        }
      };


      new Chart(document.getElementById('hourlyChart'), {
        type: 'line',
        data: {
          labels: <?php echo json_encode($hours); ?>,
          datasets: [{
            label: 'Requests',
            data: <?php echo json_encode(array_values($hourCounts)); ?>,
            borderColor: '#A28EAB',
            backgroundColor: 'rgba(162, 142, 171, 0.2)',
            fill: true,
            tension: 0.4
          }]
        },
        options: commonOptions
      });


      new Chart(document.getElementById('fuelPieChart'), {
        type: 'pie',
        data: {
          labels: <?php echo json_encode($fuelLabels); ?>,
          datasets: [{
            data: <?php echo json_encode($fuelCounts); ?>,
            backgroundColor: ['#A28EAB', '#98DDE5', '#FBBF24', '#F43F5E']
          }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: 'white' } }
            }
        }
      });


      new Chart(document.getElementById('stationBarChart'), {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($stationNames); ?>,
          datasets: [{
            label: 'Total Liters Transferred',
            data: <?php echo json_encode($stationTotals); ?>,
            backgroundColor: '#10B981'
          }]
        },
        options: commonOptions
      });
    </script>
</body>
</html>