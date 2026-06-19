<?php

require_once 'Database.php';

session_destroy();
header('Location: Login.php');
exit;
?>