<?php

include_once('../_config/constants.php');
include_once('login-check.php');
include_once('../Config.php');
//Authorization
//Check if user is logged in or not

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../style.css?v=<?= filemtime('../style.css') ?>">
    <link rel="stylesheet" href="../_plugins/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../_plugins/daterangepicker.css"/>
    <link rel="stylesheet" type="text/css" href="../_plugins/jquery.dataTables.min.css"/>
    <script src="../_plugins/sweetalert2.min.css"></script>


    <!-- <SCRIPT language="JavaScript">
function silentErrorHandler() {return true;}
window.onerror=silentErrorHandler;
</SCRIPT> -->

    <title></title>
</head>

<body>
<!-- Menu section start -->
<nav class="navbar navbar-expand-lg bg-dark">
    <div class="container">

        <a class="navbar-brand text-white"><i
                    class="fas fa-globe"></i><?php echo " " . $_SESSION['role'] . ": " . $_SESSION['fullName'] ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nvbCollapse"
                aria-controls="nvbCollapse">
            <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="nvbCollapse">
            <ul class="navbar-nav ml-auto">

                <li class="nav-item pl-1">
                    <a class="nav-link" href="../user_list"><i
                                class="fas fa-list-ul fa-fw mr-1"></i>User List</a>
                </li>

                <li class="nav-item pl-1">
                    <a class="nav-link" href="../checkins_list"><i
                                class="fas fa-list-ul fa-fw mr-1"></i>User Checkins</a>
                </li>

                <li class="nav-item pl-1">
                    <a class="nav-link" href="../profile/index.php"><i
                                class="fas fa-list-ul fa-fw mr-1"></i>Profile</a>
                </li>


                <li class="nav-item pl-1">
                    <a class="nav-link" href="../log-out.php"><i
                                class="fas fa-sign-out-alt"></i> Log out</a>
                </li>

            </ul>
        </div>
    </div>
</nav>
</body>


<!-- Menu section end -->