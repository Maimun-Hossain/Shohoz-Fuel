<?php
session_start();
include '../../db.php';


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
$userCars = [];
$userDailyCarUsage = [];
if(isset($_SESSION['user_id'])){
  $carsStmt = $conn->prepare("SELECT car_id, car_type FROM cars WHERE user_id=? ORDER BY car_id");
  $carsStmt->bind_param("i", $_SESSION['user_id']);
  $carsStmt->execute();
  $carsResult = $carsStmt->get_result();
  while($car = $carsResult->fetch_assoc()){
    $userCars[] = ['car_id' => (int)$car['car_id'], 'vehicle_type' => $car['car_type']];
  }

  $usageStmt = $conn->prepare("SELECT car_id, COALESCE(SUM(liters), 0) AS used_liters FROM tokens WHERE user_id=? AND car_id IS NOT NULL AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY GROUP BY car_id");
  $usageStmt->bind_param("i", $_SESSION['user_id']);
  $usageStmt->execute();
  $usageResult = $usageStmt->get_result();
  while($usageRow = $usageResult->fetch_assoc()){
    $userDailyCarUsage[(string)$usageRow['car_id']] = (float)$usageRow['used_liters'];
  }
}

$userLocation = null;
if(isset($_SESSION['user_id'])){
  $userLocationStmt = $conn->prepare("SELECT location_x, location_y FROM users WHERE id = ?");
  $userLocationStmt->bind_param("i", $_SESSION['user_id']);
  $userLocationStmt->execute();
  $userLocationResult = $userLocationStmt->get_result();
  if($userLocationResult && $userLocationResult->num_rows > 0){
    $userLocation = $userLocationResult->fetch_assoc();
    $userLocation['x'] = (float)$userLocation['location_x'];
    $userLocation['y'] = (float)$userLocation['location_y'];
  }
}

