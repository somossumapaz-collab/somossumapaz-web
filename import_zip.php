<?php
require_once 'database_functions.php';
require_once 'SimpleXLSX.php';
use Shuchkin\SimpleXLSX;

check_auth(); // Asegurar que solo usuarios logueados importen

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) deleteDir($file);
        else unlink($file);
    }
    if (is_dir($dirPath)) rmdir($dirPath);
}

function clean_val($val) {
    if ($val === null || $val === '') return null;
    return trim($val);
}

function format_date($val) {
    if (!$val) return null;
    if (is_numeric($val)) return date('Y-m-d', ($val - 25569) * 86400);
    $time = strtotime(str_replace('/', '-', $val));
    return $time ? date('Y-m-d', $time) : null;
}

$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_file'])) {
    if (!class_exists('ZipArchive')) {
        $logs[] = "❌ Error: La extensión 'ZipArchive' no está habilitada.";
    } else {
        $zip_file = $_FILES['zip_file'];
        if ($zip_file['error'] === UPLOAD_ERR_OK) {
            $upload_base = 'uploads/';
            $dirs = ['fotos_perfil', 'documentos_identidad', 'certificados_academicos', 'certificados_laborales'];
            foreach ($dirs as $d) { if(!is_dir($upload_base.$d)) mkdir($upload_base.$d, 0777, true); }

            $temp_dir = 'temp_import_' . uniqid() . '/';
            mkdir($temp_dir, 0777, true);

            $zip = new ZipArchive;
            if ($zip->open($zip_file['tmp_name']) === TRUE) {
                $zip->extractTo($temp_dir);
                $zip->close();
                $logs[] = "📦 ZIP extraído.";

                $excel_file = '';
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp_dir));
                foreach ($it as $f) {
                    if ($f->isFile() && !strpos($f->getPathname(), '__MACOSX')) {
                        $ext = strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION));
                        if ($ext === 'xlsx' || $ext === 'xls') { $excel_file = $f->getPathname(); break; }
                    }
                }

                if ($excel_file) {
                    $excel_dir = dirname($excel_file) . '/';
                    if ($xlsx = SimpleXLSX::parse($excel_file)) {
                        $conn = get_db_connection();
                        $user_id = $_SESSION['user_id'] ?? 0;
                        $sheetNames = $xlsx->sheetNames();

                        // Hoja Datos
                        $sIdx = array_search('Datos', $sheetNames);
                        if ($sIdx !== false) {
                            $rows = $xlsx->rows($sIdx);
                            $data = [];
                            foreach ($rows as $r) { 
                                if(!empty($r[0])) {
                                    $key = strtolower(trim($r[0]));
                                    // Normalizar claves comunes
                                    if (strpos($key, 'nombre') !== false) $key = 'nombre';
                                    if (strpos($key, 'documento') !== false || strpos($key, 'cédula') !== false || strpos($key, 'cedula') !== false) $key = 'documento';
                                    if (strpos($key, 'teléfono') !== false || strpos($key, 'telefono') !== false || strpos($key, 'celular') !== false) $key = 'telefono';
                                    if (strpos($key, 'nacimiento') !== false) $key = 'fecha_nacimiento';
                                    
                                    $data[$key] = clean_val($r[1]??null); 
                                }
                            }

                            $nombre = $data['nombre'] ?? '';
                            $doc = $data['documento'] ?? '';
                            
                            if (!$nombre || !$doc) {
                                $logs[] = "❌ Error: No se encontró 'Nombre' o 'Documento' en la hoja 'Datos'. Claves encontradas: " . implode(', ', array_keys($data));
                            } else {
                                // Buscar si ya existe la hoja de vida para este documento
                                $st = $conn->prepare("SELECT id FROM hoja_vida WHERE numero_documento = ?");
                                $st->bind_param("s", $doc);
                                $st->execute();
                                $hv_exist = $st->get_result()->fetch_assoc();
                                $hv_id = null;

                                $tipo_doc = $data['tipo documento'] ?? $data['tipo_documento'] ?? 'CC';
                                $f_nac = format_date($data['fecha_nacimiento'] ?? $data['fecha nacimiento'] ?? null);
                                $dep_nac = $data['departamento nacimiento'] ?? $data['departamento_nacimiento'] ?? '';
                                $mun_nac = $data['municipio nacimiento'] ?? $data['municipio_nacimiento'] ?? '';
                                $tel = $data['telefono'] ?? '';
                                $mail = $data['email'] ?? '';
                                $perf = $data['perfil profesional'] ?? $data['perfil_profesional'] ?? '';

                                if ($hv_exist) {
                                    $hv_id = $hv_exist['id'];
                                    $st_u = $conn->prepare("UPDATE hoja_vida SET nombre_completo=?, tipo_documento=?, fecha_nacimiento=?, departamento_nacimiento=?, municipio_nacimiento=?, telefono=?, email=?, perfil_profesional=? WHERE id=?");
                                    $st_u->bind_param("ssssssssi", $nombre, $tipo_doc, $f_nac, $dep_nac, $mun_nac, $tel, $mail, $perf, $hv_id);
                                    $st_u->execute();
                                    $logs[] = "⚠️ Hoja de vida actualizada para: $nombre (ID: $hv_id).";
                                } else {
                                    $st_i = $conn->prepare("INSERT INTO hoja_vida (usuario_id, nombre_completo, tipo_documento, numero_documento, fecha_nacimiento, departamento_nacimiento, municipio_nacimiento, telefono, email, perfil_profesional) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                    $st_i->bind_param("isssssssss", $user_id, $nombre, $tipo_doc, $doc, $f_nac, $dep_nac, $mun_nac, $tel, $mail, $perf);
                                    $st_i->execute();
                                    $hv_id = $st_i->insert_id;
                                    $logs[] = "✔ Hoja de vida creada para: $nombre (ID: $hv_id).";
                                }

                                // Fotos y Cédula
                                $foto = $excel_dir . 'foto.jpg';
                                $cedu = $excel_dir . 'cedula.pdf';
                                if (file_exists($foto)) {
                                    $fname = "foto_{$hv_id}_".time().".jpg";
                                    copy($foto, $upload_base . "fotos_perfil/" . $fname);
                                    $path = "uploads/fotos_perfil/" . $fname;
                                    $conn->query("UPDATE hoja_vida SET foto_perfil_path='$path' WHERE id=$hv_id");
                                }
                                if (file_exists($cedu)) {
                                    $fname = "doc_{$hv_id}_".time().".pdf";
                                    copy($cedu, $upload_base . "documentos_identidad/" . $fname);
                                    $path = "uploads/documentos_identidad/" . $fname;
                                    $conn->query("UPDATE hoja_vida SET documento_pdf_path='$path' WHERE id=$hv_id");
                                }

                                // Educacion
                                $edIdx = array_search('Educacion', $sheetNames);
                                if ($edIdx !== false) {
                                    $conn->query("DELETE FROM hoja_vida_formacion WHERE hoja_vida_id = $hv_id");
                                    $edRows = $xlsx->rows($edIdx);
                                    $head = array_map('strtolower', array_map('trim', $edRows[0]));
                                    for ($i=1; $i<count($edRows); $i++) {
                                        $r = $edRows[$i];
                                        $tit = clean_val($r[array_search('título', $head) !== false ? array_search('título', $head) : array_search('titulo', $head)]);
                                        if (!$tit) continue;
                                        $ins = clean_val($r[array_search('institución', $head) !== false ? array_search('institución', $head) : array_search('institucion', $head)]);
                                        $f_i = format_date($r[array_search('fecha inicio', $head)]);
                                        $f_f = format_date($r[array_search('fecha fin', $head)]);
                                        $sid = clean_val($r[array_search('id', $head)]);
                                        $sop = null;
                                        if ($sid && file_exists($excel_dir . "educacion_{$sid}.pdf")) {
                                            $sn = "edu_{$hv_id}_".uniqid().".pdf";
                                            copy($excel_dir . "educacion_{$sid}.pdf", $upload_base . "certificados_academicos/" . $sn);
                                            $sop = "uploads/certificados_academicos/" . $sn;
                                        }
                                        $st_e = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, institucion, nivel_educativo, fecha_inicio, fecha_fin, soporte_path) VALUES (?, ?, ?, ?, ?, ?)");
                                        $st_e->bind_param("isssss", $hv_id, $ins, $tit, $f_i, $f_f, $sop);
                                        $st_e->execute();
                                    }
                                    $logs[] = "✔ Educación procesada.";
                                }

                                // Experiencia
                                $exIdx = array_search('Experiencia', $sheetNames);
                                if ($exIdx !== false) {
                                    $conn->query("DELETE FROM hoja_vida_experiencia WHERE hoja_vida_id = $hv_id");
                                    $exRows = $xlsx->rows($exIdx);
                                    $head = array_map('strtolower', array_map('trim', $exRows[0]));
                                    for ($i=1; $i<count($exRows); $i++) {
                                        $r = $exRows[$i];
                                        $car = clean_val($r[array_search('cargo', $head)]);
                                        if (!$car) continue;
                                        $emp = clean_val($r[array_search('empresa', $head)]);
                                        $f_i = format_date($r[array_search('fecha inicio', $head)]);
                                        $f_f = format_date($r[array_search('fecha fin', $head)]);
                                        $des = clean_val($r[array_search('descripción', $head) !== false ? array_search('descripción', $head) : array_search('descripcion', $head)]);
                                        $sid = clean_val($r[array_search('id', $head)]);
                                        $sop = null;
                                        if ($sid && file_exists($excel_dir . "experiencia_{$sid}.pdf")) {
                                            $sn = "exp_{$hv_id}_".uniqid().".pdf";
                                            copy($excel_dir . "experiencia_{$sid}.pdf", $upload_base . "certificados_laborales/" . $sn);
                                            $sop = "uploads/certificados_laborales/" . $sn;
                                        }
                                        $st_ex = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, empresa, cargo, descripcion_cargo, fecha_inicio, fecha_fin, soporte_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                        $st_ex->bind_param("issssss", $hv_id, $emp, $car, $des, $f_i, $f_f, $sop);
                                        $st_ex->execute();
                                    }
                                    $logs[] = "✔ Experiencia procesada.";
                                }

                                // Referencias
                                $refIdx = array_search('Referencias', $sheetNames);
                                if ($refIdx !== false) {
                                    $conn->query("DELETE FROM hoja_vida_referencias WHERE hoja_vida_id = $hv_id");
                                    $refRows = $xlsx->rows($refIdx);
                                    $head = array_map('strtolower', array_map('trim', $refRows[0]));
                                    for ($i=1; $i<count($refRows); $i++) {
                                        $r = $refRows[$i];
                                        $nom = clean_val($r[array_search('nombre', $head)]);
                                        if (!$nom) continue;
                                        $tel = clean_val($r[array_search('teléfono', $head) !== false ? array_search('teléfono', $head) : array_search('telefono', $head)]);
                                        $ocu = clean_val($r[array_search('ocupación', $head) !== false ? array_search('ocupación', $head) : array_search('ocupacion', $head)]);
                                        $st_r = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, nombre, telefono, ocupacion) VALUES (?, ?, ?, ?)");
                                        $st_r->bind_param("isss", $hv_id, $nom, $tel, $ocu);
                                        $st_r->execute();
                                    }
                                    $logs[] = "✔ Referencias procesadas.";
                                }
                                $logs[] = "🚀 Importación finalizada con éxito.";
                            }
                        }
                    } else { $logs[] = "❌ Error SimpleXLSX: " . SimpleXLSX::parseError(); }
                } else { $logs[] = "❌ No se encontró Excel."; }
                deleteDir($temp_dir);
            } else { $logs[] = "❌ No se pudo abrir el ZIP."; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Importar ZIP - Talento Sumapaz</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0; }
        .card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { color: #1a73e8; margin-top: 0; text-align: center; }
        .logs { margin-top: 25px; background: #1a1a1a; color: #00ff00; padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 13px; max-height: 250px; overflow-y: auto; }
        .btn { background: #1a73e8; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn:hover { background: #1557b0; }
        .back { display: block; text-align: center; margin-top: 20px; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
<div class="card">
    <h2>📦 Importar desde ZIP</h2>
    <p style="color: #666; text-align: center; margin-bottom: 30px;">Carga masiva de hojas de vida y documentos.</p>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="zip_file" accept=".zip" required style="margin-bottom: 20px; width: 100%;">
        <button type="submit" class="btn">Procesar Archivo</button>
    </form>
    <a href="user_panel.php" class="back">← Volver al Panel</a>
    <?php if(!empty($logs)): ?>
        <div class="logs"><?php foreach($logs as $l) echo "<p style='margin:5px 0;'>$l</p>"; ?></div>
    <?php endif; ?>
</div>
</body></html>
