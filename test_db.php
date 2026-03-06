<?php

$host = "localhost";
$user = "somossum_admin";
$password = "somossumapaz2026*";
$db = "somossum_general";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

echo "Conexion exitosa 🚀";