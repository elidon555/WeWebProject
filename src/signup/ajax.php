<?php

include('../_config/constants.php');

if (isset($_SESSION['success-signup'])) {
    echo $_SESSION['success-signup']; //Displaying session
    unset($_SESSION['success-signup']); //Removing session
    echo "<br>";
}
