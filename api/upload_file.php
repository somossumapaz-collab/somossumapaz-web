<?php
// api/upload_file.php
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';
require_once '../helpers/logger.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'] ?? ''; // photo, id_doc, edu_cert, exp_cert
$hoja_vida_id = $_POST['hoja_vida_id'] ?? null;

if (!$hoja_vida_id) {
    echo json_encode(['success' => false, 'error' => 'ID de hoja de vida requerido']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No se recibió el archivo o hubo un error en la subida']);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

try {
    $target_dir = "";
    $filename = "";
    $allowed_exts = [];
    $max_size = 0;

    switch ($type) {
        case 'photo':
            $target_dir = "../uploads/fotos_perfil/";
            $allowed_exts = ['jpg', 'jpeg', 'png'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $filename = "foto_usuario_" . $hoja_vida_id . "." . $ext;
            break;
        case 'id_doc':
            $target_dir = "../uploads/documentos_identidad/";
            $allowed_exts = ['pdf'];
            $max_size = 10 * 1024 * 1024; // 10MB
            $filename = "documento_" . $user_id . "_" . time() . ".pdf";
            break;
        case 'edu_cert':
            $target_dir = "../uploads/certificados_academicos/";
            $allowed_exts = ['pdf'];
            $max_size = 10 * 1024 * 1024;
            $item_id = $_POST['item_id'] ?? 'new';
            $filename = "formacion_" . $item_id . "_" . time() . ".pdf";
            break;
        case 'exp_cert':
            $target_dir = "../uploads/certificados_laborales/";
            $allowed_exts = ['pdf'];
            $max_size = 10 * 1024 * 1024;
            $item_id = $_POST['item_id'] ?? 'new';
            $filename = "experiencia_" . $item_id . "_" . time() . ".pdf";
            break;
        default:
            throw new Exception("Tipo de archivo no soportado");
    }

    // Validations
    if ($file['size'] > $max_size) {
        throw new Exception("El archivo excede el tamaño máximo permitido (" . ($max_size / 1024 / 1024) . "MB)");
    }

    if (!in_array($ext, $allowed_exts)) {
        throw new Exception("Extensión no permitida. Solo: " . implode(', ', $allowed_exts));
    }

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_path = $target_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        if (!file_exists($target_path)) {
            throw new Exception("Error interno: El archivo no se guardó correctamente.");
        }

        $db_path = str_replace('../', '', $target_path);
        log_resume_event("Archivo subido exitosamente: $db_path (Usuario: $user_id)");

        echo json_encode([
            'success' => true,
            'path' => $db_path,
            'filename' => $filename,
            'message' => 'Archivo subido correctamente'
        ]);
    } else {
        throw new Exception("No se pudo mover el archivo al directorio de destino");
    }

} catch (Exception $e) {
    log_resume_event("Error subiendo archivo ($type): " . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
