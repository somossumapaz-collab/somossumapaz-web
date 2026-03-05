<?php
// api/save_section.php
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once '../database_functions.php';
require_once '../helpers/logger.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$user_id = $_SESSION['user_id'];
$section = $_POST['section'] ?? '';
$data = $_POST; // Contains the specific section data

$conn = get_db_connection();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión']);
    exit;
}

try {
    $conn->begin_transaction();
    $hoja_vida_id = null;

    // Get or create base hoja_vida_id
    $stmt = $conn->prepare("SELECT id FROM hoja_vida WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $hv = $stmt->get_result()->fetch_assoc();
    if ($hv) {
        $hoja_vida_id = $hv['id'];
    } else {
        $stmt = $conn->prepare("INSERT INTO hoja_vida (usuario_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $hoja_vida_id = $conn->insert_id;
    }

    switch ($section) {
        case 'personal':
            $nombre = $data['full_name'] ?? '';
            $id_type = $data['id_type'] ?? '';
            $doc_id = $data['document_id'] ?? '';
            $b_date = $data['birth_date'] ?? null;
            $b_dept = $data['birth_department'] ?? '';
            $b_city = $data['birth_city'] ?? '';
            $dept = $data['department'] ?? '';
            $city = $data['city'] ?? '';
            $phone = $data['phone'] ?? '';
            $email = $data['email'] ?? '';
            $perfil = $data['profile_description'] ?? '';

            $stmt = $conn->prepare("UPDATE hoja_vida SET 
                nombre_completo = ?, tipo_documento = ?, numero_documento = ?, 
                fecha_nacimiento = ?, departamento_nacimiento = ?, municipio_nacimiento = ?, 
                departamento_residencia = ?, municipio_residencia = ?, telefono = ?, 
                email = ?, perfil_profesional = ? 
                WHERE id = ?");
            $stmt->bind_param(
                "sssssssssssi",
                $nombre,
                $id_type,
                $doc_id,
                $b_date,
                $b_dept,
                $b_city,
                $dept,
                $city,
                $phone,
                $email,
                $perfil,
                $hoja_vida_id
            );
            if (!$stmt->execute())
                throw new Exception("Error al guardar info personal");

            // Also update usuarios table
            $stmt_u = $conn->prepare("UPDATE usuarios SET email = ?, telefono = ?, documento = ?, tipo_documento = ? WHERE id = ?");
            $stmt_u->bind_param("ssssi", $email, $phone, $doc_id, $id_type, $user_id);
            $stmt_u->execute();

            log_resume_event("Sección PERSONAL guardada para Hoja de Vida ID: $hoja_vida_id");
            break;

        case 'skills':
            $conn->query("DELETE FROM hoja_vida_habilidades WHERE hoja_vida_id = $hoja_vida_id");
            $skills_str = $data['skills'] ?? '';
            if (!empty($skills_str)) {
                $skills_arr = explode(',', $skills_str);
                $stmt = $conn->prepare("INSERT INTO hoja_vida_habilidades (hoja_vida_id, habilidad) VALUES (?, ?)");
                foreach ($skills_arr as $s) {
                    $val = trim($s);
                    if ($val) {
                        $stmt->bind_param("is", $hoja_vida_id, $val);
                        $stmt->execute();
                    }
                }
            }
            log_resume_event("Sección SKILLS guardada para Hoja de Vida ID: $hoja_vida_id");
            break;

        case 'education':
            // Recibe JSON o array de items
            $items = json_decode($data['items'], true);
            $conn->query("DELETE FROM hoja_vida_formacion WHERE hoja_vida_id = $hoja_vida_id");
            if (is_array($items)) {
                foreach ($items as $item) {
                    $stmt = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, nivel_educativo, institucion, fecha_inicio, fecha_fin, en_curso, soporte_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssis", $hoja_vida_id, $item['level'], $item['institution'], $item['start_date'], $item['end_date'], $item['is_current'], $item['file_path']);
                    $stmt->execute();
                }
            }
            log_resume_event("Sección EDUCATION guardada para Hoja de Vida ID: $hoja_vida_id");
            break;

        case 'experience':
            $items = json_decode($data['items'], true);
            $conn->query("DELETE FROM hoja_vida_experiencia WHERE hoja_vida_id = $hoja_vida_id");
            if (is_array($items)) {
                foreach ($items as $item) {
                    $stmt = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, cargo, empresa, fecha_inicio, fecha_fin, actualmente, soporte_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssis", $hoja_vida_id, $item['role'], $item['company'], $item['start_date'], $item['end_date'], $item['is_current'], $item['file_path']);
                    $stmt->execute();
                }
            }
            log_resume_event("Sección EXPERIENCE guardada para Hoja de Vida ID: $hoja_vida_id");
            break;

        case 'references':
            $items = json_decode($data['items'], true);
            $conn->query("DELETE FROM hoja_vida_referencias WHERE hoja_vida_id = $hoja_vida_id");
            if (is_array($items)) {
                foreach ($items as $item) {
                    $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, ocupacion, parentesco) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssss", $hoja_vida_id, $item['type'], $item['name'], $item['phone'], $item['occupation'], $item['relation']);
                    $stmt->execute();
                }
            }
            log_resume_event("Sección REFERENCES guardada para Hoja de Vida ID: $hoja_vida_id");
            break;

        default:
            throw new Exception("Sección '$section' no válida");
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => "Sección $section guardada correctamente", 'hoja_vida_id' => $hoja_vida_id]);

} catch (Exception $e) {
    if ($conn)
        $conn->rollback();
    log_resume_event("Error guardando sección $section: " . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
