<?php

include_once('../_config/constants.php');
include_once('../public/functions.php');

if ($_POST['action'] == "delete") {

    $conn = mysqli_connect("localhost", "root", "root", "weweb");

    $id = $_POST['id'];

    $sql_delete_user = '

       DELETE from users where user_id = ' . $id . '
        ';

    /**
     * Test if we have no query errors!
     */
    try {
        $result_checkins = mysqli_query($conn, $sql_delete_user);
    } catch (Exception $e) {
    }


    if (!$result_checkins) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    echo json_encode(array("status" => 200, "message" => "User deleted successfully " . __LINE__));
    exit;


}

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
    if ($_POST['role'] == 'User') {
        $role = 2;
    } else {
        $role = 1;
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

if ($_POST['action'] == 'load_single_user') {

    //Marrin te dhenat nga perdoruesi
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    //Bejme query per te marre te dhenat e userit

    $sql_get_user = '
                        SELECT u.user_id,
                               u.first_name,
                               u.last_name,
                               u.atesia,
                               u.date_of_birth,
                               u.email,
                               u.phone_number,
                               u.image_name,
                               r.role_id
                        
                        FROM users as u,
                             users_to_roles as r 
                        
                        Where u.user_id=' . $id . ' AND
                              u.user_id=r.user_id
                        
                        ';


    $result = mysqli_query($conn, $sql_get_user);

    if (!$result) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    $row = mysqli_fetch_assoc($result);

    echo json_encode(array("id" => $row['user_id'], "first_name" => $row['first_name'], "last_name" => $row['last_name'], "atesia" => $row['atesia'], "date" => $row['date_of_birth'], "email" => $row['email'], "phone_number" => $row['phone_number'], "image_name" => $row['image_name'], "role" => $row['role_id']));


    exit;


}

if ($_POST['action'] == 'load_table') {


    $draw = $_POST['draw'];
    $limit_start = $_POST['start'];
    $limit_end = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];

    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
    $searchQuery = " ";

    if ($searchValue != '') {
        $searchQuery = " AND (
            first_name LIKE '%" . $searchValue . "%' OR 	
            last_name LIKE '%" . $searchValue . "%' OR
            atesia LIKE '%" . $searchValue . "%' OR 
            date_of_birth LIKE '%" . $searchValue . "%' OR 
            phone_number LIKE '%" . $searchValue . "%' OR 
            email LIKE '%" . $searchValue . "%' 
            )
            
           ";
    }

    $filter_email = "";
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $filter_email = " AND (  email LIKE '%" . mysqli_real_escape_string($conn, $_POST['email']) . "%' ) ";
    }

    $filter_phone_number = "";
    if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) {
        $filter_email = " AND ( phone_number like '%" . mysqli_real_escape_string($conn, $_POST['phone_number']) . "%' ) ";
    }

    $filter_date = "";
    if (isset($_POST['startDate']) && !empty($_POST['startDate'])) {
        $filter_email = "  AND ( date_of_birth >= '" . mysqli_real_escape_string($conn, $_POST['startDate']) . "' AND date_of_birth <= '" . mysqli_real_escape_string($conn, $_POST['endDate']) . "' ) ";
    }


// Rsati kur zgjidhet All duhet te hiqen te gjitha limitimet ne pagination
    if ($limit_end == -1) {
        $pagination = "";
    } else {
        $pagination = "LIMIT " . $limit_start . ", " . $limit_end;
    }
//    echo $searchQuery;

    /**
     * Merr numrin total te rekordeve pa aplikuar filtrat. Psh kur shfaqim 10/30 rekorde,
     * numrin tital e marrim permes ketij query
     */
    $query_without_ftl = "SELECT COUNT(*) AS allcount 
                          FROM users where 1=1  ";
