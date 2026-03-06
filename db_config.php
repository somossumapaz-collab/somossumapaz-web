<?php
// db_config.php

// --- Configuration ---
$host = "localhost";
$user = "somossum_admin";
$password = "somossumapaz2026*";
$database = "somossum_general";

/**
 * Returns a database connection (PDO).
 */
function get_db_connection()
{
    global $host, $user, $password, $database;
    static $pdo = null;

    if ($pdo !== null)
        return $pdo;

    try {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $user, $password, $options);

        static $initOnce = false;
        if (!$initOnce) {
            $initOnce = true;
            require_once __DIR__ . '/database_functions.php';
        }

        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
?>