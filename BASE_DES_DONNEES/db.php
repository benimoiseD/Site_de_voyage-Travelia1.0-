<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "travelia2_safari_rdc";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error){
    die("Connexion failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

/*try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}*/
?>