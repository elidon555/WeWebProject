<?php
include_once('../_config/constants.php');
if (isset($_POST["email"]) && ($_POST["action"] == "reset")) {

    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    /** @var $conn */
    $email = openssl_decrypt(mysqli_real_escape_string($conn, $_POST["email"]), 'AES-256-CBC', '25c6c7ff35b9979b151f2136cd13b0ff');

    //Check if passwords match
    if ($password != $confirm_password) {
        echo json_encode(array("status" => 404, "message" => "Error! Password doesn't match! " . __LINE__));
        exit;
    }

    //Has the password
    $password = password_hash($password, PASSWORD_BCRYPT);

    $query_update_password = "
    UPDATE users SET password='" . $password . "'
    WHERE email='" . $email . "'
     ";

    $result_update_password = mysqli_query($conn, $query_update_password);

    mysqli_query($conn, "  
    DELETE FROM password_reset_temp 
    WHERE email='" . $email . "'
    ");

    if (!$result_update_password) {
        echo json_encode(array("status" => 404, "message" => "Internal server error! " . __LINE__));
    } else {
        echo json_encode(array("status" => 200, "message" => "Password successfully reset! " . __LINE__));
    }

    exit;
}