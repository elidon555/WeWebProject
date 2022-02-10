<?php
if ($_POST["action"] == "elenco_richieste_interventi_ss") {
/*************** Seksioni i pare ***************\
* draw -> sa here eshte manipuluar tabela, apo numri i thirrjeve ajax pa bere refresh faqjen
* columnIndex -> ruhen numrat e kolonave
* columnSortOrder -> ruhet lloji i renditjes (DESC apo ASC)
* searchValue -> vlera qe po behet serach
*/
$draw = $_POST['draw'];
$limit_start = $_POST['start'];
$limit_end = $_POST['length'];
if ($limit_end == "-1") {
$pagination = "";
} else {
$pagination = "LIMIT " . $limit_start . ", " . $limit_end;
}
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db_conn, $_POST['search']['value']);
if ($columnName == "data" || $columnName == "ora" || $columnName == "local_data") {
$columnName = "start_time";
}
if ($columnName == "date_time_appuntamento") {
$columnName = "data_app_tecnico";
}
if ($columnName == "nome_tecnico") {
$columnName = "artigiano";
}
if ($columnName == "appointment_posticipato") {
$columnName = "data_app_posticipato";
}
$searchQuery = " ";
if ($searchValue != '') {
$searchQuery = " AND (
caller_code LIKE '%" . $searchValue . "%' OR
citta LIKE '%" . $searchValue . "%' OR
locality LIKE '%" . $searchValue . "%' OR
search_call LIKE '%" . $searchValue . "%' OR
chiamate_organica LIKE '%" . $searchValue . "%' OR
manager_tecnico_full_name LIKE '%" . $searchValue . "%' OR
responsabile_number_code LIKE '%" . $searchValue . "%' OR
tipo_attivita LIKE '%" . $searchValue . "%' OR
number_dialed LIKE '%" . $searchValue . "%' OR
tipo_rich LIKE '%" . $searchValue . "%' OR
detaglio_tipo_rich LIKE '%" . $searchValue . "%' OR
tipo_segnazione LIKE '%" . $searchValue . "%' OR
data_app_tecnico LIKE '%" . $searchValue . "%' OR
ora_app_tecnico LIKE '%" . $searchValue . "%' OR
esito_assegnazione_tecnico LIKE '%" . $searchValue . "%' OR
artigiano LIKE '%" . $searchValue . "%' OR
dettaglio_non_assegnazione LIKE '%" . $searchValue . "%' OR
note_inter_no LIKE '%" . $searchValue . "%' OR
valore_preventivo LIKE '%" . $searchValue . "%' OR
valore_caparra_anticipo LIKE '%" . $searchValue . "%' OR
valore_uscita LIKE '%" . $searchValue . "%' OR
verifica_esito_esecuzione LIKE '%" . $searchValue . "%' OR
dettagli_verifica_esito_escuzone LIKE '%" . $searchValue . "%' OR
intervento LIKE '%" . $searchValue . "%' OR
new_appointment_date LIKE '%" . $searchValue . "%' OR
valore_iva_inc LIKE '%" . $searchValue . "%' OR
valore_pezzi_ricc LIKE '%" . $searchValue . "%' OR
comisione LIKE '%" . $searchValue . "%' OR
somma_incas1 LIKE '%" . $searchValue . "%' OR
somma_incas2 LIKE '%" . $searchValue . "%' OR
somma_incas3 LIKE '%" . $searchValue . "%' OR
note_esecuzione_intervento LIKE '%" . $searchValue . "%'
) ";
}


