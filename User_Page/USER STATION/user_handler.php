<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

if($action == 'rate_station'){
    $station_id = $_POST['station_id'] ?? $_GET['station_id'];
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : (isset($_GET['rating']) ? (int)$_GET['rating'] : 0);

    if($rating < 1 || $rating > 5){
        header("Location: index.php?msg=Please select a valid rating from 1 to 5.");
        exit();
    }

    $existing = $conn->query("SELECT id FROM reviews WHERE user_id=$user_id AND station_id=$station_id");
    if($existing->num_rows > 0){
        $stmt = $conn->prepare("UPDATE reviews SET rating=? WHERE user_id=? AND station_id=?");
        $stmt->bind_param("iii", $rating, $user_id, $station_id);
        $stmt->execute();
    }
    else{
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, station_id, comment, rating) VALUES (?, ?, '', ?)");
        $stmt->bind_param("iii", $user_id, $station_id, $rating);
        $stmt->execute();
    }

    header("Location: index.php?msg=Rating saved");
}

if($action == 'add_review'){
    $station_id = $_POST['station_id'];
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

    if($comment === ''){
        header("Location: index.php?msg=Please write a comment before posting.");
        exit();
    }

    if($rating !== null && ($rating < 1 || $rating > 5)){
        header("Location: index.php?msg=Please select a valid rating from 1 to 5.");
        exit();
    }

    $check = $conn->query("SELECT id FROM reviews WHERE user_id=$user_id AND station_id=$station_id");
    if($check->num_rows > 0){
        if($rating !== null){
            $stmt = $conn->prepare("UPDATE reviews SET comment=?, rating=? WHERE user_id=? AND station_id=?");
            $stmt->bind_param("siii", $comment, $rating, $user_id, $station_id);
        }
        else{
            $stmt = $conn->prepare("UPDATE reviews SET comment=? WHERE user_id=? AND station_id=?");
            $stmt->bind_param("sii", $comment, $user_id, $station_id);
        }
        $stmt->execute();
    }
    else{
        if($rating === null){
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, station_id, comment, rating) VALUES (?, ?, ?, NULL)");
            $stmt->bind_param("is", $user_id, $station_id, $comment);
        }
        else{
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, station_id, comment, rating) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isi", $user_id, $station_id, $comment, $rating);
        }
        $stmt->execute();
    }
    
    header("Location: index.php?msg=Review posted");
}

