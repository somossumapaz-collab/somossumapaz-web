<?php
// api/diagnostic.php
header('Content-Type: application/json');
require_once '../database_functions.php';

$conn = get_db_connection();
$report = [
    'database' => [
        'connection' => ($conn ? 'OK' : 'FAIL'),
        'tables' => []
    ],
    'directories' => []
];

if ($conn) {
    $tables = ['usuarios', 'hoja_vida', 'hoja_vida_habilidades', 'hoja_vida_formacion', 'hoja_vida_experiencia', 'hoja_vida_referencias'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $report['database']['tables'][$table] = ($result->num_rows > 0 ? 'EXISTS' : 'MISSING');
    }
}

$dirs = ['../uploads', '../uploads/fotos_perfil', '../uploads/documentos_identidad', '../uploads/certificados_academicos', '../uploads/certificados_laborales'];
foreach ($dirs as $dir) {
    $report['directories'][$dir] = [
        'exists' => file_exists($dir),
        'writable' => is_writable($dir)
    ];
}

echo json_encode($report, JSON_PRETTY_PRINT);
