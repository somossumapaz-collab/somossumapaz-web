<?php
// database_functions.php
require_once 'db_config.php';

function init_db()
{
    $conn = get_db_connection();
    if (!$conn)
        return;

    $conn->query("CREATE TABLE IF NOT EXISTS resumes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name TEXT NOT NULL,
        document_id TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        skills TEXT,
        experience TEXT,
        birth_date TEXT,
        city TEXT,
        department TEXT,
        profile_description TEXT,
        photo_path TEXT,
        document_path TEXT,
        diploma_path TEXT,
        id_type TEXT,
        niche TEXT,
        birth_department TEXT,
        birth_city TEXT,
        id_file_path TEXT,
        personal_references_json TEXT,
        family_references_json TEXT
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS education (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resume_id INT,
        level TEXT,
        institution TEXT,
        start_date TEXT,
        end_date TEXT,
        is_current TINYINT(1),
        certificate_path TEXT,
        FOREIGN KEY(resume_id) REFERENCES resumes(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS experience (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resume_id INT,
        role TEXT,
        company TEXT,
        start_date TEXT,
        end_date TEXT,
        is_current TINYINT(1),
        certificate_path TEXT,
        FOREIGN KEY(resume_id) REFERENCES resumes(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100),
        apellido VARCHAR(100),
        telefono VARCHAR(30),
        documento VARCHAR(50),
        tipo_documento VARCHAR(50),
        rol VARCHAR(20) DEFAULT 'usuario',
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        activo TINYINT(1) DEFAULT 1
    )");

    // Default admin user
    $checkAdmin = $conn->query("SELECT id FROM usuarios WHERE usuario = 'admin'");
    if ($checkAdmin->num_rows === 0) {
        $pass = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, email, password, rol) VALUES ('admin', 'admin@somossumapaz.com', ?, 'admin')");
        $stmt->bind_param("s", $pass);
        $stmt->execute();
    }
}

function verify_user($identifier, $password)
{
    $conn = get_db_connection();
    if (!$conn)
        return null;

    // Check by username OR email and ensure active = 1
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE (usuario = ? OR email = ?) AND activo = 1");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return null;
}

function create_user($data)
{
    $conn = get_db_connection();
    if (!$conn)
        return "Error de conexión a la base de datos";

    try {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, email, password, nombre, apellido, telefono, documento, tipo_documento, rol, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'usuario', 1)");

        $stmt->bind_param(
            "ssssssss",
            $data['usuario'],
            $data['email'],
            $hashed_password,
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['documento'],
            $data['tipo_documento']
        );

        if (!$stmt->execute()) {
            return $stmt->error;
        }
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Proteger páginas privadas.
 * Redirige a login_page.php si no hay sesión activa.
 */
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

function add_resume_comprehensive($data, $education_list = [], $experience_list = [])
{
    $conn = get_db_connection();
    if (!$conn)
        return null;

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO resumes (
            full_name, document_id, email, phone, skills, experience,
            birth_date, city, department, profile_description,
            photo_path, id_file_path, id_type, niche, birth_department, 
            birth_city, personal_references_json, family_references_json
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $full_name = $data['full_name'] ?? null;
        $document_id = $data['document_id'] ?? null;
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $skills = $data['skills'] ?? '';
        $exp_summary = $data['experience_summary'] ?? '';
        $birth_date = $data['birth_date'] ?? null;
        $city = $data['city'] ?? null;
        $dept = $data['department'] ?? null;
        $prof_desc = $data['profile_description'] ?? null;
        $photo = $data['photo'] ?? null;
        $id_file = $data['id_file_path'] ?? null;
        $id_type = $data['id_type'] ?? null;
        $niche = $data['niche'] ?? null;
        $birth_dept = $data['birth_department'] ?? null;
        $birth_city = $data['birth_city'] ?? null;
        $personal_refs = $data['personal_references_json'] ?? '[]';
        $family_refs = $data['family_references_json'] ?? '[]';

        $stmt->bind_param(
            "ssssssssssssssssss",
            $full_name,
            $document_id,
            $email,
            $phone,
            $skills,
            $exp_summary,
            $birth_date,
            $city,
            $dept,
            $prof_desc,
            $photo,
            $id_file,
            $id_type,
            $niche,
            $birth_dept,
            $birth_city,
            $personal_refs,
            $family_refs
        );
        $stmt->execute();

        $resume_id = $conn->insert_id;

        foreach ($education_list as $edu) {
            $stmt_edu = $conn->prepare("INSERT INTO education (resume_id, level, institution, start_date, end_date, is_current, certificate_path)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $is_current = $edu['is_current'] ? 1 : 0;
            $stmt_edu->bind_param(
                "issssss",
                $resume_id,
                $edu['level'],
                $edu['institution'],
                $edu['start_date'],
                $edu['end_date'],
                $is_current,
                $edu['certificate_path']
            );
            $stmt_edu->execute();
        }

        foreach ($experience_list as $exp) {
            $stmt_exp = $conn->prepare("INSERT INTO experience (resume_id, role, company, start_date, end_date, is_current, certificate_path)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $is_current_exp = $exp['is_current'] ? 1 : 0;
            $stmt_exp->bind_param(
                "issssss",
                $resume_id,
                $exp['role'],
                $exp['company'],
                $exp['start_date'],
                $exp['end_date'],
                $is_current_exp,
                $exp['certificate_path']
            );
            $stmt_exp->execute();
        }

        $conn->commit();
        return $resume_id;
    } catch (Exception $e) {
        $conn->rollback();
        return null;
    }
}

function get_all_resumes()
{
    $conn = get_db_connection();
    if (!$conn)
        return [];

    $result = $conn->query("SELECT * FROM resumes ORDER BY id DESC");
    $resumes = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($resumes as &$resume) {
        $stmt_edu = $conn->prepare("SELECT * FROM education WHERE resume_id = ?");
        $stmt_edu->bind_param("i", $resume['id']);
        $stmt_edu->execute();
        $resume['education'] = $stmt_edu->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt_exp = $conn->prepare("SELECT * FROM experience WHERE resume_id = ?");
        $stmt_exp->bind_param("i", $resume['id']);
        $stmt_exp->execute();
        $resume['experience'] = $stmt_exp->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    return $resumes;
}
?>