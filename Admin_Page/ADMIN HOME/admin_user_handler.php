<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    die("Unauthorized");
}

$admin_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? '';

if($action == 'change_role'){
    $new_role = $_GET['role'];
    if($conn->query("UPDATE users SET role='$new_role' WHERE id=$user_id")){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($admin_id, 'Change Role', 'Changed User ID $user_id role to $new_role')");
    }
}

if($action == 'toggle_ban'){
    $current_status = $_GET['status'];

    if($current_status == 'Active'){
        $new_status = 'Banned';
    }
    else{
        $new_status = 'Active';
    }
    
    if($conn->query("UPDATE users SET status='$new_status' WHERE id=$user_id")){
        $conn->query("INSERT INTO logs (user_id, action, details) VALUES ($admin_id, 'Toggle Ban', 'Toggled User ID $user_id status to $new_status')");
    }
}

header("Location: index.php?msg=User updated successfully");
?>