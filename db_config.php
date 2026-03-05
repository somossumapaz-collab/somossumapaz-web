<?php
// db_config.php

$host = "localhost";
$user = "somossum_admin";
$password = "somossumapaz2026*";
$database = "somossum_general";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error conexión: " . $conn->connect_error);
}

function get_db_connection()
{
    global $conn;
    static $initialized = false;
    if (!$initialized) {
        $initialized = true;
        init_db();
    }
    return $conn;
}
?>