//echo $query_without_ftl;
//exit;

    $result_without_ftl = mysqli_query($conn, $query_without_ftl);

    if (!$result_without_ftl) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data(0, $error);
    }

    $records = mysqli_fetch_assoc($result_without_ftl);
    $totalRecords = $records['allcount'];

    /**
     * Numrin total te rekordeve duke aplikuar filtrin search
     */
    $query_with_ftl = "SELECT COUNT(*) AS allcount 
                       FROM  users 
                       where 1=1 AND (
                                first_name like '%" . $searchValue . "%' 
                             OR last_name like '%" . $searchValue . "%' 
                             OR atesia like '%" . $searchValue . "%' 
                             OR username like '%" . $searchValue . "%' 
                             OR email like '%" . $searchValue . "%' 
                             OR phone_number like '%" . $searchValue . "%' 
                             OR date_of_birth like '%" . $searchValue . "%' )
                             $filter_email $filter_date $filter_phone_number
                             ";


    $result_with_ftl = mysqli_query($conn, $query_with_ftl);
    if (!$result_with_ftl) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
    }

    $records_with_ftl = mysqli_fetch_assoc($result_with_ftl);
    $totalRecordwithFilter = $records_with_ftl['allcount'];

    /**
     * Merren te dhenat qe do analizohen dhe do behet llogaritja perkatese
     * Behet perllogaritja e te dhenave ne vektorin data
     */
    $query_data = "SELECT user_id,
                          first_name,
                          last_name,
                          atesia,
                          username,
                          email,
                          phone_number,
                          date_of_birth,
                          image_name
                          
                   FROM users where 1 = 1   
                   $searchQuery
                    $filter_email $filter_date $filter_phone_number
                   ORDER BY  $columnName $columnSortOrder $pagination";


    $result_data = mysqli_query($conn, $query_data);


    if (!$result_data) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
    }


    $data = array();
    while ($row = mysqli_fetch_assoc($result_data)) {
        $data[$row['user_id']]['user_id'] = $row['user_id'];
        $data[$row['user_id']]['image_name'] = $row['image_name'];
        $data[$row['user_id']]['first_name'] = $row['first_name'];
        $data[$row['user_id']]['last_name'] = $row['last_name'];
        $data[$row['user_id']]['atesia'] = $row['atesia'];
        $data[$row['user_id']]['date_of_birth'] = $row['date_of_birth'];
        $data[$row['user_id']]['phone_number'] = $row['phone_number'];

    }
    /**
     * Pershtasim te dhenat sipas formatit qe i do datatable ne front-end
     */
    $buttonval = 1;
    foreach ($data as $key => $row) {

        $newDate = date("d-m-Y", strtotime($row["date_of_birth"]));

        $table_data[] = array("user_id" => '<span id="id-' . $buttonval . '">' . $row["user_id"] . '</span>', "image_name" => '<img id="image-' . $buttonval . '" style="width:50px" src="../_photos/' . $row["image_name"] . '">', "first_name" => '<span id="first-name-' . $buttonval . '">' . $row["first_name"] . '</span>', "last_name" => '<span id="last-name-' . $buttonval . '">' . $row["last_name"] . '</span>', "atesia" => '<span id="atesia-name-' . $buttonval . '">' . $row["atesia"] . '</span>', "date_of_birth" => '<span id="date-' . $buttonval . '">' . $newDate . '</span>', "phone_number" => '<span id="phone-number-' . $buttonval . '">' . $row["phone_number"] . '</span>', "actions" => '
                            <nobr>
                               <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal" type="button" id="button' . $buttonval . '" class="button-primary" 
                                value="' . $row["user_id"] . '">View</button>

                                <button class="btn btn-danger btn-sm delete"  type="button" id="button-del-' . $buttonval . '" class="button-danger" 
                                value="' . $row["user_id"] . '">Delete</button>
                            </nobr>
                        '


        );
        $buttonval++;


        /**
         * Dergojme te dhenat ne front
         */

    }
//Nese nuk kemi te dhena, i dergojm array bosh.
    if (empty($table_data)) {
        $table_data = [];
    }
    $response = array("draw" => intval($draw), "iTotalRecords" => $totalRecords, "iTotalDisplayRecords" => $totalRecordwithFilter, "aaData" => $table_data);
    echo json_encode($response);
    exit;
}
