<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')){
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

if($action == 'update_daily_limits' && $_SESSION['role'] == 'admin'){
    $bikeLimit = $_POST['bike-daily-limit'] ?? null;
    $carLimit = $_POST['car-daily-limit'] ?? null;

    if(!is_numeric($bikeLimit) || !is_finite((float)$bikeLimit) || (float)$bikeLimit < 0 || !is_numeric($carLimit) || !is_finite((float)$carLimit) || (float)$carLimit < 0){
        header("Location: index.php?msg=Daily limits must be valid non-negative amounts.");
        exit();
    }

    $limitStmt = $conn->prepare("INSERT INTO vehicle_daily_limits (vehicle_type, daily_limit) VALUES (?, ?) ON DUPLICATE KEY UPDATE daily_limit=VALUES(daily_limit)");
    foreach(['bike' => (float)$bikeLimit, 'car' => (float)$carLimit] as $vehicleType => $dailyLimit){
        $limitStmt->bind_param("sd", $vehicleType, $dailyLimit);
        $limitStmt->execute();
    }

    header("Location: index.php?msg=Daily vehicle limits updated for all stations.");
    exit();
}

if($action == 'add' && $_SESSION['role'] == 'admin'){
    $name = $_POST['name'];
    $location = $_POST['location'];
    $locationX = $_POST['location_x'] ?? null;
    $locationY = $_POST['location_y'] ?? null;
    if(!is_numeric($locationX) || !is_finite((float)$locationX) || !is_numeric($locationY) || !is_finite((float)$locationY)){
        header("Location: index.php?msg=Enter valid station coordinates.");
        exit();
    }
    $locationX = (float)$locationX;
    $locationY = (float)$locationY;
    $img_url = $_POST['img_url'] ?: '../../Assets/card-im.png';
    $map_link = $_POST['map_link'];
    $on_off_status = isset($_POST['on_off_status']) && $_POST['on_off_status'] === 'off' ? 'off' : 'on';
    $stmt = $conn->prepare("INSERT INTO stations (name, location, location_x, location_y, img_url, map_link, on_off_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsss", $name, $location, $locationX, $locationY, $img_url, $map_link, $on_off_status);
    
    if($stmt->execute()){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Add Station', 'Added station: $name')");
        header("Location: index.php?msg=Station added successfully");
    }
    else{
        echo "Error: " . $stmt->error;
    }
}

if($action == 'edit' && $_SESSION['role'] == 'admin'){
    $id = $_POST['station_id'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $locationX = $_POST['location_x'] ?? null;
    $locationY = $_POST['location_y'] ?? null;
    if(!is_numeric($locationX) || !is_finite((float)$locationX) || !is_numeric($locationY) || !is_finite((float)$locationY)){
        header("Location: index.php?msg=Enter valid station coordinates.");
        exit();
    }
    $locationX = (float)$locationX;
    $locationY = (float)$locationY;
    $img_url = $_POST['img_url'] ?: '../../Assets/card-im.png';
    $map_link = $_POST['map_link'];
    $on_off_status = isset($_POST['on_off_status']) && $_POST['on_off_status'] === 'off' ? 'off' : 'on';
    $stmt = $conn->prepare("UPDATE stations SET name=?, location=?, location_x=?, location_y=?, img_url=?, map_link=?, on_off_status=? WHERE id=?");
    $stmt->bind_param("ssddsssi", $name, $location, $locationX, $locationY, $img_url, $map_link, $on_off_status, $id);
    
    if($stmt->execute()){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Edit Station', 'Edited station ID: $id')");
        header("Location: index.php?msg=Station updated successfully");
    }
    else{
        echo "Error: " . $stmt->error;
    }
}

if($action == 'delete' && $_SESSION['role'] == 'admin'){
    $id = $_GET['id'];
    if($conn->query("DELETE FROM stations WHERE id=$id")){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Delete Station', 'Deleted station ID: $id')");
        header("Location: index.php?msg=Station deleted");
        exit();
    }
}

if($action == 'toggle'){
    $id = $_REQUEST['id'];
    
    if($conn->query("UPDATE stations SET on_off_status = IF(on_off_status='on', 'off', 'on') WHERE id=$id")){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Toggle Status', 'Toggled station ID $id')");
        
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        $sep = (strpos($redirect, '?') === false) ? '?' : '&';
        header("Location: " . $redirect . $sep . "msg=Status toggled successfully");
        exit();
    }
    else{
        echo "Error updating status: " . $conn->error;
    }
}

if($action == 'log_liter'){
    $fallback = ($_SESSION['role'] == 'admin') ? 'index.php' : '../../Staff_Page/STAFF STATION/index.php';
    $redirect = $_SERVER['HTTP_REFERER'] ?? $fallback;
    $stationId = filter_var($_POST['station_id'] ?? null, FILTER_VALIDATE_INT);
    $petrolRaw = $_POST['petrol_add'] ?? '0';
    $dieselRaw = $_POST['diesel_add'] ?? '0';

    if($stationId === false || $stationId === null || $stationId < 1 || !is_numeric($petrolRaw) || !is_finite((float)$petrolRaw) || (float)$petrolRaw < 0 || !is_numeric($dieselRaw) || !is_finite((float)$dieselRaw) || (float)$dieselRaw < 0 || ((float)$petrolRaw == 0 && (float)$dieselRaw == 0)){
        $separator = strpos($redirect, '?') === false ? '?' : '&';
        header("Location: " . $redirect . $separator . "msg=Enter a valid positive stock amount.");
        exit();
    }

    $petrolAdd = (float)$petrolRaw;
    $dieselAdd = (float)$dieselRaw;
    $conn->begin_transaction();
    try{
        $stockStmt = $conn->prepare("UPDATE stations SET petrol_stock = petrol_stock + ?, diesel_stock = diesel_stock + ? WHERE id = ?");
        $stockStmt->bind_param("ddi", $petrolAdd, $dieselAdd, $stationId);
        $stockStmt->execute();
        if($stockStmt->affected_rows !== 1){
            throw new RuntimeException("Station not found.");
        }

        $details = "Added $petrolAdd L Petrol and $dieselAdd L Diesel to station ID $stationId";
        $logStmt = $conn->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, 'Log Liter', ?)");
        $logStmt->bind_param("is", $user_id, $details);
        $logStmt->execute();
        $conn->commit();

        header("Location: " . $redirect);
        exit();
    }
    catch(Throwable $e){
        $conn->rollback();
        $separator = strpos($redirect, '?') === false ? '?' : '&';
        header("Location: " . $redirect . $separator . "msg=Stock update failed.");
        exit();
    }
}
?>