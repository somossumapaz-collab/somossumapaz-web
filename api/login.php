<?php
// api/login.php
session_start();
require_once '../database_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['username'] ?? ''; // This can be username or email
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $user = verify_user($identifier, $password);

    if ($user) {
        if ($remember) {
            ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 days
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['rol'];

        header('Location: ../user_panel.php');
        exit;
    } else {
        $_SESSION['flash_error'] = 'Usuario o contraseña incorrectos';
        header('Location: ../login_page.php');
        exit;
    }
}
?>