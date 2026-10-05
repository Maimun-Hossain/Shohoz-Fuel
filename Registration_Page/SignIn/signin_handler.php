<?php
session_start();
include '../../db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        

        if($user['status'] == 'Banned'){
            echo "<script>alert('Your account has been banned! Please contact the administrator.'); window.location='index.php';</script>";
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == 'admin'){
            header("Location: ../../Admin_Page/ADMIN HOME/index.php");
        }
        elseif ($user['role'] == 'staff'){
            header("Location: ../../Staff_Page/STAFF HOME/index.php");
        }
        else{
            header("Location: ../../home.php");
        }
    }
    else{
        echo "<script>alert('Invalid email or password'); window.location='index.php';</script>";
    }
}
?>