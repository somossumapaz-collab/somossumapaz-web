<?php
// api/get_resume.php
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';

$user_id = $_GET['user_id'] ?? ($_SESSION['user_id'] ?? null);

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Usuario no especificado']);
    exit;
}

try {
    $resume = get_complete_resume($user_id);
    if (!$resume) {
        echo json_encode(['success' => false, 'error' => 'Hoja de vida no encontrada']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $resume]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
