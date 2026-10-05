<?php
session_start();
include '../db.php';

$returnPaths = [
    'USER HOME/index.php',
    'USER STATION/index.php',
    'USER TOKEN/user-token-page.php'
];
$returnTo = $_POST['return_to'] ?? 'USER HOME/index.php';
if(!in_array($returnTo, $returnPaths, true)){
    $returnTo = 'USER HOME/index.php';
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../Registration_Page/SignIn/index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['profile_csrf_token'] ?? '', $_POST['csrf_token'] ?? '')){
    $_SESSION['profile_notice'] = 'Please reload the page and try again.';
    header("Location: " . $returnTo);
    exit();
}

$location = trim($_POST['location'] ?? '');
$locationLength = function_exists('mb_strlen') ? mb_strlen($location, 'UTF-8') : strlen($location);
if($location === '' || $locationLength > 255){
    $_SESSION['profile_notice'] = 'Enter a location with no more than 255 characters.';
    header("Location: " . $returnTo);
    exit();
}

$locationX = isset($_POST['location_x']) ? (float)$_POST['location_x'] : 0.00;
$locationY = isset($_POST['location_y']) ? (float)$_POST['location_y'] : 0.00;

if(!is_finite($locationX) || !is_finite($locationY)){
    $_SESSION['profile_notice'] = 'Enter valid coordinates for your current location.';
    header("Location: " . $returnTo);
    exit();
}

$updateStmt = $conn->prepare("UPDATE users SET location=?, location_x=?, location_y=? WHERE id=?");
$updateStmt->bind_param("sdsi", $location, $locationX, $locationY, $_SESSION['user_id']);
if($updateStmt->execute()){
    $_SESSION['profile_notice'] = 'Location and coordinates updated.';
}
else{
    $_SESSION['profile_notice'] = 'Could not update your location.';
}

header("Location: " . $returnTo);
exit();
