<?php
error_reporting(E_ALL ^ E_WARNING);
include('C:\xampp\htdocs\WeWebProject\_config\constants.php');

/**
 *
 */
if (isset($_POST['add_checking'])) {

    $conn = mysqli_connect("localhost", "root", "root", "weweb");

    /**
     * Marri mte dhenat nga useri
     */
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $checkin = mysqli_real_escape_string($conn,$_POST['checkin']);
    $checkout = mysqli_real_escape_string($conn,$_POST['checkout']);
    $checkinDate = mysqli_real_escape_string($conn,$_POST['checkin_date']);
    $checkoutDate = mysqli_real_escape_string($conn,$_POST['checkout_date']);

    if ($checkin=="" || $checkout==""){
        echo json_encode(array("status" => 404, "message" => "Error! Checkin is empty! " . __LINE__));
        exit;
    }

    if ($checkinDate=="" || $checkoutDate==""){
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

function time_to_sec($time): int
{
    return strtotime($time) - strtotime('TODAY');
}


function empty_data($total_records, $error = "")
{
    $response = array("draw" => intval($draw), "iTotalRecords" => $total_records, "iTotalDisplayRecords" => 0, "aaData" => array(), "error" => $error,);
    echo json_encode($response);
    exit;
}

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


$data = array();
while ($row = mysqli_fetch_assoc($result_data)) {
    $data[$row['user_id']]['user_id'] = $row['user_id'];
    $data[$row['user_id']]['first_name'] = $row['first_name'];
    $data[$row['user_id']]['last_name'] = $row['last_name'];


}
/**
 * Pershtasim te dhenat sipas formatit qe i do datatable ne front-end
 */
$buttonval = 1;
$id_values = array();
foreach ($data as $key => $row) {

    //ruajme id-te e userave qe ti perdorim per checkins info
    $id_values[] = $row['user_id'];


    $table_data[] = array(
        "user_id" => $row["user_id"],
        "show" => '<span id="id-' . $buttonval . '"><button id="button' . $buttonval . '" class="show" style="border:none;background:none;margin-top:8px" class="button-primary" value="' . $buttonval . '"><i class="fas fa-plus-circle text-success" style="font-size:25px" ></i></button></span>',
        "first_name" => '<span id="name-' . $buttonval . '">' . $row["first_name"] . " " . $row["last_name"] . '</span>',
        "total_hours_in" => '<span id="hours-' . $buttonval . '"></span>',
        "total_hours_out" => '<span id="hours-out-' . $buttonval . '"></span>', "dates" => '<span id="dates-' . $buttonval . '"></span>'


    );
    $buttonval++;

}
    /**
     * Dergojme te dhenat ne front
     *
     *
     */



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


    $result_checkins = mysqli_query($conn, $sql_get_checkings);

    if (!$result_checkins) {
        echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
        exit;
    }

    //krijojme nje funksion qe te na konvertoje kohen ne seconda

//Deklarojme array $checkings ku do ruajme te dhenat
    $checkins = array();

    $time = time_to_sec('23:59:59');

//deklarojme variablen ku do ruajme oret totale per cdo date
    $total_hours_in = 0;


    while ($row = mysqli_fetch_assoc($result_checkins)) {

        //convert 00:00 to 23:59 to avoid miscalculations
        if ($row['check_out_hour'] == '00:00:00') {
            $row['check_out_hour'] = '23:59:59';
        }
        //3d - checkins per date // shtojme te dhenat baze te checkings
        $checkins[$row['user_id']][$row['check_in_date']]['checkins_per_day'][$row['id']]['check_in_date'] = $row['check_in_date'];
        $checkins[$row['user_id']][$row['check_in_date']]['checkins_per_day'][$row['id']]['check_in_hour'] = $row['check_in_hour'];
        $checkins[$row['user_id']][$row['check_in_date']]['checkins_per_day'][$row['id']]['check_out_hour'] = $row['check_out_hour'];
        $checkins[$row['user_id']][$row['check_in_date']]['checkins_per_day'][$row['id']]['check_out_date'] = $row['check_out_date'];

        $daily_difference = time_to_sec($row['check_out_hour']) - time_to_sec($row['check_in_hour']);

        /**
         * //2d single date details // llogarisimin oret totale te checkinsave per dite
         */

        //ne key unik check_in_date ruajme /checkin date / oret totale per date in dhe out / id-ne e userit
        $checkins[$row['user_id']][$row['check_in_date']]['check_in_date'] = $row['check_in_date'];

        //shtojme diferencen ne ore checkout-checkin per cdo checkings qe kemi brenda nje date
        $checkins[$row['user_id']][$row['check_in_date']]['total_hours_in'] += $daily_difference;

        //ketu nuk iterojme por shtojme veten diferencen finale, pra 24 ore minus oret qe ka punuar ne total ate dite.
        $checkins[$row['user_id']][$row['check_in_date']]['total_hours_out'] = $time - $checkins[$row['user_id']][$row['check_in_date']]['total_hours_in'];

        //shtojme user id se na nevojitet
        $checkins[$row['user_id']][$row['check_in_date']]['user_id'] = $row['user_id'];


        //store how many checkings we got per day, by counting number of arrays inside checkins_per_day
        if (is_array($checkins[$row['user_id']][$row['check_in_date']]['checkins_per_day'][$row['id']])) {
            $checkins[$row['user_id']][$row['check_in_date']]['count'] += 1;
        }
    }

//Nese nuk kemi te dhena, i dergojm array bosh.
if (empty($table_data)) {
    $table_data = [];
}


$response = array("draw" => intval($draw), "iTotalRecords" => $totalRecords, "iTotalDisplayRecords" => $totalRecordwithFilter, "aaData" => $table_data, "checkinsData" => $checkins);
echo json_encode($response);
exit;
