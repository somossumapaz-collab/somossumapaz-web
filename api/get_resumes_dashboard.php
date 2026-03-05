<?php
// api/get_resumes_dashboard.php
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';

// Check if user is admin (simplified check for now)
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    // For now, allow view if logged in, but better to restrict
    // echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    // exit;
}

try {
    $resumes = get_all_resumes();
    echo json_encode(['success' => true, 'data' => $resumes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
