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
?>
<style>
    .border-none {
        border-collapse: collapse;
        border: none;

    }


    .border-none td {
        border: 1px solid rgba(0, 0, 0, 0.51);
    }

    .border-none tr:first-child td {
        border-top: none;
    }

    .border-none tr:last-child td {
        border-bottom: none;
    }

    .border-none tr td:first-child {
        border-left: none;
    }

    .border-none tr td:last-child {
        border-right: none;
    }


    .border-none th {
        border: 1px solid rgba(0, 0, 0, 0.51);
    }

    .border-none tr:first-child th {
        border-top: none;
    }

    .border-none tr:last-child th {
        border-bottom: none;
    }

    .border-none tr th:first-child {
        border-left: none;
    }

    .border-none tr th:last-child {
        border-right: none;
    }


    /* Zebra striping */
    tr:nth-of-type(odd) {
        background: #eee;
    }

    th {
        background: #3498db;
        color: white;
        font-weight: bold;
    }

    td, th {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: right;
        font-size: 18px;
    }

    /*
    Max width before this PARTICULAR table gets nasty
    This query will take effect for any screen smaller than 760px
    and also iPads specifically.
    */
    @media only screen and (max-width: 760px),
    (min-device-width: 768px) and (max-device-width: 1024px) {


        .altRow table {
            width: 100%;
        }

        /* remove the padding for full width */
        .altRow > td {
            padding: 0
        }

        table {
            width: 100%;
        }

        /* Force table to not be like tables anymore */
        table, thead, tbody, th, td, tr {
            display: block;
        }

        /* Hide table headers (but not display: none;, for accessibility) */
        thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        tr {
            border: 1px solid #ccc;
        }

        td {
            /* Behave  like a "row" */
            border: none;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 50%;
        }

        td:before {
            /* Now like a table header */
            position: absolute;
            /* Top/left values mimic padding */
            top: 6px;
            left: 6px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            /* Label the data */
            content: attr(data-column);

            color: #000;
            font-weight: bold;
        }


    }

    .month {
        display: none
    }

    .week {
        display: none
    }

    .day {
        display: none
    }
</style>
<?php
include('../_partials/header.php');


if (!isset($_POST['startDate']) || !isset($_POST['endDate'])) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d', strtotime('today'));

} else {

    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn, $_POST['endDate']);
}
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
                                WHERE 1=1 
                               
                      ) AS m 
                     ON m.user_id = users.user_id 
                     WHERE check_in_date >= '2021/06/01' AND check_in_date<= '" . $endDate . "'
                     ORDER BY checkins.check_in_date DESC, users.user_id DESC
                     ";

$result_data = mysqli_query($conn, $query_data);

if (!$result_data) {
    echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
    exit;
}

