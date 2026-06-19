<?php
// ACTIVITY A - Logout.php
require_once 'Data.php';

session_destroy();
header('Location: Login.php');
exit;
?>
