<?php
function get_db_connection()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    $host = "15.235.82.117";
    $user = "somossum_admin";
    $password = "Talento_suma";
    $database = "somossum_talento";

    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        error_log("Error de conexión DB: " . $conn->connect_error);
        return null;
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
?>