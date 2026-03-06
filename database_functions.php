<?php
// database_functions.php
require_once __DIR__ . '/db_config.php';

/**
 * Initializes the database structure.
 */
function init_db()
{
    $pdo = get_db_connection();
    if (!$pdo)
        return;

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $queries = [];

    if ($driver == 'mysql') {
        $queries[] = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            nombre VARCHAR(100),
            apellido VARCHAR(100),
            telefono VARCHAR(30),
            tipo_documento VARCHAR(20),
            documento VARCHAR(50) UNIQUE,
            password VARCHAR(255) NOT NULL,
            rol ENUM('admin', 'usuario') DEFAULT 'usuario',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida (
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
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_habilidades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hoja_vida_id INT NOT NULL,
            habilidad VARCHAR(100),
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_formacion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hoja_vida_id INT NOT NULL,
            institucion VARCHAR(255),
            nivel_educativo VARCHAR(100),
            fecha_inicio DATE,
            fecha_fin DATE,
            en_curso TINYINT(1) DEFAULT 0,
            soporte_path VARCHAR(255),
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_experiencia (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hoja_vida_id INT NOT NULL,
            empresa VARCHAR(255),
            cargo VARCHAR(255),
            descripcion_cargo TEXT,
            fecha_inicio DATE,
            fecha_fin DATE,
            actualmente TINYINT(1) DEFAULT 0,
            soporte_path VARCHAR(255),
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_referencias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hoja_vida_id INT NOT NULL,
            tipo ENUM('Personal', 'Familiar'),
            nombre VARCHAR(255),
            telefono VARCHAR(30),
            ocupacion VARCHAR(255),
            parentesco VARCHAR(100),
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";
    } else {
        $pdo->exec("PRAGMA foreign_keys = ON");
        $queries[] = "CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            nombre TEXT,
            apellido TEXT,
            telefono TEXT,
            tipo_documento TEXT,
            documento TEXT UNIQUE,
            password TEXT NOT NULL,
            rol TEXT DEFAULT 'usuario',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            nombre_completo TEXT,
            tipo_documento TEXT,
            numero_documento TEXT,
            fecha_nacimiento DATE,
            departamento_nacimiento TEXT,
            municipio_nacimiento TEXT,
            departamento_residencia TEXT,
            municipio_residencia TEXT,
            telefono TEXT,
            email TEXT,
            perfil_profesional TEXT,
            profesion TEXT,
            foto_perfil_path TEXT,
            documento_pdf_path TEXT,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )";
        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_habilidades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hoja_vida_id INTEGER NOT NULL,
            habilidad TEXT,
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";
        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_formacion (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hoja_vida_id INTEGER NOT NULL,
            institucion TEXT,
            nivel_educativo TEXT,
            fecha_inicio DATE,
            fecha_fin DATE,
            en_curso INTEGER DEFAULT 0,
            soporte_path TEXT,
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";
        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_experiencia (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hoja_vida_id INTEGER NOT NULL,
            empresa TEXT,
            cargo TEXT,
            descripcion_cargo TEXT,
            fecha_inicio DATE,
            fecha_fin DATE,
            actualmente INTEGER DEFAULT 0,
            soporte_path TEXT,
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";
        $queries[] = "CREATE TABLE IF NOT EXISTS hoja_vida_referencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hoja_vida_id INTEGER NOT NULL,
            tipo TEXT,
            nombre TEXT,
            telefono TEXT,
            ocupacion TEXT,
            parentesco TEXT,
            FOREIGN KEY (hoja_vida_id) REFERENCES hoja_vida(id) ON DELETE CASCADE
        )";
    }

    foreach ($queries as $q) {
        $pdo->exec($q);
    }
}

function verify_user($identifier, $password)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? OR email = ? OR documento = ?");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function create_user($data)
{
    $pdo = get_db_connection();
    try {
        $sql = "INSERT INTO usuarios (usuario, email, nombre, apellido, telefono, tipo_documento, documento, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['usuario'],
            $data['email'],
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['tipo_documento'],
            $data['documento'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        return true;
    } catch (PDOException $e) {
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
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT id, nombre_completo as nombre, perfil_profesional as nicho_cargo, telefono, email FROM hoja_vida ORDER BY fecha_creacion DESC");
    return $stmt->fetchAll();
}

function get_resume_by_id($id)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM hoja_vida WHERE id = ?");
    $stmt->execute([$id]);
    $resume = $stmt->fetch();
    if ($resume) {
        $resume = load_resume_relations($resume);
    }
    return $resume;
}

function load_resume_relations($resume)
{
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("SELECT habilidad FROM hoja_vida_habilidades WHERE hoja_vida_id = ?");
    $stmt->execute([$resume['id']]);
    $resume['habilidades'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("SELECT * FROM hoja_vida_formacion WHERE hoja_vida_id = ?");
    $stmt->execute([$resume['id']]);
    $resume['formacion'] = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM hoja_vida_experiencia WHERE hoja_vida_id = ?");
    $stmt->execute([$resume['id']]);
    $resume['experiencia'] = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM hoja_vida_referencias WHERE hoja_vida_id = ?");
    $stmt->execute([$resume['id']]);
    $resume['referencias'] = $stmt->fetchAll();

    return $resume;
}

function get_complete_resume($usuario_id)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM hoja_vida WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT 1");
    $stmt->execute([$usuario_id]);
    $resume = $stmt->fetch();
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
 * Saves a resume and its related data (skills, education, experience, references).
 * @param int $user_id
 * @param array $data Typically $_POST
 * @param array $files Typically $_FILES
 * @return array ['success' => bool, 'id' => int, 'error' => string]
 */
function save_resume_data($user_id, $data, $files)
{
    $pdo = get_db_connection();
    try {
        ensure_directories();
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        $nombre = $data['full_name'] ?? ($data['nombre_completo'] ?? '');
        $id_type = $data['id_type'] ?? ($data['tipo_documento'] ?? '');
        $doc_id = $data['document_id'] ?? ($data['numero_documento'] ?? '');
        $birth_date = $data['birth_date'] ?? ($data['fecha_nacimiento'] ?? null);
        $birth_dept = $data['birth_department'] ?? ($data['departamento_nacimiento'] ?? '');
        $birth_city = $data['birth_city'] ?? ($data['municipio_nacimiento'] ?? '');
        $dept = $data['department'] ?? ($data['departamento_residencia'] ?? '');
        $city = $data['city'] ?? ($data['municipio_residencia'] ?? '');
        $phone = $data['phone'] ?? ($data['telefono'] ?? '');
        $email = $data['email'] ?? ($data['email'] ?? '');
        $perfil = $data['profile_description'] ?? ($data['perfil_profesional'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO hoja_vida (
            usuario_id, nombre_completo, tipo_documento, numero_documento, 
            fecha_nacimiento, departamento_nacimiento, municipio_nacimiento, 
            departamento_residencia, municipio_residencia, telefono, email, perfil_profesional
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $user_id,
            $nombre,
            $id_type,
            $doc_id,
            $birth_date ?: null,
            $birth_dept,
            $birth_city,
            $dept,
            $city,
            $phone,
            $email,
            $perfil
        ]);

        $hoja_vida_id = (int) $pdo->lastInsertId();

        // Foto Perfil
        if (isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($files['photo']['name'], PATHINFO_EXTENSION));
            $filename = "foto_" . $hoja_vida_id . "_" . time() . "." . $ext;
            $target = __DIR__ . "/uploads/fotos_perfil/" . $filename;
            if (move_uploaded_file($files['photo']['tmp_name'], $target)) {
                $db_path = "uploads/fotos_perfil/" . $filename;
                $pdo->prepare("UPDATE hoja_vida SET foto_perfil_path = ? WHERE id = ?")->execute([$db_path, $hoja_vida_id]);
            }
        }

        // Documento Identidad
        if (isset($files['id_file']) && $files['id_file']['error'] === UPLOAD_ERR_OK) {
            $filename = "doc_" . $hoja_vida_id . "_" . time() . ".pdf";
            $target = __DIR__ . "/uploads/documentos_identidad/" . $filename;
            if (move_uploaded_file($files['id_file']['tmp_name'], $target)) {
                $db_path = "uploads/documentos_identidad/" . $filename;
                $pdo->prepare("UPDATE hoja_vida SET documento_pdf_path = ? WHERE id = ?")->execute([$db_path, $hoja_vida_id]);
            }
        }

        // Habilidades
        $skills = $data['skills'] ?? '';
        if (!empty($skills)) {
            $arr = is_array($skills) ? $skills : explode(',', $skills);
            $stmt_skill = $pdo->prepare("INSERT INTO hoja_vida_habilidades (hoja_vida_id, habilidad) VALUES (?, ?)");
            foreach ($arr as $s) {
                $val = trim($s);
                if ($val)
                    $stmt_skill->execute([$hoja_vida_id, $val]);
            }
        }

        // Educación
        $i = 0;
        while (isset($data["education_{$i}_institution"]) || isset($data["edu_inst_{$i}"])) {
            $inst = $data["education_{$i}_institution"] ?? ($data["edu_inst_{$i}"] ?? '');
            $level = $data["education_{$i}_level"] ?? ($data["edu_level_{$i}"] ?? '');
            $start = ($data["education_{$i}_start_date"] ?? ($data["edu_start_{$i}"] ?? '')) ?: null;
            $end = ($data["education_{$i}_end_date"] ?? ($data["edu_end_{$i}"] ?? '')) ?: null;
            $in_course = (isset($data["education_{$i}_is_current"]) || isset($data["edu_current_{$i}"])) ? 1 : 0;

            if ($inst) {
                $stmt_edu = $pdo->prepare("INSERT INTO hoja_vida_formacion (hoja_vida_id, institucion, nivel_educativo, fecha_inicio, fecha_fin, en_curso) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_edu->execute([$hoja_vida_id, $inst, $level, $start, $end, $in_course]);
                $edu_id = $pdo->lastInsertId();

                $file_key = isset($files["education_{$i}_file"]) ? "education_{$i}_file" : "edu_file_{$i}";
                if (isset($files[$file_key]) && $files[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $filename = "edu_" . $edu_id . "_" . time() . ".pdf";
                    $target = __DIR__ . "/uploads/certificados_academicos/" . $filename;
                    if (move_uploaded_file($files[$file_key]['tmp_name'], $target)) {
                        $db_path = "uploads/certificados_academicos/" . $filename;
                        $pdo->prepare("UPDATE hoja_vida_formacion SET soporte_path = ? WHERE id = ?")->execute([$db_path, $edu_id]);
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
            $is_current = (isset($data["experience_{$j}_is_current"]) || isset($data["exp_current_{$j}"])) ? 1 : 0;

            if ($comp) {
                $stmt_exp = $pdo->prepare("INSERT INTO hoja_vida_experiencia (hoja_vida_id, empresa, cargo, descripcion_cargo, fecha_inicio, fecha_fin, actualmente) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_exp->execute([$hoja_vida_id, $comp, $role, $desc, $start, $end, $is_current]);
                $exp_id = $pdo->lastInsertId();

                $file_key = isset($files["experience_{$j}_file"]) ? "experience_{$j}_file" : "exp_file_{$j}";
                if (isset($files[$file_key]) && $files[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $filename = "exp_" . $exp_id . "_" . time() . ".pdf";
                    $target = __DIR__ . "/uploads/certificados_laborales/" . $filename;
                    if (move_uploaded_file($files[$file_key]['tmp_name'], $target)) {
                        $db_path = "uploads/certificados_laborales/" . $filename;
                        $pdo->prepare("UPDATE hoja_vida_experiencia SET soporte_path = ? WHERE id = ?")->execute([$db_path, $exp_id]);
                    }
                }
            }
            $j++;
        }

        // Referencias
        $ref_fields = [
            ['name' => 'ref_p1_name', 'phone' => 'ref_p1_phone', 'type' => 'Personal'],
            ['name' => 'ref_p2_name', 'phone' => 'ref_p2_phone', 'type' => 'Personal'],
            ['name' => 'ref_f1_name', 'phone' => 'ref_f1_phone', 'type' => 'Familiar'],
            ['name' => 'ref_f2_name', 'phone' => 'ref_f2_phone', 'type' => 'Familiar']
        ];
        $stmt_ref = $pdo->prepare("INSERT INTO hoja_vida_referencias (hoja_vida_id, tipo, nombre, telefono) VALUES (?, ?, ?, ?)");
        foreach ($ref_fields as $rf) {
            $name = $data[$rf['name']] ?? '';
            if ($name) {
                $phone = $data[$rf['phone']] ?? '';
                $type = $rf['type'];
                $stmt_ref->execute([$hoja_vida_id, $type, $name, $phone]);
            }
        }

        $pdo->commit();
        return ['success' => true, 'id' => (int) $hoja_vida_id];
    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>