if($action == 'request_token'){
    $station_id = filter_var($_POST['station_id'] ?? null, FILTER_VALIDATE_INT);
    $car_id = filter_var($_POST['car_id'] ?? null, FILTER_VALIDATE_INT);
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';
    $litersInput = $_POST['liters'] ?? null;

    if(!$station_id || !$car_id || $car_id < 1 || !in_array($vehicle_type, ['bike', 'car'], true) || !in_array($fuel_type, ['petrol', 'diesel'], true) || !is_numeric($litersInput) || !is_finite((float)$litersInput) || (float)$litersInput <= 0){
        header("Location: index.php?msg=Please select a vehicle, enter a valid car ID, choose fuel type, and enter a valid liter amount.");
        exit();
    }

    $liters = (float)$litersInput;
    $stockColumn = $fuel_type === 'petrol' ? 'petrol_stock' : 'diesel_stock';

    $conn->begin_transaction();
    $userLockStmt = $conn->prepare("SELECT id FROM users WHERE id=? FOR UPDATE");
    $userLockStmt->bind_param("i", $user_id);
    $userLockStmt->execute();
    if($userLockStmt->get_result()->num_rows === 0){
        $conn->rollback();
        header("Location: index.php?msg=User account not found.");
        exit();
    }

    $stationStmt = $conn->prepare("SELECT petrol_stock, diesel_stock, status, on_off_status FROM stations WHERE id=? FOR UPDATE");
    $stationStmt->bind_param("i", $station_id);
    $stationStmt->execute();
    $station = $stationStmt->get_result()->fetch_assoc();

    if(!$station){
        $conn->rollback();
        header("Location: index.php?msg=Station not found.");
        exit();
    }

    if(($station['on_off_status'] ?? 'on') !== 'on'){
        $conn->rollback();
        header("Location: index.php?msg=This station is currently off.");
        exit();
    }

    if(($fuel_type === 'petrol' && (float)$station['petrol_stock'] <= 0) || ($fuel_type === 'diesel' && (float)$station['diesel_stock'] <= 0)){
        $conn->rollback();
        header("Location: index.php?msg=This station does not offer $fuel_type right now.");
        exit();
    }

    if($station['status'] === 'NO STOCK'){
        $conn->rollback();
        header("Location: index.php?msg=This station is currently not issuing tokens (NO STOCK).");
        exit();
    }

    if($liters > (float)$station[$stockColumn]){
        $available = (float)$station[$stockColumn];
        $conn->rollback();
        header("Location: index.php?msg=Not enough stock. Current $fuel_type availability is only $available L.");
        exit();
    }

    $activeTokenStmt = $conn->prepare("SELECT id FROM tokens WHERE user_id=? AND status IN ('Pending', 'Called') LIMIT 1");
    $activeTokenStmt->bind_param("i", $user_id);
    $activeTokenStmt->execute();
    if($activeTokenStmt->get_result()->num_rows > 0){
        $conn->rollback();
        header("Location: index.php?msg=You already have an active token. Only one token at a time.");
        exit();
    }

    $carStmt = $conn->prepare("SELECT car_type FROM cars WHERE user_id=? AND car_id=? FOR UPDATE");
    $carStmt->bind_param("ii", $user_id, $car_id);
    $carStmt->execute();
    $savedCar = $carStmt->get_result()->fetch_assoc();
    if($savedCar){
        $vehicle_type = $savedCar['car_type'];
    }

    $limitStmt = $conn->prepare("SELECT daily_limit FROM vehicle_daily_limits WHERE vehicle_type=?");
    $limitStmt->bind_param("s", $vehicle_type);
    $limitStmt->execute();
    $limitResult = $limitStmt->get_result()->fetch_assoc();
    $dailyLimit = $limitResult ? (float)$limitResult['daily_limit'] : ($vehicle_type === 'bike' ? 50.0 : 100.0);

    $usageStmt = $conn->prepare("SELECT COALESCE(SUM(liters), 0) AS used_liters FROM tokens WHERE user_id=? AND car_id=? AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY");
    $usageStmt->bind_param("ii", $user_id, $car_id);
    $usageStmt->execute();
    $usedLiters = (float)$usageStmt->get_result()->fetch_assoc()['used_liters'];
    if($usedLiters + $liters > $dailyLimit + 0.00001){
        $remainingLiters = max(0, $dailyLimit - $usedLiters);
        $conn->rollback();
        header("Location: index.php?msg=Daily limit for vehicle ID $car_id is $dailyLimit L. Remaining today: $remainingLiters L.");
        exit();
    }

    $serialStmt = $conn->prepare("SELECT COALESCE(MAX(serial_number), 0) + 1 AS next_serial FROM tokens WHERE station_id=? AND DATE(created_at)=CURDATE()");
    $serialStmt->bind_param("i", $station_id);
    $serialStmt->execute();
    $nextSerial = (int)$serialStmt->get_result()->fetch_assoc()['next_serial'];

    $carSaveStmt = $conn->prepare("INSERT IGNORE INTO cars (user_id, car_id, car_type) VALUES (?, ?, ?)");
    $carSaveStmt->bind_param("iis", $user_id, $car_id, $vehicle_type);
    if(!$carSaveStmt->execute()){
        $conn->rollback();
        header("Location: index.php?msg=Could not save your car information.");
        exit();
    }

    $insertStmt = $conn->prepare("INSERT INTO tokens (user_id, station_id, car_id, vehicle_type, fuel_type, liters, serial_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("iiissdi", $user_id, $station_id, $car_id, $vehicle_type, $fuel_type, $liters, $nextSerial);
    if(!$insertStmt->execute()){
        $conn->rollback();
        header("Location: index.php?msg=Could not create the fuel token.");
        exit();
    }

    $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Request Token', 'Requested $liters L $fuel_type for car ID $car_id ($vehicle_type) at station ID $station_id. Serial: #$nextSerial')");
    $conn->commit();
    header("Location: ../USER TOKEN/user-token-page.php?msg=Token requested successfully! Your serial is #$nextSerial");
    exit();
}
?>