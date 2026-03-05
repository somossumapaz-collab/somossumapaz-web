<?php
// database_functions.php
require_once __DIR__ . '/db_config.php';

function init_db()
{
    $conn = get_db_connection();
    if (!$conn)
        return;

    $conn->query("CREATE TABLE IF NOT EXISTS hoja_vida (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nombre_completo VARCHAR(255),
        tipo_documento VARCHAR(50),
        numero_documento VARCHAR(50),
        fecha_nacimiento DATE,
        departamento_nacimiento VARCHAR(100),
        municipio_nacimiento VARCHAR(100),
        departamento_residencia VARCHAR(100),
        municipio_residencia VARCHAR(100),
        telefono VARCHAR(30),
        email VARCHAR(100),
        perfil_profesional TEXT,
        profesion VARCHAR(255),
        foto_perfil_path VARCHAR(255),
        documento_pdf_path VARCHAR(255),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");

    // Migration: Remove UNIQUE constraint if it exists (dropping and recreating index is safer in MySQL for this)
    $conn->query("ALTER TABLE hoja_vida DROP INDEX IF EXISTS usuario_id");

    // Migration: Add columns to hoja_vida if they don't exist
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS nombre_completo VARCHAR(255) AFTER usuario_id");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS tipo_documento VARCHAR(50) AFTER nombre_completo");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS numero_documento VARCHAR(50) AFTER tipo_documento");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS fecha_nacimiento DATE AFTER numero_documento");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS departamento_nacimiento VARCHAR(100) AFTER fecha_nacimiento");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS municipio_nacimiento VARCHAR(100) AFTER departamento_nacimiento");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS departamento_residencia VARCHAR(100) AFTER municipio_nacimiento");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS municipio_residencia VARCHAR(100) AFTER departamento_residencia");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS telefono VARCHAR(30) AFTER municipio_residencia");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER telefono");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS perfil_profesional TEXT AFTER email");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS profesion VARCHAR(255) AFTER perfil_profesional");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS foto_perfil_path VARCHAR(255) AFTER profesion");
    $conn->query("ALTER TABLE hoja_vida ADD COLUMN IF NOT EXISTS documento_pdf_path VARCHAR(255) AFTER foto_perfil_path");

    $conn->query("CREATE TABLE IF NOT EXISTS hoja_vida_habilidades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hoja_vida_id INT NOT NULL,
        habilidad VARCHAR(100),
        nivel VARCHAR(50),
        FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS hoja_vida_formacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hoja_vida_id INT NOT NULL,
        nivel_educativo VARCHAR(100),
        institucion VARCHAR(150),
        fecha_inicio DATE,
        fecha_fin DATE,
        en_curso TINYINT(1) DEFAULT 0,
        soporte_id VARCHAR(100),
        soporte_path VARCHAR(255),
        FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS hoja_vida_experiencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hoja_vida_id INT NOT NULL,
        cargo VARCHAR(150),
        empresa VARCHAR(150),
        descripcion_cargo TEXT,
        fecha_inicio DATE,
        fecha_fin DATE,
        actualmente TINYINT(1) DEFAULT 0,
        soporte_id VARCHAR(100),
        soporte_path VARCHAR(255),
        FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS hoja_vida_referencias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hoja_vida_id INT NOT NULL,
        tipo VARCHAR(50),
        nombre VARCHAR(150),
        telefono VARCHAR(30),
        ocupacion VARCHAR(150),
        parentesco VARCHAR(100),
        FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id)
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

/**
 * Obtener o crear hoja de vida para un usuario.
 */
