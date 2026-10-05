<?php
session_start();
include '../../db.php';


if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}


$priceResult = $conn->query("SELECT * FROM fuel_market_prices");
$market_prices = [];
while($row = $priceResult->fetch_assoc()){
    $market_prices[$row['fuel_type']] = $row['price_per_liter'];
}
$vehicleDailyLimits = ['bike' => 50.00, 'car' => 100.00];
$limitResult = $conn->query("SELECT vehicle_type, daily_limit FROM vehicle_daily_limits");
while($row = $limitResult->fetch_assoc()){
  $vehicleDailyLimits[$row['vehicle_type']] = (float)$row['daily_limit'];
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
    <title>Admin Station</title>
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
        margin: 5% auto; 
        padding: 0; 
        border: 1px solid #A28EAB; 
        width: 90%; 
        max-width: 600px; 
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
        font-size: 20px; 
        font-weight: 700; 
        color: #A28EAB; 
        margin: 0; 
      }
      

      .close{ 
        color: #aaa; 
        font-size: 28px; 
        font-weight: bold; 
        cursor: pointer; 
        transition: 0.3s; 
      }
      
      .close:hover{ 
        color: white; 
      }
      

      .modal-body{ 
        padding: 30px; 
      }
      

      .modal-body input:not([type="checkbox"]), 
      .modal-body select{ 
        width: 100%; 
        padding: 12px; 
        margin-bottom: 20px; 
        border-radius: 8px; 
        border: 1px solid #4a435d; 
        background: #1a1328; 
        color: white; 
        transition: 0.3s; 
      }
      

      .modal-body input:not([type="checkbox"]):focus{ 
        border-color: #A28EAB; 
        outline: none; 
        box-shadow: 0 0 5px rgba(162, 142, 171, 0.3); 
      }
      

      .modal-body label:not(.fuel-option){ 
        display: block; 
        font-size: 13px; 
        font-weight: 600; 
        color: #A28EAB; 
        margin-bottom: 8px; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
      }

      .fuel-type-selector{ 
        display: flex; 
        gap: 40px; 
        background: #1a1328; 
        padding: 15px 20px; 
        border-radius: 10px; 
        border: 1px solid #4a435d; 
        margin-top: 10px; 
      }
      

      .fuel-option{ 
        display: inline-flex; 
        align-items: center; 
        gap: 10px; 
        cursor: pointer; 
        user-select: none; 
      }
      

      .fuel-option input[type="checkbox"]{ 
        width: 18px !important; 
        height: 18px !important; 
        margin: 0 !important; 
        padding: 0 !important; 
        cursor: pointer; 
        accent-color: #A28EAB; 
        flex-shrink: 0; 
      }
      

      .fuel-option span{ 
        font-size: 15px; 
        font-weight: 600; 
        color: #eee; 
        line-height: 1; 
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
        padding: 12px 24px; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 700; 
        transition: 0.3s; 
      }
      

      .btn-submit:hover{ 
        background-color: #8c7a96; 
        transform: translateY(-1px); 
      }
      

      .btn-cancel{ 
        background-color: transparent; 
        color: #aaa; 
        padding: 12px 24px; 
        border: 1px solid #4a435d; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 600; 
        transition: 0.3s; 
      }
      

      .btn-cancel:hover{ 
        background: #4a435d; 
        color: white; 
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
      

      .modal-grid{ 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
      }
      
      .span-2{ 
        grid-column: span 2; 
      }
      

      @media (max-width: 600px){
        .modal-grid{ 
          grid-template-columns: 1fr; 
          gap: 0; 
        }
        .span-2{ 
          grid-column: span 1; 
        }
        .modal-content{ 
          margin: 10% auto; 
          width: 95%; 
        }
        .fuel-type-selector{ 
          flex-direction: column; 
          gap: 15px; 
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
        <li><a href="./index.php">Stations</a></li>
        <li><a href="../ADMIN TOKEN/admin-token-page.php">Token</a></li>
        <li><a href="../ADMIN ANALYTICS/index.php">Analytics</a></li>
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
        <button class="add-btn" onclick="openAddModal()">+ Add New Station</button>
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

      <div class="update-price-section">
        <h2>Update Market Fuel Prices</h2>
        <form action="price_handler.php" method="POST" class="flex justify-between items-center gap-4">
            <div class="text-center flex-1">
              <p>Petrol (Bdt)</p>
              <input class="bg-white text-black w-full in" type="number" step="0.01" name="petrol-price" value="<?php echo $market_prices['petrol'] ?? ''; ?>" required>
            </div>
            <div class="text-center flex-1">
              <p>Diesel (Bdt)</p>
              <input class="bg-white text-black w-full in" type="number" step="0.01" name="diesel-price" value="<?php echo $market_prices['diesel'] ?? ''; ?>" required>
            </div>
            <button type="submit" class="up-btn">Update Price</button>
        </form>
      </div>

      <div class="update-price-section">
        <h2>Daily Vehicle Limits (All Stations)</h2>
        <form action="station_handler.php" method="POST" class="flex justify-between items-center gap-4">
            <input type="hidden" name="action" value="update_daily_limits">
            <div class="text-center flex-1">
              <p>Bike (L per user, per day)</p>
              <input class="bg-white text-black w-full in" type="number" min="0" step="0.01" name="bike-daily-limit" value="<?php echo number_format($vehicleDailyLimits['bike'], 2, '.', ''); ?>" required>
            </div>
            <div class="text-center flex-1">
              <p>Car (L per user, per day)</p>
              <input class="bg-white text-black w-full in" type="number" min="0" step="0.01" name="car-daily-limit" value="<?php echo number_format($vehicleDailyLimits['car'], 2, '.', ''); ?>" required>
            </div>
            <button type="submit" class="up-btn">Update Limits</button>
        </form>
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
              $stationRatingData = $conn->query("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(rating) AS rating_count FROM reviews WHERE station_id = $s_id")->fetch_assoc();
              $stationAverageRating = isset($stationRatingData['avg_rating']) ? (float)$stationRatingData['avg_rating'] : 0.0;
              $stationRatingCount = isset($stationRatingData['rating_count']) ? (int)$stationRatingData['rating_count'] : 0;
            ?>
            <div class="card station-card-item" 
                 data-name="<?php echo (strtolower($row['name'])); ?>" 
                 data-location="<?php echo (strtolower($row['location'])); ?>"
                 data-status="<?php echo $displayStatus; ?>">
              <div class="card-image">
                <img class="card-im" src="<?php echo $row['img_url']; ?>" onerror="this.src='../../Assets/card-im.png'">
                <div class="station-image-badges">
                  <p class="badge <?php 
                    if($isNoStock){
                      echo 'bg_red';
                    }
                  ?>"><?php echo $displayStatus; ?></p>
                </div>
                <div class="rating-badge" title="<?php echo $stationRatingCount > 0 ? number_format($stationAverageRating, 1) . ' out of 5.0' : 'No ratings yet'; ?>">
                  <?php if($stationRatingCount > 0): ?>
                    <span class="rating-star">★</span>
                    <span><?php echo number_format($stationAverageRating, 1); ?>/5.0</span>
                  <?php else: ?>
                    <span>No ratings</span>
                  <?php endif; ?>
                </div>
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
                      <div class="card-details-label">
                        <h3>petrol</h3>
                        <p>Bdt.<?php echo number_format($market_prices['petrol'], 2); ?> / L</p>
                      </div>
                      <div class="card-details-info">
                        <span>Available</span>
                        <p><?php echo $row['petrol_stock']; ?> L</p>
                      </div>
                    </div>
                  <?php endif; ?>
                  
                  <?php if(($row['on_off_status'] ?? 'on') === 'on' && $row['diesel_stock'] > 0): ?>
                    <div class="card-details">
                      <div class="card-details-label">
                        <h3>diesel</h3>
                        <p>Bdt.<?php echo number_format($market_prices['diesel'], 2); ?> / L</p>
                      </div>
                      <div class="card-details-info">
                        <span>Available</span>
                        <p><?php echo $row['diesel_stock']; ?> L</p>
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
                    <button class="edit-btn bg-[#A28EAB]" 
                            onclick='openEditModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)'>Edit Info</button>
                    <button class="edit-btn bg-[#E2C471D6]" onclick="openLogModal(<?php echo $s_id; ?>, <?php echo (($row['on_off_status'] ?? 'on') === 'on' && $row['petrol_stock'] > 0) ? 'true' : 'false'; ?>, <?php echo (($row['on_off_status'] ?? 'on') === 'on' && $row['diesel_stock'] > 0) ? 'true' : 'false'; ?>)">Log Stock</button>
                    <a href="station_handler.php?action=toggle&id=<?php echo $s_id; ?>" class="edit-btn bg-[#98DDE5]">Toggle Status</a>
                    <a href="station_handler.php?action=delete&id=<?php echo $s_id; ?>" class="edit-btn bg-[#F43F5E]" onclick="return confirm('Are you sure you want to delete this station?')">Delete</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No stations found.</p>
        <?php endif; ?>
      </div>
    </div>

    <div id="stationModal" class="modal">
      <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
          <h2 id="modalTitle">Add Station</h2>
          <span class="close" onclick="closeModal('stationModal')">&times;</span>
        </div>
        <form action="station_handler.php" method="POST">
          <input type="hidden" name="action" id="formAction" value="add">
          <input type="hidden" name="station_id" id="stationId">
          <div class="modal-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Station Name</label>
                    <input type="text" name="name" id="modalName" placeholder="e.g. Shell Central" required>
                </div>
                <div>
                    <label>Location Name</label>
                    <input type="text" name="location" id="modalLocation" placeholder="e.g. Cityville" required>
                </div>
                <div style="grid-column: span 2;">
                    <label>Image URL</label>
                    <input type="text" name="img_url" id="modalImg" placeholder="Link to station image">
                </div>
                <div style="grid-column: span 2;">
                    <label>Google Maps Link</label>
                    <input type="text" name="map_link" id="modalMap" placeholder="Map location link" required>
                </div>
            </div>
            
            <label>Station Status</label>
            <div class="fuel-type-selector">
                <label class="fuel-option">
                    <select name="on_off_status" id="modalOnOffStatus" style="width: 100%; padding: 10px; border-radius: 8px; background: #1a1328; color: #fff; border: 1px solid #4a435d;">
                        <option value="on">On</option>
                        <option value="off">Off</option>
                    </select>
                </label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('stationModal')">Cancel</button>
            <button type="submit" class="btn-submit">Save Station</button>
          </div>
        </form>
      </div>
    </div>


    <div id="logModal" class="modal">
      <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
          <h2>Log Fuel Stock</h2>
          <span class="close" onclick="closeModal('logModal')">&times;</span>
        </div>
        <form action="station_handler.php" method="POST">
          <input type="hidden" name="action" value="log_liter">
          <input type="hidden" name="station_id" id="logStationId">
          <div class="modal-body">
            <div id="petrolLog" style="display:none;">
              <label>Add Petrol (Liters)</label>
              <input type="number" name="petrol_add" value="0">
            </div>
            <div id="dieselLog" style="display:none;">
              <label>Add Diesel (Liters)</label>
              <input type="number" name="diesel_add" value="0">
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


      window.onclick = function(e){
        if(avatarContainer && !avatarContainer.contains(e.target)){
          avatarDropdown.classList.remove('show');
        }
        if(filterBtn && !filterBtn.contains(e.target)){
          filterDropdown.classList.remove('show');
        }
        if(navLinks && navLinks.classList.contains('active')){
          if(!hamburger.contains(e.target) && !navLinks.contains(e.target)){
            navLinks.classList.remove('active');
          }
        }
        if(e.target.className === 'modal'){
          e.target.style.display = 'none';
        }
      };


      function openAddModal(){
        document.getElementById('modalTitle').innerText = 'Add Station';
        document.getElementById('formAction').value = 'add';
        document.getElementById('stationId').value = '';
        document.getElementById('modalName').value = '';
        document.getElementById('modalLocation').value = '';
        document.getElementById('modalImg').value = '';
        document.getElementById('modalMap').value = '';
        document.getElementById('modalOnOffStatus').value = 'on';
        document.getElementById('stationModal').style.display = 'block';
      }


      function openEditModal(s){
        document.getElementById('modalTitle').innerText = 'Edit Station';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('stationId').value = s.id;
        document.getElementById('modalName').value = s.name;
        document.getElementById('modalLocation').value = s.location;
        document.getElementById('modalImg').value = s.img_url;
        document.getElementById('modalMap').value = s.map_link;
        document.getElementById('modalOnOffStatus').value = (s.on_off_status === 'off') ? 'off' : 'on';
        document.getElementById('stationModal').style.display = 'block';
      }

      function openLogModal(id, p, d){
        document.getElementById('logStationId').value = id;
        
        if(p){
          document.getElementById('petrolLog').style.display = 'block';
        }
        else{
          document.getElementById('petrolLog').style.display = 'none';
        }

        if(d){
          document.getElementById('dieselLog').style.display = 'block';
        }
        else{
          document.getElementById('dieselLog').style.display = 'none';
        }
        document.getElementById('logModal').style.display = 'block';
      }

      function closeModal(mid){ 
        document.getElementById(mid).style.display = 'none'; 
      }
      

      function searchStations(){
        let input = document.getElementById('stationSearch').value.toLowerCase();
        let cards = document.getElementsByClassName('station-card-item');
        for(let i = 0; i < cards.length; i++){
          let name = cards[i].getAttribute('data-name');
          if(name == null){ 
            name = ""; 
          }

          let loc = cards[i].getAttribute('data-location');
          if(loc == null){ 
            loc = ""; 
          }

          if(name.includes(input) || loc.includes(input)){
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
        
        let buttonLabel = "";
        if(status === 'all'){
          buttonLabel = 'All Stations';
        }
        else{
          buttonLabel = status;
        }

        document.getElementById('filterBtn').innerHTML = buttonLabel + ' <i class="fas fa-chevron-circle-down"></i>';
        filterDropdown.classList.remove('show');
      }
    </script>
  </body>
</html>