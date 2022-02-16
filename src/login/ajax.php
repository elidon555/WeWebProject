<?php

use PHPMailer\PHPMailer\PHPMailer;

include_once('../_config/constants.php');

if ($_POST['action'] == 'login') {


    /** @var  $conn */
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $sql = "SELECT 
           u.user_id,
           u.email,
           u.phone_number,
           u.first_name,
           u.last_name,
           u.password,
           roles.role_name 
        FROM users as u,
            users_to_roles as r,roles 
        WHERE ( email='" . $email . "' OR phone_number='" . $email . "' )
        AND u.user_id=r.user_id AND r.role_id=roles.role_id
    ";

    $res = mysqli_query($conn, $sql);

    if (!$res) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    if (mysqli_num_rows($res) === 1 && $pass != "") {

        $row = mysqli_fetch_assoc($res);
        $id = $row['user_id'];
        $firstName = ucfirst(strtolower($row['first_name']));
        $lastName = ucfirst(strtolower($row['last_name']));
        $fullName = $firstName . " " . $lastName;
        $role = $row['role_name'];
        $hash = $row['password'];

        if (password_verify($pass, $hash)) {

            $_SESSION['user'] = $email;
            $_SESSION['id'] = $id;
            $_SESSION['fullName'] = $fullName;
            $_SESSION['role'] = $role;

            echo json_encode(array("status" => 200, "message" => "Success" . __LINE__));
            exit;

        } else {
            echo json_encode(array("status" => 404, "message" => "Wrong login information!" . __LINE__));
            exit;
        }

    } else {
        echo json_encode(array("status" => 404, "message" => "User does not exist!" . __LINE__));
        exit;
    }

}

if ($_POST['action'] == 'reset') {

    /** @var $conn */
    //Filter email and validate it
    $email = mysqli_escape_string($conn, $_POST['email']);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    $query = "select email,password from users where email='$email'";

    $result_user = mysqli_query($conn, $query);

    if (!$result_user) {
        echo json_encode(array("status" => 404, "message" => 'Internal server error!!' . __LINE__));
        exit;
    }


    if (mysqli_num_rows($result_user) == 1) {
        $row = mysqli_fetch_assoc($result_user);

        $email = $row['email'];

        $encrypted_email = urlencode(openssl_encrypt($row['email'], 'AES-256-CBC', '25c6c7ff35b9979b151f2136cd13b0ff'));

        if (!$email) {
            echo json_encode(array("status" => 404, "message" => 'Invalid email address please type a valid email address!' . __LINE__));
            exit;
        } else {
            $query_check_email = "SELECT * FROM `users` WHERE email='" . $email . "'";
            $results = mysqli_query($conn, $query_check_email);
            $row = mysqli_num_rows($results);
            if ($row == "") {
                echo json_encode(array("status" => 404, "message" => 'No user is registered with this email address!' . __LINE__));
                exit;
            }
        }

        $expFormat = mktime(
            date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y")
        );
        $expDate = date("Y-m-d H:i:s", $expFormat);
        $key = md5((2418 * 2) . "" . $email);
        $addKey = substr(md5(uniqid(rand(), 1)), 3, 10);
        $key = $key . $addKey;

        $query_key = "INSERT INTO `password_reset_temp` (`email`, `key`, `expDate`)
                        VALUES ('" . $email . "', '" . $key . "', '" . $expDate . "') ";
        $result_key = mysqli_query($conn, $query_key);

        if (!$result_key) {
            echo json_encode(array("status" => 404, "message" => 'Internal server error!' . __LINE__));
            exit;
        }


        $body = '<p>Dear user,</p>';
        $body .= '<p>Please click on the following link to reset your password.</p>';
        $body .= '<p>-------------------------------------------------------------</p>';
        $body .= '<p><a href="http://localhost/WeWebProject/src/reset-password/index.php?key=' . $key . '&email=' . $encrypted_email . '&action=reset" target="_blank">
                    http://localhost/WeWebProject/src/reset-password/index.php?key=' . $key . '&email=' . $encrypted_email . '&action=reset</a></p>';
        $body .= '<p>-------------------------------------------------------------</p>';
        $body .= '<p>Please be sure to copy the entire link into your browser.
                    The link will expire after 1 day for security reason.</p>';
        $body .= '<p>If you did not request this forgotten password email, no action 
                    is needed, your password will not be reset.</p>';
        $body .= '<p>Thanks,</p>';
        $body .= '<p>WeWeb</p>';

        $subject = "Password Recovery - WeWeb";
        $email_to = $email;
        $from_server = "noreply@weweb123.com";


        /**
         * Import phpmailer class
         */
        require '../_partials/PHPMailer/src/PHPMailer.php';
        require '../_partials/PHPMailer/src/Exception.php';
        require '../_partials/PHPMailer/src/SMTP.php';
        $mail = new PHPMailer();


        /**
         * Set up the email
         */
        $mail->CharSet = "utf-8";
        $mail->IsSMTP();
        $mail->SMTPSecure = "ssl";
        $mail->SMTPAuth = true;

        $mail->Username = "elidonneziri@gmail.com";
        $mail->Password = "ElidonNeziri.1%";
        $mail->Host = "smtp.gmail.com";
        $mail->Port = "465";

        $mail->Sender = $from_server; // indicates ReturnPath header
        $mail->FromName = 'Elidon Neziri';

        //Email address,subject,and content
        $mail->AddAddress($email_to);
        $mail->Subject = $subject;
        $mail->IsHTML(true);
        $mail->Body = $body;
        if ($mail->Send()) {
            echo json_encode(array("status" => 200, "message" => "Mail sent!" . __LINE__));
        } else {
            echo json_encode(array("status" => 404, 'Failed to send email!' . __LINE__));
        }
    }

}


?>


