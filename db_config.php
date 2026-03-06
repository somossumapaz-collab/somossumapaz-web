<?php
// db_config.php

// --- Configuración ---
$host = "localhost";
$user = "somossum_admin";
$password = "somossumapaz2026*";
$database = "somossum_general";

/**
 * Obtiene una conexión MySQLi a la base de datos
 */
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

    // Importante para caracteres especiales (tildes, ñ, etc.)
    $conn->set_charset("utf8mb4");

    return $conn;
}
?>