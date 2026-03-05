<?php
// api/submit_resume.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database_functions.php';

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

function ensure_directories()
{
    $base = '../uploads/';
    $subs = ['fotos_perfil', 'documentos_identidad', 'certificados_academicos', 'certificados_laborales'];
    foreach ($subs as $sub) {
        $path = $base . $sub;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }
}

try {
    ensure_directories();
    $conn->begin_transaction();

    // 0. Extract variables
    $nombre = $_POST['full_name'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $doc_id = $_POST['document_id'] ?? '';
    $birth_date = $_POST['birth_date'] ?? null;
    $birth_country = $_POST['birth_country'] ?? 'Colombia';
    $birth_dept = $_POST['birth_department'] ?? '';
    $birth_city = $_POST['birth_city'] ?? '';
    $dept = $_POST['department'] ?? '';
    $city = $_POST['city'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $perfil = $_POST['profile_description'] ?? '';

    // 1. ALWAYS INSERT (Non-destructive)
    // We remove the unique constraint check and the UPDATE logic
    $stmt = $conn->prepare("INSERT INTO hoja_vida (
        usuario_id, nombre_completo, tipo_documento, numero_documento, 
        fecha_nacimiento, departamento_nacimiento, municipio_nacimiento, 
        departamento_residencia, municipio_residencia, telefono, email, perfil_profesional
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssssssss",
        $user_id,
        $nombre,
        $id_type,
        $doc_id,
        $birth_date,
        $birth_dept, // Birth location stored here
        $birth_city,
        $dept,       // Residence location
        $city,
        $phone,
        $email,
        $perfil
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar hoja_vida: " . $stmt->error);
    }
    $hoja_vida_id = $conn->insert_id;

    // 2. Profile Photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $filename = "foto_" . $hoja_vida_id . "_" . time() . "." . $ext;
        $target = "../uploads/fotos_perfil/" . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $db_path = "uploads/fotos_perfil/" . $filename;
            $conn->query("UPDATE hoja_vida SET foto_perfil_path = '$db_path' WHERE id = $hoja_vida_id");
        }
    }

    // 3. ID Document (PDF)
    if (isset($_FILES['id_file']) && $_FILES['id_file']['error'] === UPLOAD_ERR_OK) {
        $filename = "doc_" . $hoja_vida_id . "_" . time() . ".pdf";
        $target = "../uploads/documentos_identidad/" . $filename;
        if (move_uploaded_file($_FILES['id_file']['tmp_name'], $target)) {
            $db_path = "uploads/documentos_identidad/" . $filename;
            $conn->query("UPDATE hoja_vida SET documento_pdf_path = '$db_path' WHERE id = $hoja_vida_id");
        }
    }

    // 4. Skills (Mosaic)
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
    }

    // 5. Education
    $i = 0;
    while (isset($_POST["education_{$i}_institution"])) {
        $inst = $_POST["education_{$i}_institution"];
        $level = $_POST["education_{$i}_level"];
        $start = $_POST["education_{$i}_start_date"] ?: null;
        $end = $_POST["education_{$i}_end_date"] ?: null;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, institucion, nivel_educativo, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $hoja_vida_id, $inst, $level, $start, $end);
        $stmt->execute();
        $i++;
    }

    // 6. Experience
    $j = 0;
    while (isset($_POST["experience_{$j}_company"])) {
        $comp = $_POST["experience_{$j}_company"];
        $role = $_POST["experience_{$j}_role"];
        $start = $_POST["experience_{$j}_start_date"] ?: null;
        $end = $_POST["experience_{$j}_end_date"] ?: null;

        $stmt = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, empresa, cargo, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $hoja_vida_id, $comp, $role, $start, $end);
        $stmt->execute();
        $j++;
    }

    // 7. References
    $ref_fields = [
        ['name' => 'ref_p1_name', 'phone' => 'ref_p1_phone', 'occ' => 'ref_p1_occupation', 'type' => 'Personal'],
        ['name' => 'ref_p2_name', 'phone' => 'ref_p2_phone', 'occ' => 'ref_p2_occupation', 'type' => 'Personal'],
        ['name' => 'ref_f1_name', 'phone' => 'ref_f1_phone', 'relation' => 'ref_f1_relation', 'type' => 'Familiar'],
        ['name' => 'ref_f2_name', 'phone' => 'ref_f2_phone', 'relation' => 'ref_f2_relation', 'type' => 'Familiar']
    ];

    $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, ocupacion, parentesco) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($ref_fields as $rf) {
        $name = $_POST[$rf['name']] ?? '';
        if ($name) {
            $phone = $_POST[$rf['phone']] ?? '';
            $type = $rf['type'];
            $occ = $_POST[$rf['occ'] ?? ''] ?? '';
            $rel = $_POST[$rf['relation'] ?? ''] ?? '';
            $stmt->bind_param("isssss", $hoja_vida_id, $type, $name, $phone, $occ, $rel);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($conn)
        $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}