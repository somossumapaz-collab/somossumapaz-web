<?php
// api/submit_resume.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';

// Verificar autenticación
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
    $conn->begin_transaction();

    // 1. Recopilar datos básicos de hoja_vida
    $profesion = $_POST['niche'] ?? ''; // Usamos el nicho como profesión inicial
    $descripcion_perfil = $_POST['profile_description'] ?? '';

    // Buscar si ya existe una hoja de vida para este usuario
    $stmt = $conn->prepare("SELECT id FROM hoja_vida WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hv = $result->fetch_assoc();

    if ($hv) {
        $hoja_vida_id = $hv['id'];
        $stmt = $conn->prepare("UPDATE hoja_vida SET profesion = ?, descripcion_perfil = ? WHERE id = ?");
        $stmt->bind_param("ssi", $profesion, $descripcion_perfil, $hoja_vida_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO hoja_vida (usuario_id, profesion, descripcion_perfil) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $profesion, $descripcion_perfil);
        $stmt->execute();
        $hoja_vida_id = $conn->insert_id;
    }

    // 2. Manejar Foto de Perfil
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $filename = "foto_usuario_" . $hoja_vida_id . "." . $ext;
            $target = "../uploads/fotos_perfil/" . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $db_path = "uploads/fotos_perfil/" . $filename;
                $stmt = $conn->prepare("UPDATE hoja_vida SET foto_perfil_path = ? WHERE id = ?");
                $stmt->bind_param("si", $db_path, $hoja_vida_id);
                $stmt->execute();
            }
        }
    }

    // 3. Manejar Documento de Identidad
    if (isset($_FILES['id_file']) && $_FILES['id_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['id_file']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $num_doc = $_POST['document_id'] ?? 'unknown';
            $filename = "documento_" . $num_doc . ".pdf";
            $target = "../uploads/documentos_identidad/" . $filename;
            if (move_uploaded_file($_FILES['id_file']['tmp_name'], $target)) {
                $db_path = "uploads/documentos_identidad/" . $filename;
                $stmt = $conn->prepare("UPDATE hoja_vida SET documento_identidad_path = ? WHERE id = ?");
                $stmt->bind_param("si", $db_path, $hoja_vida_id);
                $stmt->execute();
            }
        }
    }

    // 4. Habilidades
    $skills_raw = $_POST['skills'] ?? '';
    if (!empty($skills_raw)) {
        // Limpiar habilidades previas
        $stmt = $conn->prepare("DELETE FROM hoja_vida_habilidades WHERE hoja_vida_id = ?");
        $stmt->bind_param("i", $hoja_vida_id);
        $stmt->execute();

        $skills_array = explode(',', $skills_raw);
        $stmt = $conn->prepare("INSERT INTO hoja_vida_habilidades (hoja_vida_id, habilidad) VALUES (?, ?)");
        foreach ($skills_array as $skill) {
            $skill_name = trim($skill);
            if (!empty($skill_name)) {
                $stmt->bind_param("is", $hoja_vida_id, $skill_name);
                $stmt->execute();
            }
        }
    }

    // 5. Formación Académica
    // Limpiar previas para re-insertar o simplificar
    $conn->query("DELETE FROM hoja_vida_formacion WHERE hoja_vida_id = $hoja_vida_id");
    $edu_index = 0;
    while (isset($_POST["education_{$edu_index}_institution"])) {
        $nivel = $_POST["education_{$edu_index}_level"] ?? '';
        $inst = $_POST["education_{$edu_index}_institution"] ?? '';
        $inicio = $_POST["education_{$edu_index}_start_date"] ?? null;
        $fin = $_POST["education_{$edu_index}_end_date"] ?? null;
        $en_curso = isset($_POST["education_{$edu_index}_is_current"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, nivel_educativo, institucion, fecha_inicio, fecha_fin, en_curso) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $hoja_vida_id, $nivel, $inst, $inicio, $fin, $en_curso);
        $stmt->execute();
        $edu_id = $conn->insert_id;

        // Soporte de formación
        if (isset($_FILES["education_{$edu_index}_file"]) && $_FILES["education_{$edu_index}_file"]['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES["education_{$edu_index}_file"]['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $filename = "formacion_" . $edu_id . ".pdf";
                $target = "../uploads/certificados_academicos/" . $filename;
                if (move_uploaded_file($_FILES["education_{$edu_index}_file"]['tmp_name'], $target)) {
                    $db_path = "uploads/certificados_academicos/" . $filename;
                    $stmt = $conn->prepare("UPDATE hoja_vida_formacion SET soporte_path = ? WHERE id = ?");
                    $stmt->bind_param("si", $db_path, $edu_id);
                    $stmt->execute();
                }
            }
        }
        $edu_index++;
    }

    // 6. Experiencia Laboral
    $conn->query("DELETE FROM hoja_vida_experiencia WHERE hoja_vida_id = $hoja_vida_id");
    $exp_index = 0;
    while (isset($_POST["experience_{$exp_index}_company"])) {
        $cargo = $_POST["experience_{$exp_index}_role"] ?? '';
        $empresa = $_POST["experience_{$exp_index}_company"] ?? '';
        $inicio = $_POST["experience_{$exp_index}_start_date"] ?? null;
        $fin = $_POST["experience_{$exp_index}_end_date"] ?? null;
        $actual = isset($_POST["experience_{$exp_index}_is_current"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, cargo, empresa, fecha_inicio, fecha_fin, actualmente) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $hoja_vida_id, $cargo, $empresa, $inicio, $fin, $actual);
        $stmt->execute();
        $exp_id = $conn->insert_id;

        // Soporte de experiencia
        if (isset($_FILES["experience_{$exp_index}_file"]) && $_FILES["experience_{$exp_index}_file"]['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES["experience_{$exp_index}_file"]['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $filename = "experiencia_" . $exp_id . ".pdf";
                $target = "../uploads/certificados_laborales/" . $filename;
                if (move_uploaded_file($_FILES["experience_{$exp_index}_file"]['tmp_name'], $target)) {
                    $db_path = "uploads/certificados_laborales/" . $filename;
                    $stmt = $conn->prepare("UPDATE hoja_vida_experiencia SET soporte_path = ? WHERE id = ?");
                    $stmt->bind_param("si", $db_path, $exp_id);
                    $stmt->execute();
                }
            }
        }
        $exp_index++;
    }

    // 7. Referencias (Personales y Familiares)
    $conn->query("DELETE FROM hoja_vida_referencias WHERE hoja_vida_id = $hoja_vida_id");

    // Referencias Personales (P1, P2)
    for ($i = 1; $i <= 2; $i++) {
        $nombre = $_POST["ref_p{$i}_name"] ?? '';
        $tel = $_POST["ref_p{$i}_phone"] ?? '';
        $ocu = $_POST["ref_p{$i}_occupation"] ?? '';
        if (!empty($nombre)) {
            $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, ocupacion) VALUES (?, 'Personal', ?, ?, ?)");
            $stmt->bind_param("isss", $hoja_vida_id, $nombre, $tel, $ocu);
            $stmt->execute();
        }
    }

    // Referencias Familiares (F1, F2)
    for ($i = 1; $i <= 2; $i++) {
        $nombre = $_POST["ref_f{$i}_name"] ?? '';
        $tel = $_POST["ref_f{$i}_phone"] ?? '';
        $rel = $_POST["ref_f{$i}_relation"] ?? '';
        if (!empty($nombre)) {
            $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, parentesco) VALUES (?, 'Familiar', ?, ?, ?)");
            $stmt->bind_param("isss", $hoja_vida_id, $nombre, $tel, $rel);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Hoja de vida guardada correctamente', 'resume_id' => $hoja_vida_id]);

} catch (Exception $e) {
    if ($conn)
        $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>