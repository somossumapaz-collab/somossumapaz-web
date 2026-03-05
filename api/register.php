<?php
// api/register.php
session_start();
require_once '../database_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (create_user($username, $password)) {
        $_SESSION['flash_success'] = 'Usuario creado exitosamente. Por favor ingresa.';
    } else {
        $_SESSION['flash_error'] = 'El nombre de usuario ya existe.';
    }

    header('Location: ../index.php');
    exit;
}
?>