<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$petrol_price = (float)$_POST['petrol-price'];
$diesel_price = (float)$_POST['diesel-price'];

$conn->query("INSERT INTO fuel_market_prices (fuel_type, price_per_liter) VALUES ('petrol', $petrol_price) ON DUPLICATE KEY UPDATE price_per_liter=$petrol_price");
$conn->query("INSERT INTO fuel_market_prices (fuel_type, price_per_liter) VALUES ('diesel', $diesel_price) ON DUPLICATE KEY UPDATE price_per_liter=$diesel_price");

$conn->query("INSERT INTO logs (user_id, action, details) VALUES ($user_id, 'Update Price', 'Updated Petrol to $petrol_price and Diesel to $diesel_price')");

header("Location: index.php?msg=Prices updated successfully");
?>