<?php
session_start();
require_once '../database_functions.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $hoja_vida_id = $_POST['hoja_vida_id'] ?? null;
    if (!$hoja_vida_id) {
        echo json_encode(['success' => false, 'message' => 'ID de hoja de vida no proporcionado']);
        exit;
    }

    $file = $_FILES['foto'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes JPG o PNG']);
        exit;
    }

    $filename = "foto_usuario_" . $hoja_vida_id . "." . $ext;
    $target_dir = "../uploads/fotos_perfil/";
    $target_path = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $db_path = "uploads/fotos_perfil/" . $filename;
        $conn = get_db_connection();
        $stmt = $conn->prepare("UPDATE hoja_vida SET foto_perfil_path = ? WHERE id = ?");
        $stmt->bind_param("si", $db_path, $hoja_vida_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'path' => $db_path]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar base de datos']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
    }
}
?>