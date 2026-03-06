<?php
function get_db_connection()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    $host = "localhost";
    $user = "somossum_admin";
    $password = "somossumapaz2026*";
    $database = "somossum_general";

    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
?>