/****************** Seksioni 2 ******************\
* Te gjitha Filtrat
*/
/**
* Filter DATA
*/
if (!empty($_POST['date'])) {
$date_filter = explode(" a ", $_POST['date']);
$filter_date = " and $chia_inter_urgenti.start_time>='" . mysqli_real_escape_string($db_conn, $date_filter[0]) . " 00:00:00' and $chia_inter_urgenti.start_time<='" . mysqli_real_escape_string($db_conn, $date_filter[1]) . " 23:59:59' ";
} elseif (empty($_POST["ids"])) {
$filter_date = " and $chia_inter_urgenti.start_time>='" . date('Y-m-d') . " 00:00:00' and $chia_inter_urgenti.start_time<='" . date('Y-m-d') . " 23:59:59' ";
}
/**
* Filter Citta
*/
if (!empty($_POST["citta_flt"])) {
$filter_citta_pre = explode(',', $_POST["citta_flt"]);
foreach ($filter_citta_pre as $citta) {
$citta_flt[] = mysqli_real_escape_string($db_conn, trim($citta, "`"));
}
$citta_flt_string = "'" . implode("','", $citta_flt) . "'";
$filter_citta = "  and citta in (" . $citta_flt_string . ") ";
}

/**
* Filter Manager Tecnico
*/
if (!empty($_POST["manager_tecnico_flt"])) {
$filter_manager_technico = explode(',', $_POST["manager_tecnico_flt"]);
foreach ($filter_manager_technico as $manager_technico) {
$manager_technico_flt[] = mysqli_real_escape_string($db_conn, trim($manager_technico, "'"));
}
$manager_technico_flt_string = "'" . implode("','", $manager_technico_flt) . "'";
$filter_manager_tecnico = "  and manager_tecnicho in (" . $manager_technico_flt_string . ")  ";
}
/**
* Filter area
*/
if (!empty($_POST["area"])) {
$filter_area = explode(',', $_POST["area"]);
foreach ($filter_area as $area) {
$area_flt[] = mysqli_real_escape_string($db_conn, trim($area, "'"));
}

$area_flt_string = "'" . implode("','", $area_flt) . "'";
$filter_area = "  and consulenteGroup in (" . $area_flt_string . ") ";
}

/**
* Filter Pagato
*/
if (!empty($_POST['pagato_flt'])) {
if ($_POST['pagato_flt'] == 'Si') {
$filter_pagato = " AND ((somma_incas1 + somma_incas2 + somma_incas3 = comisione) AND  comisione !=0)";
}
if ($_POST['pagato_flt'] == 'No') {
$filter_pagato = " AND ((somma_incas1 + somma_incas2 + somma_incas3 != comisione) OR  comisione =0)";
}
}

/**
* Filter Responsabile
*/
if (!empty($_POST["responsabile"])) {
$filter_responsabile_pre = explode(',', $_POST["responsabile"]);

foreach ($filter_responsabile_pre as $responsabile_loop) {
$responsabile_flt[] = mysqli_real_escape_string($db_conn, trim($responsabile_loop, "'"));

}
$responsabile_flt_string = "'" . implode("','", $responsabile_flt) . "'";
$filter_responsabile_risposta = " and responsabile in (" . $responsabile_flt_string . ") ";
}

/**
* Filter Campagna
*/

if (!empty($_POST["campagna_flt"])) {
$tipo_attivita_pre = explode(',', $_POST["campagna_flt"]);
foreach ($tipo_attivita_pre as $tipo_attivita) {
$tipo_attivita_flt[] = mysqli_real_escape_string($db_conn, trim($tipo_attivita, "'"));
}
$tipo_attivita_string = "'" . implode("','", $tipo_attivita_flt) . "'";
$filter_campagna = "  and tipo_attivita in (" . $tipo_attivita_string . ") ";
}

/**
* Filter Responsabile Area
*/
if (!empty($_POST["responsabile_area"])) {
$filter_responsabile_area = explode(',', $_POST["responsabile_area"]);
foreach ($filter_responsabile_area as $responsabile_area) {
$responsabile_area_flt[] = mysqli_real_escape_string($db_conn, trim($responsabile_area, "'"));
}
$responsabile_area_flt_string = "'" . implode("','", $responsabile_area_flt) . "'";
$filter_responsabile_area = "  and responsabile_area_username in (" . $responsabile_area_flt_string . ") ";
}

/**
* Filter Numero Chiamante
*/
if (!empty($_POST["caller_code"])) {
$filter_caller_code = " AND caller_code = '" . mysqli_real_escape_string($db_conn, $_POST["caller_code"]) . "'";
}

