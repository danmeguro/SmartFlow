<?php
// backend/conexao.php

define('DB_SERVER', 'www.thyagoquintas.com.br');
define('DB_USERNAME', 'engenharia_50');
define('DB_PASSWORD', 'tucano');
define('DB_NAME', 'engenharia_50');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn->connect_error){
    die("ERRO DE CONEXAO: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>