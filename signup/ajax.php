<?php

include('../_config/constants.php');

if (isset($_SESSION['success-signup'])) {
    echo $_SESSION['success-signup']; //Displaying session
    unset($_SESSION['success-signup']); //Removing session
    echo "<br>";
}

if ($_POST['action']=="signup") {

    /**
     * Marrja e te dhenave qe vijne nga front end per userin qe po rregjistrohet
     */
    $first_name = str_replace(' ', '', ucfirst(mysqli_real_escape_string($conn, $_POST['first_name'])));
    $last_name = str_replace(' ', '', ucfirst(mysqli_real_escape_string($conn, $_POST['last_name'])));
    $username = strtolower(substr($first_name, 0, 1) . "" . $last_name);
    $atesia = ucfirst(mysqli_real_escape_string($conn, $_POST['atesia']));

    $date = mysqli_real_escape_string($conn,$_POST['date']);
    $dateArray = explode("/", $date);
    $date = $dateArray[2] . "-" . $dateArray[1] . "-" . $dateArray[0];
    $epochDateSubmitted = strtotime($date);
    $epochDateCurrent = time();
    $dateDifference = $epochDateCurrent - $epochDateSubmitted;

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $pwd = $_POST['password'];
    $cnf_pwd = $_POST['confirm_password'];

    /**
     * Validimi i te dhenave
     */
    // Shohim nese ka user me kete E-MAIL dhe numer telefoni
    $query_check = "SELECT email, phone_number from users where email = '" . $email . "' OR phone_number = '" . $phone_number . "' ";

    $result_check = mysqli_query($conn, $query_check);

    $num_result = mysqli_num_rows($result_check);

    if (!$result_check) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    if ($num_result > 0) {
        echo json_encode(array("status" => 404, "message" => "E-Mail or phone number already exists on the system " . __LINE__));
        exit;
    }

    // validimi i emrit
    if (empty($first_name)) {
        echo json_encode(array("status" => 404, "message" => "First name can not be empty " . __LINE__));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,50}$/', $first_name)) {
        echo json_encode(array("status" => 404, "message" => "First name must be minimum 3 chars and should contains charachters only " . __LINE__));
        exit;
    }

    // Validimi i mbiemrit
    if (empty($last_name)) {
        echo json_encode(array("status" => 404, "message" => "Last name can not be empty"));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,50}$/', $last_name)) {
        echo json_encode(array("status" => 404, "message" => "Last name must be minimum 3 chars and should contains charachters only " . __LINE__));
        exit;
    }

    // Validimi i atesise
    if (empty($atesia)) {
        echo json_encode(array("status" => 404, "message" => "Atesia can not be empty " . __LINE__));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,50}$/', $atesia)) {
        echo json_encode(array("status" => 404, "message" => "Atesia must be minimum 3 chars and should contains charachters only " . __LINE__));
        exit;
    }

    // Validimi i dates
    if (empty($date)) {
        echo json_encode(array("status" => 404, "message" => "Date can not be empty " . __LINE__));
        exit;
    }

    if ($dateDifference < 568092165) {
        echo json_encode(array("status" => 404, "message" => "You must be over 18 " . __LINE__));
        exit;
    }

    if (intval($dateArray[2]) < 1900) {
        echo json_encode(array("status" => 404, "message" => "You can not possibly be alive! " . __LINE__));
        exit;
    }

    // Validimi i E-Mailit
    if (empty($email)) {
        echo json_encode(array("status" => 404, "message" => "Email can not be empty " . __LINE__));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array("status" => 404, "message" => "Email format can not be empty " . __LINE__));
        exit;
    }

    // Validimi i numrit te telefonit
    if (empty($phone_number)) {
        echo json_encode(array("status" => 404, "message" => "Email can not be empty " . __LINE__));
        exit;
    }

    if (!preg_match('/^[0-9]{10}+$/', $phone_number)) {
        echo json_encode(array("status" => 404, "message" => "Phone number format not correct" . __LINE__));
        exit;
    }

    // Validimi i password-it
    if (empty($pwd)) {
        echo json_encode(array("status" => 404, "message" => "Password can not be empty " . __LINE__));
        exit;
    }




    if (validate_password($pwd)) {
        echo json_encode(array("status" => 404, "message" => "Password must have at least" . __LINE__));
        exit;
    }

    if (empty($cnf_pwd)) {
        echo json_encode(array("status" => 404, "message" => "Password can not be empty " . __LINE__));
        exit;
    }

    if (validate_password($cnf_pwd)) {
        echo json_encode(array("status" => 404, "message" => "Confirm password must have at least" . __LINE__));
        exit;
    }

    //hash the password;
    $hash = password_hash($pwd, PASSWORD_BCRYPT);
    /**
     *  Shtimi i te dhenave te userit ne databazew
     */
    $query_insert = "INSERT INTO users SET
                     first_name='" . $first_name . "',
                     last_name='" . $last_name . "',
                     atesia='" . $atesia . "',
                     username='" . $username . "',
                     email='" . $email . "',
                     phone_number='" . $phone_number . "',
                     password='" .$hash. "',
                     date_of_birth='" . $date . "',
                     image_name='default.jpg'
            ";

    //Ekzekutojme dhe validojme querin
    $result_insert = mysqli_query($conn, $query_insert);

    if (!$result_insert) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    // kthejme pergjigjie qe u shtua me suksess
    echo json_encode(array("status" => 200, "message" => "Account created successfully " . __LINE__));
    exit;
}
