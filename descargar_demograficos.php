<?php
$file = __DIR__ . '/Datos_Demograficos_Hojas_de_Vida.xlsx';

// Always regenerate to serve fresh database information
exec('python ' . escapeshellarg(__DIR__ . '/exportar_demograficos_excel.py'));

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Datos_Demograficos_Hojas_de_Vida.xlsx"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    echo "Error: El archivo Excel no se pudo generar.";
}
?>