//array ku do ruajme te dhenat qe ja cojme frontend-it
$data = array();

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
            $data[$row['user_id']]['normal_salary_holiday'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['normal_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['normal_salary_holiday'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary_holiday'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary_holiday'] += $checkins_difference * $k1;

        } else if ($k1 == 1.25 * $sph) {
            $data[$row['user_id']]['normal_hours_weekend'] += $checkins_difference;
            $data[$row['user_id']]['normal_salary_weekend'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['normal_hours_weekend'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['normal_salary_weekend'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours_weekend'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary_weekend'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours_weekend'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary_weekend'] += $checkins_difference * $k1;
        } else {
            $data[$row['user_id']]['normal_hours_normal'] += $checkins_difference;
            $data[$row['user_id']]['normal_salary_normal'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['normal_hours_normal'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['normal_salary_normal'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours_normal'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary_normal'] += $checkins_difference * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours_normal'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary_normal'] += $checkins_difference * $k1;
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
            $data[$row['user_id']]['normal_salary_holiday'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['overtime_hours_holiday'] += ($total_hours - $time);
            $data[$row['user_id']]['overtime_salary_holiday'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['normal_hours_holiday'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['normal_salary_holiday'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['overtime_hours_holiday'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['overtime_salary_holiday'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours_holiday'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary_holiday'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_hours_holiday'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary_holiday'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours_holiday'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary_holiday'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_hours_holiday'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary_holiday'] += ($total_hours - $time) * $k2;


        } else if ($k1 == 1.25 * $sph) {

            $data[$row['user_id']]['normal_hours_weekend'] += ($time - $prev_total);
            $data[$row['user_id']]['normal_salary_weekend'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['overtime_hours_weekend'] += ($total_hours - $time);
            $data[$row['user_id']]['overtime_salary_weekend'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['normal_hours_weekend'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['normal_salary_weekend'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['overtime_hours_weekend'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['overtime_salary_weekend'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours_weekend'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary_weekend'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_hours_weekend'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary_weekend'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours_weekend'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary_weekend'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_hours_weekend'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary_weekend'] += ($total_hours - $time) * $k2;
        } else {

            $data[$row['user_id']]['normal_hours_normal'] += ($time - $prev_total);
            $data[$row['user_id']]['normal_salary_normal'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['overtime_hours_normal'] += ($total_hours - $time);
            $data[$row['user_id']]['overtime_salary_normal'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['normal_hours_normal'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['normal_salary_normal'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['overtime_hours_normal'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['overtime_salary_normal'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_hours_normal'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['normal_salary_normal'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_hours_normal'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary_normal'] += ($total_hours - $time) * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_hours_normal'] += ($time - $prev_total);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['normal_salary_normal'] += ($time - $prev_total) * $k1;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_hours_normal'] += ($total_hours - $time);
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary_normal'] += ($total_hours - $time) * $k2;
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

        if ($k1 == 1.5 * $sph) {
            $data[$row['user_id']]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['overtime_salary_holiday'] += $checkins_difference * $k2;

            $data[$row['user_id']]['row_details'][$month]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['overtime_salary_holiday'] += $checkins_difference * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary_holiday'] += $checkins_difference * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary_holiday'] += $checkins_difference * $k2;
        } else if ($k1 == 1.25 * $sph) {
            $data[$row['user_id']]['overtime_hours_weekend'] += $checkins_difference;
            $data[$row['user_id']]['overtime_salary_holiday'] += $checkins_difference * $k2;

            $data[$row['user_id']]['row_details'][$month]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['overtime_salary_holiday'] += $checkins_difference * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['overtime_salary_holiday'] += $checkins_difference * $k2;

            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_hours_holiday'] += $checkins_difference;
            $data[$row['user_id']]['row_details'][$month]['row_details'][$week]['row_details'][$row['check_in_date']]['overtime_salary_holiday'] += $checkins_difference * $k2;
        } else {
            $data[$row['user_id']]['overtime_hours_normal'] += $checkins_difference;
            $data[$row['user_id']]['overtime_salary_normal'] += $checkins_difference * $k2;
        }

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
    $temp = $user_details['normal_hours'] + $user_details['overtime'];

    //Calculate totalhours in,salary and salary per hour
    $user_details['salary'] = "$ " . round($user_details['normal_salary'] + $user_details['overtime_salary'], 2);
    $user_details['salary_hour'] = "$ " . round(($user_details['normal_salary'] + $user_details['overtime_salary']) * 3600 / $temp, 2);
    $user_details['total_hours'] = seconds2human($temp);

    //Convert hours into readable human language
    $user_details['normal_hours'] = seconds2human($user_details['normal_hours']);
    $user_details['overtime'] = seconds2human($user_details['overtime']);


    //Do the same for normal hours's types.///////////////////////////////////////////////////////////
    $user_details['normal_hours_holiday'] = seconds2human($user_details['normal_hours_holiday']);
    $user_details['normal_hours_weekend'] = seconds2human($user_details['normal_hours_weekend']);
    $user_details['normal_hours_normal'] = seconds2human($user_details['normal_hours_normal']);

    $user_details['normal_salary_holiday'] = "$ " . round($user_details['normal_salary_holiday'], 2);
    $user_details['normal_salary_weekend'] = "$ " . round($user_details['normal_salary_weekend'], 2);
    $user_details['normal_salary_normal'] = "$ " . round($user_details['normal_salary_normal'], 2);

    $user_details['overtime_hours_holiday'] = seconds2human($user_details['overtime_hours_holiday']);
    $user_details['overtime_hours_weekend'] = seconds2human($user_details['overtime_hours_weekend']);
    $user_details['overtime_hours_normal'] = seconds2human($user_details['overtime_hours_normal']);

    $user_details['overtime_salary_holiday'] = "$ " . round($user_details['overtime_salary_holiday'], 2);
    $user_details['overtime_salary_weekend'] = "$ " . round($user_details['overtime_salary_weekend'], 2);
    $user_details['overtime_salary_normal'] = "$ " . round($user_details['overtime_salary_normal'], 2);
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //Convert salary into string.
    $user_details['normal_salary'] = "$ " . round($user_details['normal_salary'], 2);
    $user_details['overtime_salary'] = "$ " . round($user_details['overtime_salary'], 2);

    $month_array = [];
    foreach ($user_details['row_details'] as &$month_details) {

        $temp = $month_details['normal_hours'] + $month_details['overtime'];

        //Calculate totalhours in,salary and salary per hour
        $month_details['salary'] = "$ " . round($month_details['normal_salary'] + $month_details['overtime_salary'], 2);
        $month_details['salary_hour'] = "$ " . round(($month_details['normal_salary'] + $month_details['overtime_salary']) * 3600 / $temp, 2);
        $month_details['total_hours'] = seconds2human($temp);
        //Convert hours into seconds and calculate normal and overtime salary
        $month_details['normal_hours'] = seconds2human($month_details['normal_hours']);
        $month_details['normal_salary'] = "$ " . round($month_details['normal_salary'], 2);
        $month_details['overtime'] = seconds2human($month_details['overtime']);
        $month_details['overtime_salary'] = "$ " . round($month_details['overtime_salary'], 2);


        //Do the same for normal hours's types.///////////////////////////////////////////////////////////
        $month_details['normal_hours_holiday'] = seconds2human($month_details['normal_hours_holiday']);
        $month_details['normal_hours_weekend'] = seconds2human($month_details['normal_hours_weekend']);
        $month_details['normal_hours_normal'] = seconds2human($month_details['normal_hours_normal']);

        $month_details['normal_salary_holiday'] = "$ " . round($month_details['normal_salary_holiday'], 2);
        $month_details['normal_salary_weekend'] = "$ " . round($month_details['normal_salary_weekend'], 2);
        $month_details['normal_salary_normal'] = "$ " . round($month_details['normal_salary_normal'], 2);

        $month_details['overtime_hours_holiday'] = seconds2human($month_details['overtime_hours_holiday']);
        $month_details['overtime_hours_weekend'] = seconds2human($month_details['overtime_hours_weekend']);
        $month_details['overtime_hours_normal'] = seconds2human($month_details['overtime_hours_normal']);

        $month_details['overtime_salary_holiday'] = "$ " . round($month_details['overtime_salary_holiday'], 2);
        $month_details['overtime_salary_weekend'] = "$ " . round($month_details['overtime_salary_weekend'], 2);
        $month_details['overtime_salary_normal'] = "$ " . round($month_details['overtime_salary_normal'], 2);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////


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

            //Do the same for normal hours's types.///////////////////////////////////////////////////////////
            $week_details['normal_hours_holiday'] = seconds2human($week_details['normal_hours_holiday']);
            $week_details['normal_hours_weekend'] = seconds2human($week_details['normal_hours_weekend']);
            $week_details['normal_hours_normal'] = seconds2human($week_details['normal_hours_normal']);

            $week_details['normal_salary_holiday'] = "$ " . round($week_details['normal_salary_holiday'], 2);
            $week_details['normal_salary_weekend'] = "$ " . round($week_details['normal_salary_weekend'], 2);
            $week_details['normal_salary_normal'] = "$ " . round($week_details['normal_salary_normal'], 2);

            $week_details['overtime_hours_holiday'] = seconds2human($week_details['overtime_hours_holiday']);
            $week_details['overtime_hours_weekend'] = seconds2human($week_details['overtime_hours_weekend']);
            $week_details['overtime_hours_normal'] = seconds2human($week_details['overtime_hours_normal']);

            $week_details['overtime_salary_holiday'] = "$ " . round($week_details['overtime_salary_holiday'], 2);
            $week_details['overtime_salary_weekend'] = "$ " . round($week_details['overtime_salary_weekend'], 2);
            $week_details['overtime_salary_normal'] = "$ " . round($week_details['overtime_salary_normal'], 2);
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////


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


                //Do the same for normal hours's types.///////////////////////////////////////////////////////////
                $day_details['normal_hours_holiday'] = seconds2human($day_details['normal_hours_holiday']);
                $day_details['normal_hours_weekend'] = seconds2human($day_details['normal_hours_weekend']);
                $day_details['normal_hours_normal'] = seconds2human($day_details['normal_hours_normal']);

                $day_details['normal_salary_holiday'] = "$ " . round($day_details['normal_salary_holiday'], 2);
                $day_details['normal_salary_weekend'] = "$ " . round($day_details['normal_salary_weekend'], 2);
                $day_details['normal_salary_normal'] = "$ " . round($day_details['normal_salary_normal'], 2);

                $day_details['overtime_hours_holiday'] = seconds2human($day_details['overtime_hours_holiday']);
                $day_details['overtime_hours_weekend'] = seconds2human($day_details['overtime_hours_weekend']);
                $day_details['overtime_hours_normal'] = seconds2human($day_details['overtime_hours_normal']);

                $day_details['overtime_salary_holiday'] = "$ " . round($day_details['overtime_salary_holiday'], 2);
                $day_details['overtime_salary_weekend'] = "$ " . round($day_details['overtime_salary_weekend'], 2);
                $day_details['overtime_salary_normal'] = "$ " . round($day_details['overtime_salary_normal'], 2);
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $day_array[] = $day_details;
            }

            $week_details['row_details'] = $day_array;
            $week_array[] = $week_details;
        }

        $month_details['row_details'] = $week_array;
        $month_array[] = $month_details;
    }

    $hours_description = " Total: " . $user_details['normal_hours'] . "<br>  Normal: " . $user_details['normal_hours_normal'] . "<br>  Weekends: " . $user_details['normal_hours_weekend'] . "<br>  Holidays: " . $user_details['normal_hours_holiday'];

    $cal_data[] = $user_details;
    $cal_data[$i]['row_details'] = $month_array;
    $cal_data[$i]['normal_hours_description'] = $hours_description;

    $i++;
}

?>
<body id="body" style="font-family: Arial">

<div style='width:1800px !important;margin: auto'>
    <table style='outline: 1px solid black;table-layout:fixed' class="table table-striped main_table border-none">
        <thead>

        <tr>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' rowspan='2'
                colspan='2'>Full Name
            </th>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>
                Hours In
            </th>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>
                Hours Out
            </th>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>
                Payment In
            </th>
            <th style='vertical-align : middle;text-align:center' scope='col' colspan='4'>
                Payment Out
            </th>
        </tr>

        <tr>
            <th class='border-right' scope='col' colspan='1'>Holiday</th>
            <th class='border-right' scope='col' colspan='1'>Weekend</th>
            <th class='border-right' scope='col' colspan='1'>Normal</th>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>
                Total
            </th>
            <th class='border-right' scope='col' colspan='1'>Holiday</th>
            <th class='border-right' scope='col' colspan='1'>Weekend</th>
            <th class='border-right' scope='col' colspan='1'>Normal</th>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>
                Total
            </th>
            <th class='border-right' scope='col' colspan='1'>Holiday</th>
            <th class='border-right' scope='col' colspan='1'>Weekend</th>
            <th class='border-right' scope='col' colspan='1'>Normal</th>
            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>
                Total
            </th>
            <th class='border-right' scope='col' colspan='1'>Holiday</th>
            <th class='border-right' scope='col' colspan='1'>Weekend</th>
            <th class='border-right' scope='col' colspan='1'>Normal</th>
            <th style='vertical-align : middle;text-align:center' scope='col' colspan='1'>
                Total
            </th>
        <tr>

        </thead>
        <?php
        $i = 0;
        foreach ($cal_data as $data) {


            ?>
            <tr class="user">
                <td><i class='fas fa-plus-circle fa-lg text-dark ' style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                <td style='vertical-align : middle;text-align:center;border-right: 2px solid red'><?= $data['first_name'] ?></td>
                <td><?= $data['normal_hours_holiday'] ?></td>
                <td><?= $data['normal_hours_weekend'] ?></td>
                <td><?= $data['normal_hours_normal'] ?></td>
                <td style='border-right: 2px solid red'><?= $data['normal_hours'] ?></td>

                <td><?= $data['overtime_hours_holiday'] ?></td>
                <td><?= $data['overtime_hours_weekend'] ?></td>
                <td><?= $data['overtime_hours_normal'] ?> </td>
                <td style='border-right: 2px solid red'><?= $data['overtime'] ?></td>

                <td><?= $data['normal_salary_holiday'] ?></td>
                <td><?= $data['normal_salary_weekend'] ?></td>
                <td><?= $data['normal_salary_normal'] ?></td>
                <td style='border-right: 2px solid red'><?= $data['normal_salary'] ?></td>

                <td><?= $data['overtime_salary_holiday'] ?></td>
                <td><?= $data['overtime_salary_weekend'] ?> </td>
                <td><?= $data['overtime_salary_normal'] ?> </td>
                <td><?= $data['overtime_salary'] ?> </td>
            </tr>

            <tr class='altRow month'>
                <td class=' p-0' colspan='18'>
                    <table style='width: 1800px;table-layout: fixed' class='table m-0 p-0 border-none'>
                        <thead>
                        <tr>
                            <th class='border-right' scope='col'>Show</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red'
                                scope='col' colspan='1'>Month
                            </th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red'
                                scope='col' colspan='1'>
                                Total
                            </th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='border-right: 2px solid red'
                                scope='col' colspan='1'>
                                Total
                            </th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='border-right: 2px solid red'
                                scope='col' colspan='1'>
                                Total
                            </th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='vertical-align : middle;text-align:center' scope='col' colspan='1'>
                                Total
                            </th>
                        <tr>
                        </thead>


                        <?php
                        $i++;


                        $j = 0;
                        foreach ($data['row_details'] as $month_data) {

                            ?>
                            <tr style="color: blue !important;">
                                <td><i class='fas fa-plus-circle fa-lg text-dark ' style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                                <td style='border-right: 2px solid red'><?= $month_data['date'] ?></td>


                                <td><?= $month_data['normal_hours_holiday'] ?></td>
                                <td><?= $month_data['normal_hours_weekend'] ?></td>
                                <td><?= $month_data['normal_hours_normal'] ?></td>
                                <td style='border-right: 2px solid red'><?= $month_data['normal_hours'] ?></td>

                                <td><?= $month_data['overtime_hours_holiday'] ?></td>
                                <td><?= $month_data['overtime_hours_weekend'] ?> </td>
                                <td><?= $month_data['overtime_hours_normal'] ?> </td>
                                <td style='border-right: 2px solid red'><?= $month_data['overtime'] ?> </td>

                                <td><?= $month_data['normal_salary_holiday'] ?></td>
                                <td><?= $month_data['normal_salary_weekend'] ?></td>
                                <td><?= $month_data['normal_salary_normal'] ?></td>
                                <td style='border-right: 2px solid red'><?= $month_data['normal_salary'] ?></td>

                                <td><?= $month_data['overtime_salary_holiday'] ?></td>
                                <td><?= $month_data['overtime_salary_weekend'] ?> </td>
                                <td><?= $month_data['overtime_salary_normal'] ?> </td>
                                <td><?= $month_data['overtime_salary'] ?> </td>
                            </tr>

                            <tr class='altRow week m-0 p-0'>
                                <td class='m-0 p-0' colspan='18'>
                                    <table style='width: 1800px;table-layout:fixed' class='table m-0 p-0 border-none'>
                                        <thead>
                                        <tr>
                                            <th class='border-right' scope='col' colspan='1'>Show</th>
                                            <th style='border-right: 2px solid red' scope='col' colspan='1'>Week</th>
                                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                            <th style='border-right: 2px solid red'
                                                scope='col' colspan='1'>
                                                Total
                                            </th>
                                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                            <th style='border-right: 2px solid red'
                                                scope='col' colspan='1'>
                                                Total
                                            </th>
                                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                            <th style='border-right: 2px solid red'
                                                scope='col' colspan='1'>
                                                Total
                                            </th>
                                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                            <th style='vertical-align : middle;text-align:center' scope='col'
                                                colspan='1'>
                                                Total
                                            </th>
                                        <tr>
                                        </thead>
                                        <?php

                                        $j++;

                                        $z = 0;
                                        foreach ($month_data['row_details'] as $week_data) {

                                            ?>
                                            <tr style="color: green !important;">
                                                <td><i class='fas fa-plus-circle fa-lg text-dark ' style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                                                <td style='border-right: 2px solid red'><?= $week_data['date'] ?></td>


                                                <td><?= $week_data['normal_hours_holiday'] ?></td>
                                                <td><?= $week_data['normal_hours_weekend'] ?></td>
                                                <td><?= $week_data['normal_hours_normal'] ?></td>
                                                <td style='border-right: 2px solid red'><?= $week_data['normal_hours'] ?></td>

                                                <td><?= $week_data['overtime_hours_holiday'] ?></td>
                                                <td><?= $week_data['overtime_hours_weekend'] ?> </td>
                                                <td><?= $week_data['overtime_hours_normal'] ?> </td>
                                                <td style='border-right: 2px solid red'><?= $week_data['overtime'] ?> </td>

                                                <td><?= $week_data['normal_salary_holiday'] ?></td>
                                                <td><?= $week_data['normal_salary_weekend'] ?></td>
                                                <td><?= $week_data['normal_salary_normal'] ?></td>
                                                <td style='border-right: 2px solid red'><?= $week_data['normal_salary'] ?></td>

                                                <td><?= $week_data['overtime_salary_holiday'] ?></td>
                                                <td><?= $week_data['overtime_salary_weekend'] ?></td>
                                                <td><?= $week_data['overtime_salary_normal'] ?> </td>
                                                <td><?= $week_data['overtime_salary'] ?></td>
                                            </tr>
                                            <tr class='altRow day m-0 p-0'>
                                                <td class='m-0 p-0' colspan='18'>
                                                    <table style='width: 1800px;table-layout:fixed'
                                                           class='table m-0 p-0 border-none'>
                                                        <thead>
                                                        <tr>
                                                            <th class='border-right' scope='col' colspan='1'>Show</th>
                                                            <th style='border-right: 2px solid red' scope='col'
                                                                colspan='1'>Date
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Holiday
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Weekend
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                                            <th style='border-right: 2px solid red'
                                                                scope='col' colspan='1'>
                                                                Total
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Holiday
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Weekend
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                                            <th style='border-right: 2px solid red'
                                                                scope='col' colspan='1'>
                                                                Total
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Holiday
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Weekend
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                                            <th style='border-right: 2px solid red'
                                                                scope='col' colspan='1'>
                                                                Total
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Holiday
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Weekend
                                                            </th>
                                                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                                                            <th style='vertical-align : middle;text-align:center'
                                                                scope='col' colspan='1'>
                                                                Total
                                                            </th>
                                                        <tr>
                                                        </thead>
                                                        <?php
                                                        $z++;

                                                        foreach ($week_data['row_details'] as $day_data) {

                                                            ?>
                                                            <tr style="color: darkorange !important;">

                                                                <td colspan='2'
                                                                    style='border-right: 2px solid red'><?= $day_data['date'] ?></td>

                                                                <td><?= $day_data['normal_hours_holiday'] ?></td>
                                                                <td><?= $day_data['normal_hours_weekend'] ?></td>
                                                                <td><?= $day_data['normal_hours_normal'] ?></td>
                                                                <td style='border-right: 2px solid red'><?= $day_data['normal_hours'] ?></td>

                                                                <td><?= $day_data['overtime_hours_holiday'] ?></td>
                                                                <td><?= $day_data['overtime_hours_weekend'] ?></td>
                                                                <td><?= $day_data['overtime_hours_normal'] ?></td>
                                                                <td style='border-right: 2px solid red'><?= $day_data['overtime'] ?> </td>

                                                                <td><?= $day_data['normal_salary_holiday'] ?></td>
                                                                <td><?= $day_data['normal_salary_weekend'] ?></td>
                                                                <td><?= $day_data['normal_salary_normal'] ?></td>
                                                                <td style='border-right: 2px solid red'><?= $day_data['normal_salary'] ?></td>

                                                                <td><?= $day_data['overtime_salary_holiday'] ?></td>
                                                                <td><?= $day_data['overtime_salary_weekend'] ?></td>
                                                                <td><?= $day_data['overtime_salary_normal'] ?></td>
                                                                <td><?= $day_data['overtime_salary'] ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php
                                        } ?>
                                    </table>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <?php
        }
        ?>

    </table>

</body>

<?php include('../_partials/footer.php'); ?>

<script type='text/javascript'>

   // $('table.main_table').on('click', 'i.fas', function() {
   //    $(this).toggleClass('fa-plus-circle fa-minus-circle');
   //
   //    let element = $(this).closest('tr').next();
   //
   //    if (element.is(':visible')) {
   //       element.fadeOut();
   //    } else {
   //       element.fadeIn();
   //    }
   //
   // });
</script>

<script>
   window.onload = function() {
      var anchors = document.querySelectorAll(".fas")
      for(var i = 0; i < anchors.length; i++) {
         var anchor = anchors[i];
         let detail =  anchor.parentNode.parentNode.nextElementSibling
         anchor.addEventListener('click', function() {
               if (window.getComputedStyle(detail).display === "none") {
                  detail.style.display = "block";
               }
               else {
                  detail.style.display = "none";
               }
         })
      }

   }
</script>

