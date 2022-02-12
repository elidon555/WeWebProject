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

    if ($columnName == "") {
        $columnName = "user_id";
    }

    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
    $searchQuery = " ";

    if ($searchValue != '') {
        $searchQuery = " AND (
            first_name LIKE '%" . $searchValue . "%' OR
            last_name LIKE '%" . $searchValue . "%' )
           ";
    }

    if (!isset($_POST['startDate']) || !isset($_POST['endDate'])) {
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d', strtotime('today'));

    } else {

        $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
        $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
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

    //array ku do ruajme te dhenat qe ja cojme frontend-it
    $data = array();

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
    $holiday_array = array("01-01", "03-14", "03-22", "04-17", "04-18", "05-01", "05-02", "05-13", "07-20", "09-05", "11-28", "11-29", "12-08", "05-25");

    $i = 0;


    while ($row = mysqli_fetch_assoc($result_data)) {

        $date = $row['check_in_date'];

        $week = date("d-m-y", strtotime('sunday this week', strtotime($date))) . "<br>";
        $week .= date("d-m-y", strtotime('monday this week', strtotime($date)));

        $month = date("M-y", strtotime($row['check_in_date']));


        $data[$row['user_id']]['row_details'][$month]['date'] = $month;
        $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['date'] = $week;
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

        //Vendosim id-ne e userit si dhe emrin
        $data[$row['user_id']]['user_id'] = $row['user_id'];
        $data[$row['user_id']]['first_name'] = $row["first_name"] . " " . $row["last_name"];


        /**
         * Let's setup the coefficients
         */
        $month_test = substr($row['check_in_date'], 5);
        $sph = 10 / 3600;
        if (in_array($month_test, $holiday_array)) {
            $k1 = 1.5 * $sph;
            $k2 = 2 * $sph;
        } else if (isWeekend($row['check_in_date'])) {
            $k1 = 1.25 * $sph;
            $k2 = 1.5 * $sph;
        } else {
            $k1 = 1 * $sph;
            $k2 = 1.25 * $sph;
        }
        /**
         * Llogarisimin differencen per cdocheckin dhe nese eshte negative, I shtojme 24 ore se ashtu I bie.
         */
        $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['date'] = $row['check_in_date'];
        $checkins_difference = time_to_sec($row['check_out_hour']) - time_to_sec($row['check_in_hour']);

        if ($checkins_difference < 0) {
            $checkins_difference += time_to_sec("24:00:00");
        }

        //Incrementojme totalin me cdo checkin brenda 1 dite
        $total_hours += $checkins_difference;


        //Nese totali eshte me i vogel se orari normal, thjesht shtojme sa ka punuar per ate checkin specifik
        if ($total_hours < $time) {

            if ($k1 == 1.5 * $sph) {
                $data[$row['user_id']]['normal_hours_holiday'] += $checkins_difference;
            } else if ($k1 == 1.25 * $sph) {
                $data[$row['user_id']]['normal_hours_weekend'] += $checkins_difference;
            } else {
                $data[$row['user_id']]['normal_hours_normal'] += $checkins_difference;
            }

            $data[$row['user_id']]['normal_hours'] += $checkins_difference;
            $data[$row['user_id']]['normal_salary'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['normal_hours'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['normal_salary'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary'] += $checkins_difference * $k1;

            //Kjo ekzekutohet vetem 1 here per dite
            //Nese Totali eshte me i madh se orari normal, por totali i checkinit te kaluar ishte me i vogel se orari normal
        } else if ($total_hours > $time && $prev_total < $time) {

            if ($k1 == 1.5 * $sph) {
                $data[$row['user_id']]['normal_hours_holiday'] += ($time - $prev_total);
            } else if ($k1 == 1.25 * $sph) {
                $data[$row['user_id']]['normal_hours_weekend'] += ($time - $prev_total);
            } else {
                $data[$row['user_id']]['normal_hours_normal'] += ($time - $prev_total);
            }

            //calculate total per user hours and salary
            $data[$row['user_id']]['normal_hours'] += ($time - $prev_total);
            $data[$row['user_id']]['normal_salary'] += ($time - $prev_total) * $k1;
            $data[$row['user_id']]['overtime'] += ($total_hours - $time);
            $data[$row['user_id']]['overtime_salary'] += ($total_hours - $time) * $k2;

            //calculate total per month and salary
            $data[$row['user_id']]['row_details'][$month]['normal_hours'] += $time - $prev_total;
            $data[$row['user_id']]['row_details'][$month]['normal_salary'] += ($time - $prev_total) * $k1;
            $data[$row['user_id']]['row_details'][$month]['overtime'] += $total_hours - $time;
            $data[$row['user_id']]['row_details'][$month]['overtime_salary'] += ($total_hours - $time) * $k2;

            //calculate total per week and salary
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours'] += $time - $prev_total;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary'] += ($time - $prev_total) * $k1;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime'] += $total_hours - $time;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary'] += ($total_hours - $time) * $k2;

            //calculate total per day and salary
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours'] = $time;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary'] = $time * $k1;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime'] = $total_hours - $time;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary'] = ($total_hours - $time) * $k2;


            //Kjo i bie qe kemi vetem overtime, kshu qe shtojme vetem differencen e checkins
        } else if ($total_hours > $time && $prev_total > $time) {

            //calculate total per user hours and salary
            $data[$row['user_id']]['overtime'] += $checkins_difference;
            $data[$row['user_id']]['overtime_salary'] += $checkins_difference * $k2;

            //calculate total per month and salary
            $data[$row['user_id']]['row_details'][$month]['overtime'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['overtime_salary'] += $checkins_difference * $k2;

            //calculate total per week and salary
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary'] += $checkins_difference * $k2;

            //calculate total per day and salary
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary'] += $checkins_difference * $k2;

        }
        $prev_total = $total_hours;


        $data[$row['user_id']]['All_Dates_ARRAY'][$row['check_in_date']] = $row['check_in_date'];
        $data[$row['user_id']]['nr_dates'] = count($data[$row['user_id']]['All_Dates_ARRAY']);
    }


    $cal_data = array();

    foreach ($data as &$user_details) {
        //Calculate total hours
        $temp=$user_details['normal_hours'] + $user_details['overtime'];
        //Calculate total salary
        $user_details['salary'] = "$ " . round($user_details['normal_salary'] + $user_details['overtime_salary'], 2);
        //Calculate salary per hour
        $user_details['salary_hour'] = "$ " . round(($user_details['normal_salary'] + $user_details['overtime_salary']) * 3600 / $temp, 2);

        //Convert hours into readable human language
        $user_details['normal_hours'] = seconds2human($user_details['normal_hours']);
        $user_details['overtime'] = seconds2human($user_details['overtime']);
        $user_details['total_hours_in'] = seconds2human($temp);

        //Do the same for normal hours's types.
        $user_details['normal_hours_holiday'] = seconds2human($user_details['normal_hours_holiday']);
        $user_details['normal_hours_weekend'] = seconds2human($user_details['normal_hours_weekend']);
        $user_details['normal_hours_normal'] = seconds2human($user_details['normal_hours_normal']);

        //Convert salary into string.
        $user_details['normal_salary'] = "$ " . round($user_details['normal_salary'], 2);
        $user_details['overtime_salary'] = "$ " . round($user_details['overtime_salary'], 2);

        //Show the user normal hours which include job done on weekends,holidays and normal dates.
        $user_details['normal_hours'] = "Total: " . $user_details['normal_hours'] . "<br> Normal: " . $user_details['normal_hours_normal'] . "<br> Weekends: " . $user_details['normal_hours_weekend'] . "<br> Holidays: " . $user_details['normal_hours_holiday'];


        $month_array = [];
        foreach ($user_details['row_details'] as &$month_details) {

            $month_details['total_hours'] = $month_details['normal_hours'] + $month_details['overtime'];

            //Calculate totalhours in,salary and salary per hour

            $month_details['salary'] = "$ " . round($month_details['normal_salary'] + $month_details['overtime_salary'], 2);
            $month_details['salary_hour'] = "$ " . round(($month_details['normal_salary'] + $month_details['overtime_salary']) * 3600 / $month_details['total_hours'], 2);

            //Convert hours into seconds and calculate normal and overtime salary
            $month_details['total_hours'] = seconds2human($month_details['total_hours']);
            $month_details['normal_hours'] = seconds2human($month_details['normal_hours']);
            $month_details['normal_salary'] = "$ " . round($month_details['normal_salary'], 2);
            $month_details['overtime'] = seconds2human($month_details['overtime']);
            $month_details['overtime_salary'] = "$ " . round($month_details['overtime_salary'], 2);


            $week_array = [];
            foreach ($month_details['row_details'] as &$week_details) {

                $temp = $week_details['normal_hours'] + $week_details['overtime'];

                //Calculate totalhours in,salary and salary per hour
                $week_details['total_hours'] = seconds2human($week_details['normal_hours'] + $week_details['overtime']);
                $week_details['salary'] = "$ " . round($week_details['normal_salary'] + $week_details['overtime_salary'], 2);
                $week_details['salary_hour'] = "$ " . round(($week_details['normal_salary'] + $week_details['overtime_salary']) * 3600 / $temp, 2);

                //Convert hours into seconds and calculate normal and overtime salary
                $week_details['total_hours'] = seconds2human($temp);
                $week_details['normal_hours'] = seconds2human($week_details['normal_hours']);
                $week_details['normal_salary'] = "$ " . round($week_details['normal_salary'], 2);
                $week_details['overtime'] = seconds2human($week_details['overtime']);
                $week_details['overtime_salary'] = "$ " . round($week_details['overtime_salary'], 2);


                $day_array = [];
                foreach ($week_details['row_details'] as &$day_details) {

                    $temp = $day_details['normal_hours'] + $day_details['overtime'];

                    $day_details['salary'] = "$ " . round($day_details['normal_salary'] + $day_details['overtime_salary'], 2);
                    $day_details['salary_hour'] = "$ " . round(($day_details['normal_salary'] + $day_details['overtime_salary']) * 3600 / $temp, 2);


                    $day_details['total_hours'] = seconds2human($temp);
                    $day_details['normal_hours'] = seconds2human($day_details['normal_hours']);
                    $day_details['overtime'] = seconds2human($day_details['overtime']);


                    $day_details['overtime_salary'] = "$ " . round($day_details['overtime_salary'], 2);
                    $day_details['normal_salary'] = "$ " . round($day_details['normal_salary'], 2);


                   $day_array[]=$day_details;
                }

                $week_details['row_details'] = $day_array;
                $week_array[] = $week_details;
            }

            $month_details['row_details'] = $week_array;
            $month_array[] = $month_details;
        }

        $cal_data[] = $user_details;
        $cal_data[$i]['row_details'] = $month_array;

        $i++;
    }


//Nese nuk kemi te dhena, i dergojm array bosh.
    if (empty($table_data)) {
        $table_data = [];
    }

    //Ja dergojme response-in backendit
    $response = array("draw" => intval($draw), "iTotalRecords" => $totalRecords, "iTotalDisplayRecords" => $totalRecordwithFilter, "aaData" => $cal_data);
    echo json_encode($response);
    exit;
}
