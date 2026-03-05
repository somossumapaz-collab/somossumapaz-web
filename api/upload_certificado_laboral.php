<?php
session_start();
require_once '../database_functions.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificado'])) {
    $experiencia_id = $_POST['experiencia_id'] ?? null;
    if (!$experiencia_id) {
        echo json_encode(['success' => false, 'message' => 'ID de experiencia no proporcionado']);
        exit;
    }

    $file = $_FILES['certificado'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos PDF']);
        exit;
    }

    $filename = "experiencia_" . $experiencia_id . ".pdf";
    $target_dir = "../uploads/certificados_laborales/";
    $target_path = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $db_path = "uploads/certificados_laborales/" . $filename;
        $conn = get_db_connection();
        $stmt = $conn->prepare("UPDATE hoja_vida_experiencia SET soporte_path = ? WHERE id = ?");
        $stmt->bind_param("si", $db_path, $experiencia_id);
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