<?php
//Inbcludeconstants.pohp for siteurl
include('_config/constants.php');

//Query to display session
//Destroy the session
session_destroy();
header('Location: '.SITEURL.'login');


// $helper = array_keys($_SESSION);
// foreach ($helper as $key){
//     unset($_SESSION[$key]);
// }
