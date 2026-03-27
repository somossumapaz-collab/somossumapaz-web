<?php
function get_db_connection()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    $host = "srv1220.hstgr.io";
    $user = "u949171480_sumapaz_admin";
    $password = "Somossumapaz2026*";
    $database = "u949171480_somos_sumapaz";

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