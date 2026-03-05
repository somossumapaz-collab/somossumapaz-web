<?php
// api/login.php
session_start();
require_once '../database_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $user = verify_user($username, $password);

    if ($user) {
        if ($remember) {
            ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 days
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: ../dashboard.php');
        exit;
    } else {
        $_SESSION['flash_error'] = 'Usuario o contraseña incorrectos';
        header('Location: ../index.php');
        exit;
    }
}
?>