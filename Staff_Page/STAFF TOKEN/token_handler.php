<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')){
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if($action == 'call'){
    $station_id = $_GET['station_id'];
    

    $result = $conn->query("SELECT id, serial_number FROM tokens WHERE station_id=$station_id AND status='Pending' AND DATE(created_at) = CURDATE() ORDER BY serial_number ASC LIMIT 1");
    if($row = $result->fetch_assoc()){
        $token_id = $row['id'];
        $serial = $row['serial_number'];
        
        if($conn->query("UPDATE tokens SET status='Called' WHERE id=$token_id")){
            $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Call Token', 'Called token #$serial at station ID $station_id')");
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'staff-token-page.php';
            $sep = (strpos($redirect, '?') === false) ? '?' : '&';
            header("Location: " . $redirect . $sep . "msg=Token #$serial called");
        }
    }
    else{
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'staff-token-page.php';
        $sep = (strpos($redirect, '?') === false) ? '?' : '&';
        header("Location: " . $redirect . $sep . "msg=No pending tokens");
    }
}

if($action == 'complete'){
    $token_id = $_GET['token_id'];
    

    $tokenResult = $conn->query("SELECT * FROM tokens WHERE id=$token_id");
    if($token = $tokenResult->fetch_assoc()){
        $station_id = $token['station_id'];
        $fuel_type = $token['fuel_type'];
        $liters = $token['liters'];
        $serial = $token['serial_number'];
        

        if($fuel_type == 'petrol'){
            $stock_col = 'petrol_stock';
        }
        else{
            $stock_col = 'diesel_stock';
        }
        

        $conn->begin_transaction();
        try{
            $conn->query("UPDATE tokens SET status='Completed' WHERE id=$token_id");
            $conn->query("UPDATE stations SET $stock_col = $stock_col - $liters WHERE id=$station_id");
            $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Complete Token', 'Completed token #$serial. Deducted $liters L $fuel_type from station ID $station_id')");
            $conn->commit();
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'staff-token-page.php';
            $sep = (strpos($redirect, '?') === false) ? '?' : '&';
            header("Location: " . $redirect . $sep . "msg=Token #$serial marked as completed");
        }
        catch (Exception $e){
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}
?>