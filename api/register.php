<?php
session_start();
require_once '../database_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['username'] ?? ''; // From form 'username'
    $email = $_POST['email'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $telefono = $_POST['phone'] ?? ''; // From form 'phone'
    $documento = $_POST['documento'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($usuario) || empty($password) || empty($email)) {
        $_SESSION['flash_message'] = "El usuario, el correo y la contraseña son obligatorios";
        header('Location: ../register_page.php');
        exit;
    }

    if ($password !== $confirm) {
        $_SESSION['flash_message'] = "Las contraseñas no coinciden";
        header('Location: ../register_page.php');
        exit;
    }

    $userData = [
        'usuario' => $usuario,
        'email' => $email,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'telefono' => $telefono,
        'documento' => $documento,
        'password' => $password
    ];

    if (create_user($userData)) {
        $_SESSION['flash_message'] = "Registro exitoso. Ahora puedes iniciar sesión.";
        header('Location: ../login_page.php');
    } else {
        $_SESSION['flash_message'] = "Error al crear el usuario. El nombre de usuario o correo ya pueden estar en uso.";
        header('Location: ../register_page.php');
    }
}
?>