/**
* Filter Numero Chiamante
*/
if (!empty($_POST["number_dialed"])) {
$filter_number_dialed = " AND number_dialed = '" . mysqli_real_escape_string($db_conn, $_POST["number_dialed"]) . "'";
}

/**
* Filter Categoria Chiamate
*/
if (!empty($_POST['search_call_flt'])) {
$search_call_flt_array_pre = explode(',', $_POST["search_call_flt"]);
foreach ($search_call_flt_array_pre as $search_call) {

if (trim($search_call, "'") == 'Organica') {
$filter_search_call = " and chiamate_organica = 'Si' ";
} else {
$search_call_flt[] = mysqli_real_escape_string($db_conn, trim($search_call, "'"));
$filter_search_call = '';
}
}
$search_call_flt_string = "'" . implode("','", $search_call_flt) . "'";
if (!empty($search_call_flt)) {
$filter_search_call .= " and search_call in (" . $search_call_flt_string . ") ";
}
}


/**
* Filter Tecnico
*/
if (!empty($_POST["tecnico_flt"])) {
$filter_tecnico_pre = explode(',', $_POST["tecnico_flt"]);
foreach ($filter_tecnico_pre as $tecnico) {
$tecnico_flt[] = mysqli_real_escape_string($db_conn, trim($tecnico, "`"));
}
$tecnico_flt_string = "'" . implode("','", $tecnico_flt) . "'";
$filter_tecnico = "  and id_cnt in (" . $tecnico_flt_string . ") ";
}
/**
* Filter Tipo chiamate
*/
if (!empty($_POST['tipo_chiamate'])) {
if (mysqli_real_escape_string($db_conn, $_POST['tipo_chiamate']) == 'immediato') {
$tipo_chiamate_flt = " and tipo_segnazione = 'immediato'";
} else {
$tipo_chiamate_flt = " and tipo_segnazione = 'appuntamento'";
}
}
/**
* Kontroll Riparazione elettrodomestici
* Duhet ti shfaqen Riparazione elettrodomestici
*/
$elettrodomestici = checkEletrodomestici($_SESSION['operatore']);
// Filtrimi sipas Rolit ne momentin qe ato hapin faqjen
if (($_SESSION['role_name'] == Config::Manager_Tecnici || $_SESSION['role_name'] == Config::Manager_Tecnici_Junior) && empty($elettrodomestici) && !check_permissions('view_all_elenco_richieste_access')) {
$query_get_cities = "SELECT citta from $interventi_urgenti_operatore_citta where operatore = '" . $_SESSION['operatore'] . "'";


$result_get_cities = mysqli_query($db_conn, $query_get_cities);
if (!$result_get_cities) {
show_alert("danger", "Internal server error. " . __LINE__);
}

$cities = array();
while ($row = mysqli_fetch_assoc($result_get_cities)) {
$cities[$row['citta']] = $row['citta'];
}

$cities_string = "'" . implode("','", $cities) . "'";


$filter_manager_tecnico_on_page_load .= " and citta in ($cities_string) ";
}
// Filtrimi sipas Rolit ne momentin qe ato hapin faqjen
if ($_SESSION['role_name'] == Config::Responsabile_Area && empty($elettrodomestici)) {
$consulenteGroup_string = "'" . implode("','", $_SESSION['consulenteGroup']) . "'";
$filter_responsabile_area_on_page_load = " and consulenteGroup in (" . $consulenteGroup_string . ")";
}
if ($_SESSION['role_name'] == Config::Coordinatore && !check_permissions('show_fatt_performance_access')) {
$cordinatore_string = "'" . implode("','", $_SESSION['cordinatore']) . "'";
$filter_responsabile_area_on_page_load = " AND consulenteGroup in (" . $cordinatore_string . ")";
}

/**
* Filtri i Telefonatave me ID te percaktuara ne URL
*/
if (!empty($_POST["ids"])) {
$arrayIds = explode(";", trim($_POST["ids"], ";"));
$arrayIds_string = "'" . implode("','", $arrayIds) . "'";
$filter_by_ids = " and " . $chia_inter_urgenti . ".id in (" . $arrayIds_string . ")";
}

