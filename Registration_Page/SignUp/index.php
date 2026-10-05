<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Crisis - Sign Up</title>
    <style>
    *{
      margin: 0;
      padding: 0;
    }
    body{
        background-color: #0F051A;
        font-family: 'Segoe UI';
        color: #ffffff;
    }
    .box-title{
        text-align: center;
    }
    .box{
        background-color: #302B3F;
        border: 1px solid #A28EAB;
        padding: 40px 60px;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
        margin: 100px auto;
    }

    @media (max-width: 500px){
      .box{
        padding: 30px 20px;
        margin: 50px auto;
      }
    }
    .inp{
        width: 95%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 8px;
    }
    .box-body{
        margin-top: 10px;
    }
    .link{
        color: #A28EAB;
        text-decoration: none;
    }
     .link:hover{
        color: #ffffff;
    }
    .info{
        text-align: center;
        margin-top: 20px;
    }
    .box button{
        width: 100%;
        background-color: #A28EAB;
        color: #ffffff;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
    }
    </style>
</head>
<body>
    <div class="box">
        <div class="box-title">
        <h1>Create Account</h1>
        <p>Join the Fuel Crisis Management System</p>
        </div>
        <form action="signup_handler.php" method="POST">
            <div class="box-body">
                <label for="full-name">Full Name</label>
                <input class="inp" type="text" name="full-name" id="full-name" required>
            </div>
            <div class="box-body">
                <label for="email">Email Address</label>
                <input class="inp" type="email" name="email" id="email" required>
            </div>
            <div class="box-body">
                <label for="password">Password</label>
                <input class="inp" type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="inp">Create Account</button>
        </form>
        <p class="info">Already have an account? <a class="link" href="../SignIn/index.php">Click Here.</a></p>
    </div>
</body>
</html>