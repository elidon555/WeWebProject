<?php include('../_config/constants.php'); ?>

<head>
    <?php include('../_partials/visitor_header.php'); ?>
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
        <div class='text-center'><a href="#" data-target="#resetModal" data-toggle="modal">Forgot password?</a></div>
        <div style="border-bottom: 1px solid #dadde1;"></div>
        <br>
        <div class="text-center">


            <a href="http://localhost/WeWebProject/src/signup/">

                <button type="button" id="signup" class="btn btn-success btn-lg alignButtonCenter">
                    Create new account
                </button>
            </a>
        </div>
    </form>
    <br>
    <p class="text-center">Created By Elidon </p>


</div>

<div id="resetModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h2 class='col-11 modal-title text-center'>Password Reset</h2>
                <button type="button" class="close col-1" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="text-center">
                                <p>If you have forgotten your password you can reset it here.</p>
                                <div class="panel-body">
                                    <fieldset>
                                        <div class="form-group">
                                            <input id='Email' class="form-control input-lg" placeholder="E-mail Address"
                                                   name="email" type="email">
                                        </div>
                                        <input id='submitReset' class="btn btn-lg btn-primary btn-block"
                                               value="Send reset link!" type="submit">
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                    <button class="btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->


</body>

<?php
include_once('../_partials/footer.php');
?>
<script src="scripts.js?v=<?= time(); ?>"></script>






