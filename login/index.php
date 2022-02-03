<?php include('../_config/constants.php'); ?>


<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css?v=<?= filemtime('../_partials/style.css') ?>">
    <link rel="stylesheet" href="../_plugins/bootstrap.css">
    <link rel="stylesheet" href="../_plugins/animate.css">
    <script src="../_plugins/sweetalert2.all.min.js"></script>
    <script src="../_plugins/sweetalert2.min.css"></script>
    <!-- here form validation -->

    <title>Login - System</title>
</head>

<body>

<div class="login">
    <br><br>
    <h1 class="text-center">Login</h1> <br>

    <?php include('files/php/login-alerts.php') ?>

    <!-- loginform -->

    <form id="loginForm" style="width:400px;margin:auto" name="login" class="shadow p-3 mb-5 bg-white rounded"
          action="index.php" method="POST">
        <div class="form-group">

            <input id="email" type="text" class="form-control loginForm" name="email"
                   placeholder="Enter email or phone number!">

        </div>
        <div class="form-group">

            <input id="password" type="password" name="password" class="form-control loginForm" placeholder="Password">
        </div>

        <button id="login_btn" type="submit" name="submit" class="btn btn-primary btn-lg btn-block submitButtonLogin"
                value="Login">Submit
        </button>
        <p></p>
        <p class="text-center">Forgot password?</p>
        <div style="border-bottom: 1px solid #dadde1;"></div>
        <br>
        <div class="text-center">


            <a href="http://localhost/WeWebProject/signup/">

                <button type="button" id="signup" class="btn btn-success btn-lg alignButtonCenter">
                    Create new account
                </button>
            </a>
        </div>
    </form>
    <br>
    <p class="text-center">Created By Elidon </p>
</div>


<!-- Modal -->


</body>

<?php
include_once ('../_partials/footer.php');
?>

<script type="text/javascript" src="scripts.js?v=<?= filemtime('files/js/scripts.js') ?>"></script>


</html>