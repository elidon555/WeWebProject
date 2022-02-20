<?php include('../_partials/header.php');
if ($_SESSION['role'] != "Admin") {
    header('location:' . SITEURL . '_config/errors/error403.html');
}
?>


<body id="body" style="font-family: Arial">

<div style="width:100% ;margin:auto">


    <br>

    <div class="container">
        <h2 style="text-align:center">Internship Project</h2>
        <div class="card">
            <div style="font-size:large" class="card-header">User information.<a style="float:right">

                </a></div>
            <div class="card-body">
                <button id="applyFilter" class="btn btn-dark float-right">Apply Filter</button>
                <div class="form-inline ">

                    <label>Date
                        <input class="form-control daterange" type="text" placeholder=""
                               value="">
                    </label>

                </div>
                <br> <br>
                <table id="checkins_list" class="display" style="width:100%">
                    <thead>
                    <tr>
                        <th>Show</th>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Normal hours</th>
                        <th>Overtime</th>
                        <th>Total hours</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Show</th>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Normal hours</th>
                        <th>Overtime</th>
                        <th>Total hours</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>



</body>

<?php include_once('../_partials/footer.php') ?>

<!--<script src="plugins/jquery.js"></script>-->
<script src="scripts.js?v=<?= filemtime('scripts.js') ?>"></script>





