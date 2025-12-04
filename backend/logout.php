<?php
// backend/logout.php
session_start();
$_SESSION = array();
session_destroy();
header("location: view/index.php");
exit;
?>