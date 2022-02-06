<?php
//Inbcludeconstants.pohp for siteurl
include('_config/constants.php');

//Query to display session
//Destroy the session
session_destroy();

// $helper = array_keys($_SESSION);
// foreach ($helper as $key){
//     unset($_SESSION[$key]);
// }

//Redirect to login page
header('location:' . SITEURL . "login/index.php");
?>