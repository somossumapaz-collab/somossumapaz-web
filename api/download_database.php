<?php
// api/download_database.php
require_once '../database_functions.php';

$resumes = get_all_resumes();

if (empty($resumes)) {
    die("No hay datos para exportar");
}

$filename = "base_de_datos_empleo_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
// BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header
$header = array_keys($resumes[0]);
// Remove education and experience from main CSV (or handle them)
$header = array_filter($header, function ($k) {
    return $k !== 'education' && $k !== 'experience';
});
fputcsv($output, array_values($header));

// Data
foreach ($resumes as $resume) {
    $row = [];
    foreach ($header as $col) {
        $row[] = $resume[$col] ?? '';
    }
    fputcsv($output, $row);
}

fclose($output);
exit;
?>