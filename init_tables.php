<?php
require_once 'db_config.php';

$conn = get_db_connection();

$queries = [
    "CREATE TABLE IF NOT EXISTS persona_datos_personales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255),
        tipo_documento VARCHAR(50),
        documento VARCHAR(50) UNIQUE,
        fecha_nacimiento DATE,
        departamento_nacimiento VARCHAR(100),
        municipio_nacimiento VARCHAR(100),
        telefono VARCHAR(50),
        email VARCHAR(100),
        vereda VARCHAR(100),
        ruta_foto VARCHAR(255),
        ruta_cedula VARCHAR(255),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS persona_educacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        persona_id INT,
        titulo VARCHAR(255),
        institucion VARCHAR(255),
        fecha_inicio DATE,
        fecha_fin DATE,
        ruta_soporte VARCHAR(255),
        FOREIGN KEY (persona_id) REFERENCES persona_datos_personales(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS persona_experiencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        persona_id INT,
        cargo VARCHAR(255),
        empresa VARCHAR(255),
        fecha_inicio DATE,
        fecha_fin DATE,
        descripcion TEXT,
        ruta_soporte VARCHAR(255),
        FOREIGN KEY (persona_id) REFERENCES persona_datos_personales(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS persona_referencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        persona_id INT,
        nombre VARCHAR(255),
        telefono VARCHAR(50),
        relacion VARCHAR(100),
        ocupacion VARCHAR(100),
        FOREIGN KEY (persona_id) REFERENCES persona_datos_personales(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

echo "<h2>Iniciando creación de tablas...</h2>";

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✔ Tabla procesada exitosamente.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al crear tabla: " . $conn->error . "</p>";
    }
}

$conn->close();
echo "<h3>Proceso terminado.</h3>";
echo "<a href='import_zip.php'>Ir al Importador</a>";
?>
