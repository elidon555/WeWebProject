<?php
error_reporting(E_ALL ^ E_WARNING);
include_once('../_config/constants.php');
if (!$_SESSION['id']){
    header('location:' . SITEURL . 'login');
}
if ($_SESSION['role']!="Admin"){
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
                          FROM users where 1=1 ";

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
                       where first_name like '%" . $searchValue . "%' 
                             OR last_name like '%" . $searchValue . "%'
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
                          last_name
                          
                          
                   FROM users where 1 = 1   
                   $searchQuery  ORDER BY  $columnName $columnSortOrder $pagination";


    $result_data = mysqli_query($conn, $query_data);


    if (!$result_data) {
        $error = mysqli_error($conn) . " " . __LINE__;
        empty_data($totalRecords, $error);
    }


    //vlerat e id-se te cilat na duhen per checkin
    $id_values = array();

    //array ku do ruajme te dhenat qe ja cojme frontend-it
    $data = array();

    while ($row = mysqli_fetch_assoc($result_data)) {
        //krijojme variablen temp qe ti bejme push te dhenat ne array
        $temp = array();

        //Marrim te dhenat nga SQL-dhe I bejme push ne array
        $temp['user_id'] = $row['user_id'];
        $temp['show'] = " ";
        $temp['dates'] = " ";
        $temp['first_name'] = $row["first_name"] . " " . $row["last_name"];
        $temp['total_hours_in'] = " ";
        $temp['normal_hours'] = " ";
        $temp['normal_salary'] = " ";
        $temp['overtime'] = " ";
        $temp['overtime_salary'] = " ";
        $temp['salary_per_hour'] = " ";
        $temp['salary'] = " ";

        $data[] = $temp;
        $id_values[] = $row['user_id'];

    }

    if (!isset($_POST['startDate']) || !isset($_POST['endDate'])) {
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d', strtotime('today'));

    } else {

        $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
        $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
    }

    $sql_get_checkings = 'SELECT
    checkins.id,
    checkins.user_id,
    checkins.check_in_date,
    checkins.check_in_hour,
    checkins.check_out_hour,
    checkins.check_out_date

    FROM weweb.checkins as checkins ,weweb.users as users

    WHERE users.user_id = checkins.user_id AND checkins.check_in_date >= "' . $startDate . '" AND checkins.check_in_date <= "' . $endDate . '"

    ';

    for ($i = 0; $i < sizeOf($id_values); $i++) {

        if (sizeOf($id_values) == 1) {
            $sql_get_checkings .= ' AND checkins.user_id IN (' . $id_values[$i] . ') ';
            break;
        }

        if ($i == 0) {
            $sql_get_checkings .= ' AND checkins.user_id IN (' . $id_values[$i] . ', ';
        }

        if ($i == (sizeOf($id_values) - 1)) {
            $sql_get_checkings .= ' ' . $id_values[$i] . ') ';
        } else {
            $sql_get_checkings .= ' ' . $id_values[$i] . ', ';
        }
    }
    $sql_get_checkings .= " ORDER BY checkins.user_id ASC, checkins.check_in_date DESC";

    $result_checkins = mysqli_query($conn, $sql_get_checkings);

    if (!$result_checkins) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    //Array te cilin do cojme te dhenat ne front end
    $checkins = array();

    //Deklarojme variablen e cila tregon orarin normal ne sekonda
    $time = time_to_sec('09:00:00');

    //Variabla ku do ruajme oret totale te punes per cdo checkin
    $total_hours = 0;
    //Variabla ku do ruajme totalin e meparshem
    $prev_total = 0;
    //Holiday array
    $year = date("Y");
    $holiday_array = array(
        "01-01",
        "03-14",
        "03-22",
        "04-17",
        "04-18",
        "05-01",
        "05-02",
        "05-13",
        "07-20",
        "09-05",
        "11-28",
        "11-29",
        "12-08",
        "05-25",);

    $i=0;

    while ($row = mysqli_fetch_assoc($result_checkins)) {
        $date = new DateTime($row['check_in_date']);
        $week = $date->format("W-Y");
        /**
         * If id or date changes,prevTotal and total resets back to 0
         */
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
        /**
         * End of total hour reset.
         */

        /**
         * Let's setup the coefficients
         */
        $month = substr($row['check_in_date'], 5);

        if (in_array($month, $holiday_array)) {
            $k1 = 1.5;
            $k2 = 2;
        } else if (isWeekend($row['check_in_date'])) {
            $k1 = 1.25;
            $k2 = 1.5;
        } else {
            $k1 = 1;
            $k2 = 1.25;
        }

        /**
         * Llogarisimin differencen per cdocheckin dhe nese eshte negative, I shtojme 24 ore se ashtu I bie.
         */
        $checkins_difference = time_to_sec($row['check_out_hour']) - time_to_sec($row['check_in_hour']);

        if ($checkins_difference < 0) {
            $checkins_difference += time_to_sec("24:00:00");
        }

        //Incrementojme totalin me cdo checkin brenda 1 dite
        $total_hours += $checkins_difference;

        //3d - checkins per date // shtojme te dhenat baze te checkings
        $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['checkins_per_day'][$row['id']] = [
            'check_in_date' => $row['check_in_date'],
            'count' => $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['checkins_per_day'][$row['id']]['count'] + $row['count'],
        ];


        //ruajme daten
        $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['check_in_date'] = $row['check_in_date'];
        //salary
        $checkins[$row['user_id']][$week]['normal_hours']=0;
        $checkins[$row['user_id']][$week]['normal_salary']=0;
        $checkins[$row['user_id']][$week]['overtime'] =0;
        $checkins[$row['user_id']][$week]['overtime_salary'] =0;
        $checkins[$row['user_id']][$week]['total_hours']=0;
        $checkins[$row['user_id']][$week]['total_salary']=0;
        $checkins[$row['user_id']][$week]['week']=$week;
        $checkins[$row['user_id']][$week]['user_id']=$row['user_id'];



        //shtojme diferencen ne ore checkout-checkin per cdo checkings qe kemi brenda nje date
        $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['hours_per_date'] += $checkins_difference;

        //Llogarisimin sa ka punuar overtime
        $overtime = $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['hours_per_date'] - $time;

        /**
         * Llogarisim sa ore ka punuar overtime brenda 1 dite
         */
        if ($overtime > 0) {
            $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['overtime'] = $overtime;
            $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['normal_hours'] = $time;



        } else {
            $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['overtime'] = 0;
            $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['normal_hours'] += $checkins_difference;

        }
        $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['k1'] = $k1;
        $checkins[$row['user_id']][$week]['dates'][$row['check_in_date']]['k2'] = $k2;



    }




//Nese nuk kemi te dhena, i dergojm array bosh.
    if (empty($table_data)) {
        $table_data = [];
    }

    //Ja dergojme response-in backendit
    $response = array("draw" => intval($draw), "iTotalRecords" => $totalRecords, "iTotalDisplayRecords" => $totalRecordwithFilter, "aaData" => $data, "checkinsData" => $checkins);
    echo json_encode($response);
    exit;
}
