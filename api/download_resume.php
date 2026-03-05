<?php
// api/download_resume.php
require_once '../database_functions.php';

$resume_id = $_GET['resume_id'] ?? null;
if (!$resume_id) {
    die("ID de resume no proporcionado");
}

$resumes = get_all_resumes();
$resume = null;
foreach ($resumes as $r) {
    if ($r['id'] == $resume_id) {
        $resume = $r;
        break;
    }
}

if (!$resume) {
    die("Resumen no encontrado");
}

$zip = new ZipArchive();
$filename = "HojaDeVida_" . preg_replace('/[^a-zA-Z0-9]/', '_', $resume['full_name']) . ".zip";
$temp_file = tempnam(sys_get_temp_dir(), 'resume_zip');

if ($zip->open($temp_file, ZipArchive::CREATE) !== TRUE) {
    die("No se pudo crear el archivo ZIP");
}

// 1. Add Info Text File
$info_text = "Nombre: " . $resume['full_name'] . "\n" .
    "Documento: " . $resume['document_id'] . "\n" .
    "Email: " . $resume['email'] . "\n" .
    "Teléfono: " . $resume['phone'] . "\n" .
    "Ciudad: " . $resume['city'] . "\n" .
    "Departamento: " . $resume['department'] . "\n" .
    "Perfil: " . $resume['profile_description'] . "\n\n" .
    "Habilidades:\n" . $resume['skills'] . "\n";

$zip->addFromString($resume['full_name'] . "_info.txt", $info_text);

// 2. Add Files
$upload_folder = '../uploads/';
$files_to_add = [
    ['label' => 'Foto_Perfil', 'filename' => $resume['photo_path']],
    ['label' => 'Documento_Identidad', 'filename' => $resume['id_file_path']],
];

// Add Education Certificates
foreach ($resume['education'] as $i => $edu) {
    if ($edu['certificate_path']) {
        $files_to_add[] = ['label' => 'Certificado_Estudio_' . ($i + 1), 'filename' => $edu['certificate_path']];
    }
}

// Add Experience Certificates
foreach ($resume['experience'] as $i => $exp) {
    if ($exp['certificate_path']) {
        $files_to_add[] = ['label' => 'Certificado_Laboral_' . ($i + 1), 'filename' => $exp['certificate_path']];
    }
}

foreach ($files_to_add as $file_info) {
    if ($file_info['filename']) {
        $file_path = $upload_folder . $file_info['filename'];
        if (file_exists($file_path)) {
            $ext = pathinfo($file_info['filename'], PATHINFO_EXTENSION);
            $safe_label = preg_replace('/[^a-zA-Z0-9_]/', '', $file_info['label']);
            $arcname = $safe_label . "_" . preg_replace('/[^a-zA-Z0-9_]/', '', $resume['full_name']) . "." . $ext;
            $zip->addFile($file_path, $arcname);
        }
    }
}

$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($temp_file));
readfile($temp_file);
unlink($temp_file);
exit;
?>