/*** Tregon numrin total te rekordeve pa filter  ***/
$query_without_ftl = "SELECT COUNT(*) AS allcount
FROM " . $chia_inter_urgenti . "
where stato_segnazione = 'Si'
and nr_chiamata = 'Unica'
$elettrodomestici
" . $filter_date .
$filter_citta .
$filter_manager_tecnico .
$filter_responsabile_area .
$filter_pagato .
$tipo_chiamate_flt .
$filter_tecnico .
$filter_search_call .
$filter_number_dialed .
$filter_caller_code .
$filter_area .
$filter_responsabile_risposta .
$filter_manager_tecnico_on_page_load .
$filter_responsabile_area_on_page_load .
$filter_by_ids .
$filter_campagna;
$sel = mysqli_query($db_conn, $query_without_ftl);
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];


/*** Tregon numrin total te rekordeve me filter  ***/
$query_with_ftl = "SELECT COUNT(*) AS allcount
FROM " . $chia_inter_urgenti . "
WHERE stato_segnazione = 'Si'
AND nr_chiamata = 'Unica'
$elettrodomestici
" . $searchQuery .
$filter_date .
$filter_citta .
$filter_manager_tecnico .
$filter_responsabile_area .
$filter_pagato .
$tipo_chiamate_flt .
$filter_tecnico .
$filter_search_call .
$filter_number_dialed .
$filter_caller_code .
$filter_area .
$filter_responsabile_risposta .
$filter_manager_tecnico_on_page_load .
$filter_responsabile_area_on_page_load .
$filter_by_ids .
$filter_campagna;
$sel = mysqli_query($db_conn, $query_with_ftl);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];
/**
* Nese nuk gjendet asnje vlere me kete karakter qe po kerkohet
*/
emptyResponse($totalRecordwithFilter);
/*** Fetch records ***/
$query_chiamate = "SELECT " . $chia_inter_urgenti . ".id,
uniqueid,
caller_code,
" . $chia_inter_urgenti . ".start_time,
citta,
locality,
search_call,
chiamate_organica,
tipo_attivita,
number_dialed,
tipo_segnazione,
responsabile_number_code,
data_app,
ora_app,
data_app_tecnico,
ora_app_tecnico,
data_app_posticipato,
ora_app_posticipato,
esito_assegnazione_tecnico,
artigiano,
possible_tech_name,
note_assegnazione_tecnico,
dettaglio_non_assegnazione,
note_inter_no,
intervento,
valore_iva_inc,
valore_pezzi_ricc,
comisione,
somma_incas1,
somma_incas2,
somma_incas3,
new_appointment_date,
tipo_rich,
consulenteGroup,
manager_tecnicho,
manager_tecnico_full_name,
detaglio_tipo_rich,
met_customer,
did_tecnico,
fatto_preventivo,
fatto_uscita,
did_not_tecnico,
dett_causa_interv_no,
valore_preventivo,
valore_caparra_anticipo,
valore_uscita,
" . $qcc_oper_urgente . ".stato_qcc,
" . $qcc_oper_urgente . ".note_qcc,
verifica_esito_esecuzione,
dettagli_verifica_esito_escuzone,
note_esecuzione_intervento,
caller_code_email_status,
CASE
WHEN intervento = 'Si' and  met_customer != '' THEN 'OK'
WHEN intervento = 'No' and met_customer != '' THEN 'KO'
WHEN  met_customer != '' THEN 'WIP'
ELSE ''
END as stato_esecuzione,

CASE
WHEN (fatto_preventivo = 'Non Accettato' or fatto_preventivo = 'No') and  fatto_uscita = 'Si' THEN 'Preventivo Non Accetato Pagato Uscita'
WHEN did_tecnico != '' THEN did_tecnico
WHEN did_not_tecnico != '' THEN did_not_tecnico
WHEN dett_causa_interv_no != '' THEN dett_causa_interv_no
ELSE ''
END as detaglio_esecuzione

