<?php
include_once('../_config/constants.php');

if (!$_SESSION['id']){
    header('location:' . SITEURL . 'login');
}

//Check if weekend
function isWeekend($date)
{
    return (date('N', strtotime($date)) >= 6);
}

function seconds2human($ss) {
    $m = floor(($ss%3600)/60);
    $h = floor($ss / 3600);

    if ($ss>=3600) {
        return "$h hours";
    }

    else if($ss==0){
        return "-";
    }

   else if ($ss<3600){
       return  "$m min";
    }

}

function x_week_range($date) {
    $ts = strtotime($date);
    $start = (date('w', $ts) == 0) ? $ts : strtotime('last last sunday', $ts);
    return array(date('d-M-y', $start),
        date('d-M-y', strtotime('next saturday', $start)));
}

//Upload image func
function uploadImage($file, $old_image)
{
    if ($old_image == "") {
        return "default.jpg";
    }

    if (empty($file['name'])) {
        return $old_image;
    }

    $image = $file['name'];
    $image = "0" . uniqid() . "." . pathinfo($image, PATHINFO_EXTENSION);

    /**
     * Validojme file-in
     */
    $size = $file['size'];
    if ($size > 5242880) {
        echo json_encode(array("status" => 404, "message" => "File cant be bigger than 5 Megabytes" . __LINE__));
        exit;
    }

    /**
     * Percaktojme llojin e files
     */
    $valid_extensions = array('jpg' => "jpg", 'jpeg' => "jpeg", 'png' => "png",);
    //shikojme cfare extensioni ka
//    $location = "../../_photos/" . $image;
//    $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    $array_extension = explode('.', $file['name']);
    $imageFileType = end($array_extension);
    if (!isset($valid_extensions[strtolower($imageFileType)])) {
        echo json_encode(array("status" => 404, "message" => "Unsupported file extension" . __LINE__));
        exit;
    }
    /**
     * Ruajme filen ne pathin e percaktuar
     */
    $location = "../_photos/" . $image;
    if (move_uploaded_file($file['tmp_name'], $location)) {

        return $image;
    } else {
        return $old_image;
    }
}

//Convert time to seconds func
function time_to_sec($time): int
{
    return strtotime($time) - strtotime('TODAY');
}

//Datatables funct
function empty_data($total_records, $error = "")
{
    $response = array("draw" => intval($draw), "iTotalRecords" => $total_records, "iTotalDisplayRecords" => 0, "aaData" => array(), "error" => $error,);
    echo json_encode($response);
    exit;
}

//Password validation func
function validate_password($pwd): bool
{
    if (!preg_match('@[A-Z]@', $pwd) or !preg_match('@[a-z]@', $pwd) or !preg_match('@[0-9]@', $pwd) or !preg_match('@[^\w]@', $pwd) or !strlen($pwd) < 8) return false;

    else {
        return true;
    }
}

/**
 * Function to UPDATE,CREATE,ADD new user
 */
