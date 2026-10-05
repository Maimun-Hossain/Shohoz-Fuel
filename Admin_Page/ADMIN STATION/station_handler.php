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
    $img_url = $_POST['img_url'] ?: '../../Assets/card-im.png';
    $map_link = $_POST['map_link'];
    $on_off_status = isset($_POST['on_off_status']) && $_POST['on_off_status'] === 'off' ? 'off' : 'on';
    $stmt = $conn->prepare("INSERT INTO stations (name, location, img_url, map_link, on_off_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $location, $img_url, $map_link, $on_off_status);
    
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
    $img_url = $_POST['img_url'] ?: '../../Assets/card-im.png';
    $map_link = $_POST['map_link'];
    $on_off_status = isset($_POST['on_off_status']) && $_POST['on_off_status'] === 'off' ? 'off' : 'on';
    $stmt = $conn->prepare("UPDATE stations SET name=?, location=?, img_url=?, map_link=?, on_off_status=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $location, $img_url, $map_link, $on_off_status, $id);
    
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
    $id = $_POST['station_id'];
    $petrol_add = (float)$_POST['petrol_add'];
    $diesel_add = (float)$_POST['diesel_add'];

    if($conn->query("UPDATE stations SET petrol_stock = petrol_stock + $petrol_add, diesel_stock = diesel_stock + $diesel_add WHERE id=$id")){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Log Liter', 'Added $petrol_add L Petrol and $diesel_add L Diesel to station ID $id')");
        
        if(isset($_SERVER['HTTP_REFERER'])){
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
        else{
            $fallback = ($_SESSION['role'] == 'admin') ? 'index.php' : '../../Staff_Page/STAFF STATION/index.php';
            header("Location: $fallback");
        }
        exit();
    }
}
?>