from " . $chia_inter_urgenti . "
LEFT JOIN $qcc_oper_urgente using(uniqueid)
where stato_segnazione = 'Si'
and nr_chiamata = 'Unica'
$elettrodomestici
$filter_date
$filter_citta
$filter_manager_tecnico
$filter_responsabile_area
$filter_pagato
$tipo_chiamate_flt
$filter_tecnico
$filter_search_call
$filter_number_dialed
$filter_caller_code
$filter_area
$filter_responsabile_risposta
$filter_manager_tecnico_on_page_load
$filter_responsabile_area_on_page_load
$filter_by_ids
$filter_campagna
" . $searchQuery . " ORDER BY " . $columnName . " " . $columnSortOrder . " " . $pagination;

$result_chiamate = mysqli_query($db_conn, $query_chiamate);
if (!$result_chiamate) {
show_alert("danger", "Internal server error. " . __LINE__);
}
$today_date = date("Y-m-d H:i:s");
$all_uniqueids = array();
$data = array();
$responsabile_risposta = array();
while ($row = mysqli_fetch_assoc($result_chiamate)) {
$area = trim($row['consulenteGroup']);
$manager_tecnico_username = trim($row['manager_tecnicho']);
$responsabile_number_code = trim($row['responsabile_number_code']);
$manager_tecnico_full_name = trim($row['manager_tecnico_full_name']);
$all_uniqueids[$row['uniqueid']] = $row['uniqueid'];

//llogarisim kohen e shtetit USA
if ($state == 'Stati Uniti') {
$usa_time = date('Y-m-d H:i:s', strtotime($row['start_time']) - 21600);
$data_six_hours[$row['uniqueid']]['usa_data'] = $usa_time;
$data[$row['uniqueid']]['data_usa'] = date("Y-m-d", strtotime($usa_time));
$data[$row['uniqueid']]['ora_usa'] = date("H:i:s", strtotime($usa_time));
}

//llogarisim kohen e shtetit
if ($state == 'UK' || $state == 'Ireland') {
$local_date = date('Y-m-d H:i:s', strtotime($row['start_time']) - 3600);
$data[$row['uniqueid']]['local_data'] = $local_date;
}

$start_time_array = explode(" ", $row['start_time']);
$data[$row['uniqueid']]['id'] = $row['id'];
$data[$row['uniqueid']]['uniqueid'] = $row['uniqueid'];
$data[$row['uniqueid']]['caller_code'] = $row['caller_code'];
$data[$row['uniqueid']]['start_time'] = $row['start_time'];
$data[$row['uniqueid']]['ora'] = $start_time_array[1];
$data[$row['uniqueid']]['data'] = $start_time_array[0];
$data[$row['uniqueid']]['citta'] = $row['citta'];
$data[$row['uniqueid']]['locality'] = $row['locality'];
$data[$row['uniqueid']]['search_call'] = $row['search_call'];
$data[$row['uniqueid']]['chiamate_organica'] = $row['chiamate_organica'];
$data[$row['uniqueid']]['manager_tecnico_username'] = $manager_tecnico_username;
$data[$row['uniqueid']]['manager_tecnico_full_name'] = $row['manager_tecnico_full_name'];
$data[$row['uniqueid']]['responsabile_number_code'] = $row['responsabile_number_code'];
$data[$row['uniqueid']]['tipo_attivita'] = $row['tipo_attivita'];
$data[$row['uniqueid']]['number_dialed'] = $row['number_dialed'];
$data[$row['uniqueid']]['tipo_rich'] = $row['tipo_rich'];
$data[$row['uniqueid']]['detaglio_tipo_rich'] = $row['detaglio_tipo_rich'];
$data[$row['uniqueid']]['tipo_segnazione'] = $row['tipo_segnazione'];
$data[$row['uniqueid']]['last_card_numbers'] = "";
$data[$row['uniqueid']]['payment_ip_address'] = "";
$data[$row['uniqueid']]['caller_code_email_status'] = $row['caller_code_email_status'];

$data[$row['uniqueid']]['stato_qcc'] = $row['stato_qcc'];
$data[$row['uniqueid']]['note_qcc'] = $row['note_qcc'];
// Nese eshte appuntamento duhet marre data dhe ora nga 2 fusha sepse mund te jete njera e plotesuar dhe tjetra jo
// prandaj duhen pare te dy fushat
if ($row['tipo_segnazione'] == 'appuntamento') {
if ($row['data_app_tecnico'] != '0000-00-00' && $row['ora_app_tecnico'] != '00:00:00') {
$data[$row['uniqueid']]['date_time_appuntamento'] = $row['data_app_tecnico'] . " " . $row['ora_app_tecnico'];
} elseif ($row['data_app'] != '0000-00-00' && $row['ora_app'] != '00:00:00') {
$data[$row['uniqueid']]['date_time_appuntamento'] = $row['data_app'] . " " . $row['ora_app'];
} else {
$data[$row['uniqueid']]['date_time_appuntamento'] = "";
}
} else {
$data[$row['uniqueid']]['date_time_appuntamento'] = "";
}

$data[$row['uniqueid']]['manager_tecnio'] = $row['manager_tecnio'];
$data[$row['uniqueid']]['esito_assegnazione_tecnico'] = $row['esito_assegnazione_tecnico'];
$data[$row['uniqueid']]['nome_tecnico'] = $row['artigiano'];
$data[$row['uniqueid']]['note_assegnazione_tecnico'] = $row['note_assegnazione_tecnico'];
$data[$row['uniqueid']]['dettaglio_non_assegnazione'] = $row['dettaglio_non_assegnazione'];
$data[$row['uniqueid']]['note_inter_no'] = $row['note_inter_no'];
$data[$row['uniqueid']]['intervento'] = $row['intervento'];
$data[$row['uniqueid']]['valore_iva_inc'] = $row['valore_iva_inc'];
if ($row['data_app_posticipato'] != '0000-00-00' && $row['ora_app_posticipato'] != '00:00:00') {
$data[$row['uniqueid']]['appointment_posticipato'] = $row['data_app_posticipato'] . " " . $row['ora_app_posticipato'];
} else {
$data[$row['uniqueid']]['appointment_posticipato'] = "";
}

if ($row['new_appointment_date'] != '0000-00-00 00:00:00') {
$data[$row['uniqueid']]['new_appointment_date'] = $row['new_appointment_date'];
} else {
$data[$row['uniqueid']]['new_appointment_date'] = "";
}

$data[$row['uniqueid']]['comisione'] = $row['comisione'];
$data[$row['uniqueid']]['valore_pezzi_ricc'] = $row['valore_pezzi_ricc'];
$data[$row['uniqueid']]['somma_incas1'] = $row['somma_incas1'];
$data[$row['uniqueid']]['somma_incas2'] = $row['somma_incas2'];
$data[$row['uniqueid']]['somma_incas3'] = $row['somma_incas3'];
$data[$row['uniqueid']]['note_esecuzione_intervento'] = $row['note_esecuzione_intervento'];
// Percaktohet Stato Esecuzione
if ($row['intervento'] == "No" && !empty($row['met_customer'])) {
$data[$row['uniqueid']]['stato_esecuzione'] = "KO";
} elseif ($row['intervento'] == "Si" && !empty($row['met_customer'])) {
$data[$row['uniqueid']]['stato_esecuzione'] = "OK";
} elseif (!empty($row['met_customer'])) {
$data[$row['uniqueid']]['stato_esecuzione'] = "WIP";
}
if (!empty($row['did_tecnico'])) {
$data[$row['uniqueid']]['detaglio_esecuzione'] = $row['did_tecnico'];
} elseif (!empty($row['did_not_tecnico'])) {
$data[$row['uniqueid']]['detaglio_esecuzione'] = $row['did_not_tecnico'];
} elseif (!empty($row['dett_causa_interv_no'])) {
$data[$row['uniqueid']]['detaglio_esecuzione'] = $row['dett_causa_interv_no'];
}
//Preventivo non accetato ose No dhe pagato uscita si
if (($row['fatto_preventivo'] == 'Non Accettato' || $row['fatto_preventivo'] == 'No') && $row['fatto_uscita'] == 'Si') {
$data[$row['uniqueid']]['detaglio_esecuzione'] = 'Preventivo Non Accetato Pagato Uscita';
}
$data[$row['uniqueid']]['valore_preventivo'] = $row['valore_preventivo'];
$data[$row['uniqueid']]['valore_caparra_anticipo'] = $row['valore_caparra_anticipo'];
$data[$row['uniqueid']]['valore_uscita'] = $row['valore_uscita'];
$data[$row['uniqueid']]['verifica_esito_esecuzione'] = $row['verifica_esito_esecuzione'];
$data[$row['uniqueid']]['dettagli_verifica_esito_escuzone'] = $row['dettagli_verifica_esito_escuzone'];
}
/**
* Marrim te dhenat e pageses nese eshte paguar si intervento
*/
$all_uniqueids_string = "'" . implode("','", $all_uniqueids) . "'";
$query_paymnet_data = "select
last4,
ip_address,
source_brand,
country,
uniqueid
from $dati_pag_stripe_inter_urg
where uniqueid in (" . $all_uniqueids_string . ")
";
$result_payment_data = mysqli_query($db_conn, $query_paymnet_data);
if (!$result_payment_data) {
show_alert("danger", "Internal server error. " . __LINE__);
}
while ($row = mysqli_fetch_assoc($result_payment_data)) {
$data[$row['uniqueid']]['last_card_numbers'] = $row['last4'] . " <b>" . $row['source_brand'] . "<b>";
        $data[$row['uniqueid']]['payment_ip_address'] = "<b>Country</b>: " . $row['country'] . "<br><b>IP:</b> " . $row['ip_address'];
        }

        $i = 0;
        foreach ($data as $key => $row) {
        //incassati
        $sum_incasato_check = number_format(($row['somma_incas1'] + $row['somma_incas2'] + $row['somma_incas3']), 2, ".", "");
        $row['comisione'] = number_format($row['comisione'], 2, ".", "");
        if ($row['comisione'] <= $sum_incasato_check) {
        $style_pagati = 'style = "background-color: #00d8ac;"';
        } else {
        $style_pagati = '';
        }
        //adding confirmation if is it clicked
        //it will change the status in 'Si'
        $link = 'scheda_elenco_intervento.php?uniqueid=' . $key;
        if (check_permissions('caller_code_elenco_interventi_email') || check_permissions('programmatore')) {
        if ($row['caller_code_email_status'] == "Si") {
        $button = '<button class="btn btn-primary btn-sm" onclick = "sent_caller_code_email(`' . $row['id'] . '`)" >
            <i class="fa fa-envelope-o" ></i >
        </button >';
        } else {
        $button = '<button class="btn btn-warning btn-sm" onclick = "sent_caller_code_email(`' . $row['id'] . '`)" >
            <i class="fa fa-envelope-o" ></i >
        </button >';
        }
        }
        if ($_SESSION['username'] == 'tm54399') {
        $caller_code_row = $row['caller_code'];
        } else {
        $caller_code_row = caller_code_mask($row['caller_code']);
        }
        $table_data[] = array(
        "id" => "<center>
            <a href= " . $link . "
               target='_blank' class='btn btn-primary btn-sm' onclick='save_logs(`" . $row['uniqueid'] . "`)'>
            <i class='fa fa-edit'></i>
            </a>" . $button .
            "</center>",
        "caller_code" => "<center>" . $caller_code_row . "</center>",
        "ora" => "<center>" . $row['ora'] . "</center>",
        "data" => "<center>" . $row['data'] . "</center>",
        "ora_usa" => "<center>" . $row['ora_usa'] . "</center>",
        "data_usa" => "<center>" . $row['data_usa'] . "</center>",
        "citta" => "<center>" . $row['citta'] . "</center>",
        "locality" => "<center>" . $row['locality'] . "</center>",
        "search_call" => "<center>" . $row['search_call'] . "</center>",
        "chiamate_organica" => "<center>" . translate($row['chiamate_organica']) . "</center>",
        "manager_tecnico_full_name" => "<center>" . $row['manager_tecnico_full_name'] . "</center>",
        "responsabile_number_code" => "<center>" . $row['responsabile_number_code'] . "</center>",
        "tipo_attivita" => "<center>" . $row['tipo_attivita'] . "</center>",
        "number_dialed" => "<center>" . $row['number_dialed'] . "</center>",
        "tipo_rich" => "<center>" . translate($row['tipo_rich']) . "</center>",
        "detaglio_tipo_rich" => "<center>" . translate($row['detaglio_tipo_rich']) . "</center>",
        "tipo_segnazione" => "<center>" . translate($row['tipo_segnazione']) . "</center>",
        "date_time_appuntamento" => "<center>" . $row['date_time_appuntamento'] . "</center>",
        "esito_assegnazione_tecnico" => "<center>" . translate($row['esito_assegnazione_tecnico']) . "</center>",
        "nome_tecnico" => "<center>" . $row['nome_tecnico'] . "</center>",
        "possible_tech_name" => "<center>" . translate($row['possible_tech_name']) . "</center>",
        "dettaglio_non_assegnazione" => "<center>" . $row['dettaglio_non_assegnazione'] . "</center>",
        "appointment_posticipato" => "<center>" . $row['appointment_posticipato'] . "</center>",
        "note_inter_no" => "<center><textarea>" . $row['note_inter_no'] . "</textarea></center>",
        "stato_esecuzione" => "<center>" . $row['stato_esecuzione'] . "</center>",
        "detaglio_esecuzione" => "<center>" . translate($row['detaglio_esecuzione']) . "</center>",
        "valore_preventivo" => "<center>" . $row['valore_preventivo'] . "</center>",
        "valore_caparra_anticipo" => "<center>" . $row['valore_caparra_anticipo'] . "</center>",
        "valore_uscita" => "<center>" . $row['valore_uscita'] . "</center>",
        "verifica_esito_esecuzione" => "<center>" . $row['verifica_esito_esecuzione'] . "</center>",
        "dettagli_verifica_esito_escuzone" => "<center><textarea>" . $row['dettagli_verifica_esito_escuzone'] . "</textarea></center>",
        "intervento" => "<center>" . translate($row['intervento']) . "</center>",
        "new_appointment_date" => "<center>" . $row['new_appointment_date'] . "</center>",
        "valore_iva_inc" => "<center>" . zeroToEmpty($row['valore_iva_inc']) . "</center>",
        "valore_pezzi_ricc" => "<center>" . zeroToEmpty($row['valore_pezzi_ricc']) . "</center>",
        "comisione" => "<center " . $style_pagati . " >" . zeroToEmpty($row['comisione']) . "</center>",
        "somma_incas1" => "<center>" . number_format(zeroToEmpty($row['somma_incas1']), 2, ",", ".") . "</center>",
        "somma_incas2" => "<center>" . number_format(zeroToEmpty($row['somma_incas2']), 2, ",", ".") . "</center>",
        "somma_incas3" => "<center>" . number_format(zeroToEmpty($row['somma_incas3']), 2, ",", ".") . "</center>",
        "last_card_numbers" => "<td><center>" . zeroToEmpty($row['last_card_numbers']) . "</center></td>",
        "payment_ip_address" => "<td><nobr>" . $row['payment_ip_address'] . "</nobr></td>",
        "note_esecuzione_intervento" => "<center><textarea>" . $row['note_esecuzione_intervento'] . "</textarea></center>",
        "stato_qcc" => "<center>" . $row['stato_qcc'] . "</center>",
        "note_qcc" => "<center><textarea>" . $row['note_qcc'] . "</textarea></center>",
        );

        //shtojme kushtin ne varesi te shtetit
        if ($state == 'UK' || $state == 'Ireland') {
        $table_data[$i]['local_data'] = "<center><nobr>" . $row['local_data'] . "<nobr></center>";
        }
        $i++;
        }
        ## Response
        $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $table_data,
        "query" => $query_chiamate
        );
        echo json_encode($response);
        }