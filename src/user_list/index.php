<?php
include('../_partials/header.php');


/**
 * Nese nuk je Admin te con te profili pasi ske te drejte te shohesh listen e userave
 */
if ($_SESSION['role'] == Config::USER) {
    $_SESSION['no-access'] = "<br><div class='error text-center alert alert-danger' style='width:400px;margin:auto'>You don't have permission to access this page!</div>";

    header('Location: http://localhost/WeWebProject/profile/index.php');
    die();
}

?>

<body>

<div id="moveHere"></div>
<button id="loadAfterAction" type="button" style="display:none"></button>


<br />
<div class="container">
    <h2 style=" text-align: center;">Internship Project</h2>
    <br />
    <button class="btn btn-success float-right" data-toggle="modal" data-target="#signupModal">Add User</button>
    <br>


    <div class="row shadow p-3 m-3 bg-white rounded">
        <div class="col-sm-6 col-md-6 col-xs-12 col-lg-4">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-default">Email</span>
                </div>
                <input id="emailFilter" type="text" class=""
                       placeholder="" aria-controls="user_list" aria-describedby="inputGroup-sizing-default">
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-xs-12 col-lg-4">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-default">Phone</span>
                </div>
                <input id="phoneFilter" type="number" class="" placeholder=""
                       aria-controls="user_list" aria-describedby="inputGroup-sizing-default">
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-xs-12 col-lg-4">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroup-sizing-default">Date</span>
                </div>
                <input id="dateFilter" type="text" class="form-control" placeholder="" aria-controls="user_list"
                       aria-describedby="inputGroup-sizing-default">
            </div>
        </div>
    </div>

    <button id="applyFilter" class="btn btn-dark">Apply Filter</button>
    <p></p>

    <table id="user_list" class="display" style="width:100%">
        <thead>
        <tr>
            <th>User ID</th>
            <th>Image</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Atesia</th>
            <th>Start date</th>
            <th>Phone Number</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>User ID</th>
            <th>Image</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Atesia</th>
            <th>Start date</th>
            <th>Phone Number</th>
            <th>Actions</th>
        </tr>
        </tfoot>
    </table>


    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit user information.</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">


                    <div class="form-row">

                        <input style="display:none" type="text" id="id_user" value="">


                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">First Name</label>
                            <input type="text" class="edit form-control loginForm" name="first_name"
                                   placeholder="first Name"
                                   id="first_name">
                            <small class="error_form" id="first_name_error_message"></small>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">Last Name</label>
                            <input type="text" class="edit form-control loginForm" name="last_name"
                                   placeholder="Last Name"
                                   id="last_name">
                            <small class="error_form" id="last_name_error_message"></small>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">Atesia</label>
                            <input type="text" class="edit form-control loginForm" name="atesia"
                                   placeholder="Atesia"
                                   id="atesia">
                            <small class="error_form" id="atesia_error_message"></small>
                        </div>

                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Date of birth</label>
                        <input type="text" name="date" class="edit form-control loginForm" placeholder="Date"
                               id="date">
                        <small class="error_form" id="date_error_message"></small>
                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="text" name="email" class="edit form-control loginForm" placeholder="Email"
                               id="email">
                        <small class="error_form" id="email_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Phone</label>
                        <input type="text" name="phone_number" class="edit form-control loginForm"
                               placeholder="Phone number"
                               id="phone_number" value="">
                        <small class="error_form" id="phone_number_error_message"></small>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" class="edit form-control loginForm"
                               placeholder="Password - Leave empty if necessary" id="password">
                        <small class="error_form" id="password_error_message"></small>
                    </div>

                    <div class="form-group">
                        <input type="password" name="confirm_password" class="edit form-control loginForm"
                               placeholder="Confirm Password"
                               id="confirm_password">
                        <small class="error_form" id="confirm_password_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Role</label><br>

                        <div class="form-check">
                            <input class="edit form-check-input" type="radio" name="flexRadioDefault"
                                   id="roleUser"
                                   checked>
                            <label class="form-check-label" for="flexRadioDefault1">
                                User
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="roleAdmin">
                            <label class="form-check-label" for="flexRadioDefault2">
                                Admin
                            </label>
                        </div>

                    </div>

                    <div class="form-group">
                        <input id="file" type="file" name="image" accept=".png,.jpg"
                               onchange="document.getElementById('blah2').src = window.URL.createObjectURL(this.files[0]);$('#blah2').show()">
                        <button type="button" class="btn-secondary"
                                onclick="document.getElementById('file').value= null;$('#blah2').hide()">Clear
                            input
                        </button>
                    </div>

                    <div class="form-group ">
                        <img class="border border-secondary" id="blah2" alt="your image" width="200"
                             style="display:none;aspect-ratio: inherit;border:2px" />
                    </div>


                    <div class="form-group text-center">
                        <img id="edit_form_image" src="" width="400px">
                    </div>
                    <a class="btn btn-outline-info ml-3" id="dw" class="button" href="" download="image.png">Download
                        image</a>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" onclick="create_edit_user()"
                            class="btn btn-success">Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add user!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">First Name</label>
                            <input type="text" class="add form-control loginForm" name="first_name"
                                   placeholder="First Name"
                                   id="First_name">
                            <small class="error_form" id="First_name_error_message"></small>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">Last Name</label>
                            <input type="text" class="add form-control loginForm" name="last_name"
                                   placeholder="Last Name"
                                   id="Last_name">
                            <small class="error_form" id="Last_name_error_message"></small>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">Atesia</label>
                            <input type="text" class="add form-control loginForm" name="atesia" placeholder="Atesia"
                                   id="Atesia">
                            <small class="error_form" id="Atesia_error_message"></small>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Date of birth</label>
                        <input type="text" name="date" class="add form-control loginForm" placeholder="Date"
                               id="Date">
                        <small class="error_form" id="Date_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="text" name="email" class="add form-control loginForm" placeholder="Email"
                               id="Email">
                        <small class="error_form" id="Email_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Phone</label>
                        <input type="text" name="phone_number" class="add form-control loginForm"
                               placeholder="Phone number"
                               id="Phone_number" value="">
                        <small class="error_form" id="Phone_number_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Password</label>
                        <input type="password" name="password" class="add form-control loginForm"
                               placeholder="Password"
                               id="Password">
                        <small class="error_form" id="Password_error_message"></small>

                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Confirm Password</label>
                        <input type="password" name="confirm_password" class="add form-control loginForm"
                               placeholder="Confirm Password" id="Confirm_password">
                        <small class="error_form" id="Confirm_password_error_message"></small>

                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Role</label><br>

                        <div class="form-check">
                            <input class="add form-check-input" type="radio" name="flexRadioDefault" id="roleUser"
                                   checked>
                            <label class="form-check-label" for="flexRadioDefault1">
                                User
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="add form-check-input" type="radio" name="flexRadioDefault" id="roleAdmin">
                            <label class="form-check-label" for="flexRadioDefault2">
                                Admin
                            </label>
                        </div>


                    </div>


                    <div class="form-group">
                        <input id="File" type="file" name="image" accept=".png,.jpg"
                               onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0]);$('#blah').show()">
                        <button type="button" class="btn-secondary"
                                onclick="document.getElementById('File').value= null;$('#blah').hide()">Clear input
                        </button>

                        <input type="hidden" id="image_name" name="image_name">

                    </div>

                    <div class="form-group">
                        <img class="border border-secondary" id="blah" alt="your image" width="200"
                             style="display:none;aspect-ratio: inherit;border:2px" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" onclick="create_edit_user()"
                            class="btn btn-success">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>


</body>


<?php
include "../_partials/footer.php"
?>
<script src="scripts.js?v=<?= time(); ?>"></script>
