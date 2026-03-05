<?php
session_start();
require_once '../database_functions.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $documento_numero = $_POST['documento_numero'] ?? 'unknown';
    $hoja_vida_id = $_POST['hoja_vida_id'] ?? null;

    $file = $_FILES['documento'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos PDF']);
        exit;
    }

    $filename = "documento_" . $documento_numero . ".pdf";
    $target_dir = "../uploads/documentos_identidad/";
    $target_path = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $db_path = "uploads/documentos_identidad/" . $filename;
        $conn = get_db_connection();
        $stmt = $conn->prepare("UPDATE hoja_vida SET documento_identidad_path = ? WHERE id = ?");
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