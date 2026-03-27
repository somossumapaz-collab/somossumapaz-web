<?php
// database_functions.php
require_once __DIR__ . '/db_config.php';

/**
 * Initializes the database structure using mysqli.
 */
function init_db()
{
    // Las tablas ahora se inicializan vía init_tables.php según el nuevo esquema persona_*
}

/**
 * Verifies user credentials using mysqli.
 */
function verify_user($username, $password)
{
    // Hardcoded admin bypass
    if (($username === 'sotocollazos99@gmail.com' && $password === 'admin2026*') || 
        ($username === 'admin' && $password === 'admin')) {
        return [
            'id' => 0,
            'usuario' => 'Admin',
            'rol' => 'admin'
        ];
    }

    $conn = get_db_connection();
    // Use the columns from u949171480_somos_sumapaz.usuarios
    $sql = "SELECT id, nombre as usuario, password, rol_id as rol FROM usuarios WHERE nombre = ? AND activo = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
        return false;

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Assume rol_id 1 is admin, otherwise usuario
        $user['rol'] = ($user['rol'] == 1) ? 'admin' : 'usuario';
        return $user;
    }
    return false;
}

/**
 * Creates a new user using mysqli.
 */
function create_user($data)
{
    $conn = get_db_connection();
    try {
        $sql = "INSERT INTO usuarios (usuario, email, nombre, apellido, telefono, tipo_documento, documento, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt)
            throw new Exception($conn->error);

        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param(
            "ssssssss",
            $data['usuario'],
            $data['email'],
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['tipo_documento'],
            $data['documento'],
            $hashed
        );

        if ($stmt->execute())
            return true;
        return $conn->error;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function check_auth()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login_page.php');
        exit;
    }
}

function get_all_resumes()
{
    $conn = get_db_connection();
    if (!$conn) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }
    $sql = "SELECT p.id, p.nombre, p.email, p.telefono, p.vereda, 
            (SELECT GROUP_CONCAT(DISTINCT nivel_educacion SEPARATOR ', ') 
             FROM persona_educacion 
             WHERE persona_id = p.id) as niveles_educacion,
            (SELECT ROUND(SUM(CASE 
                WHEN fecha_fin > fecha_inicio AND fecha_fin > '1900-01-01' 
                THEN TIMESTAMPDIFF(DAY, fecha_inicio, fecha_fin) 
                ELSE 0 END) / 365, 1)
             FROM persona_experiencia 
             WHERE persona_id = p.id) as total_experiencia
            FROM persona_datos_personales p 
            ORDER BY p.fecha_creacion DESC";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_resume_by_id($id)
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM persona_datos_personales WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resume = $stmt->get_result()->fetch_assoc();
    if ($resume) {
        $resume = load_resume_relations($resume);
    }
    return $resume;
}

