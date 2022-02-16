<?php
include_once('../_config/constants.php');


if (isset($_GET["key"]) && isset($_GET["email"]) && $_GET["action"] == "reset") {

    $key = mysqli_real_escape_string($conn, $_GET["key"]);

    $emailEncrypted = mysqli_real_escape_string($conn, $_GET['email']);

    $email = openssl_decrypt(mysqli_real_escape_string($conn, $_GET['email']), 'AES-256-CBC', '25c6c7ff35b9979b151f2136cd13b0ff');


    $curDate = date("Y-m-d H:i:s");
    $query_check_key =
        "SELECT * FROM `password_reset_temp` WHERE `key`='" . $key . "' and `email`='" . $email . "' ";


    /** @var $conn */
    $result_check_key = mysqli_query($conn, $query_check_key);
    $row_count = mysqli_num_rows($result_check_key);
    $row = mysqli_fetch_assoc($result_check_key);
    $expDate = $row['expDate'];
    if ($row_count == 0 || $expDate <= $curDate) {
        ?>
        <script>
           localStorage.setItem('resetExpired', '1');
           window.location.href = '../login';
        </script>
        <?php
        exit;
    } else {
        ?>
        <head>
            <?php include_once('../_partials/visitor_header.php') ?>
            <!-- here form validation -->

            <title>Reset - Password</title>
        </head>

        <style>
            .mainDiv {
                display: flex;
                min-height: 100%;
                align-items: center;
                justify-content: center;
                background-color: #f9f9f9;
                font-family: 'Open Sans', sans-serif;
            }

            .cardStyle {
                width: 500px;
                border-color: white;
                background: #fff;
                padding: 36px 0;
                border-radius: 4px;
                margin: 30px 0;
                box-shadow: 0px 0 2px 0 rgba(0, 0, 0, 0.25);
            }

            #signupLogo {
                max-height: 100px;
                margin: auto;
                display: flex;
                flex-direction: column;
            }

            .formTitle {
                font-weight: 600;
                margin-top: 20px;
                color: #2F2D3B;
                text-align: center;
            }

            .inputLabel {
                font-size: 12px;
                color: #555;
                margin-bottom: 6px;
                margin-top: 24px;
            }

            .inputDiv {
                width: 70%;
                display: flex;
                flex-direction: column;
                margin: auto;
            }

            input {
                height: 40px;
                font-size: 16px;
                border-radius: 4px;
                border: none;
                border: solid 1px #ccc;
                padding: 0 11px;
            }

            input:disabled {
                cursor: not-allowed;
                border: solid 1px #eee;
            }

            .buttonWrapper {
                margin-top: 40px;
            }

            .submitButton {
                width: 70%;
                height: 40px;
                margin: auto;
                display: block;
                color: #fff;
                background-color: #065492;
                border-color: #065492;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.12);
                box-shadow: 0 2px 0 rgba(0, 0, 0, 0.035);
                border-radius: 4px;
                font-size: 14px;
                cursor: pointer;
            }

            .submitButton:disabled,
            button[disabled] {
                border: 1px solid #cccccc;
                background-color: #cccccc;
                color: #666666;
            }

        </style>

        <body>

        <div class="mainDiv">
            <div class="cardStyle">
                <img
                    src="https://s3-us-west-2.amazonaws.com/shipsy-public-assets/shipsy/SHIPSY_LOGO_BIRD_BLUE.png"
                    id="signupLogo" alt='logo' />

                <h2 class="formTitle">
                    Reset your password
                </h2>

                <div class="inputDiv">
                    <label class="inputLabel" for="password">New Password</label>
                    <input type="password" id="Password" name="pass1" required>
                    <small class="error_form" id="password_error_message"></small>
                </div>

                <div class="inputDiv">
                    <label class="inputLabel" for="ConfirmPassword">Confirm Password</label>
                    <input type="password" id="Confirm_password" name="pass2">
                    <small class="error_form" id="Confirm_password_error_message"></small>
                </div>

                <input type="hidden" id="Email" name="email" value="<?php echo $emailEncrypted; ?>" />

                <div class="buttonWrapper">
                    <button type="submit" value="Reset Password" id="submitButton"
                            class="submitButton pure-button pure-button-primary">
                        <span>Continue</span>
                    </button>
                </div>
            </div>
        </div>

        </body>

        <?php include_once('../_partials/footer.php');
    }
} ?>
<script src="../public/scripts.js?v=<?= filemtime('../public/scripts.js') ?>"></script>
<script src="scripts.js?v=<?= filemtime('scripts.js') ?>"></script>
