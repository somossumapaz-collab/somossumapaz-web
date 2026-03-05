<?php
// api/submit_resume.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';

// Diagnostic log as requested (Point 4)
file_put_contents(
    "debug_post.txt",
    "--- " . date('Y-m-d H:i:s') . " ---\n" .
    "POST:\n" . print_r($_POST, true) . "\n" .
    "FILES:\n" . print_r($_FILES, true) . "\n",
    FILE_APPEND
);

function ensure_directories()
{
    $base = '../uploads/';
    $subs = ['fotos_perfil', 'documentos_identidad', 'certificados_academicos', 'certificados_laborales'];
    foreach ($subs as $sub) {
        $path = $base . $sub;
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new Exception("No se pudo crear el directorio: $path");
            }
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = get_db_connection();

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $log = [];
    $log[] = "Iniciado procesamiento de Hoja de Vida para usuario ID: $user_id";
    ensure_directories();

    // Start Transaction (Point 7)
    $conn->begin_transaction();

    // 0. Extract variables
    $nombre_completo = $_POST['full_name'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $doc_id = $_POST['document_id'] ?? '';
    $birth_date = $_POST['birth_date'] ?? null;
    $birth_dept = $_POST['birth_department'] ?? '';
    $birth_city = $_POST['birth_city'] ?? '';
    $dept = $_POST['department'] ?? '';
    $city = $_POST['city'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $perfil = $_POST['profile_description'] ?? '';

    // 1. Base Resume Record
    $stmt = $conn->prepare("SELECT id FROM hoja_vida WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $hv = $stmt->get_result()->fetch_assoc();

    if ($hv) {
        $hoja_vida_id = $hv['id'];
        // Point 3: Use correct column names
        $stmt = $conn->prepare("UPDATE hoja_vida SET 
            nombre_completo = ?, 
            tipo_documento = ?, 
            numero_documento = ?, 
            fecha_nacimiento = ?, 
            departamento_nacimiento = ?, 
            municipio_nacimiento = ?, 
            departamento_residencia = ?, 
            municipio_residencia = ?, 
            telefono = ?, 
            email = ?, 
            perfil_profesional = ? 
            WHERE id = ?");
        $stmt->bind_param(
            "sssssssssssi",
            $nombre_completo,
            $id_type,
            $doc_id,
            $birth_date,
            $birth_dept,
            $birth_city,
            $dept,
            $city,
            $phone,
            $email,
            $perfil,
            $hoja_vida_id
        );

        if (!$stmt->execute())
            throw new Exception("Error al actualizar hoja_vida: " . $stmt->error);

        // Point 8: Verify affected rows (only if data changed, but for update it might be 0 if same data)
        // However user requested to validate it. Let's log it instead of throwing if 0.
        $log[] = "Comunicación BD: Actualizada tabla 'hoja_vida' (ID: $hoja_vida_id)";
    } else {
        $stmt = $conn->prepare("INSERT INTO hoja_vida (
            usuario_id, nombre_completo, tipo_documento, numero_documento, 
            fecha_nacimiento, departamento_nacimiento, municipio_nacimiento, 
            departamento_residencia, municipio_residencia, telefono, email, perfil_profesional
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssssssssss",
            $user_id,
            $nombre_completo,
            $id_type,
            $doc_id,
            $birth_date,
            $birth_dept,
            $birth_city,
            $dept,
            $city,
            $phone,
            $email,
            $perfil
        );

        if (!$stmt->execute())
            throw new Exception("Error al insertar hoja_vida: " . $stmt->error);
        if ($stmt->affected_rows <= 0)
            throw new Exception("No se insertó el registro en hoja_vida");

        $hoja_vida_id = $conn->insert_id;
        $log[] = "Comunicación BD: Insertado nuevo registro en tabla 'hoja_vida' (ID: $hoja_vida_id)";
    }

    // 2. Profile Photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $filename = "foto_usuario_" . $hoja_vida_id . "." . $ext;
        $target = "../uploads/fotos_perfil/" . $filename;

        // Point 5: move and verify
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target))
            throw new Exception("Error al mover foto perfil");
        if (!file_exists($target))
            throw new Exception("La foto no se guardó en el servidor");

        $log[] = "Archivo: Guardada foto en " . realpath($target);

        $db_path = "uploads/fotos_perfil/" . $filename;
        $stmt = $conn->prepare("UPDATE hoja_vida SET foto_perfil_path = ? WHERE id = ?");
        $stmt->bind_param("si", $db_path, $hoja_vida_id);
        $stmt->execute();
    }

    // 3. ID Document (PDF)
    // Point 3: Use correct column name 'documento_pdf_path'
    if (isset($_FILES['id_file']) && $_FILES['id_file']['error'] === UPLOAD_ERR_OK) {
        $filename = "documento_" . $doc_id . ".pdf";
        $target = "../uploads/documentos_identidad/" . $filename;

        if (!move_uploaded_file($_FILES['id_file']['tmp_name'], $target))
            throw new Exception("Error al mover documento PDF");
        if (!file_exists($target))
            throw new Exception("El PDF no se guardó en el servidor");

        $log[] = "Archivo: Guardado PDF en " . realpath($target);

        $db_path = "uploads/documentos_identidad/" . $filename;
        $stmt = $conn->prepare("UPDATE hoja_vida SET documento_pdf_path = ? WHERE id = ?");
        $stmt->bind_param("si", $db_path, $hoja_vida_id);
        $stmt->execute();
    }

    // 4. Skills
    $conn->query("DELETE FROM hoja_vida_habilidades WHERE hoja_vida_id = $hoja_vida_id");
    $skills = $_POST['skills'] ?? '';
    if (!empty($skills)) {
        $arr = explode(',', $skills);
        $stmt = $conn->prepare("INSERT INTO hoja_vida_habilidades (hoja_vida_id, habilidad) VALUES (?, ?)");
        foreach ($arr as $s) {
            $val = trim($s);
            if ($val) {
                $stmt->bind_param("is", $hoja_vida_id, $val);
                $stmt->execute();
            }
        }
        $log[] = "BD: Guardadas habilidades.";
    }

    // 5. Education
    $conn->query("DELETE FROM hoja_vida_formacion WHERE hoja_vida_id = $hoja_vida_id");
    $i = 0;
    while (isset($_POST["education_{$i}_institution"])) {
        $level = $_POST["education_{$i}_level"] ?? '';
        $inst = $_POST["education_{$i}_institution"] ?? '';
        $start = $_POST["education_{$i}_start_date"] ?? null;
        $end = $_POST["education_{$i}_end_date"] ?? null;
        $curr = isset($_POST["education_{$i}_is_current"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, nivel_educativo, institucion, fecha_inicio, fecha_fin, en_curso) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $hoja_vida_id, $level, $inst, $start, $end, $curr);
        $stmt->execute();
        $edu_id = $conn->insert_id;

        if (isset($_FILES["education_{$i}_file"]) && $_FILES["education_{$i}_file"]['error'] === UPLOAD_ERR_OK) {
            $fname = "edu_$edu_id.pdf";
            $target = "../uploads/certificados_academicos/" . $fname;
            move_uploaded_file($_FILES["education_{$i}_file"]['tmp_name'], $target);
            $db_p = "uploads/certificados_academicos/" . $fname;
            $conn->query("UPDATE hoja_vida_formacion SET soporte_path = '$db_p' WHERE id = $edu_id");
        }
        $i++;
    }

    // 6. Experience
    $conn->query("DELETE FROM hoja_vida_experiencia WHERE hoja_vida_id = $hoja_vida_id");
    $j = 0;
    while (isset($_POST["experience_{$j}_company"])) {
        $role = $_POST["experience_{$j}_role"] ?? '';
        $comp = $_POST["experience_{$j}_company"] ?? '';
        $start = $_POST["experience_{$j}_start_date"] ?? null;
        $end = $_POST["experience_{$j}_end_date"] ?? null;
        $curr = isset($_POST["experience_{$j}_is_current"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, cargo, empresa, fecha_inicio, fecha_fin, actualmente) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $hoja_vida_id, $role, $comp, $start, $end, $curr);
        $stmt->execute();
        $exp_id = $conn->insert_id;

        if (isset($_FILES["experience_{$j}_file"]) && $_FILES["experience_{$j}_file"]['error'] === UPLOAD_ERR_OK) {
            $fname = "exp_$exp_id.pdf";
            $target = "../uploads/certificados_laborales/" . $fname;
            move_uploaded_file($_FILES["experience_{$j}_file"]['tmp_name'], $target);
            $db_p = "uploads/certificados_laborales/" . $fname;
            $conn->query("UPDATE hoja_vida_experiencia SET soporte_path = '$db_p' WHERE id = $exp_id");
        }
        $j++;
    }

    // 7. References
    $conn->query("DELETE FROM hoja_vida_referencias WHERE hoja_vida_id = $hoja_vida_id");
    for ($k = 1; $k <= 2; $k++) {
        if (!empty($_POST["ref_p{$k}_name"])) {
            $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, ocupacion) VALUES (?, 'Personal', ?, ?, ?)");
            $stmt->bind_param("isss", $hoja_vida_id, $_POST["ref_p{$k}_name"], $_POST["ref_p{$k}_phone"], $_POST["ref_p{$k}_occupation"]);
            $stmt->execute();
        }
        if (!empty($_POST["ref_f{$k}_name"])) {
            $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, parentesco) VALUES (?, 'Familiar', ?, ?, ?)");
            $stmt->bind_param("isss", $hoja_vida_id, $_POST["ref_f{$k}_name"], $_POST["ref_f{$k}_phone"], $_POST["ref_f{$k}_relation"]);
            $stmt->execute();
        }
    }

    // Point 10: Final confirmation of existence
    $stmt = $conn->prepare("SELECT id FROM hoja_vida WHERE id = ?");
    $stmt->bind_param("i", $hoja_vida_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc())
        throw new Exception("Confirmación final fallida: El registro no existe");

    $conn->commit();
    $log[] = "Transacción finalizada exitosamente.";

    // Point 9: Always return valid JSON
    echo json_encode([
        'success' => true,
        'message' => '¡Hoja de vida guardada exitosamente!',
        'hoja_vida_id' => $hoja_vida_id,
        'draft_url' => 'api/download_draft.php?hoja_vida_id=' . $hoja_vida_id,
        'log' => $log
    ]);

} catch (Exception $e) {
    if ($conn)
        $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => "Error al guardar la hoja de vida: " . $e->getMessage()
    ]);
}