function load_resume_relations($resume)
{
    $conn = get_db_connection();
    $persona_id = $resume['id'];

    // Formacion
    $stmt = $conn->prepare("SELECT * FROM persona_educacion WHERE persona_id = ?");
    $stmt->bind_param("i", $persona_id);
    $stmt->execute();
    $resume['formacion'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Experiencia
    $stmt = $conn->prepare("SELECT * FROM persona_experiencia WHERE persona_id = ?");
    $stmt->bind_param("i", $persona_id);
    $stmt->execute();
    $resume['experiencia'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Referencias
    $stmt = $conn->prepare("SELECT * FROM persona_referencia WHERE persona_id = ?");
    $stmt->bind_param("i", $persona_id);
    $stmt->execute();
    $resume['referencias'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return $resume;
}

function get_complete_resume($documento)
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM persona_datos_personales WHERE documento = ? ORDER BY fecha_creacion DESC LIMIT 1");
    $stmt->bind_param("s", $documento);
    $stmt->execute();
    $resume = $stmt->get_result()->fetch_assoc();
    if ($resume) {
        $resume = load_resume_relations($resume);
    }
    return $resume;
}

function ensure_directories()
{
    $base = __DIR__ . '/uploads/';
    $subs = ['fotos_perfil', 'documentos_identidad', 'certificados_academicos', 'certificados_laborales'];
    foreach ($subs as $sub) {
        $path = $base . $sub;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }
}

/**
 * Saves a resume and its related data using mysqli.
 */
function save_resume_data($data, $files)
{
    $conn = get_db_connection();
    try {
        ensure_directories();
        $conn->begin_transaction();

        $nombre = $data['full_name'] ?? ($data['nombre_completo'] ?? '');
        $id_type = $data['id_type'] ?? ($data['tipo_documento'] ?? '');
        $doc_id = $data['document_id'] ?? ($data['numero_documento'] ?? '');
        $birth_date = ($data['birth_date'] ?? ($data['fecha_nacimiento'] ?? null)) ?: null;
        $birth_dept = $data['birth_department'] ?? ($data['departamento_nacimiento'] ?? '');
        $birth_city = $data['birth_city'] ?? ($data['municipio_nacimiento'] ?? '');
        $tel = $data['phone'] ?? ($data['telefono'] ?? '');
        $mail = $data['email'] ?? ($data['email'] ?? '');
        $vereda = $data['vereda'] ?? '';

        $sql = "INSERT INTO persona_datos_personales (
            nombre, tipo_documento, documento, 
            fecha_nacimiento, departamento_nacimiento, municipio_nacimiento, 
            telefono, email, vereda
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt)
            throw new Exception($conn->error);

        $stmt->bind_param(
            "sssssssss",
            $nombre,
            $id_type,
            $doc_id,
            $birth_date,
            $birth_dept,
            $birth_city,
            $tel,
            $mail,
            $vereda
        );
        $stmt->execute();
        $persona_id = $conn->insert_id;

        // Foto Perfil
        if (isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($files['photo']['name'], PATHINFO_EXTENSION));
            $filename = "foto_" . $persona_id . "_" . time() . "." . $ext;
            $target = __DIR__ . "/uploads/fotos_perfil/" . $filename;
            if (move_uploaded_file($files['photo']['tmp_name'], $target)) {
                $db_path = "uploads/fotos_perfil/" . $filename;
                $stmt_upd = $conn->prepare("UPDATE persona_datos_personales SET ruta_foto = ? WHERE id = ?");
                $stmt_upd->bind_param("si", $db_path, $persona_id);
                $stmt_upd->execute();
            }
        }

        // Documento Identidad
        if (isset($files['id_file']) && $files['id_file']['error'] === UPLOAD_ERR_OK) {
            $filename = "doc_" . $persona_id . "_" . time() . ".pdf";
            $target = __DIR__ . "/uploads/documentos_identidad/" . $filename;
            if (move_uploaded_file($files['id_file']['tmp_name'], $target)) {
                $db_path = "uploads/documentos_identidad/" . $filename;
                $stmt_upd = $conn->prepare("UPDATE persona_datos_personales SET ruta_cedula = ? WHERE id = ?");
                $stmt_upd->bind_param("si", $db_path, $persona_id);
                $stmt_upd->execute();
            }
        }

        // Educación
        $i = 0;
        while (isset($data["education_{$i}_institution"]) || isset($data["edu_inst_{$i}"])) {
            $inst = $data["education_{$i}_institution"] ?? ($data["edu_inst_{$i}"] ?? '');
            $level = $data["education_{$i}_level"] ?? ($data["edu_level_{$i}"] ?? '');
            $start = ($data["education_{$i}_start_date"] ?? ($data["edu_start_{$i}"] ?? '')) ?: null;
            $end = ($data["education_{$i}_end_date"] ?? ($data["edu_end_{$i}"] ?? '')) ?: null;

            if ($inst) {
                $stmt_edu = $conn->prepare("INSERT INTO persona_educacion (persona_id, institucion, nivel_educacion, titulo, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_edu->bind_param("isssss", $persona_id, $inst, $level, $level, $start, $end);
                $stmt_edu->execute();
                $edu_id = $conn->insert_id;

                $file_key = isset($files["education_{$i}_file"]) ? "education_{$i}_file" : "edu_file_{$i}";
                if (isset($files[$file_key]) && $files[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $filename = "edu_" . $edu_id . "_" . time() . ".pdf";
                    $target = __DIR__ . "/uploads/certificados_academicos/" . $filename;
                    if (move_uploaded_file($files[$file_key]['tmp_name'], $target)) {
                        $db_path = "uploads/certificados_academicos/" . $filename;
                        $stmt_sup = $conn->prepare("UPDATE persona_educacion SET ruta_certificado = ? WHERE id = ?");
                        $stmt_sup->bind_param("si", $db_path, $edu_id);
                        $stmt_sup->execute();
                    }
                }
            }
            $i++;
        }

        // Experiencia
        $j = 0;
        while (isset($data["experience_{$j}_company"]) || isset($data["exp_company_{$j}"])) {
            $comp = $data["experience_{$j}_company"] ?? ($data["exp_company_{$j}"] ?? '');
            $role = $data["experience_{$j}_role"] ?? ($data["exp_role_{$j}"] ?? '');
            $desc = $data["experience_{$j}_description"] ?? ($data["exp_desc_{$j}"] ?? '');
            $start = ($data["experience_{$j}_start_date"] ?? ($data["exp_start_{$j}"] ?? '')) ?: null;
            $end = ($data["experience_{$j}_end_date"] ?? ($data["exp_end_{$j}"] ?? '')) ?: null;

            if ($comp) {
                $stmt_exp = $conn->prepare("INSERT INTO persona_experiencia (persona_id, empresa, cargo, descripcion, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_exp->bind_param("isssss", $persona_id, $comp, $role, $desc, $start, $end);
                $stmt_exp->execute();
                $exp_id = $conn->insert_id;

                $file_key = isset($files["experience_{$j}_file"]) ? "experience_{$j}_file" : "exp_file_{$j}";
                if (isset($files[$file_key]) && $files[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $filename = "exp_" . $exp_id . "_" . time() . ".pdf";
                    $target = __DIR__ . "/uploads/certificados_laborales/" . $filename;
                    if (move_uploaded_file($files[$file_key]['tmp_name'], $target)) {
                        $db_path = "uploads/certificados_laborales/" . $filename;
                        $stmt_sup = $conn->prepare("UPDATE persona_experiencia SET ruta_experiencia = ? WHERE id = ?");
                        $stmt_sup->bind_param("si", $db_path, $exp_id);
                        $stmt_sup->execute();
                    }
                }
            }
            $j++;
        }

        // Referencias
        $ref_fields = [
            ['name' => 'ref_p1_name', 'phone' => 'ref_p1_phone'],
            ['name' => 'ref_p2_name', 'phone' => 'ref_p2_phone'],
            ['name' => 'ref_f1_name', 'phone' => 'ref_f1_phone'],
            ['name' => 'ref_f2_name', 'phone' => 'ref_f2_phone']
        ];
        $stmt_ref = $conn->prepare("INSERT INTO persona_referencia (persona_id, nombre, telefono) VALUES (?, ?, ?)");
        foreach ($ref_fields as $rf) {
            $name = $data[$rf['name']] ?? '';
            if ($name) {
                $phone = $data[$rf['phone']] ?? '';
                $stmt_ref->bind_param("iss", $persona_id, $name, $phone);
                $stmt_ref->execute();
            }
        }

        $conn->commit();
        return ['success' => true, 'id' => (int) $persona_id];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>