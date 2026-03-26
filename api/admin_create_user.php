<?php
session_start();
require_once '../database_functions.php';
check_auth();

// Only admin allowed
if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../user_panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol_id = $_POST['rol_id'] ?? 2;

    if (empty($nombre) || empty($email) || empty($password)) {
        $_SESSION['flash_error'] = 'Todos los campos son obligatorios.';
        header('Location: ../admin_create_user.php');
        exit;
    }

    $conn = get_db_connection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['flash_error'] = 'El correo electrónico ya está registrado.';
            header('Location: ../admin_create_user.php');
            exit;
        }
    }

    // Insert new user
    $sql = "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssi", $nombre, $email, $hashed, $rol_id);
        
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = 'Usuario creado exitosamente.';
        } else {
            $_SESSION['flash_error'] = 'Error al crear el usuario en la base de datos.';
        }
    } else {
        $_SESSION['flash_error'] = 'Error de conexión con la base de datos.';
    }

    header('Location: ../admin_create_user.php');
    exit;
} else {
    header('Location: ../admin_create_user.php');
    exit;
}
?>
