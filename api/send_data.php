<?php
// api/send_data.php
// API intended for devices (mobile, IoT, etc.) to send resume data and files.

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../database_functions.php';

// Support identification via user_id in POST or existing session
session_start();
$user_id = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? null);

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Usuario no identificado (user_id requerido)']);
    exit;
}

// Save data using centralized logic
$result = save_resume_data($user_id, $_POST, $_FILES);

echo json_encode($result);