if ($_POST['action'] == 'update||delete') {

    /**
     * Marrin te dhenat nga front endi
     */
    if (isset($_POST['id'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
    }
    $first_name = ucfirst(mysqli_real_escape_string($conn, $_POST['first_name']));
    $last_name = ucfirst(mysqli_real_escape_string($conn, $_POST['last_name']));
    $username = strtolower(substr($first_name, 0, 1) . "" . $last_name);
    $atesia = ucfirst(mysqli_real_escape_string($conn, $_POST['atesia']));
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $pwd = $_POST['password'];
    $cnf_pwd = $_POST['confirm_password'];

    //Rregullojme formatimin e dates dhe bejme gati variablat per validim moshe
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $dateArray = explode("/", $_POST['date']);
    $date = $dateArray[2] . "-" . $dateArray[1] . "-" . $dateArray[0];
    $epochDateSubmitted = strtotime($date);
    $epochDateCurrent = time();
    $dateDifference = $epochDateCurrent - $epochDateSubmitted;

    if (isset($_POST['id'])) {
        $query_check_details = 'SELECT email, phone_number,image_name from users where user_id=' . $id . ' ';
        $result_check_details = mysqli_query($conn, $query_check_details);
        $result_user_info = mysqli_fetch_assoc($result_check_details);
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
        echo json_encode(array("status" => 404, "message" => "Last name must be minimum 3 chars and should contains characters only " . __LINE__));
        exit;
    }

    // Validimi i atesise
    if (empty($atesia)) {
        echo json_encode(array("status" => 404, "message" => "Atesia can not be empty " . __LINE__));
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,50}$/', $atesia)) {
        echo json_encode(array("status" => 404, "message" => "Atesia must be minimum 3 chars and should contains characters only " . __LINE__));
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

    if ($dateDifference < 568092165) {
        echo json_encode(array("status" => 404, "message" => "You must be over 18 " . __LINE__));
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
    if (strcmp($pwd, $cnf_pwd) == 0) {
        if (!empty($pwd)) {

            if (validate_password($pwd)) {
                echo json_encode(array("status" => 404, "message" => "Password must have at least" . __LINE__));
                exit;
            }
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        } else {
            if (isset($_POST['id'])) {
                $hash = "";
            } else {
                echo json_encode(array("status" => 404, "message" => "Password can not be empty!" . __LINE__));
                exit;
            }
        }
    } else {
        echo json_encode(array("status" => 404, "message" => "Password and confirm password doesn't match " . __LINE__));
        exit;
    }

    /**
     * Upload image
     */
    $image = uploadImage($_FILES['file'], $result_user_info['image_name']);


    if (isset($_POST['id'])) {
        $sql_query = " UPDATE users SET
                    first_name='" . $first_name . "',
                    last_name='" . $last_name . "',
                    atesia='" . $atesia . "',
                    username='" . $username . "',
                    email='" . $email . "',
                    phone_number='" . $phone_number . "',
                    date_of_birth='" . $date . "',
                    image_name='" . $image . "'
                    
        ";


        if (!empty($hash)) {
            $sql_query .= ", password='" . $hash . "' ";
        }

        $sql_query .= "WHERE user_id=" . $conn->escape_string($id) . " ";

    } else {
        $sql_query = "INSERT INTO users SET
                     first_name='" . $first_name . "',
                     last_name='" . $last_name . "',
                     atesia='" . $atesia . "',
                     username='" . $username . "',
                     email='" . $email . "',
                     phone_number='" . $phone_number . "',
                     password='" . $hash . "',
                     image_name='" . $image . "',
                     date_of_birth='" . $date . "'
                     ";
    }


    try {
        $res = mysqli_query($conn, $sql_query);
    } catch (mysqli_sql_exception $exception) {
        if (($conn->errno) == 1062) {
            if (preg_match("%(?=.*'users.email')(?=.*'users.phone_number')%", $exception)) {
                echo json_encode(array("status" => 404, "message" => "User already exists with this email and phone" . __LINE__));
                exit;
            } else if (preg_match("%(?=.*'users.email')%", $exception)) {
                echo json_encode(array("status" => 404, "message" => "User already exists with this email" . __LINE__));
                exit;
            } else {
                echo json_encode(array("status" => 404, "message" => "User already exists with this phone" . __LINE__));
                exit;
            }
        }
    }

    if (!$res) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    /**
     * Add role to the user.
     */

    if ($_SESSION['role'] == "Admin") {
        if ($_POST['role'] == 'User') {
            $role = 2;
        }
        if ($_POST['role'] == 'Admin') {
            $role = 1;
        }
    } else {
        $role = 2;
    }

    if (!isset($_POST['id'])) {
        $sql_get_id = "select user_id from users where email='" . $email . "'   ";
        $res = mysqli_query($conn, $sql_get_id);
        $user_info = mysqli_fetch_assoc($res);
        $id = $user_info['user_id'];

        $sqlAddRole = "
        INSERT INTO users_to_roles (role_id,user_id) VALUES
                       ($role,$id)
        ";

    } else {

        $sqlAddRole = " 
                       UPDATE users_to_roles
                       SET role_id='$role'
                       WHERE user_id='$id'
            ";
    }

    $resAddRole = mysqli_query($conn, $sqlAddRole);

    echo json_encode(array("status" => 200, "message" => "Success! " . __LINE__));
    exit();

//Funksion validimi passwordi;
    function validate_password($pwd): bool
    {
        if (!preg_match('@[A-Z]@', $pwd) or !preg_match('@[a-z]@', $pwd) or !preg_match('@[0-9]@', $pwd) or !preg_match('@[^\w]@', $pwd) or !strlen($pwd) < 8) return false;

        else {
            return true;
        }

    }
}

?>