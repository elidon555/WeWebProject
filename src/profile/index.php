<?php
include('../_partials/header.php');
include('ajax.php');

?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!-- here form validation -->

    <title>Login - System</title>
</head>

<body>


<?php
if (isset($_SESSION['no-access'])) {
    echo $_SESSION['no-access'];
    unset($_SESSION['no-access']);
}
?>


<style>
    .profile-pic {
        position: relative;
        display: inline-block;
    }


    #loadedImage:hover {
        filter: brightness(70%);
    }

    #loadedImage:hover + .edit {
        display: block;
        pointer-events: none;
    }


    .edit {
        color: white;
        font-size: 40px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, 0);
        -ms-transform: translate(-50%, 0);
        text-align: center;
        display: none;
    }

    .edit span {
        color: #000;
    }
</style>


<div class="container rounded bg-white mt-5 mb-5">
    <div class="row">
        <div class="col-md-3 border-right">
            <div class="d-flex flex-column align-items-center text-center p-3 py-5">

                <div class="profile-pic">

                    <input id="loadedImage" type="image" class="rounded-circle mt-5" width="150px" height="150px"
                           src="../_photos/<?php echo $current_image ?>"
                    >
                    <div class="edit ">
                        <span><i class="fas fa-user-edit"></i></span>
                    </div>
                </div>

                <span class="font-weight-bold"><?php echo $first_name . " " . $last_name ?></span>
                <span class="text-black-50"><?php echo $email ?></span>
                <span> </span>

            </div>

        </div>
        <div class="col-md-5 border-right">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-right">Profile Settings</h4>
                </div>
                <form style="width:400px;margin:auto" id="editProfile" name="editProfile" action="" method="POST">

                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">First Name</label>
                            <input type="text" class="form-control loginForm" name="first_name" placeholder="First Name"
                                   id="first_name" value="<?php echo $first_name ?>">
                            <small class="error_form" id="first_name_error_message"></small>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">Last Name</label>
                            <input type="text" class="form-control loginForm" name="last_name" placeholder="Last Name"
                                   id="last_name" value="<?php echo $last_name ?>">
                            <small class="error_form" id="last_name_error_message"></small>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">Atesia</label>
                            <input type="text" class="form-control loginForm" name="atesia" placeholder="Atesia"
                                   id="atesia" value="<?php echo $atesia ?>">
                            <small class="error_form" id="atesia_error_message"></small>
                        </div>

                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Date of birth</label>
                        <input type="text" name="date" class="form-control loginForm" placeholder="Date" id="date"
                               value="<?php echo $date ?>">
                        <small class="error_form" id="date_error_message"></small>
                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="text" name="email" class="form-control loginForm" placeholder="Email" id="email"
                               value="<?php echo $email ?>">
                        <small class="error_form" id="email_error_message"></small>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Phone</label>
                        <input type="text" name="phone_number" class="form-control loginForm" placeholder="Phone number"
                               id="phone_number" value="<?php echo $phone_number ?>">
                        <small class="error_form" id="phone_number_error_message"></small>
                    </div>

                    <div class="form-group">
                        <input id="file" type="file" name="image" style="display:none" accept=".png,.jpg"
                               onchange="document.getElementById('loadedImage').src = window.URL.createObjectURL(this.files[0])">
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit"
                                class="btn btn-primary profile-button alignButtonCenter pl-5 pr-5"
                                id="edit_profile_btn">Save profile
                        </button>

                    </div>

                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-right">Change password!</h4>
                </div>

                <br>


                <div class="col-md-12"><label class="labels">Old password</label><input type="password"
                                                                                        id="old_password"
                                                                                        class="form-control"
                                                                                        placeholder="Old password"
                                                                                        value="">
                </div>
                <br>
                <div class="col-md-12"><label class="labels">New password</label><input type="password" id="password"
                                                                                        class="pw form-control"
                                                                                        placeholder="New password"
                                                                                        value="">
                    <small class="error_form" id="password_error_message"></small>
                </div>
                <br>
                <div class="col-md-12"><label class="labels">Confirm password</label><input type="password"
                                                                                            id="confirm_password"
                                                                                            class="pw form-control"
                                                                                            placeholder="Confirm password!"
                                                                                            value="">
                    <small class="error_form" id="confirm_password_error_message"></small>
                </div>
            </div>
            <button class="btn btn-outline-info profile-button alignButtonCenter ml-4 pl-4 pr-4" id="edit_password">Edit
                password
            </button>
        </div>

    </div>
</div>

<button id="edit_profile_btn" style="display:none"></button>

<?php include_once('../_partials/footer.php') ?>

</body>
<script type="text/javascript" src="scripts.js?v=<?= filemtime('scripts.js') ?>"></script>

