<?php
session_start();
require_once '../database_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $department = $_POST['department'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($email)) {
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
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'department' => $department,
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