<?php
// debug_inspect.php
require_once 'database_functions.php';

echo "<h1>Diagnóstico de Sistema</h1>";

// 1. Verificación de Conexión y Base de Datos
$conn = get_db_connection();
echo "<h2>1. Base de Datos</h2>";
if ($conn->connect_error) {
    echo "<p style='color:red;'>Error de conexión: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>Conectado a la base de datos.</p>";
    $res = $conn->query("SELECT DATABASE()");
    $row = $res->fetch_row();
    echo "<p>Base de datos activa: <strong>" . $row[0] . "</strong></p>";

    $tables = ['usuarios', 'hoja_vida', 'hoja_vida_habilidades', 'hoja_vida_formacion', 'hoja_vida_experiencia', 'hoja_vida_referencias'];
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'><tr><th>Tabla</th><th>Registros</th><th>Engine</th></tr>";
    foreach ($tables as $t) {
        $r = $conn->query("SELECT COUNT(*) FROM $t");
        $count = "ERROR";
        if ($r) {
            $count = $r->fetch_row()[0];
        }

        $e_res = $conn->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$t'");
        $engine = "Desconocido";
        if ($e_res && $erow = $e_res->fetch_assoc()) {
            $engine = $erow['ENGINE'];
        }

        echo "<tr><td>$t</td><td>$count</td><td>$engine</td></tr>";
    }
    echo "</table>";
}

// 2. Verificación de Archivos y Rutas
echo "<h2>2. Sistema de Archivos</h2>";
$base = realpath('uploads');
echo "<p>Ruta absoluta de 'uploads': <strong>" . ($base ? $base : "NO ENCONTRADA") . "</strong></p>";

if ($base) {
    $subs = ['fotos_perfil', 'documentos_identidad', 'certificados_academicos', 'certificados_laborales'];
    echo "<ul>";
    foreach ($subs as $sub) {
        $path = $base . DIRECTORY_SEPARATOR . $sub;
        if (file_exists($path)) {
            $files = array_diff(scandir($path), array('.', '..'));
            echo "<li>Directorio <strong>$sub</strong>: " . count($files) . " archivos internos.</li>";
            if (count($files) > 0) {
                echo "<ul>";
                foreach (array_slice($files, 0, 5) as $f) {
                    echo "<li>$f (" . filesize($path . DIRECTORY_SEPARATOR . $f) . " bytes)</li>";
                }
                if (count($files) > 5)
                    echo "<li>... y otros " . (count($files) - 5) . " más.</li>";
                echo "</ul>";
            }
        } else {
            echo "<li style='color:red;'>Directorio <strong>$sub</strong>: NO EXISTE</li>";
        }
    }
    echo "</ul>";
}

// 3. Sesión
session_start();
echo "<h2>3. Sesión Actual</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

?>