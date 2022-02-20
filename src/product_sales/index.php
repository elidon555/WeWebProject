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
        margin: 50px auto;
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
    @media only screen and (max-width: 760px),
    (min-device-width: 768px) and (max-device-width: 1024px) {

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


    .table-users .dates {
        display: none;
    }

    .table-users .product {
        display: none
    }



    .table-products .dates {
        display: none;
    }

    .table-products .users {
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

</style>
<?php
include('../_partials/header.php');

$query_data = "SELECT purchase.id as purchase_id,
                       purchase.date_of_purchase,
                       product_id,
                       user_id,
                       first_name,
                       last_name,
                       price,
                       quantity,
                       manufacturer,
                       category,
                       expire,
                       name
                from purchase
                         INNER JOIN  users on users.user_id = purchase.buyer_id
                         INNER JOIN product on purchase.product_id = product.id
                WHERE 1 = 1
                ORDER BY user_id ASC";

$result_data = mysqli_query($conn, $query_data);

if (!$result_data) {
    echo json_encode(array("status" => 404, "message" => "Internal Server Error " . __LINE__));
    exit;
}
//array ku do ruajme te dhenat qe ja cojme frontend-it
$data = array();
$arrayProductName=array();
$arrayUnitsSold=array();
$arrayTotalSales=array();
while ($row = mysqli_fetch_assoc($result_data)) {

    /**
     * User_data
     */

    //product info
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['product_name'] = $row['name'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['price'] = $row['price'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['category'] = $row['category'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['expire_date'] = $row['expire'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['manufacturer'] = $row['manufacturer'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['units_sold'] = $row['quantity'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['details'][$row['product_id']]['total_sales'] = $row['quantity'] * $row['price'];

    $count_product_day = sizeof($data['user_data'][$row['user_id']]['dates']);

    //count categories per date
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['categories'][$row['category']] = $row['category'];

    //day product data
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['date'] = $row['date_of_purchase'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['count'] = $count_product_day;

    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['units_sold'] += $row['quantity'];
    $data['user_data'][$row['user_id']]['dates'][$row['date_of_purchase']]['total_sales'] += $row['quantity'] * $row['price'];


    //user_data
    $count_categories[$row['user_id']]['categories'][$row['category']] = $row['category'];
    //category count per user
    $data['user_data'][$row['user_id']]['category'] = sizeof($count_categories[$row['user_id']]['categories']);

    //user general info
    $data['user_data'][$row['user_id']]['date_of_purchase'] = $row['date_of_purchase'];
    $data['user_data'][$row['user_id']]['name'] = $row['first_name'] . " " . $row['last_name'];
    $data['user_data'][$row['user_id']]['quantity'] += $row['quantity'];
    $data['user_data'][$row['user_id']]['total_spent'] += $row['quantity'] * $row['price'];


////////////////////////////////////////////////////////////////////////////////////////////////////

    //user info
    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['details'][$row['user_id']]['name'] = $row['first_name'] . " " . $row['last_name'];
    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['details'][$row['user_id']]['quantity'] += $row['quantity'];
    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['details'][$row['user_id']]['total_spent'] += $row['quantity'] * $row['price'];

    //product info
    $data['product_data'][$row['product_id']]['product_name'] = $row['name'];
    $data['product_data'][$row['product_id']]['price'] = $row['price'];
    $data['product_data'][$row['product_id']]['category']= $row['category'];
    $data['product_data'][$row['product_id']]['expire_date']= $row['expire'];
    $data['product_data'][$row['product_id']]['manufacturer'] = $row['manufacturer'];
    $data['product_data'][$row['product_id']]['quantity'] += $row['quantity'];
    $data['product_data'][$row['product_id']]['total_sales'] += $row['quantity'] * $row['price'];

    $count_product_day = sizeof($data['product_data'][$row['product_id']]['dates']);

    //day product data

    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['date'] = $row['date_of_purchase'];
    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['count'] = $count_product_day;

    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['units_sold'] += $row['quantity'];
    $data['product_data'][$row['product_id']]['dates'][$row['date_of_purchase']]['total_sales'] += $row['quantity'] * $row['price'];



    $data['product_data'][$row['product_id']]['date_of_purchase'] = $row['date_of_purchase'];
    $data['product_data'][$row['product_id']]['name'] = $row['first_name'] . " " . $row['last_name'];
    $data['product_data'][$row['product_id']]['quantity'] += $row['quantity'];
    $data['product_data'][$row['product_id']]['total_spent'] += $row['quantity'] * $row['price'];
    


    $total_units_sold += $row['quantity'];
    $total_sales += $row['quantity'] * $row['price'];
}

?>
<body id="body" style="font-family: Arial">

<div style='width:1200px !important;margin: auto' class='table-users table-responsive'>
    <table class="table table-striped main_table">
        <thead>

        <tr>
            <th></th>
            <th scope='col'>Nr</th>
            <th scope='col' colspan='2'>Full Name</th>
            <th scope='col'>Categories</th>
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
            <tr class="user">
                <td><i index='dates'
                       main='user'
                       class='fas fa-plus-circle fa-lg text-dark '
                       style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                <td><?= $i++ ?></td>
                <td colspan='2'><?= $user_data['name'] ?></td>
                <td><?= $user_data['category'] ?> </td>
                <td colspan='2'><?= $user_data['quantity'] ?> </td>
                <td colspan='2'><?= $user_data['total_spent'] ?> </td>
            </tr>


            <tr class='dates'>


                <th scope='col'>
                    Date
                </th>

                <th colspan='2' scope='col'>
                    Price
                </th>

                <th colspan='3' scope='col'>
                    Units sold
                </th>
                <th colspan='3' scope='col'>
                    Total sales
                </th>
            </tr>

            <?php
            $j = 1;
            $index = 0;

            foreach ($user_data['dates'] as $date_data) {

                ?>
                <tr class='dates' style="color: blue !important;">
                    <td><i index='product'
                           main='dates'
                           class='fas fa-plus-circle fa-lg text-dark '
                           style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                    <td colspan='2'><?= $date_data['date'] ?></td>
                    <td colspan='3'><?= $date_data['units_sold'] ?></td>
                    <td colspan='3'><?= $date_data['total_sales'] ?></td>

                </tr>

                <tr class='product'>
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

                <?php
                $j = 1;
                $index = 0;

                foreach ($date_data['details'] as $day_data) {

                    ?>
                    <tr class='product'>
                        <td>
                        </td>
                        <td>0</td>
                        <td><?= $day_data['product_name'] ?></td>
                        <td><?= $day_data['price'] ?></td>
                        <td><?= $day_data['category'] ?></td>
                        <td><?= $day_data['manufacturer'] ?></td>
                        <td><?= $day_data['expire_date'] ?></td>
                        <td><?= $day_data['units_sold'] ?></td>
                        <td><?= $day_data['total_sales'] ?></td>
                    </tr>

                    <?php
//                    $index++;
                }
                ?>

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

</div>
<br><br><br>

<div style='width:1200px !important;margin: auto' class='table-products table-responsive'>
    <table class="table table-striped main_table">
        <thead>

        <tr class='product'>
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
        $i = 1; //here

        foreach ($data['product_data'] as $product_data) {



            $arrayProductName[] = $product_data['product_name'];
            $arrayUnitsSold[] = $product_data['quantity'];
            $arrayTotalSales[] = $product_data['total_spent'];
            ?>
            <tr class='product'>
                <td><i index='dates'
                       main='product'
                       class='fas fa-plus-circle fa-lg text-dark '
                       style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i>
                </td>
                <td><?=$i++;?></td>
                <td><?= $product_data['product_name'] ?></td>
                <td><?= $product_data['price'] ?></td>
                <td><?= $product_data['category'] ?></td>
                <td><?= $product_data['manufacturer'] ?></td>
                <td><?= $product_data['expire_date'] ?></td>
                <td><?= $product_data['units_sold'] ?></td>
                <td><?= $product_data['total_sales'] ?></td>
            </tr>


            <tr class='dates'>


                <th scope='col'>
                    Date
                </th>

                <th colspan='2' scope='col'>
                    Price
                </th>

                <th colspan='3' scope='col'>
                    Units sold
                </th>
                <th colspan='3' scope='col'>
                    Total sales
                </th>
            </tr>

            <?php
            $j = 1;
            $index = 0;

            foreach ($product_data['dates'] as $date_data) {

                ?>
                <tr class='dates' style="color: blue !important;">
                    <td><i index='users'
                           main='dates'
                           class='fas fa-plus-circle fa-lg text-dark '
                           style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i></td>
                    <td colspan='2'><?= $date_data['date'] ?></td>
                    <td colspan='3'><?= $date_data['units_sold'] ?></td>
                    <td colspan='3'><?= $date_data['total_sales'] ?></td>

                </tr>

                <tr class='users'>

                    <th scope='col'>Nr</th>
                    <th scope='col' colspan='2'>Full Name</th>
                    <th colspan='3' scope='col'>Quantity Bought</th>
                    <th colspan='3' scope='col'>Total Spent</th>
                </tr>

                <?php
                $j = 1;
                $index = 0;

                foreach ($date_data['details'] as $user_data) {

                    ?>
                    <tr class='users'>
                        <td><?=$j++;?>
                        </td>
                        <td colspan='2'><?= $user_data['name'] ?></td>
                        <td colspan='3'><?= $user_data['quantity'] ?></td>
                        <td colspan='3'><?= $user_data['total_spent'] ?></td>
                    </tr>

                    <?php
//                    $index++;
                }
                ?>

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
        <canvas id="product_data_sales" style="display: block; width: 1379px; height: 689px;" width="1379" height="689"
                class="chartjs-render-monitor"></canvas>
    </div>
</div>
<br><br><br>
</body>

<?php include('../_partials/footer.php'); ?>

<script type='text/javascript'>

   $('table.main_table').on('click', 'i.fas', function() {
      $(this).toggleClass('fa-plus-circle fa-minus-circle');
      let attribute = $(this).attr('index');
      let main = $(this).attr('main');

      let element = $(this).closest('tr').nextUntil(`tr.${main}`).filter(`tr.${attribute}`);

      if (element.is(':visible')) {
         element.hide();
      } else {
         element.show();
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
         },{
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
         },{
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
</script>