<?php
session_start();
include '../../db.php';


if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}


$priceResult = $conn->query("SELECT * FROM fuel_market_prices");
$market_prices = [];
while($row = $priceResult->fetch_assoc()){
    $market_prices[$row['fuel_type']] = $row['price_per_liter'];
}


$stations = $conn->query("SELECT * FROM stations ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <title>Staff Station</title>
    <link rel="stylesheet" href="style.css">
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

      .modal{ 
        display: none; 
        position: fixed; 
        z-index: 2000; 
        left: 0; 
        top: 0; 
        width: 100%; 
        height: 100%; 
        background-color: rgba(0,0,0,0.8); 
        backdrop-filter: blur(5px); 
      }
      
      .modal-content{ 
        background-color: #302b3f; 
        margin: 10% auto; 
        padding: 0; 
        border: 1px solid #A28EAB; 
        width: 90%; 
        max-width: 400px; 
        border-radius: 16px; 
        color: white; 
        overflow: hidden; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
      }
      
      .modal-header{ 
        background: #1a1328; 
        padding: 20px 30px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom: 1px solid #4a435d; 
      }
      
      .modal-header h2{ 
        font-size: 18px; 
        font-weight: 700; 
        color: #A28EAB; 
        margin: 0; 
      }
      
      .close{ 
        color: #aaa; 
        font-size: 28px; 
        font-weight: bold; 
        cursor: pointer; 
      }
      

      .modal-body{ 
        padding: 30px; 
      }
      

      .modal-body label{ 
        display: block; 
        font-size: 13px; 
        font-weight: 600; 
        color: #A28EAB; 
        margin-bottom: 8px; 
        text-transform: uppercase; 
      }
      

      .modal-body input{ 
        width: 100%; 
        padding: 12px; 
        margin-bottom: 20px; 
        border-radius: 8px; 
        border: 1px solid #4a435d; 
        background: #1a1328; 
        color: white; 
      }
      

      .modal-footer{ 
        background: #1a1328; 
        padding: 20px 30px; 
        display: flex; 
        justify-content: flex-end; 
        gap: 12px; 
        border-top: 1px solid #4a435d; 
      }
      

      .btn-submit{ 
        background-color: #A28EAB; 
        color: white; 
        padding: 10px 24px; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 700; 
      }
      

      .btn-cancel{ 
        background-color: transparent; 
        color: #aaa; 
        padding: 10px 24px; 
        border: 1px solid #4a435d; 
        border-radius: 8px; 
        cursor: pointer; 
      }


      .filter-dropdown{ 
        position: relative; 
        display: inline-block; 
      }
      

      .filter-content{ 
        display: none; 
        position: absolute; 
        background-color: #302b3f; 
        min-width: 160px; 
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.4); 
        z-index: 1000; 
        border-radius: 8px; 
        border: 1px solid #a28eab; 
        margin-top: 5px; 
        overflow: hidden; 
      }
      

      .filter-content.show{ 
        display: block; 
      }
      

      .filter-content button{ 
        color: white; 
        padding: 12px 16px; 
        text-decoration: none; 
        display: block; 
        width: 100%; 
        text-align: left; 
        background: none; 
        border: none; 
        cursor: pointer; 
        font-size: 14px; 
        transition: background-color 0.3s; 
      }
      

      .filter-content button:hover{ 
        background-color: #4a435d; 
      }
      
      .location-icon i{ 
        font-size: 20px; 
        color: #a28eab; 
      }
      
      .location-icon:hover i{ 
        color: white; 
      }
      
      .fas{ 
        font-family: "Font Awesome 5 Free" !important; 
        font-weight: 900 !important; 
      }
    </style>
  </head>
  <body>
    <?php if(isset($_GET['msg'])): ?>
        <script>alert("<?php echo htmlspecialchars($_GET['msg']); ?>");</script>
    <?php endif; ?>

    <nav class="navbar">
      <span class="logo">FUEL CRISIS</span>
      <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
      <ul class="nav-links" id="nav-links">
        <li><a href="../STAFF HOME/index.php">Dashboard</a></li>
        <li><a href="./index.php">Stations</a></li>
        <li><a href="../STAFF TOKEN/staff-token-page.php">Token</a></li>
        <li>
          <div class="avatar-container" id="avatar-container">
            <img id="user-avatar" src="../../Assets/image.png" alt="User Avatar" />
            <div class="dropdown-menu" id="avatar-dropdown">
              <a href="../../logout.php">LogOut</a>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <div class="main-content">
      <div class="flex justify-between items-center title-con">
        <h1 class="page-title">Fuel Stations</h1>
      </div>
      <div class="search-container">
        <input placeholder="🔍 Search Station Name or Location" class="input-bar" type="text" id="stationSearch" onkeyup="searchStations()">
        <div class="filter-dropdown">
          <button class="all-station-btn" id="filterBtn">All Stations <i class="fas fa-chevron-circle-down"></i></button>
          <div class="filter-content" id="filterDropdown">
            <button onclick="filterByStatus('all')">All Stations</button>
            <button onclick="filterByStatus('AVAILABLE')">Available</button>
            <button onclick="filterByStatus('NO STOCK')">No Stock</button>
          </div>
        </div>
      </div>
      
      <div class="cards-container" id="stationsGrid">
        <?php if($stations->num_rows > 0): ?>
          <?php while($row = $stations->fetch_assoc()): ?>
            <?php 
              $s_id = $row['id'];
              
              $stationOnOff = isset($row['on_off_status']) ? $row['on_off_status'] : 'on';
              $hasAnyStock = false;
              if($stationOnOff === 'on' && $row['petrol_stock'] > 0){
                $hasAnyStock = true;
              }
              if($stationOnOff === 'on' && $row['diesel_stock'] > 0){
                $hasAnyStock = true;
              }
              
              if($stationOnOff === 'off' || $row['status'] == 'NO STOCK' || !$hasAnyStock){
                $isNoStock = true;
              }
              else{
                $isNoStock = false;
              }

              if($stationOnOff === 'off'){
                $displayStatus = 'OFF';
              }
              elseif($isNoStock){
                $displayStatus = 'NO STOCK';
              }
              else{
                $displayStatus = 'AVAILABLE';
              }
            ?>
            <div class="card station-card-item" 
                 data-name="<?php echo htmlspecialchars(strtolower($row['name'])); ?>" 
                 data-location="<?php echo htmlspecialchars(strtolower($row['location'])); ?>"
                 data-status="<?php echo $displayStatus; ?>">
              <div class="card-image">
                <p class="badge <?php 
                  if($isNoStock){
                    echo 'bg_red';
                  }
                ?>"><?php echo $displayStatus; ?></p>
                <img class="card-im" src="<?php echo $row['img_url']; ?>" onerror="this.src='../../Assets/card-im.png'">
              </div>
              <div class="card-contents">
                <div class="card-title">
                  <div class="card-title-text">
                    <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                    <p><?php echo htmlspecialchars($row['location']); ?></p>
                  </div>
                  <a href="<?php echo htmlspecialchars($row['map_link']); ?>" target="_blank" class="location-icon">
                    <i class="fas fa-map-marker-alt"></i>
                  </a>
                </div>
                  <?php if(($row['on_off_status'] ?? 'on') === 'on' && $row['petrol_stock'] > 0): ?>
                    <div class="card-details">
                        <h3>petrol</h3>
                        <div class="card-details-info">
                            <p><?php echo $row['petrol_stock']; ?> L</p>
                            <p>Bdt.<?php 
                              if(isset($market_prices['petrol'])){
                                echo number_format($market_prices['petrol'], 2);
                              }
                              else{
                                echo '0.00';
                              }
                            ?> / L</p>
                        </div>
                    </div>
                  <?php endif; ?>
                  <?php if(($row['on_off_status'] ?? 'on') === 'on' && $row['diesel_stock'] > 0): ?>
                    <div class="card-details">
                        <h3>diesel</h3>
                        <div class="card-details-info">
                            <p><?php echo $row['diesel_stock']; ?> L</p>
                            <p>Bdt.<?php 
                              if(isset($market_prices['diesel'])){
                                echo number_format($market_prices['diesel'], 2);
                              }
                              else{
                                echo '0.00';
                              }
                            ?> / L</p>
                        </div>
                    </div>
                  <?php endif; ?>
                
                <?php
                  $queueQuery = "SELECT 
                    (SELECT serial_number FROM tokens WHERE station_id=$s_id AND status='Called' AND DATE(created_at) = CURDATE() ORDER BY serial_number DESC LIMIT 1) as serving,
                    (SELECT COUNT(*) FROM tokens WHERE station_id=$s_id AND status='Pending' AND DATE(created_at) = CURDATE()) as waiting";
                  $queueData = $conn->query($queueQuery)->fetch_assoc();
                  
                  if($queueData['serving'] != null){
                    $serving = $queueData['serving'];
                  }
                  else{
                    $serving = '---';
                  }

                  if($queueData['waiting'] != null){
                    $waiting = $queueData['waiting'];
                  }
                  else{
                    $waiting = 0;
                  }
                ?>
                <div class="queue-status" style="margin-bottom: 15px; padding: 12px; background: #1a1328; border: 1px solid #4a435d; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Currently Serving</span>
                        <span style="font-size: 15px; font-weight: 700; color: #fff;">Token <span class="queue-badge">#<?php echo $serving; ?></span></span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px; text-align: right;">
                        <span style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">In Queue</span>
                        <span style="font-size: 15px; font-weight: 700; color: #fff;"><span class="queue-badge"><?php echo $waiting; ?></span> People</span>
                    </div>
                </div>

                <div class="card-info">
                  <p>Updated: <?php echo date('h:i:s A', strtotime($row['created_at'])); ?></p>
                </div>

                <div class="edit-btns">
                    <button class="edit-btn bg-[#E2C471D6]" onclick="openLogModal(<?php echo $row['id']; ?>, <?php echo (($row['on_off_status'] ?? 'on') === 'on' && $row['petrol_stock'] > 0) ? 'true' : 'false'; ?>, <?php echo (($row['on_off_status'] ?? 'on') === 'on' && $row['diesel_stock'] > 0) ? 'true' : 'false'; ?>)">Log Stock</button>
                    <a href="../../Admin_Page/ADMIN STATION/station_handler.php?action=toggle&id=<?php echo $row['id']; ?>" class="edit-btn bg-[#98DDE5] text-center" style="display: flex; align-items: center; justify-content: center;">Toggle Status</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No stations found.</p>
        <?php endif; ?>
      </div>
    </div>


    <div id="logModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Log Fuel Stock</h2>
          <span class="close" onclick="closeModal('logModal')">&times;</span>
        </div>
        <form action="../../Admin_Page/ADMIN STATION/station_handler.php" method="POST">
          <input type="hidden" name="action" value="log_liter">
          <input type="hidden" name="station_id" id="logStationId">
          <div class="modal-body">
            <div id="petrolLog">
              <label>Add Petrol (Liters)</label>
              <input type="number" name="petrol_add" value="0" min="0" step="0.01">
            </div>
            <div id="dieselLog">
              <label>Add Diesel (Liters)</label>
              <input type="number" name="diesel_add" value="0" min="0" step="0.01">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('logModal')">Cancel</button>
            <button type="submit" class="btn-submit">Update Stock</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      const hamburger = document.getElementById('hamburger');
      const navLinks = document.getElementById('nav-links');
      const avatarContainer = document.getElementById('avatar-container');
      const avatarDropdown = document.getElementById('avatar-dropdown');
      const filterBtn = document.getElementById('filterBtn');
      const filterDropdown = document.getElementById('filterDropdown');

      if(hamburger && navLinks){
        hamburger.addEventListener('click', function(e){
          e.stopPropagation();
          navLinks.classList.toggle('active');
        });
      }

      if(avatarContainer){
        avatarContainer.addEventListener('click', function(e){ 
          e.stopPropagation(); 
          avatarDropdown.classList.toggle('show'); 
        });
      }

      if(filterBtn){
        filterBtn.addEventListener('click', function(e){ 
          e.stopPropagation(); 
          filterDropdown.classList.toggle('show'); 
        });
      }

      function openLogModal(id){
        document.getElementById('logStationId').value = id;
        document.getElementById('logModal').style.display = 'block';
      }


      function closeModal(){ 
        document.getElementById('logModal').style.display = 'none'; 
      }


      window.onclick = function(e){
        if(avatarContainer && !avatarContainer.contains(e.target)){
          avatarDropdown.classList.remove('show');
        }
        if(filterBtn && !filterBtn.contains(e.target)){
          filterDropdown.classList.remove('show');
        }
        if(e.target.id === 'logModal'){
          closeModal();
        }
      };


      function searchStations(){
        let input = document.getElementById('stationSearch').value.toLowerCase();
        let cards = document.getElementsByClassName('station-card-item');
        for(let i = 0; i < cards.length; i++){
          let name = cards[i].getAttribute('data-name');
          if(name == null){ name = ""; }

          let location = cards[i].getAttribute('data-location');
          if(location == null){ location = ""; }

          if(name.includes(input) || location.includes(input)){
            cards[i].style.display = "";
          }
          else{
            cards[i].style.display = "none";
          }
        }
      }


      function filterByStatus(status){
        let cards = document.getElementsByClassName('station-card-item');
        for(let i = 0; i < cards.length; i++){
          let cardStatus = cards[i].getAttribute('data-status');
          if(status === 'all' || cardStatus === status){
            cards[i].style.display = "";
          }
          else{
            cards[i].style.display = "none";
          }
        }
        
        let label = "";
        if(status === 'all'){
          label = 'All Stations';
        }
        else{
          label = status;
        }

        document.getElementById('filterBtn').innerHTML = label + ' <i class="fas fa-chevron-circle-down"></i>';
        filterDropdown.classList.remove('show');
      }
    </script>
  </body>
</html>