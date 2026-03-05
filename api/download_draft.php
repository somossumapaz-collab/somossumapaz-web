<?php
// api/download_draft.php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once '../database_functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Sesión no iniciada");
}

$hoja_vida_id = $_GET['hoja_vida_id'] ?? null;
if (!$hoja_vida_id) {
    die("ID de hoja de vida no proporcionado");
}

$conn = get_db_connection();
$stmt = $conn->prepare("SELECT usuario_id FROM hoja_vida WHERE id = ?");
$stmt->bind_param("i", $hoja_vida_id);
$stmt->execute();
$hv_res = $stmt->get_result()->fetch_assoc();

if (!$hv_res) {
    die("Hoja de vida no encontrada");
}

// Security: ensure user owns the resume or is admin
if ($hv_res['usuario_id'] != $_SESSION['user_id'] && $_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

$resume = get_complete_resume($hv_res['usuario_id']);

if (!$resume) {
    die("Error al cargar datos completos de la hoja de vida");
}

// Generate a simple HTML draft for easy printing
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Borrador Hoja de Vida -
        <?php echo htmlspecialchars($resume['full_name']); ?>
    </title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #eee;
        }

        h1 {
            color: #2e7d32;
            border-bottom: 2px solid #2e7d32;
            padding-bottom: 10px;
        }

        h2 {
            color: #f57c00;
            border-bottom: 1px solid #eee;
            margin-top: 30px;
        }

        .section {
            margin-bottom: 20px;
        }

        .item {
            margin-bottom: 15px;
        }

        .item strong {
            display: block;
        }

        .date {
            color: #666;
            font-size: 0.9em;
        }

        .footer {
            margin-top: 50px;
            font-size: 0.8em;
            text-align: center;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                border: none;
                margin: 0;
                padding: 0;
            }
        }

        .btn-print {
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Imprimir / Guardar como PDF</button>
        <p><small>Este es un borrador generado automáticamente después de guardar su información.</small></p>
    </div>

    <h1>
        <?php echo htmlspecialchars($resume['full_name']); ?>
    </h1>
    <p><strong>Profesión:</strong>
        <?php echo htmlspecialchars($resume['niche']); ?>
    </p>

    <div class="section">
        <h2>Perfil Profesional</h2>
        <p>
            <?php echo nl2br(htmlspecialchars($resume['profile_description'])); ?>
        </p>
    </div>

    <div class="section">
        <h2>Información de Contacto</h2>
        <p><strong>Email:</strong>
            <?php echo htmlspecialchars($resume['email']); ?>
        </p>
        <p><strong>Teléfono:</strong>
            <?php echo htmlspecialchars($resume['phone']); ?>
        </p>
        <p><strong>Documento:</strong>
            <?php echo htmlspecialchars($resume['document_id']); ?>
        </p>
    </div>

    <div class="section">
        <h2>Habilidades</h2>
        <p>
            <?php echo htmlspecialchars($resume['skills']); ?>
        </p>
    </div>

    <div class="section">
        <h2>Formación Académica</h2>
        <?php foreach ($resume['education'] as $edu): ?>
            <div class="item">
                <strong>
                    <?php echo htmlspecialchars($edu['nivel_educativo']); ?> -
                    <?php echo htmlspecialchars($edu['institucion']); ?>
                </strong>
                <span class="date">
                    <?php echo $edu['fecha_inicio']; ?> a
                    <?php echo $edu['en_curso'] ? 'Presente' : $edu['fecha_fin']; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>Experiencia Laboral</h2>
        <?php foreach ($resume['experiencia'] as $exp): ?>
            <div class="item">
                <strong>
                    <?php echo htmlspecialchars($exp['cargo']); ?> en
                    <?php echo htmlspecialchars($exp['empresa']); ?>
                </strong>
                <span class="date">
                    <?php echo $exp['fecha_inicio']; ?> a
                    <?php echo $exp['actualmente'] ? 'Presente' : $exp['fecha_fin']; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>Referencias</h2>
        <div style="display: flex; gap: 40px;">
            <div>
                <h3>Personales</h3>
                <?php foreach ($resume['referencias'] as $ref):
                    if ($ref['tipo'] === 'Personal'): ?>
                        <div class="item">
                            <strong>
                                <?php echo htmlspecialchars($ref['nombre']); ?>
                            </strong>
                            <?php echo htmlspecialchars($ref['telefono']); ?> (
                            <?php echo htmlspecialchars($ref['ocupacion']); ?>)
                        </div>
                    <?php endif; endforeach; ?>
            </div>
            <div>
                <h3>Familiares</h3>
                <?php foreach ($resume['referencias'] as $ref):
                    if ($ref['tipo'] === 'Familiar'): ?>
                        <div class="item">
                            <strong>
                                <?php echo htmlspecialchars($ref['nombre']); ?>
                            </strong>
                            <?php echo htmlspecialchars($ref['telefono']); ?> (
                            <?php echo htmlspecialchars($ref['parentesco']); ?>)
                        </div>
                    <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <div class="footer">
        Documento generado por Talento Sumapaz el
        <?php echo date('d/m/Y H:i'); ?>
    </div>
</body>

</html>