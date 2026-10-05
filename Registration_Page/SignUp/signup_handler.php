<?php
session_start();
include '../../db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $full_name = $_POST['full-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if($result->num_rows > 0){
        echo "<script>alert('Email already registered! Please use another email.'); window.location='index.php';</script>";
    }
    else{
        $sql = "INSERT INTO users (full_name, email, password, role, location_x, location_y) VALUES ('$full_name', '$email', '$password', 'user', 0.00, 0.00)";
        if($conn->query($sql) === TRUE){
            header("Location: ../SignIn/index.php?msg=Registration successful. Please login.");
        }
        else{
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>