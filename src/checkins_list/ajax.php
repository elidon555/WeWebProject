<?php
error_reporting(E_ALL ^ E_WARNING);
include_once('../_config/constants.php');
if (!$_SESSION['id']) {
    header('location:' . SITEURL . 'login');
}
if ($_SESSION['role'] != "Admin") {
    header('location:' . SITEURL . '_config/errors/error403.html');
}
include_once('../public/functions.php');
/**
 *
 */
if ($_POST['action'] == 'add_checking') {

    $conn = mysqli_connect("localhost", "root", "root", "weweb");

    /**
     * Marri mte dhenat nga useri
     */
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $checkin = mysqli_real_escape_string($conn, $_POST['checkin']);
    $checkout = mysqli_real_escape_string($conn, $_POST['checkout']);
    $checkinDate = mysqli_real_escape_string($conn, $_POST['checkin_date']);
    $checkoutDate = mysqli_real_escape_string($conn, $_POST['checkout_date']);

    if ($checkin == "" || $checkout == "") {
        echo json_encode(array("status" => 404, "message" => "Error! Checkin is empty! " . __LINE__));
        exit;
    }

    if ($checkinDate == "" || $checkoutDate == "") {
        echo json_encode(array("status" => 404, "message" => "Error! Date can not be empty! " . __LINE__));
        exit;
    }

    //Bejme query id-ne e userit.
    $query_find_id = '
                SELECT user_id from users where email="' . $email . '"
       ';

    $result_find_id = mysqli_query($conn, $query_find_id);

    if (!$result_find_id) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    $row = mysqli_fetch_assoc($result_find_id);
    $id = $row['user_id'];

    //Bejme insert te dhenat ne database
    $queryInsertCheckins = ' 
            INSERT INTO checkins (user_id, check_in_date, check_in_hour, check_out_date, check_out_hour,created_at,updated_at)
            VALUES (' . $id . ',"' . $checkinDate . '","' . $checkin . '", "' . $checkoutDate . '","' . $checkout . '", "' . date('Y-m-d H:i:s', time()) . '", "' . date('Y-m-d H:i:s', time()) . '"  );
        ';
    //Bejme querin dhe checkojme nese te dhenat u rregjistruan ne database
    $statement = $conn->query($queryInsertCheckins);

    if ($statement) {
        echo json_encode(array("status" => 200, "message" => "Success! " . __LINE__));
    } else {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
    }
    exit();
}
/**
 * @param $time
 * @return int
 */

