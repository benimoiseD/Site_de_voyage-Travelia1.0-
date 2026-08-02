<?php
require_once __DIR__ . '/../config.php';

/**
 * Retourne une connexion MySQLi configurée.
 *
 * @return mysqli
 */
function get_db_connection(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die('Erreur de connexion à la base de données : ' . $conn->connect_error);
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}
