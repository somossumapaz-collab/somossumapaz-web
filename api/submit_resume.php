<?php
// api/submit_resume.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';

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
    ensure_directories();
    $conn->begin_transaction();

    // 1. Hoja de Vida Base
    $profesion = $_POST['niche'] ?? '';
    $descripcion_perfil = $_POST['profile_description'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM hoja_vida WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $hv = $stmt->get_result()->fetch_assoc();

    if ($hv) {
        $hoja_vida_id = $hv['id'];
        $stmt = $conn->prepare("UPDATE hoja_vida SET profesion = ?, descripcion_perfil = ? WHERE id = ?");
        $stmt->bind_param("ssi", $profesion, $descripcion_perfil, $hoja_vida_id);
        if (!$stmt->execute())
            throw new Exception("Error al actualizar hoja_vida: " . $stmt->error);
    } else {
        $stmt = $conn->prepare("INSERT INTO hoja_vida (usuario_id, profesion, descripcion_perfil) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $profesion, $descripcion_perfil);
        if (!$stmt->execute())
            throw new Exception("Error al insertar hoja_vida: " . $stmt->error);
        $hoja_vida_id = $conn->insert_id;
    }

    // 2. Foto de Perfil
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png']))
            throw new Exception("Formato de foto no permitido");

        $filename = "foto_usuario_" . $hoja_vida_id . "." . $ext;
        $target = "../uploads/fotos_perfil/" . $filename;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target))
            throw new Exception("Error al mover foto de perfil");

        $db_path = "uploads/fotos_perfil/" . $filename;
        $stmt = $conn->prepare("UPDATE hoja_vida SET foto_perfil_path = ? WHERE id = ?");
        $stmt->bind_param("si", $db_path, $hoja_vida_id);
        if (!$stmt->execute())
            throw new Exception("Error al actualizar ruta de foto");
    }

    // 3. Documento ID
    if (isset($_FILES['id_file']) && $_FILES['id_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['id_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf')
            throw new Exception("El documento de identidad debe ser PDF");

        $num_doc = $_POST['document_id'] ?? 'unknown';
        $filename = "documento_" . $num_doc . ".pdf";
        $target = "../uploads/documentos_identidad/" . $filename;
        if (!move_uploaded_file($_FILES['id_file']['tmp_name'], $target))
            throw new Exception("Error al mover documento ID");

        $db_path = "uploads/documentos_identidad/" . $filename;
        $stmt = $conn->prepare("UPDATE hoja_vida SET documento_identidad_path = ? WHERE id = ?");
        $stmt->bind_param("si", $db_path, $hoja_vida_id);
        if (!$stmt->execute())
            throw new Exception("Error al actualizar ruta de documento");
    }

    // 4. Habilidades
    $stmt = $conn->prepare("DELETE FROM hoja_vida_habilidades WHERE hoja_vida_id = ?");
    $stmt->bind_param("i", $hoja_vida_id);
    $stmt->execute();

    $skills_raw = $_POST['skills'] ?? '';
    if (!empty($skills_raw)) {
        $skills_array = explode(',', $skills_raw);
        $stmt = $conn->prepare("INSERT INTO hoja_vida_habilidades (hoja_vida_id, habilidad) VALUES (?, ?)");
        foreach ($skills_array as $skill) {
            $name = trim($skill);
            if ($name) {
                $stmt->bind_param("is", $hoja_vida_id, $name);
                if (!$stmt->execute())
                    throw new Exception("Error al insertar habilidad: $name");
            }
        }
    }

    // 5. Formación
    $conn->query("DELETE FROM hoja_vida_formacion WHERE hoja_vida_id = $hoja_vida_id");
    $i = 0;
    while (isset($_POST["education_{$i}_institution"])) {
        $nivel = $_POST["education_{$i}_level"] ?? '';
        $inst = $_POST["education_{$i}_institution"] ?? '';
        $ini = $_POST["education_{$i}_start_date"] ?? null;
        $fin = $_POST["education_{$i}_end_date"] ?? null;
        $cur = isset($_POST["education_{$i}_is_current"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, nivel_educativo, institucion, fecha_inicio, fecha_fin, en_curso) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $hoja_vida_id, $nivel, $inst, $ini, $fin, $cur);
        if (!$stmt->execute())
            throw new Exception("Error al insertar formación $i");
        $edu_id = $conn->insert_id;

        if (isset($_FILES["education_{$i}_file"]) && $_FILES["education_{$i}_file"]['error'] === UPLOAD_ERR_OK) {
            $fname = "formacion_" . $edu_id . ".pdf";
            $target = "../uploads/certificados_academicos/" . $fname;
            if (!move_uploaded_file($_FILES["education_{$i}_file"]['tmp_name'], $target))
                throw new Exception("Error al mover certificado formación $i");
            $db_p = "uploads/certificados_academicos/" . $fname;
            $stmt = $conn->prepare("UPDATE hoja_vida_formacion SET soporte_path = ? WHERE id = ?");
            $stmt->bind_param("si", $db_p, $edu_id);
            $stmt->execute();
        }
        $i++;
    }

    // 6. Experiencia
    $conn->query("DELETE FROM hoja_vida_experiencia WHERE hoja_vida_id = $hoja_vida_id");
    $j = 0;
    while (isset($_POST["experience_{$j}_company"])) {
        $cargo = $_POST["experience_{$j}_role"] ?? '';
        $emp = $_POST["experience_{$j}_company"] ?? '';
        $ini = $_POST["experience_{$j}_start_date"] ?? null;
        $fin = $_POST["experience_{$j}_end_date"] ?? null;
        $act = isset($_POST["experience_{$j}_is_current"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, cargo, empresa, fecha_inicio, fecha_fin, actualmente) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $hoja_vida_id, $cargo, $emp, $ini, $fin, $act);
        if (!$stmt->execute())
            throw new Exception("Error al insertar experiencia $j");
        $exp_id = $conn->insert_id;

        if (isset($_FILES["experience_{$j}_file"]) && $_FILES["experience_{$j}_file"]['error'] === UPLOAD_ERR_OK) {
            $fname = "experiencia_" . $exp_id . ".pdf";
            $target = "../uploads/certificados_laborales/" . $fname;
            if (!move_uploaded_file($_FILES["experience_{$j}_file"]['tmp_name'], $target))
                throw new Exception("Error al mover certificado laboral $j");
            $db_p = "uploads/certificados_laborales/" . $fname;
            $stmt = $conn->prepare("UPDATE hoja_vida_experiencia SET soporte_path = ? WHERE id = ?");
            $stmt->bind_param("si", $db_p, $exp_id);
            $stmt->execute();
        }
        $j++;
    }

    // 7. Referencias
    $conn->query("DELETE FROM hoja_vida_referencias WHERE hoja_vida_id = $hoja_vida_id");
    for ($k = 1; $k <= 2; $k++) {
        $np = $_POST["ref_p{$k}_name"] ?? '';
        if ($np) {
            $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, ocupacion) VALUES (?, 'Personal', ?, ?, ?)");
            $stmt->bind_param("isss", $hoja_vida_id, $np, $_POST["ref_p{$k}_phone"], $_POST["ref_p{$k}_occupation"]);
            if (!$stmt->execute())
                throw new Exception("Error al insertar ref personal $k");
        }
        $nf = $_POST["ref_f{$k}_name"] ?? '';
        if ($nf) {
            $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, parentesco) VALUES (?, 'Familiar', ?, ?, ?)");
            $stmt->bind_param("isss", $hoja_vida_id, $nf, $_POST["ref_f{$k}_phone"], $_POST["ref_f{$k}_relation"]);
            if (!$stmt->execute())
                throw new Exception("Error al insertar ref familiar $k");
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => '¡Hoja de vida guardada exitosamente!', 'hoja_vida_id' => $hoja_vida_id]);

} catch (Exception $e) {
    if ($conn)
        $conn->rollback();
    error_log("Error crítico en submit_resume: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => "No se pudo guardar: " . $e->getMessage()]);
}
?>