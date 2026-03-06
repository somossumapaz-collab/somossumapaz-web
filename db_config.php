<?php
// db_config.php

$host = "localhost";
$user = "somossum_somossum_admin";
$password = "somossumapaz2026*";
$database = "somossum_somossum_general";

function get_db_connection()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    global $host, $user, $password, $database;

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
?>