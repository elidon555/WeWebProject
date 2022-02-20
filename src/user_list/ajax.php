<?php

include_once('../_config/constants.php');

if (!$_SESSION['id']) {
    header('location:' . SITEURL . '/login');
}
if ($_SESSION['role'] != "Admin") {
    header('location:' . SITEURL . '_config/errors/error403.html');
}

include_once('../public/functions.php');

if ($_POST['action'] == "delete") {


    $id = $_POST['id'];

    $sql_delete_user = '

       DELETE from users where user_id =' . $id . '
        ';
    /**
     * Test if we have no query errors!
     */
    try {
        //Execute the query to see if the user with that id exists
        /** @var $conn */
        $result_checkins = mysqli_query($conn, $sql_delete_user);
        //get the count of users with that id, should be 1
        //if its not 1, it means that user doesnt exist!
        if (mysqli_affected_rows($conn) == 0) {
            echo json_encode(array("status" => 404, "message" => "User doesn't exist! " . __LINE__));
            exit;
        }

    } catch (Exception $e) {
        //If we fail to execute the query that deletes the user
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    echo json_encode(array("status" => 200, "message" => "User deleted successfully " . __LINE__));
    exit;
}


if ($_POST['action'] == 'load_single_user') {

    //Marrin te dhenat nga perdoruesi
    /** @var $conn */
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


    $result_get_user = mysqli_query($conn, $sql_get_user);

    if (!$result_get_user) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    if (mysqli_num_rows($result_get_user) == 0) {
        echo json_encode(array("status" => 404, "message" => "User does not exists " . __LINE__));
        exit;
    }

    $row_get_user = mysqli_fetch_assoc($result_get_user);

    $data = array("id" => $row_get_user['user_id'], "first_name" => $row_get_user['first_name'], "last_name" => $row_get_user['last_name'], "atesia" => $row_get_user['atesia'], "date" => $row_get_user['date_of_birth'], "email" => $row_get_user['email'], "phone_number" => $row_get_user['phone_number'], "image_name" => $row_get_user['image_name'], "role" => $row_get_user['role_id']);

    echo json_encode($data);


    exit;


}

if ($_GET['action'] == 'select2Filter') {

    $email = !$_GET['email'] == "" ? mysqli_escape_string($conn, $_GET['email']) : "";

    if (!$_GET['phone_number'] == "")
        $phone_number = mysqli_escape_string($conn, $_GET['phone_number']);
    else {
        $phone_number = "";
    }
    $name = mysqli_escape_string($conn, $_GET['name']);


    $searchQuery = " 
    SELECT user_id,$name 
    from users 
    where        email LIKE '%" . $email . "%' 
    AND   phone_number LIKE '%" . $phone_number . "%'
    LIMIT 10
    ";


    $resultQuery = mysqli_query($conn, $searchQuery);

    if (!$resultQuery) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error! " . __LINE__));
        exit;
    }

    $json = [];
    while ($row = mysqli_fetch_assoc($resultQuery)) {

        $json[] = ['id' => $row['user_id'], 'text' => $row[$name]];
    }

    echo json_encode($json);
    exit;

}

if ($_POST['action'] == 'load_table') {

    /**
     * Standarti DATATABLE
     */
    $draw = $_POST['draw'];
    $limit_start = $_POST['start'];
    $limit_end = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    if ($_POST['startDate'] == null || $_POST['endDate'] == null) {
        $startDate = null;
        $endDate = null;
    } else {

        /** @var $conn */
        $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
        $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    }


    /**
     * Pagination
     */
    // Rsati kur zgjidhet All duhet te hiqen te gjitha limitimet ne pagination
    if ($limit_end == -1) {
        $pagination = "";
    } else {
        $pagination = "LIMIT " . $limit_start . ", " . $limit_end;
    }


    /**
     * +++++++++++++ Filtrat ++++++++++++++++++
     */
    // Filtri Search
    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);

    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = " AND (
            first_name LIKE '%" . $searchValue . "%' OR 	
            last_name LIKE '%" . $searchValue . "%' OR
            atesia LIKE '%" . $searchValue . "%' OR 
            date_of_birth LIKE '%" . $searchValue . "%' OR 
            phone_number LIKE '%" . $searchValue . "%' OR 
            email LIKE '%" . $searchValue . "%' 
            )";
    }


    // Fitri i Dates
    $date_flt = "";
    if (!empty($startDate) && !empty($endDate)) {

        $date_flt = " AND ( date_of_birth >= '" . $startDate . "' AND date_of_birth <= '" . $endDate . "' ) ";
    }


    /**
     * Filtri E-MAIL
     */
    $filter_email = "";
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $filter_email = " AND (  email LIKE '%" . mysqli_real_escape_string($conn, $_POST['email']) . "%' ) ";
    }


    /**
     * Filtri Phone Number
     */
    $filter_phone_number = "";
    if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) {
        $filter_email = " AND ( phone_number like '%" . mysqli_real_escape_string($conn, $_POST['phone_number']) . "%' ) ";
    }


    /**
     * Merr numrin total te rekordeve pa aplikuar filtrat. Psh kur shfaqim 10/30 rekorde,
     * numrin tital e marrim permes ketij query
     */
    $query_without_ftl = "SELECT COUNT(*) AS total_records 
                          FROM users where 1=1  ";

    $result_without_ftl = mysqli_query($conn, $query_without_ftl);

    if (!$result_without_ftl) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data(0, $error);
    }

    $records = mysqli_fetch_assoc($result_without_ftl);
    $totalRecords = $records['total_records'];

    /**
     * Numrin total te rekordeve duke aplikuar te gjithe filtrat qe ka zgjedhur perdoruesi. Ketu merret ne kosniderate dhe search
     */
    $query_with_ftl = "SELECT COUNT(*) AS total_displayed_records 
                       FROM  users 
                       where 1=1 
                             $searchQuery
                             $filter_email
                             $filter_phone_number
                             $date_flt
                             ";


    $result_with_ftl = mysqli_query($conn, $query_with_ftl);
    if (!$result_with_ftl) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
        exit;
    }

    $records_with_ftl = mysqli_fetch_assoc($result_with_ftl);
    $totalRecordwithFilter = $records_with_ftl['total_displayed_records'];

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
                   $filter_email 
                   $filter_phone_number
                   $date_flt
                   ORDER BY  $columnName $columnSortOrder $pagination";


    $result_data = mysqli_query($conn, $query_data);


    if (!$result_data) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
    }


    $data = array();
    while ($row = mysqli_fetch_assoc($result_data)) {
        $temp = array();

        $temp['user_id'] = $row['user_id'];
        $temp['image_name'] = $row['image_name'];
        $temp['first_name'] = $row['first_name'];
        $temp['last_name'] = $row['last_name'];
        $temp['atesia'] = $row['atesia'];
        $temp['date_of_birth'] = $row['date_of_birth'];
        $temp['phone_number'] = $row['phone_number'];
        $temp['actions'] = " ";

        $data[] = $temp;
    }

//Nese nuk kemi te dhena, i dergojm array bosh.

    $response = array("draw" => intval($draw), "iTotalRecords" => $totalRecords, "iTotalDisplayRecords" => $totalRecordwithFilter, "aaData" => $data);
    echo json_encode($response);
    exit;
}
