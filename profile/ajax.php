<?php

include_once('../_config/constants.php');
include_once('../_config/upload-image.php');



//Check if button submit is clicked
if (isset($_SESSION['user'])) {

    $email = mysqli_real_escape_string($conn,$_SESSION['user']);

    $sql = "SELECT* 
           FROM users 
           WHERE email='" .$email. "' or phone_number='" .$email. "'
           ";

    $res = mysqli_query($conn, $sql);

    if (!$res) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    $row = mysqli_fetch_assoc($res);


    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $atesia = $row['atesia'];

    $date = $row['date_of_birth'];
    $date = date("d/m/Y", strtotime($date));


    $email = $row['email'];
    $phone_number = $row['phone_number'];
    $current_image = $row['image_name'];


}
if (isset($_POST['update'])) {

    //Get values from form to update
    $first_name = mysqli_real_escape_string($conn, strtoupper($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, strtoupper($_POST['last_name']));
    $atesia = mysqli_real_escape_string($conn, strtoupper($_POST['atesia']));
    $_date = mysqli_real_escape_string($conn, $_POST['date']);
    $dateArray = explode("/", $_date);
    $date = $dateArray[2] . "-" . $dateArray[1] . "-" . $dateArray[0];
    $id = $_SESSION['id'];

    $username = strtolower(substr($first_name, 0, 1) . "" . $last_name);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);

    /**
     * marrin te dhenat e userit me kete id/email dhe marrim dhe te dhenat e userit
     */
    $query_check_details = "SELECT email,
                                   phone_number,
                                   image_name 
                            from users where user_id = '" . $id. "' ";


    $result_check_details = mysqli_query($conn, $query_check_details);

    if (!$result_check_details) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }
    // nese nuk gjendet user me kete ID
    if (mysqli_num_rows($result_check_details) == 0) {
        echo json_encode(array("status" => 404, "message" => "User does not exists. Bad Request " . __LINE__));
        exit;
    }
    // te dhenat e userit
    $result_user_info = mysqli_fetch_assoc($result_check_details);


    /**
     * Validojme Imazhin dhe percaktojme Pathin
     */

    $image = uploadImage($_FILES['file'], $result_user_info['image_name']);



    // validimi i emrit
    if (empty($first_name)) {
        echo json_encode(array("status" => 404, "message" => "First name can not be empty " . __LINE__));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,}$/', $first_name)) {
        echo json_encode(array("status" => 404, "message" => "First name must be minimum 3 chars and should contains charachters only " . __LINE__));
        exit;
    }

    // Validimi i mbiemrit
    if (empty($last_name)) {
        echo json_encode(array("status" => 404, "message" => "Last name can not be empty"));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,}$/', $last_name)) {
        echo json_encode(array("status" => 404, "message" => "Last name must be minimum 3 chars and should contains charachters only " . __LINE__));
        exit;
    }

    // Validimi i atesise
    if (empty($atesia)) {
        echo json_encode(array("status" => 404, "message" => "Atesia can not be empty " . __LINE__));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,}$/', $atesia)) {
        echo json_encode(array("status" => 404, "message" => "Atesia must be minimum 3 chars and should contains charachters only " . __LINE__));
        exit;
    }

    // Validimi i dates
    if (empty($date)) {
        echo json_encode(array("status" => 404, "message" => "Date can not be empty " . __LINE__));
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


    //if no file is submitted, dont do anything;


    $sql_update_user = "UPDATE users SET
            first_name='" . $first_name . "',
            last_name='" . $last_name . "',
            atesia='" . $atesia . "',
            username='" . $username . "',
            email='" . $email . "',
            phone_number='" . $phone_number . "',
            date_of_birth='" . $date . "',
            image_name='" . $image . "'
            WHERE email='" . $email . "' OR phone_number='" . $phone_number . "'
        ";


    //Execute query

    $res = mysqli_query($conn, $sql_update_user);

    if (!$res) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }
    $_SESSION['user'] = $email;

    echo json_encode(array("status" => 200, "message" => "Success! " . __LINE__));
    exit();


}
if (isset($_POST['edit_pwd'])) {


    /**
     * Marrim te dhenat nga useri
     */
    $email = mysqli_real_escape_string($conn, $_SESSION['user']);
    $old_pwd = $_POST['old_password'];
    $pwd = $_POST['password'];
    $confirm_pwd = $_POST['confirm_password'];

//Marrim passwordin e hashuar ne database per krahasim
    $sql_get_pwd = 'SELECT password from users where email ="' . $email . '"
    ';

//Ekzekutojme querin
    $result_password_check = mysqli_query($conn, $sql_get_pwd);

//Validimi i querit
    if (!$result_password_check) {
        echo json_encode(array("status" => 404, "message" => "Internal server error!" . __LINE__));
        exit;
    }


    $query_old_pwd = mysqli_fetch_assoc($result_password_check);

//Verifikojme nese password-et jane te njejta
    if ($pwd == $confirm_pwd) {

        //Verifikojme nese passwordi i vjeter ka rezultat te njejte hashi me hashin e vjeter
        if (password_verify($old_pwd, $query_old_pwd['password'])) {

            //Enkriptojne passwordin e ri me hash
            $hash = password_hash($pwd, PASSWORD_BCRYPT);

            //Bejme gati querin me hash-in e ri dhe emailin e userit
            $sql_update_pwd = 'UPDATE users
                    SET password="' . $hash . '"
                    where email ="' . $email . '"
                    ';

            //Ekzekutojme dhe validojme nese te dhenat update-ohen ne database
            $result_password_update = mysqli_query($conn, $sql_update_pwd);

            if (!$result_password_update) {
                echo json_encode(array("status" => 404, "message" => "Internal Server Error! " . __LINE__));
                exit;
            }

        } else {
            echo json_encode(array("status" => 404, "message" => "Old password doesn't match! " . __LINE__));
            exit;
        }
    } else {
        echo json_encode(array("status" => 404, "message" => "Passwords don't match! " . __LINE__));
        exit;

    }
}

?>