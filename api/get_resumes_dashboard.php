<?php
// api/get_resumes_dashboard.php
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database_functions.php';

// Allow any logged in user for now to verify it works
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

try {
    $resumes = get_all_resumes();

    // Diagnostic logging
    $count = is_array($resumes) ? count($resumes) : 0;
    error_log("[" . date('Y-m-d H:i:s') . "] Dashboard API: Retrieved $count resumes for user " . ($_SESSION['user_id'] ?? 'unknown') . "\n", 3, __DIR__ . "/../logs/dashboard_diag.log");

    echo json_encode(['success' => true, 'data' => $resumes]);
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Dashboard API ERROR: " . $e->getMessage() . "\n", 3, __DIR__ . "/../logs/dashboard_diag.log");
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
