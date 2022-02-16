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
    table {
        width: 750px;
        border-collapse: collapse;
        margin:50px auto;
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
        text-align: left;
        font-size: 18px;
    }

    /*
    Max width before this PARTICULAR table gets nasty
    This query will take effect for any screen smaller than 760px
    and also iPads specifically.
    */
    @media
    only screen and (max-width: 760px),
    (min-device-width: 768px) and (max-device-width: 1024px)  {

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

        tr { border: 1px solid #ccc; }

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
    .user {
        display: none
    }

    @keyframes chartjs-render-animation {
        from {
            opacity: .99
        }
        to {
            opacity: 1
        }
    }

    .chartjs-render-monitor {
        animation: chartjs-render-animation 1ms
    }

    .chartjs-size-monitor, .chartjs-size-monitor-expand, .chartjs-size-monitor-shrink {
        position: absolute;
        direction: ltr;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        overflow: hidden;
        pointer-events: none;
        visibility: hidden;
        z-index: -1
    }

    .chartjs-size-monitor-expand > div {
        position: absolute;
        width: 1000000px;
        height: 1000000px;
        left: 0;
        top: 0
    }

    .chartjs-size-monitor-shrink > div {
        position: absolute;
        width: 200%;
        height: 200%;
        left: 0;
        top: 0
    }

    .user {
        display: none
    }
</style>
<?php
include('../_partials/header.php');

$query_data = "    
                  SELECT purchase.id as purchase_id,purchase.date_of_purchase,product_id,user_id,first_name,last_name,price,quantity,manufacturer,category,expire,name from purchase
INNER JOIN product on purchase.product_id = product.id
INNER JOIN (SELECT user_id,first_name,last_name from users ORDER BY user_id ) as m on m.user_id=purchase.buyer_id 
WHERE 1=1 ORDER BY user_id ASC

                     ";

$result_data = mysqli_query($conn, $query_data);

if (!$result_data) {
    echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
    exit;
}
//array ku do ruajme te dhenat qe ja cojme frontend-it
$data = array();

while ($row = mysqli_fetch_assoc($result_data)) {

    /**
     * User_data
     */
    $data['user_data'][$row['user_id']]['dates'][] = array(
        'product_name' => $row['name'],
        'date_of_purchase' => $row['date_of_purchase'],
        'price' => $row['price'],
        'category' => $row['category'],
        'expire_date' => $row['expire'],
        'manufacturer' => $row['manufacturer'],
        'units_sold' => $row['quantity'],
        'total_sales' => $row['quantity'] * $row['price']);

    $data['user_data'][$row['user_id']]['date'] = $row['date_of_purchase'];
    $data['user_data'][$row['user_id']]['date_of_purchase'] = $row['date_of_purchase'];
    $data['user_data'][$row['user_id']]['name'] = $row['first_name'] . " " . $row['last_name'];
    $data['user_data'][$row['user_id']]['quantity'] += $row['quantity'];
    $data['user_data'][$row['user_id']]['total_spent'] += $row['quantity'] * $row['price'];
    /**
     * Product data
     */
    $data['product_data'][$row['product_id']]['product_name'] = $row['name'];
    $data['product_data'][$row['product_id']]['price'] = $row['price'];
    $data['product_data'][$row['product_id']]['category'] = $row['category'];
    $data['product_data'][$row['product_id']]['expire_date'] = $row['expire'];
    $data['product_data'][$row['product_id']]['manufacturer'] = $row['manufacturer'];
    $data['product_data'][$row['product_id']]['units_sold'] += $row['quantity'];
    $data['product_data'][$row['product_id']]['total_sales'] += $row['quantity'] * $row['price'];

    $data['product_data'][$row['product_id']]['users'][] = array(
        'name' => $row['first_name'] . " " . $row['last_name'],
        'date_of_purchase' => $row['date_of_purchase'],
        'quantity' => $row['quantity'],
        'total_spent' => $row['quantity'] * $row['price']);
    $total_units_sold += $row['quantity'];
    $total_sales += $row['quantity'] * $row['price'];
}

?>
<body id="body" style="font-family: Arial">

<div style='width:1200px !important;margin: auto' class='table-responsive'>
    <table class="table table-striped main_table">
        <thead>

        <tr>
            <th></th>
            <th scope='col'>Nr</th>
            <th scope='col' colspan='2'>Full Name</th>
            <th scope='col'>Date Purchased</th>
            <th colspan='2' scope='col'>Quantity Bought</th>
            <th colspan='2' scope='col'>Total Spent</th>
        </tr>
        </thead>
        <?php
        $i = 1; //here
        foreach ($data['user_data'] as $user_data) {
            $arrayUserName[] = $user_data['name'];
            $arrayUnitsBought[] = $user_data['quantity'];
            $arrayTotalSpent[] = $user_data['total_spent'];
            ?>
            <tr class="product"  !important;">
                <td><i index='user'
                       main='product'
                       class='fas fa-plus-circle fa-lg text-dark '
                       style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                <td><?= $i++ ?></td>
                <td colspan='2'><?= $user_data['name'] ?></td>
                <td><?= $user_data['date_of_purchase'] ?> </td>
                <td colspan='2'><?= $user_data['quantity'] ?> </td>
                <td colspan='2'><?= $user_data['total_spent'] ?> </td>
            </tr>

            <tbody class="user" style='border-color: black;margin-top: 50px'>
            <tr>

                <th scope='col'>
                    Dt
                </th>
                <th scope='col'>
                    Date
                </th>
                <th scope='col'>
                    Product Name
                </th>
                <th scope='col'>
                    Price
                </th>
                <th scope='col'>
                    Category
                </th>
                <th scope='col'>
                    Manufacturer
                </th>
                <th scope='col'>
                    Expire Date
                </th>
                <th scope='col'>
                    Units sold
                </th>
                <th scope='col'>
                    Total sales
                </th>
            </tr>

            <?php
            $j = 1;
            $index = 0;

            foreach ($user_data['dates'] as $date_data) {

                ?>
                <tr style="color: blue !important;">

                    <td><i index='user'
                                                                                                         style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                    <td><?= $date_data['date_of_purchase'] ?></td>
                    <td><?= $date_data['product_name'] ?></td>
                    <td><?= $date_data['price'] ?></td>
                    <td><?= $date_data['category'] ?></td>
                    <td><?= $date_data['manufacturer'] ?></td>
                    <td><?= $date_data['expire_date'] ?></td>
                    <td><?= $date_data['units_sold'] ?></td>
                    <td><?= $date_data['total_sales'] ?></td>
                </tr>

                <?php
                $index++;
            }
            ?>
            <tbody
            <?php
        }
        ?>
        <row>
            <td colspan='7'></td>
            <td colspan='1'
                class='text-right'><?= $total_units_sold ?> units sold
            </td>
            <td colspan='2'><span
                    class='float-right'>$ <?= $total_sales ?></span></td>
        </row>
    </table>

    <br><br>

    <div style="width:75%;">
        <div class="chartjs-size-monitor">
            <div class="chartjs-size-monitor-expand">
                <div class=""></div>
            </div>
            <div class="chartjs-size-monitor-shrink">
                <div class=""></div>
            </div>
        </div>
        <canvas id="user_data_quantity" style="display: block; width: 1379px; height: 689px;" width="1379" height="689"
                class="chartjs-render-monitor"></canvas>
    </div>

    <br><br>

    <div style="width:75%;">
        <div class="chartjs-size-monitor">
            <div class="chartjs-size-monitor-expand">
                <div class=""></div>
            </div>
            <div class="chartjs-size-monitor-shrink">
                <div class=""></div>
            </div>
        </div>
        <canvas id="user_data_sales" style="display: block; width: 1379px; height: 689px;" width="1379" height="689"
                class="chartjs-render-monitor"></canvas>
    </div>
</div>
<br><br><br>

<div style='width:1200px !important;margin: auto' class='table-responsive'>
    <table class="table table-striped main_table">
        <thead>

        <tr>
            <th scope='col'>
                Dt
            </th>
            <th scope='col'>
                Nr
            </th>
            <th scope='col'>
                Product Name
            </th>
            <th scope='col'>
                Price
            </th>
            <th scope='col'>
                Category
            </th>
            <th scope='col'>
                Manufacturer
            </th>
            <th scope='col'>
                Expire Date
            </th>
            <th scope='col'>
                Units sold
            </th>
            <th scope='col'>
                Total sales
            </th>
        </tr>
        </thead>
        <?php
        $i = 1;

        foreach ($data['product_data'] as $product) {

            $arrayProductName[] = $product['product_name'];
            $arrayUnitsSold[] = $product['units_sold'];
            $arrayTotalSales[] = $product['total_sales'];
            ?>

            <tr class="product" >
                <td><i index='user'
                                                                                                     main='product'
                                                                                                     class='fas fa-plus-circle fa-lg text-dark '
                                                                                                     style='font-size:25px;cursor: pointer;user-select: none;'
                                                                                                     aria-hidden='true'></i>
                </td>
                <td><?= $i++ ?></td>
                <td><?= $product['product_name'] ?></td>
                <td><?= $product['price'] ?></td>
                <td><?= $product['category'] ?></td>
                <td><?= $product['manufacturer'] ?></td>
                <td><?= $product['expire_date'] ?></td>
                <td><?= $product['units_sold'] ?></td>
                <td><?= $product['total_sales'] ?></td>
            </tr>

            <tbody class="user" style='border-color: black;margin-top: 50px'>
            <tr class='m-5'>

                <th></th>
                <th scope='col'>Nr</th>
                <th scope='col' colspan='2'>Full Name</th>
                <th scope='col'>Date Purchased</th>
                <th colspan='2' scope='col'>Quantity Bought</th>
                <th colspan='2' scope='col'>Total Spent</th>
            </tr>

            <?php
            $j = 1;
            $index = 0;

            foreach ($product['users'] as $user_data) {
                ?>
                <tr style="color: blue !important;">

                    <td></td>
                    <td><?= $j++ ?></td>
                    <td colspan='2'><?= $user_data['name'] ?></td>
                    <td><?= $user_data['date_of_purchase'] ?> </td>
                    <td colspan='2'><?= $user_data['quantity'] ?> </td>
                    <td colspan='2'><?= $user_data['total_spent'] ?> </td>
                </tr>

                <?php
                $index++;
            }
            ?>
            <tbody
            <?php
        }
        ?>
        <row>
            <td colspan='7'></td>
            <td colspan='1'
               ><?= $total_units_sold ?> units
            </td>
            <td colspan='2'><span
                    class='float-right text-right'>$ <?= $total_sales ?></span></td>
        </row>
    </table>

    <br><br>

    <div style="width:75%;">
        <div class="chartjs-size-monitor">
            <div class="chartjs-size-monitor-expand">
                <div class=""></div>
            </div>
            <div class="chartjs-size-monitor-shrink">
                <div class=""></div>
            </div>
        </div>
        <canvas id="product_data_quantity" style="display: block; width: 1379px; height: 689px;" width="1379"
                height="689" class="chartjs-render-monitor"></canvas>
    </div>

    <br><br>

    <div style="width:75%;">
        <div class="chartjs-size-monitor">
            <div class="chartjs-size-monitor-expand">
                <div class=""></div>
            </div>
            <div class="chartjs-size-monitor-shrink">
                <div class=""></div>
            </div>
        </div>
        <canvas id="product_data_sales" style="display: block; width: 1379px; height: 689px;" width="1379" height="689"
                class="chartjs-render-monitor"></canvas>
    </div>

</body>

<?php include('../_partials/footer.php'); ?>

<script type='text/javascript'>

   $('table.main_table').on('click', 'i.fas', function() {
      $(this).toggleClass('fa-plus-circle fa-minus-circle');
      let attribute = $(this).attr('index');
      let main = $(this).attr('main');

      let element = $(this).parent().parent().parent().nextUntil(`tbody.${main}`).filter(`tbody.${attribute}`);

      if (element.is(':visible')) {
         element.fadeOut();
      } else {
         element.fadeIn();
      }

   });

</script>

<script type='text/javascript'>
   const ctx1 = document.getElementById('user_data_quantity').getContext('2d');
   const user_data_quantity = new Chart(ctx1, {
      type: 'bar',
      data: {
         labels: <?php echo json_encode($arrayUserName);?>,
         datasets: [{ //here
            label: 'Quantity',
            data: <?php echo json_encode($arrayUnitsBought);?>,
            backgroundColor: [
               'rgba(255, 99, 132, 0.2)',
               'rgba(54, 162, 235, 0.2)',
               'rgba(255, 206, 86, 0.2)',
               'rgba(75, 192, 192, 0.2)',
               'rgba(153, 102, 255, 0.2)',
               'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
               'rgba(255, 99, 132, 1)',
               'rgba(54, 162, 235, 1)',
               'rgba(255, 206, 86, 1)',
               'rgba(75, 192, 192, 1)',
               'rgba(153, 102, 255, 1)',
               'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
         }]
      },
      options: {
         scales: {
            y: {
               beginAtZero: true
            }
         }
      }
   });


   const ctx2 = document.getElementById('user_data_sales').getContext('2d');
   const user_data_sales = new Chart(ctx2, {
      type: 'bar',
      data: {
         labels: <?php echo json_encode($arrayUserName);?>,
         datasets: [{
            label: 'Sales',
            data: <?php echo json_encode($arrayTotalSpent);?>,
            backgroundColor: [
               'rgba(255, 99, 132, 0.2)',
               'rgba(54, 162, 235, 0.2)',
               'rgba(255, 206, 86, 0.2)',
               'rgba(75, 192, 192, 0.2)',
               'rgba(153, 102, 255, 0.2)',
               'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
               'rgba(255, 99, 132, 1)',
               'rgba(54, 162, 235, 1)',
               'rgba(255, 206, 86, 1)',
               'rgba(75, 192, 192, 1)',
               'rgba(153, 102, 255, 1)',
               'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
         }]
      },
      options: {
         scales: {
            y: {
               beginAtZero: true
            }
         }
      }
   });

   const ctx3 = document.getElementById('product_data_quantity').getContext('2d');
   const product_data_quantity = new Chart(ctx3, {
      type: 'bar',
      data: {
         labels: <?php echo json_encode($arrayProductName);?>,
         datasets: [{
            label: 'Quantity',
            data: <?php echo json_encode($arrayUnitsSold);?>,
            backgroundColor: [
               'rgba(255, 99, 132, 0.2)',
               'rgba(54, 162, 235, 0.2)',
               'rgba(255, 206, 86, 0.2)',
               'rgba(75, 192, 192, 0.2)',
               'rgba(153, 102, 255, 0.2)',
               'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
               'rgba(255, 99, 132, 1)',
               'rgba(54, 162, 235, 1)',
               'rgba(255, 206, 86, 1)',
               'rgba(75, 192, 192, 1)',
               'rgba(153, 102, 255, 1)',
               'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
         }]
      },
      options: {
         scales: {
            y: {
               beginAtZero: true
            }
         }
      }
   });


   const ctx4 = document.getElementById('product_data_sales').getContext('2d');
   const product_data_sales = new Chart(ctx4, {
      type: 'bar',
      data: {
         labels: <?php echo json_encode($arrayProductName);?>,
         datasets: [{
            label: 'Sales',
            data: <?php echo json_encode($arrayTotalSales);?>,
            backgroundColor: [
               'rgba(255, 99, 132, 0.2)',
               'rgba(54, 162, 235, 0.2)',
               'rgba(255, 206, 86, 0.2)',
               'rgba(75, 192, 192, 0.2)',
               'rgba(153, 102, 255, 0.2)',
               'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
               'rgba(255, 99, 132, 1)',
               'rgba(54, 162, 235, 1)',
               'rgba(255, 206, 86, 1)',
               'rgba(75, 192, 192, 1)',
               'rgba(153, 102, 255, 1)',
               'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
         }]
      },
      options: {
         scales: {
            y: {
               beginAtZero: true
            }
         }
      }
   });
</script>