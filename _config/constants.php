<?php

error_reporting(E_ALL ^ E_WARNING);
//sTART SESSION
session_start();
ob_start();
define('SITEURL','http://localhost/WeWebProject/');
define('LOCALHOST','localhost');
define('DB_USERNAME','root');
define('DB_PASSWORD','root');
define('DB_NAME','weweb');

$conn=mysqli_connect(LOCALHOST,DB_USERNAME,DB_PASSWORD) or die('Error '. mysqli_error($conn));
$db_select = mysqli_select_db($conn,DB_NAME) or die('Error '. mysqli_error($conn));



?>