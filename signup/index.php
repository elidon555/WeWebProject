<?php include_once('../_config/constants.php');

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../_plugins/bootstrap.css">
    <link rel="stylesheet" href="../_plugins/animate.css">
    <script src="../_plugins/sweetalert2.all.min.js"></script>
    <script src="../_plugins/sweetalert2.min.css"></script>
    <link rel="stylesheet" type="text/css" href="../_plugins/daterangepicker.css"/>
    <!-- here form validation -->

    <title>Login - System</title>
</head>

<body>

<div class="login shadow p-3 mb-5 bg-white rounded" style="width:450px;margin:auto ">

    <h2 class="modal-title font-weight-bold m-0 p-0 text-center" id="exampleModalLabel">Sign Up</h2>

    <h6 id="emailHelp" class="form-text text-muted text-center">It's quick and easy.</h6>
    <div style="border-bottom: 1px solid #dadde1;"></div>
    <br>

    <form style="width:400px;margin:auto" id="signUp" name="signUp" action="" method="POST">

        <div class="form-row">

            <div class="form-group col-md-4">
                <input type="text" class="signup form-control loginForm" name="first_name" placeholder="First Name"
                       id="first_name">
                <?php if (isset($_SESSION['firstNameError'])) {
                    echo $_SESSION['firstNameError'];
                    unset($_SESSION['firstNameError']);
                } ?>
                <small class="error_form" id="first_name_error_message"></small>
            </div>

            <div class="form-group col-md-4">
                <input type="text" class="signup form-control loginForm" name="last_name" placeholder="Last Name"
                       id="last_name">
                <small class="error_form" id="last_name_error_message"></small>
                <?php if (isset($_SESSION['lastNameError'])) {
                    echo $_SESSION['lastNameError'];
                    unset($_SESSION['lastNameError']);
                } ?>
            </div>

            <div class="form-group col-md-4">
                <input type="text" class="signup form-control loginForm" name="atesia" placeholder="Atesia" id="atesia">
                <?php if (isset($_SESSION['atesia'])) {
                    echo $_SESSION['atesia'];
                    unset($_SESSION['atesia']);
                } ?>
                <small class="error_form" id="atesia_error_message"></small>
            </div>

        </div>


        <div class="form-group">
            <input type="text" name="date" class="signup form-control loginForm" placeholder="Date" id="date">
            <small class="error_form" id="date_error_message"></small>
            <?php if (isset($_SESSION['dateErrorNull'])) {
                echo $_SESSION['dateErrorNull'];
                unset($_SESSION['dateErrorNull']);
                $skip = 1;
            }
            if (isset($_SESSION['dateError'])) {
                echo $_SESSION['dateError'];
                unset($_SESSION['dateError']);
            } ?>

        </div>


        <div class="form-group">
            <input type="text" name="email" class="signup form-control loginForm" placeholder="Email" id="email">
            <small class="error_form" id="email_error_message"></small>
            <?php if (isset($_SESSION['emailError'])) {
                echo $_SESSION['emailError'];
                unset($_SESSION['emailError']);
            } ?>
        </div>

        <div class="form-group">
            <input type="text" name="phone_number" class="signup form-control loginForm" placeholder="Phone number"
                   id="phone_number">
            <small class="error_form" id="phone_number_error_message"></small>
            <?php if (isset($_SESSION['phone_numberError'])) {
                echo $_SESSION['phone_numberError'];
                unset($_SESSION['phone_numberError']);
            } ?>
        </div>

        <div class="form-group">
            <input type="password" name="password" class="signup form-control loginForm" placeholder="Password" id="password">
            <small class="error_form" id="password_error_message"></small>
            <?php if (isset($_SESSION['pwdError'])) {
                echo $_SESSION['pwdError'];
                unset($_SESSION['pwdError']);
            } ?>
        </div>

        <div class="form-group">
            <input type="password" name="confirm_password" class="signup form-control loginForm" placeholder="Confirm Password"
                   id="confirm_password">
            <small class="error_form" id="confirm_password_error_message"></small>
            <?php if (isset($_SESSION['cnfpwdError'])) {
                echo $_SESSION['cnfpwdError'];
                unset($_SESSION['cnfpwdError']);
            } ?>
        </div>

        <div class="form-check" id="term_check">
            <input type="checkbox" class="signup form-check-input" id="terms" name="terms">
            <input type="text" id="checkJS" name="checkJS" style="display:none">
            <label class="form-check-label">I agree to the terms & conditions</label>
            <small class="error_form" id="terms_error_message"></small>
        </div>

        <small class="form-text text-muted">By clicking Sign Up, you agree to our Terms, Data Policy and Cookies Policy.
            You may receive SMS Notifications from us and can opt out any time.</small>
        <?php if (isset($_SESSION['checkTerms'])) {
            echo $_SESSION['checkTerms'];
            unset($_SESSION['checkTerms']);
        } ?>

        <h6 class="form-text text-muted text-center">Already a member? <a style=" text-decoration: underline"
                                                                          href="http://localhost/WeWebProject/login/">Log
                in!</a></h6>
        <br>

        <div class="text-center">
            <button type="submit" name="submit" class="btn btn-success btn-lg alignButtonCenter pl-5 pr-5" id="butsave">
                Sign Up
            </button>
        </div>


    </form>


</div>

<input id="formChecker" value="1" style="display:none">
<button id="signup-ajax" style="display:none"></button>

</body>

<?php
include_once ('../_partials/footer.php');
?>
<script type="text/javascript" src="scripts.js?v=<?= time(); ?>"></script>


</html>