if ($_POST['action'] == 'load_table') {


    $draw = $_POST['draw'];
    $limit_start = $_POST['start'];
    $limit_end = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];

    if ($columnName == "user_id") {
        $columnName = "user_id";
    }
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);

    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
    $searchQuery = " ";

    if ($searchValue != '') {
        $searchQuery = " AND (
            first_name LIKE '%" . $searchValue . "%' OR
            last_name LIKE '%" . $searchValue . "%' )
           ";
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
                          FROM users
 ";

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
    $query_with_ftl =  "SELECT COUNT(distinct users.user_id) AS allcount 
                       FROM  users
                        LEFT JOIN checkins on users.user_id = checkins.user_id
                       WHERE check_in_date >= '" . $startDate . "' AND check_in_date<= '" . $endDate . "'
                       AND (first_name like '%" . $searchValue . "%' 
                             OR last_name like '%" . $searchValue . "%' )
                             ";

    $result_with_ftl = mysqli_query($conn, $query_with_ftl);
    if (!$result_with_ftl) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
    }

    $records_with_ftl = mysqli_fetch_assoc($result_with_ftl);
    $totalRecordwithFilter = $records_with_ftl['allcount'];


    //        $query_data .= "AND "

    /**
     * Merren te dhenat qe do analizohen dhe do behet llogaritja perkatese
     * Behet perllogaritja e te dhenave ne vektorin data
     */
    $query_data = "    
                      SELECT 
                      users.first_name,
                      users.last_name,
                      checkins.user_id,
                      checkins.id, 
                      check_in_date, 
                      check_in_hour, 
                      check_out_hour, 
                      check_out_date 
                      FROM 
                      checkins 
                      INNER JOIN users 
                      ON users.user_id = checkins.user_id     
                      INNER JOIN (
                                SELECT DISTINCT user_id 
                                FROM users 
                                WHERE 1=1 $searchQuery
                                ORDER BY 
                                $columnName $columnSortOrder $pagination
                      ) AS m 
                     ON m.user_id = users.user_id 
                     WHERE check_in_date >= '" . $startDate . "' AND check_in_date<= '" . $endDate . "'
                     ORDER BY checkins.check_in_date DESC, users.user_id DESC
                     ";


    $result_data = mysqli_query($conn, $query_data);


    if (!$result_data) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
    }



    $time = time_to_sec('09:00:00');
    $total_hours = 0;
    $prev_total = 0;
    while ($row = mysqli_fetch_assoc($result_data)) {
        //krijojme variablen temp qe ti bejme push te dhenat ne array

        $date_new = $row['check_in_date'];
        $id_new = $row['user_id'];
//        echo $date." ".$id_new." ||||";
        if (isset($date_old)) {
            // If id or date changes,prevTotal and total resets back to 0
            if ($date_old !== $date_new || $id_old !== $id_new) {

                $prev_total = 0;
                $total_hours = 0;
            }
        }

        $date_old = $row['check_in_date'];
        $id_old = $row['user_id'];

        //Marrim te dhenat nga SQL-dhe I bejme push ne array
        $data[$row['user_id']]['user_id'] = $row['user_id'];
        $data[$row['user_id']]['first_name'] = $row["first_name"] . " " . $row["last_name"];

        /**
         * Llogarisimin differencen per cdocheckin dhe nese eshte negative, I shtojme 24 ore se ashtu I bie.
         */
        $checkins_difference = time_to_sec($row['check_out_hour']) - time_to_sec($row['check_in_hour']);

        if ($checkins_difference < 0) {
            $checkins_difference += time_to_sec("24:00:00");
        }

        $total_hours += $checkins_difference;

        $temp_day_details = array();
        $temp_day_details['check_in_date'] = $row['check_in_date'];
        $temp_day_details['check_in_hour'] = $row['check_in_hour'];
        $temp_day_details['check_out_date'] = $row['check_out_date'];
        $temp_day_details['check_out_hour'] = $row['check_out_hour'];




        //Shtojme
        $data[$row['user_id']]['row_details'][$row['check_in_date']]['row_details'][] = $temp_day_details;


        //shtojme user id se na nevojitet
        $data[$row['user_id']]['row_details'][$row['check_in_date']]['user_id'] = $row['user_id'];
        //ruajme daten
        $data[$row['user_id']]['row_details'][$row['check_in_date']]['check_in_date'] = $row['check_in_date'];

        //shtojme diferencen ne ore checkout-checkin per cdo checkings qe kemi brenda nje date
        $data[$row['user_id']]['row_details'][$row['check_in_date']]['hours_per_date'] += $checkins_difference;
        $data[$row['user_id']]['total_hours_in'] += $checkins_difference;
        //Llogarisimin sa ka punuar overtime

        if ($total_hours < $time) {

            $data[$row['user_id']]['normal_hours'] += $checkins_difference;

            $data[$row['user_id']]['row_details'][$row['check_in_date']]['normal_hours'] += $checkins_difference;

        } else if ( $prev_total < $time) {

            $data[$row['user_id']]['overtime'] += $total_hours - $time;
            $data[$row['user_id']]['normal_hours'] += ($time - $prev_total);

            $data[$row['user_id']]['row_details'][$row['check_in_date']]['overtime'] = $total_hours - $time;
            $data[$row['user_id']]['row_details'][$row['check_in_date']]['normal_hours'] = $time;

        } else if ( $prev_total > $time) {

            $data[$row['user_id']]['overtime'] += $total_hours - $prev_total;

            $data[$row['user_id']]['row_details'][$row['check_in_date']]['overtime'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$row['check_in_date']]['normal_hours'] = $time;

        }
        //Ruajme totalin per iterimin e ardhshem
        $prev_total = $total_hours;

        //Numrojme datat
        $data[$row['user_id']]['All_Dates_ARRAY'][$row['check_in_date']] = $row['check_in_date'];
        $data[$row['user_id']]['nr_dates'] = count($data[$row['user_id']]['All_Dates_ARRAY']);

        if (is_array($data[$row['user_id']]['row_details'][$row['check_in_date']]['row_details'])) {
            $data[$row['user_id']]['row_details'][$row['check_in_date']]['count'] = sizeof($data[$row['user_id']]['row_details'][$row['check_in_date']]['row_details']);
            $data[$row['user_id']]['dates'] += 1;
        }
    }


    $cal_data = array();
    $details1 = array();
    $i = 0;

    foreach ($data as &$value) {

        $value['total_hours_in'] = seconds2human($value['total_hours_in']);
        $value['overtime'] = seconds2human($value['overtime']);
        $value['normal_hours'] = seconds2human($value['normal_hours']);

        $week_array = [];

        foreach ($value['row_details'] as &$week_details) {

            $week_details['normal_hours'] = seconds2human($week_details['normal_hours']);
            $week_details['overtime'] = seconds2human($week_details['overtime']);
            $week_details['hours_per_date'] = seconds2human($week_details['hours_per_date']);

            $week_array[] = $week_details;
        }
        $cal_data[] = $value;
        $cal_data[$i]['row_details'] = $week_array;

        $i++;
    }


    //Ja dergojme response-in backendit
    $response = array("draw" => intval($draw), "iTotalRecords" => $totalRecords, "iTotalDisplayRecords" => $totalRecordwithFilter, "aaData" => $cal_data);
    echo json_encode($response);
    exit;
}
