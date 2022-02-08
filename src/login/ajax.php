<?php

include_once('../_config/constants.php');

if ($_POST['action']=='login') {


    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass =$_POST['password'];

    $sql = "SELECT u.user_id,
       u.email,
       u.phone_number,
       u.first_name,
       u.last_name,
       u.password,
       roles.role_name 
    FROM users as u,
        users_to_roles as r,roles 
    WHERE ( email='" .$email."' OR phone_number='" .$email. "' )
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

} ?>