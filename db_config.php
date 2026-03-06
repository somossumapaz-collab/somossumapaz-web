<?php
// db_config.php

// --- Configuración ---
$host = "localhost";
$user = "somossum_admin";
$password = "somossumapaz2026*";
$database = "somossum_general";

/**
 * Obtiene una conexión PDO a la base de datos
 */
function get_db_connection()
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    global $host, $user, $password, $database;

    try {

        $dsn = "mysql:host=$host;port=3306;dbname=$database;charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // mostrar errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // fetch asociativo
            PDO::ATTR_EMULATE_PREPARES => false // prepared statements reales
        ]);

        return $pdo;

    } catch (PDOException $e) {

        die("Error de conexión: " . $e->getMessage());

    }
}
?>