$stations = $conn->query("SELECT * FROM stations ORDER BY name ASC");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <title>User Station</title>
    <link rel="stylesheet" href="style.css?v=2" />
    <link rel="stylesheet" href="../profile-menu.css" />
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
        background-color: rgba(0,0,0,0.7); 
      }
      

      .modal-content{ 
        background-color: #302b3f; 
        margin: 10% auto; 
        padding: 30px; 
        border: 1px solid #A28EAB; 
        width: 90%; 
        max-width: 450px; 
        border-radius: 12px; 
        color: white; 
      }
      

      .modal-header{ 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px; 
      }
      
      .close{ 
        color: #aaa; 
        font-size: 28px; 
        font-weight: bold; 
        cursor: pointer; 
      }
      
      .modal-body input, 
      .modal-body select, 
      .modal-body textarea{ 
        width: 100%; 
        padding: 10px; 
        margin: 10px 0; 
        border-radius: 8px; 
        border: 1px solid #4a435d; 
        background: #1a1328; 
        color: white; 
      }
      

      .modal-footer{ 
        display: flex; 
        justify-content: flex-end; 
        gap: 10px; 
        margin-top: 20px; 
      }
      

      .btn-submit{ 
        background-color: #A28EAB; 
        color: white; 
        padding: 10px 20px; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
      }
      

      .btn-cancel{ 
        background-color: #4a435d; 
        color: white; 
        padding: 10px 20px; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
      }


      .reviews-container{ 
        margin-top: 15px; 
        max-height: 160px; 
        overflow-y: auto; 
        padding-right: 8px; 
        margin-bottom: 20px; 
      }
      

      .reviews-container::-webkit-scrollbar{ 
        width: 6px; 
      }
      
      .reviews-container::-webkit-scrollbar-track{ 
        background: #1a1328; 
      }
      
      .reviews-container::-webkit-scrollbar-thumb{ 
        background: #a28eab; 
        border-radius: 10px; 
      }

      .card-reviews{ 
        display: flex; 
        justify-content: space-between; 
        background-color: #302b3f; 
        border: 3px dotted #a28eab; 
        padding: 10px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
      }
      
      .review-author h3{ 
        font-size: 16px; 
        font-weight: 700; 
      }
      
      .review-author p{ 
        font-size: 14px; 
        font-weight: 500; 
        color: #cccccc; 
      }
      
      .review-date{ 
        font-size: 12px; 
        color: #cccccc; 
        text-align: right; 
      }
      

      .req-tok-btn.disabled{ 
        background-color: #4a435d; 
        color: #888; 
        cursor: not-allowed; 
        border: 1px solid #5c5470; 
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

      .station-distance{ 
        margin-top: 12px; 
        margin-bottom: 2px; 
        padding: 10px 12px; 
        background: linear-gradient(135deg, rgba(170, 145, 185, 0.18), rgba(122, 103, 140, 0.22));
        border: 1px solid #bca7d5; 
        border-radius: 10px; 
        color: #f7f0ff; 
        font-size: 12.5px; 
        font-weight: 700; 
        letter-spacing: 0.2px;
      }

      .station-distance span{ 
        color: #8ef0b7; 
        font-weight: 800; 
      }
      
      .fas{ 
        font-family: "Font Awesome 5 Free" !important; 
        font-weight: 900 !important; 
      }
      

      .queue-badge{ 
        background: #302b3f; 
        padding: 4px 8px; 
        border-radius: 4px; 
        font-weight: 700; 
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
        .footer-top{ 
          grid-template-columns: 1fr; 
        }
        .footer-bottom{ 
          flex-direction: column; 
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
        <li><a href="./index.php">Stations</a></li>
        <li><a href="../USER TOKEN/user-token-page.php">Token</a></li>
        <li>
          <?php $profileReturnPath = 'USER STATION/index.php'; include '../profile-menu.php'; ?>
        </li>
      </ul>
    </nav>

    <div class="main-content">
      <h1 class="page-title">Fuel Stations</h1>
      <div class="search-container">
        <input placeholder="🔍 Search Station Name or Location" class="input-bar" type="text" id="stationSearch" onkeyup="searchStations()"/>
        <div class="filter-dropdown">
          <button class="all-station-btn" id="filterBtn">
            All Stations <i class="fas fa-chevron-circle-down"></i>
          </button>
          <div class="filter-content" id="filterDropdown">
            <button onclick="filterByStatus('all')">All Stations</button>
            <button onclick="filterByStatus('nearest')">Nearest</button>
            <button onclick="filterByStatus('AVAILABLE')">Available</button>
            <button onclick="filterByStatus('NO STOCK')">No Stock</button>
          </div>
        </div>
      </div>
      <div class="cards-container" id="stationsGrid">
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
          <?php
            $distanceValue = null;
            if($userLocation !== null){
              $distanceValue = sqrt(pow($userLocation['x'] - (float)$row['location_x'], 2) + pow($userLocation['y'] - (float)$row['location_y'], 2));
            }
          ?>
          <div class="card station-card-item" 
               data-name="<?php echo (strtolower($row['name'])); ?>" 
               data-location="<?php echo (strtolower($row['location'])); ?>" 
               data-status="<?php echo $displayStatus; ?>"
               data-distance="<?php echo $distanceValue !== null ? number_format((float)$distanceValue, 4, '.', '') : '999999'; ?>">
            <div class="card-image">
              <?php
                $stationRatingData = $conn->query("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(rating) AS rating_count FROM reviews WHERE station_id = $s_id")->fetch_assoc();
                $stationAverageRating = isset($stationRatingData['avg_rating']) ? (float)$stationRatingData['avg_rating'] : 0.0;
                $stationRatingCount = isset($stationRatingData['rating_count']) ? (int)$stationRatingData['rating_count'] : 0;
              ?>
              <img class="card-im" src="<?php echo $row['img_url']; ?>" onerror="this.src='../../Assets/card-im.png'"/>
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
                  <h2><?php echo ($row['name']); ?></h2>
                  <p><?php echo ($row['location']); ?></p>
                </div>
                <a href="<?php echo ($row['map_link']); ?>" target="_blank" class="location-icon">
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
              <div class="queue-status" style="margin-top: 15px; padding: 12px; background: #1a1328; border: 1px solid #4a435d; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                  <div style="display: flex; flex-direction: column; gap: 4px;">
                      <span style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Currently Serving</span>
                      <span style="font-size: 15px; font-weight: 700; color: #fff;">Token <span class="queue-badge">#<?php echo $serving; ?></span></span>
                  </div>
                  <div style="display: flex; flex-direction: column; gap: 4px; text-align: right;">
                      <span style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">In Queue</span>
                      <span style="font-size: 15px; font-weight: 700; color: #fff;"><span class="queue-badge"><?php echo $waiting; ?></span> People</span>
                  </div>
              </div>

              <div class="station-distance">
                <?php if($userLocation !== null): ?>
                  Distance: <span><?php echo number_format((float)$distanceValue, 2); ?></span> km
                <?php else: ?>
                  Distance: <span>N/A</span>
                <?php endif; ?>
              </div>

              <div class="card-info">
                <p>Updated: <?php echo date('h:i:s A', strtotime($row['created_at'])); ?></p>
              </div>
              <?php
                $currentUserRating = $conn->query("SELECT rating FROM reviews WHERE user_id={$_SESSION['user_id']} AND station_id=$s_id LIMIT 1")->fetch_assoc();
                $selectedRating = $currentUserRating ? (int)$currentUserRating['rating'] : 0;
              ?>
              <div class="card-review">
                <form method="POST" action="user_handler.php" class="station-rating-form">
                  <input type="hidden" name="action" value="rate_station">
                  <input type="hidden" name="station_id" value="<?php echo $s_id; ?>">
                  <div class="station-rating-stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                      <button type="submit" name="rating" value="<?php echo $i; ?>" class="star-rate-btn <?php echo ($selectedRating >= $i) ? 'selected' : ''; ?>" aria-label="Rate <?php echo $i; ?> out of 5">★</button>
                    <?php endfor; ?>
                  </div>
                </form>
                <p class="font-bold cursor-pointer" onclick="openReviewModal(<?php echo $s_id; ?>)">Review</p>
              </div>
              
              <div class="reviews-container">
                <?php
                    $reviews = $conn->query("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.station_id=$s_id ORDER BY r.created_at DESC");
                    $hasVisibleReviews = false;
                    if($reviews->num_rows > 0){
                        while($rev = $reviews->fetch_assoc()){
                            $commentText = trim((string)$rev['comment']);
                            if($commentText === '' || strtolower($commentText) === 'null'){
                                continue;
                            }
                            $hasVisibleReviews = true;
                ?>
                    <div class="card-reviews">
                        <div class="review-author">
                            <div class="review-text">
                                <h3 class="text-[#10B981]"><?php echo ($rev['full_name']); ?></h3>
                                <p>"<?php echo ($rev['comment']); ?>"</p>
                            </div>
                        </div>
                        <div>
                            <p class="review-date"><?php echo date('n/j/Y', strtotime($rev['created_at'])); ?></p>
                        </div>
                    </div>
                <?php 
                        }
                    }
                    if(!$hasVisibleReviews){ 
                ?>
                    <p style="font-size: 13px; color: #888; text-align: center; margin-bottom: 20px;">No reviews yet.</p>
                <?php 
                    } 
                ?>
              </div>

              <?php
                if($isNoStock){
              ?>
                <button class="req-tok-btn disabled" disabled onclick="void(0)">
                  OUT OF STOCK
                </button>
              <?php
                }
                else{
              ?>
                <button class="req-tok-btn" onclick="openTokenModal(<?php echo $s_id; ?>, <?php echo (($row['on_off_status'] ?? 'on') === 'on' && $row['petrol_stock'] > 0) ? 'true' : 'false'; ?>, <?php echo (($row['on_off_status'] ?? 'on') === 'on' && $row['diesel_stock'] > 0) ? 'true' : 'false'; ?>)">
                  REQUEST TOKEN
                </button>
              <?php
                }
              ?>
            </div>
          </div>
        <?php endwhile; ?>
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
              <li><a href="../USER HOME/index.php">Dashboard</a></li>
              <li><a href="./index.php">Stations</a></li>
              <li>
                <a href="../USER TOKEN/user-token-page.php">Token</a>
              </li>
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


    <div id="tokenModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Request Fuel Token</h2>
          <span class="close" onclick="closeModal('tokenModal')">&times;</span>
        </div>
        <form action="user_handler.php" method="POST">
          <input type="hidden" name="action" value="request_token">
          <input type="hidden" name="station_id" id="tokenStationId">
          <div class="modal-body">
            <label>Vehicle Type</label>
            <select name="vehicle_type" id="vehicleTypeSelect" onchange="updateVehicleDailyLimit()" required>
              <option value="" selected disabled>Select vehicle</option>
              <option value="bike">Bike</option>
              <option value="car">Car</option>
            </select>
            <input type="hidden" name="vehicle_type" id="vehicleTypeHidden" disabled>
            <label>Vehicle ID</label>
            <select name="car_id" id="tokenCarSelect" onchange="updateVehicleSelection()" <?php echo empty($userCars) ? 'disabled style="display:none"' : ''; ?> required>
              <option value="" selected disabled>Select vehicle ID</option>
              <?php foreach($userCars as $car): ?>
                <option value="<?php echo (int)$car['car_id']; ?>"><?php echo htmlspecialchars((string)$car['car_id'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars(ucfirst($car['vehicle_type']), ENT_QUOTES, 'UTF-8'); ?>)</option>
              <?php endforeach; ?>
              <?php if(!empty($userCars)): ?>
                <option value="__new__">Enter a new vehicle ID</option>
              <?php endif; ?>
            </select>
            <input type="number" name="car_id" id="tokenCarId" min="1" step="1" oninput="updateVehicleDailyLimit()" <?php echo empty($userCars) ? '' : 'disabled style="display:none"'; ?> required>
            <label>Fuel Type</label>
            <select name="fuel_type" id="fuelTypeSelect" required>
              <option value="petrol" id="optPetrol">Petrol</option>
              <option value="diesel" id="optDiesel">Diesel</option>
            </select>
            <p id="vehicleDailyLimitHint">Select a vehicle to see your remaining daily limit across all stations.</p>
            <label>Liters</label>
            <input type="number" name="liters" id="tokenLiters" min="0.01" step="0.01" disabled required>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn-submit" id="tokenSubmitButton" disabled>Get Token</button>
          </div>
        </form>
      </div>
    </div>

    <div id="reviewModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Add Review</h2>
          <span class="close" onclick="closeModal('reviewModal')">&times;</span>
        </div>
        <form action="user_handler.php" method="POST">
          <input type="hidden" name="action" value="add_review">
          <input type="hidden" name="station_id" id="reviewStationId">
          <div class="modal-body">
            <label style="display:block; margin-bottom:8px; font-weight:600; color:#fff;">Comment</label>
            <textarea name="comment" rows="5" required placeholder="Share your experience..."></textarea>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn-submit">Post</button>
          </div>
        </form>
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

      const filterBtn = document.getElementById('filterBtn');
      const filterDropdown = document.getElementById('filterDropdown');
      if(filterBtn && filterDropdown){
        filterBtn.addEventListener('click', function(e){ 
          e.stopPropagation(); 
          filterDropdown.classList.toggle('show'); 
        });
        

        window.addEventListener('click', function(){ 
          if(filterDropdown.classList.contains('show')){
            filterDropdown.classList.remove('show'); 
          }
        });
      }


      function filterByStatus(status){
        let cards = Array.from(document.getElementsByClassName('station-card-item'));
        const grid = document.getElementById('stationsGrid');

        if(status === 'nearest'){
          cards.sort((a, b) => Number(a.getAttribute('data-distance')) - Number(b.getAttribute('data-distance')));
          cards.forEach(card => grid.appendChild(card));
          document.getElementById('filterBtn').innerHTML = 'Nearest <i class="fas fa-chevron-circle-down"></i>';
          filterDropdown.classList.remove('show');
          return;
        }

        if(status === 'all'){
          cards.sort((a, b) => {
            const aName = (a.getAttribute('data-name') || '').toLowerCase();
            const bName = (b.getAttribute('data-name') || '').toLowerCase();
            return aName.localeCompare(bName);
          });
          cards.forEach(card => grid.appendChild(card));
        }

        for(let i = 0; i < cards.length; i++){
          let cardStatus = cards[i].getAttribute('data-status');
          if(status === 'all' || cardStatus === status){
            cards[i].style.display = "";
          }
          else{
            cards[i].style.display = "none";
          }
        }
        
 
        let newText = "";
        if(status === 'all'){
          newText = 'All Stations';
        }
        else{
          newText = status;
        }
        document.getElementById('filterBtn').innerHTML = newText + ' <i class="fas fa-chevron-circle-down"></i>';
        filterDropdown.classList.remove('show');
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


      const vehicleDailyLimits = <?php echo json_encode($vehicleDailyLimits); ?>;
      const userCars = <?php echo json_encode($userCars); ?>;
      const userDailyCarUsage = <?php echo json_encode($userDailyCarUsage); ?>;
      const hasSavedCars = userCars.length > 0;

      function openTokenModal(id, hasPetrol, hasDiesel){

        let isLoggedIn = <?php 
          if(isset($_SESSION['user_id'])){
            echo 'true';
          }
          else{
            echo 'false';
          }
        ?>;
        
        if(!isLoggedIn){ 
          alert("Please login to request a token."); 
          window.location.href = "../../Registration_Page/SignIn/index.php"; 
          return; 
        }
        
        const fuelTypeSelect = document.getElementById('fuelTypeSelect');
        const availableFuels = [];

        if(hasPetrol){
          availableFuels.push('petrol');
        }
        if(hasDiesel){
          availableFuels.push('diesel');
        }

        if(availableFuels.length === 0){
          fuelTypeSelect.innerHTML = '<option value="" selected disabled>No fuel available</option>';
          fuelTypeSelect.value = '';
          document.getElementById('tokenLiters').value = '';
          document.getElementById('tokenLiters').disabled = true;
          document.getElementById('tokenSubmitButton').disabled = true;
          document.getElementById('vehicleDailyLimitHint').textContent = 'This station has no fuel currently available.';
          document.getElementById('tokenModal').style.display = 'block';
          return;
        }

        fuelTypeSelect.innerHTML = availableFuels.map(fuel => `<option value="${fuel}">${fuel.charAt(0).toUpperCase() + fuel.slice(1)}</option>`).join('');
        fuelTypeSelect.value = availableFuels[0];

        document.getElementById('tokenStationId').value = id;
        document.getElementById('vehicleTypeSelect').value = '';
        document.getElementById('vehicleTypeSelect').disabled = false;
        document.getElementById('vehicleTypeHidden').value = '';
        document.getElementById('vehicleTypeHidden').disabled = true;
        document.getElementById('tokenCarSelect').value = '';
        document.getElementById('tokenCarId').value = '';
        updateVehicleSelection();
        document.getElementById('tokenLiters').value = '';
        document.getElementById('tokenLiters').removeAttribute('max');
        document.getElementById('tokenLiters').disabled = true;
        document.getElementById('tokenSubmitButton').disabled = true;
        document.getElementById('vehicleDailyLimitHint').textContent = 'Choose or enter a vehicle ID to see its remaining daily limit.';
        
        document.getElementById('tokenModal').style.display = 'block';
      }

      function updateVehicleDailyLimit(){
        const vehicleType = document.getElementById('vehicleTypeSelect').value;
        const fuelType = document.getElementById('fuelTypeSelect').value;
        const carSelect = document.getElementById('tokenCarSelect');
        const carInput = document.getElementById('tokenCarId');
        const carId = hasSavedCars && carSelect.value !== '__new__' ? carSelect.value : carInput.value;
        const dailyLimit = Number(vehicleDailyLimits[vehicleType] || 0);
        const usedToday = Number(userDailyCarUsage[carId] || 0);
        const remainingLimit = Math.max(0, dailyLimit - usedToday);
        const litersInput = document.getElementById('tokenLiters');
        const hasVehicleId = /^\d+$/.test(carId) && Number(carId) > 0;

        litersInput.max = remainingLimit.toFixed(2);
        litersInput.disabled = !vehicleType || !hasVehicleId || !fuelType || remainingLimit <= 0;
        document.getElementById('tokenSubmitButton').disabled = !vehicleType || !hasVehicleId || !fuelType || remainingLimit <= 0;
        document.getElementById('vehicleDailyLimitHint').textContent = vehicleType && hasVehicleId
          ? `Vehicle ${carId}: ${remainingLimit.toFixed(2)} L remaining today of ${dailyLimit.toFixed(2)} L.`
          : 'Choose or enter a vehicle ID to see its remaining daily limit.';
      }

      function updateVehicleSelection(){
        const carSelect = document.getElementById('tokenCarSelect');
        const carInput = document.getElementById('tokenCarId');
        const vehicleTypeSelect = document.getElementById('vehicleTypeSelect');
        const vehicleTypeHidden = document.getElementById('vehicleTypeHidden');
        const selectedCar = userCars.find(car => String(car.car_id) === carSelect.value);
        const isNewCar = !hasSavedCars || carSelect.value === '__new__';

        carSelect.disabled = !hasSavedCars || isNewCar;
        carSelect.style.display = hasSavedCars && !isNewCar ? '' : 'none';
        carInput.disabled = !isNewCar;
        carInput.style.display = isNewCar ? '' : 'none';
        vehicleTypeSelect.disabled = Boolean(selectedCar);
        vehicleTypeHidden.disabled = !selectedCar;

        if(selectedCar){
          vehicleTypeSelect.value = selectedCar.vehicle_type;
          vehicleTypeHidden.value = selectedCar.vehicle_type;
        }
        else if(isNewCar && hasSavedCars){
          carInput.value = '';
          vehicleTypeSelect.value = '';
        }

        updateVehicleDailyLimit();
      }


      function openReviewModal(id){
        let isLoggedIn = <?php 
          if(isset($_SESSION['user_id'])){
            echo 'true';
          }
          else{
            echo 'false';
          }
        ?>;
        
        if(!isLoggedIn){ 
          alert("Please login to post a review."); 
          window.location.href = "../../Registration_Page/SignIn/index.php"; 
          return; 
        }
        
        document.getElementById('reviewStationId').value = id;
        document.getElementById('reviewModal').style.display = 'block';
      }


      function closeModal(modalId){ 
        document.getElementById(modalId).style.display = 'none'; 
      }


      window.onclick = function(event){ 
        if(event.target.className === 'modal'){
          event.target.style.display = 'none'; 
        }
      }
    </script>
    <script src="../profile-menu.js" defer></script>
  </body>
</html>