function get_or_create_resume($usuario_id)
{
    $conn = get_db_connection();
    if (!$conn)
        return null;

    $stmt = $conn->prepare("SELECT * FROM hoja_vida WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resume = $result->fetch_assoc();

    if (!$resume) {
        $stmt = $conn->prepare("INSERT INTO hoja_vida (usuario_id) VALUES (?)");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $id = $conn->insert_id;
        return ['id' => $id, 'usuario_id' => $usuario_id];
    }

    return $resume;
}

/**
 * Agregar experiencia laboral.
 */
function add_experience($hoja_vida_id, $data)
{
    $conn = get_db_connection();
    if (!$conn)
        return false;

    $stmt = $conn->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, cargo, empresa, descripcion_cargo, fecha_inicio, fecha_fin, actualmente, soporte_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $actualmente = $data['actualmente'] ? 1 : 0;
    $stmt->bind_param("isssssis", $hoja_vida_id, $data['cargo'], $data['empresa'], $data['descripcion_cargo'], $data['fecha_inicio'], $data['fecha_fin'], $actualmente, $data['soporte_id']);
    return $stmt->execute();
}

/**
 * Agregar formación académica.
 */
function add_education($hoja_vida_id, $data)
{
    $conn = get_db_connection();
    if (!$conn)
        return false;

    $stmt = $conn->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, nivel_educativo, institucion, fecha_inicio, fecha_fin, en_curso, soporte_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $en_curso = $data['en_curso'] ? 1 : 0;
    $stmt->bind_param("issssis", $hoja_vida_id, $data['nivel_educativo'], $data['institucion'], $data['fecha_inicio'], $data['fecha_fin'], $en_curso, $data['soporte_id']);
    return $stmt->execute();
}

/**
 * Agregar referencia.
 */
function add_reference($hoja_vida_id, $data)
{
    $conn = get_db_connection();
    if (!$conn)
        return false;

    $stmt = $conn->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono, ocupacion, parentesco) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $hoja_vida_id, $data['tipo'], $data['nombre'], $data['telefono'], $data['ocupacion'], $data['parentesco']);
    return $stmt->execute();
}

/**
 * Obtener todas las hojas de vida con info básica del usuario para el Dashboard.
 */
function get_all_resumes()
{
    $conn = get_db_connection();
    if (!$conn)
        return [];

    // User's recommended SQL
    $sql = "SELECT 
                hv.id, 
                COALESCE(CONCAT_WS(' ', NULLIF(u.nombre, ''), NULLIF(u.apellido, '')), hv.nombre_completo, u.usuario) AS nombre, 
                hv.profesion AS nicho_cargo, 
                u.telefono, 
                u.email,
                hv.usuario_id
            FROM hoja_vida hv 
            JOIN usuarios u ON hv.usuario_id = u.id 
            ORDER BY hv.id DESC";

    $result = $conn->query($sql);
    $resumes = [];
    while ($row = $result->fetch_assoc()) {
        $resumes[] = $row;
    }
    return $resumes;
}

/**
 * Obtener hoja de vida completa por ID de Hoja de Vida.
 */
function get_resume_by_id($id)
{
    $conn = get_db_connection();
    if (!$conn)
        return null;

    $stmt = $conn->prepare("SELECT hv.*, u.email as user_email, u.nombre, u.apellido 
                           FROM hoja_vida hv 
                           JOIN usuarios u ON hv.usuario_id = u.id 
                           WHERE hv.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resume = $stmt->get_result()->fetch_assoc();

    if ($resume) {
        return load_resume_relations($resume);
    }
    return null;
}

/**
 * Helper para cargar relaciones de la hoja de vida.
 */
function load_resume_relations($resume)
{
    $conn = get_db_connection();
    $id = $resume['id'];

    $stmt = $conn->prepare("SELECT * FROM hoja_vida_habilidades WHERE hoja_vida_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resume['habilidades'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $resume['skills'] = implode(', ', array_column($resume['habilidades'], 'habilidad'));

    $stmt = $conn->prepare("SELECT * FROM hoja_vida_formacion WHERE hoja_vida_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resume['education'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM hoja_vida_experiencia WHERE hoja_vida_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resume['experiencia'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM hoja_vida_referencias WHERE hoja_vida_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resume['referencias'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return $resume;
}

/**
 * Obtener hoja de vida completa por Usuario ID.
 */
function get_complete_resume($usuario_id)
{
    $conn = get_db_connection();
    if (!$conn)
        return null;

    $stmt = $conn->prepare("SELECT hv.*, u.email as user_email 
                           FROM hoja_vida hv 
                           JOIN usuarios u ON hv.usuario_id = u.id 
                           WHERE hv.usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resume = $stmt->get_result()->fetch_assoc();

    if ($resume) {
        return load_resume_relations($resume);
    }
    return null;
}
?>