<?php
// api/get_resumes.php
header('Content-Type: application/json');
require_once '../database_functions.php';

try {
    $resumes = get_all_resumes();
    echo json_encode($resumes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>