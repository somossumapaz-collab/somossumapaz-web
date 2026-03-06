<?php
// api/submit_resume.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database_functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Use centralized logic
$result = save_resume_data($user_id, $_POST, $_FILES);

echo json_encode($result);