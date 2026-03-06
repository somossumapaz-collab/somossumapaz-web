<?php
// api/download_resume_pdf.php
// Provides a professional printable view of the resume
session_start();
require_once __DIR__ . '/../database_functions.php';

$user_id = $_GET['user_id'] ?? ($_SESSION['user_id'] ?? null);
$hv_id = $_GET['id'] ?? null;

if (!$hv_id && !$user_id) {
    die("ID no especificado");
}

if ($hv_id) {
    $resume = get_resume_by_id($hv_id);
} else {
    $resume = get_complete_resume($user_id);
}
if (!$resume) {
    die("Hoja de vida no encontrada");
}

// Map data
$personal = $resume;
$skills = $resume['habilidades'] ?? [];
$education = $resume['formacion'] ?? [];
$experience = $resume['experiencia'] ?? [];
$references = $resume['referencias'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Hoja de Vida -
        <?php echo htmlspecialchars($personal['nombre_completo']); ?>
    </title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --text: #333;
            --bg: #fff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            color: var(--text);
            background-color: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg);
            padding: 50px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid var(--accent);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .photo-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #eee;
            margin-right: 30px;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header-info h1 {
            margin: 0;
            font-size: 2.2rem;
            color: var(--primary);
            text-transform: uppercase;
        }

        .header-info h2 {
            margin: 5px 0;
            font-size: 1.2rem;
            color: var(--accent);
            font-weight: 400;
        }

        .contact-info {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--primary);
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
            padding-bottom: 5px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .section-body {
            line-height: 1.6;
        }

        .item {
            margin-bottom: 15px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            color: var(--secondary);
        }

        .item-sub {
            font-style: italic;
            color: #666;
            margin-bottom: 5px;
        }

        .item-desc {
            font-size: 0.95rem;
        }

        .skills-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .skill {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            border-left: 3px solid var(--accent);
        }

        .references-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .container {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            padding: 10px 20px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
        <button class="btn-print" style="background:#666;" onclick="window.close()">Cerrar</button>
    </div>

    <script>
        // Auto-print when loaded
        window.onload = function () {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>

    <div class="container">
        <div class="header">
            <?php if (!empty($personal['foto_perfil_path'])): ?>
                <div class="photo-container">
                    <img src="../<?php echo $personal['foto_perfil_path']; ?>" alt="Foto">
                </div>
            <?php endif; ?>
            <div class="header-info">
                <h1>
                    <?php echo htmlspecialchars($personal['nombre_completo']); ?>
                </h1>
                <h2>
                    <?php echo htmlspecialchars((string) ($personal['profesion'] ?? 'Candidato')); ?>
                </h2>
                <div class="contact-info">
                    <span><i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($personal['email']); ?>
                    </span>
                    <span><i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($personal['telefono']); ?>
                    </span>
                    <span><i class="fas fa-id-card"></i>
                        <?php echo htmlspecialchars($personal['numero_documento']); ?>
                    </span>
                    <span><i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($personal['municipio_residencia'] . ', ' . $personal['departamento_residencia']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">PERFIL PROFESIONAL</div>
            <div class="section-body">
                <?php echo nl2br(htmlspecialchars($personal['perfil_profesional'])); ?>
            </div>
        </div>

        <?php if (!empty($skills)): ?>
            <div class="section">
                <div class="section-title">HABILIDADES</div>
                <div class="skills-grid">
                    <?php foreach ($skills as $s): ?>
                        <div class="skill">
                            <?php echo htmlspecialchars((string) ($s['habilidad'] ?? '')); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($education)): ?>
            <div class="section">
                <div class="section-title">FORMACIÓN ACADÉMICA</div>
                <div class="section-body">
                    <?php foreach ($education as $e): ?>
                        <div class="item">
                            <div class="item-header">
                                <span>
                                    <?php echo htmlspecialchars((string) ($e['nivel_educativo'] ?? '')); ?>
                                </span>
                                <span>
                                    <?php echo $e['fecha_inicio']; ?> —
                                    <?php echo ($e['en_curso'] ? 'Actualidad' : $e['fecha_fin']); ?>
                                </span>
                            </div>
                            <div class="item-sub">
                                <?php echo htmlspecialchars((string) ($e['institucion'] ?? '')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($experience)): ?>
            <div class="section">
                <div class="section-title">EXPERIENCIA LABORAL</div>
                <div class="section-body">
                    <?php foreach ($experience as $ex): ?>
                        <div class="item">
                            <div class="item-header">
                                <span>
                                    <?php echo htmlspecialchars((string) ($ex['cargo'] ?? '')); ?>
                                </span>
                                <span>
                                    <?php echo $ex['fecha_inicio']; ?> —
                                    <?php echo ($ex['actualmente'] ? 'Actualidad' : $ex['fecha_fin']); ?>
                                </span>
                            </div>
                            <div class="item-sub">
                                <?php echo htmlspecialchars((string) ($ex['empresa'] ?? '')); ?>
                            </div>
                            <div class="item-desc">
                                <?php echo nl2br(htmlspecialchars($ex['descripcion_cargo'] ?? '')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($references)): ?>
            <div class="section">
                <div class="section-title">REFERENCIAS</div>
                <div class="references-grid">
                    <?php foreach ($references as $r): ?>
                        <div class="item">
                            <div class="item-header">
                                <?php echo htmlspecialchars((string) ($r['nombre'] ?? '')); ?>
                            </div>
                            <div class="item-sub">
                                <?php echo htmlspecialchars((string) ($r['tipo'] ?? '')); ?> —
                                <?php echo htmlspecialchars((string) ($r['ocupacion'] ?? $r['parentesco'] ?? '')); ?>
                            </div>
                            <div class="item-desc">Teléfono:
                                <?php echo htmlspecialchars((string) ($r['telefono'] ?? '')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>