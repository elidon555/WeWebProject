<?php

//Authorization
//Check if user is logged in or not
if (!isset($_SESSION['user'])) {

    //User is not loggedin
    //Redirect to loginpage with message
    $_SESSION['not-logged-in'] = "<div class='error text-center alert alert-danger' style='width:400px;margin:auto'>Please login first!</div>";
    echo $_SESSION['not-logged-in'];

    header('location:' . SITEURL . 'login/index.php');

}

?>