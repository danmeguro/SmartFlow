<?php
// backend/logout.php
session_start();
// Limpa sessão
$_SESSION = array();
session_destroy();
// Volta para o login
header("location: view/index.php");
exit;
?>