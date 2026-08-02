<?php
require_once __DIR__ . '/../config.php';

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASSWORD;
$dbname = DB_NAME;

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Connexion failed: ' . $conn->connect_error);
}

$conn->set_charset(DB_CHARSET);


?>
