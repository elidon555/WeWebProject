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
        <br><br>
        <div class="card">
            <div class="card-body ">
                <div class='shadow-sm p-3 m-2 bg-white rounded'>

                    <button id="applyFilter" class="btn btn-dark float-right">Apply Filter</button>
                    <div class="form-inline ">

                        <label>Date
                            <input class="form-control daterange" type="text" placeholder=""
                                   value="">
                        </label>

                    </div>
                </div>

                <br><br>
                <table id="checkins_list" class="table_class1 display" style="width:100%">
                    <thead>
                    <tr>
                        <th>Show</th>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Normal hours</th>
                        <th>Normal Salary</th>
                        <th>Overtime hours</th>
                        <th>Overtime Salary</th>
                        <th>Total hours</th>
                        <th>Pay/Hour</th>
                        <th>Salary/total</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Show</th>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Normal hours</th>
                        <th>Normal Salary</th>
                        <th>Overtime</th>
                        <th>Overtime Salary</th>
                        <th>Total hours</th>
                        <th>Pay/Hour</th>
                        <th>Salary/total</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hour_details" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Normal hours details</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add checkin!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form style="width:400px;margin:auto" id="addUser" name="addUser" action="" method="POST">

                    <div class="form-group ">
                        <label for="email">Email</label>
                        <input type="text" class="form-control loginForm" name="first_name"
                               placeholder="Enter Email" id="email">
                        <small class="error_form" id="First_name_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="daterange">Check in - Check out Date</label>
                        <input type="text" name="Date" class="form-control loginForm" placeholder="Date"
                               id="daterange">
                        <small class="error_form" id="Date_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="checkin">Check in</label>
                        <input type="time" name="timestamp" step="1" class="form-control loginForm"
                               placeholder="Phone number" id="checkin" value="">
                        <small class="error_form" id="Phone_number_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="checkout">Check out</label>
                        <input type="time" name="timestamp" step="1" class="form-control loginForm"
                               placeholder="Phone number" id="checkout" value="">
                        <small class="error_form" id="Phone_number_error_message"></small>
                    </div>

                    <div class="text-center">
                        <button style="display:none" type="submit" name="submit"
                                class="btn btn-primary profile-button alignButtonCenter pl-5 pr-5" id="addCheckin">
                            Save profile
                        </button>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" onclick="document.getElementById('addCheckin').click()"
                        class="btn btn-success">Save changes
                </button>
            </div>
        </div>
    </div>
</div>

</body>

<?php include_once('../_partials/footer.php') ?>

<!--<script src="plugins/jquery.js"></script>-->
<script src="scripts.js?v=<?= filemtime('scripts.js') ?>"></script>





