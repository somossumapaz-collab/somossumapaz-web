<?php
require_once 'database_functions.php';
require_once 'SimpleXLSX.php';
use Shuchkin\SimpleXLSX;

// check_auth(); // Deshabilitado temporalmente para pruebas por solicitud del usuario

ensure_directories(); // Asegurar que existan las carpetas de carga

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

                // Listar archivos para el log
                $allFiles = scandir($temp_dir);
                $foundFiles = [];
                foreach ($allFiles as $f) {
                    if ($f !== '.' && $f !== '..' && !str_starts_with($f, '__MACOSX')) {
                        $size = round(filesize($temp_dir . $f) / 1024, 2);
                        $foundFiles[] = "📄 $f ($size KB)";
                    }
                }
                $logs[] = "<strong>📂 Contenido del ZIP:</strong><br>" . implode('<br>', $foundFiles);

                $excel_file = $temp_dir . 'datos.xlsx';
                if (!file_exists($excel_file)) {
                    // Buscar si está en alguna subcarpeta o tiene otro nombre (pero el usuario pide datos.xlsx)
                    $logs[] = "❌ Error: No se encontró el archivo 'datos.xlsx' en la raíz del ZIP.";
                    deleteDir($temp_dir);
                } else {
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
                                $st = $conn->prepare("SELECT id FROM persona_datos_personales WHERE documento = ?");
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
                                $vereda = $data['vereda'] ?? '';

                                if ($hv_exist) {
                                    $hv_id = $hv_exist['id'];
                                    $st_u = $conn->prepare("UPDATE persona_datos_personales SET nombre=?, tipo_documento=?, fecha_nacimiento=?, departamento_nacimiento=?, municipio_nacimiento=?, telefono=?, email=?, vereda=? WHERE id=?");
                                    if (!$st_u) die("Error prepare update: " . $conn->error);
                                    $st_u->bind_param("ssssssssi", $nombre, $tipo_doc, $f_nac, $dep_nac, $mun_nac, $tel, $mail, $vereda, $hv_id);
                                    $st_u->execute();
                                    $logs[] = "⚠️ Datos actualizados para: $nombre (ID: $hv_id).";
                                } else {
                                    $st_i = $conn->prepare("INSERT INTO persona_datos_personales (nombre, tipo_documento, documento, fecha_nacimiento, departamento_nacimiento, municipio_nacimiento, telefono, email, vereda) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                    if (!$st_i) die("Error prepare insert: " . $conn->error);
                                    $st_i->bind_param("sssssssss", $nombre, $tipo_doc, $doc, $f_nac, $dep_nac, $mun_nac, $tel, $mail, $vereda);
                                    $st_i->execute();
                                    $hv_id = $st_i->insert_id;
                                    $logs[] = "✔ Datos creados para: $nombre (ID: $hv_id).";
                                }

                                // Fotos y Cédula
                                $foto = $excel_dir . 'foto.jpg';
                                $cedu = $excel_dir . 'cedula.pdf';
                                if (file_exists($foto)) {
                                    $fname = "foto_{$hv_id}_".time().".jpg";
                                    copy($foto, $upload_base . "fotos_perfil/" . $fname);
                                    $path = "uploads/fotos_perfil/" . $fname;
                                    $conn->query("UPDATE persona_datos_personales SET ruta_foto='$path' WHERE id=$hv_id");
                                }
                                if (file_exists($cedu)) {
                                    $fname = "doc_{$hv_id}_".time().".pdf";
                                    copy($cedu, $upload_base . "documentos_identidad/" . $fname);
                                    $path = "uploads/documentos_identidad/" . $fname;
                                    $conn->query("UPDATE persona_datos_personales SET ruta_cedula='$path' WHERE id=$hv_id");
                                }

                                // Educacion
                                $edIdx = array_search('Educacion', $sheetNames);
                                if ($edIdx !== false) {
                                    $conn->query("DELETE FROM persona_educacion WHERE persona_id = $hv_id");
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
                                        $st_e = $conn->prepare("INSERT INTO persona_educacion (persona_id, institucion, nivel_educacion, titulo, fecha_inicio, fecha_fin, ruta_certificado) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                        if (!$st_e) die("Error prepare edu: " . $conn->error);
                                        $st_e->bind_param("issssss", $hv_id, $ins, $tit, $tit, $f_i, $f_f, $sop);
                                        $st_e->execute();
                                    }
                                    $logs[] = "✔ Educación procesada.";
                                }

                                // Experiencia
                                $exIdx = array_search('Experiencia', $sheetNames);
                                if ($exIdx !== false) {
                                    $conn->query("DELETE FROM persona_experiencia WHERE persona_id = $hv_id");
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
                                        $st_ex = $conn->prepare("INSERT INTO persona_experiencia (persona_id, empresa, cargo, descripcion, fecha_inicio, fecha_fin, ruta_experiencia) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                        if (!$st_ex) die("Error prepare exp: " . $conn->error);
                                        $st_ex->bind_param("issssss", $hv_id, $emp, $car, $des, $f_i, $f_f, $sop);
                                        $st_ex->execute();
                                    }
                                    $logs[] = "✔ Experiencia procesada.";
                                }

                                // Referencias
                                $refIdx = array_search('Referencias', $sheetNames);
                                if ($refIdx !== false) {
                                    $conn->query("DELETE FROM persona_referencia WHERE persona_id = $hv_id");
                                    $refRows = $xlsx->rows($refIdx);
                                    $head = array_map('strtolower', array_map('trim', $refRows[0]));
                                    for ($i=1; $i<count($refRows); $i++) {
                                        $r = $refRows[$i];
                                        $nom = clean_val($r[array_search('nombre', $head)]);
                                        if (!$nom) continue;
                                        $tel = clean_val($r[array_search('teléfono', $head) !== false ? array_search('teléfono', $head) : array_search('telefono', $head)]);
                                        $ocu = clean_val($r[array_search('ocupación', $head) !== false ? array_search('ocupación', $head) : array_search('ocupacion', $head)]);
                                        $st_r = $conn->prepare("INSERT INTO persona_referencia (persona_id, nombre, telefono, ocupacion) VALUES (?, ?, ?, ?)");
                                        if (!$st_r) die("Error prepare ref: " . $conn->error);
                                        $st_r->bind_param("isss", $hv_id, $nom, $tel, $ocu);
                                        $st_r->execute();
                                    }
                                    $logs[] = "✔ Referencias procesadas.";
                                }
                                $logs[] = "🚀 Importación finalizada con éxito.";
                            }
                        } else { $logs[] = "❌ Error SimpleXLSX: " . SimpleXLSX::parseError(); }
                    }
                }
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
        .logs { margin-top: 25px; background: #1a1a1a; color: #00ff00; padding: 20px; border-radius: 12px; font-family: 'Consolas', 'Courier New', monospace; font-size: 14px; max-height: 400px; overflow-y: auto; border: 2px solid #00ff00; box-shadow: 0 0 15px rgba(0,255,0,0.2); }
        .btn { background: #1a73e8; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn:hover { background: #1557b0; }
        .back { display: block; text-align: center; margin-top: 20px; color: #666; text-decoration: none; font-size: 14px; }
        .log-item { margin-bottom: 12px; border-left: 3px solid #333; padding-left: 10px; }
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
        <div class="logs">
            <h3 style="margin-top:0; color:#00ff00; text-align:center; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid #333; padding-bottom:10px;">📦 Contenido y Procesamiento</h3>
            <?php foreach($logs as $l): ?>
                <div class="log-item"><?php echo $l; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body></html>
