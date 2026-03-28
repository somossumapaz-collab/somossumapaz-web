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
        <?php echo htmlspecialchars($personal['nombre']); ?>
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

    <div class="no-print" style="margin-top: 20px; display: flex; justify-content: center; gap: 15px;">
        <a href="../dashboard.php" class="btn-print" style="background: #34495e; text-decoration: none; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Volver al Tablero
        </a>
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print" style="margin-right: 8px;"></i> Imprimir / PDF
        </button>
    </div>
    <div class="no-print" style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9rem;">
        ID de Hoja de Vida: <?php echo $hv_id; ?>
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
            <?php if (!empty($personal['ruta_foto'])): ?>
                <div class="photo-container">
                    <img src="../<?php echo $personal['ruta_foto']; ?>" alt="Foto">
                </div>
            <?php endif; ?>
            <div class="header-info">
                <h1>
                    <?php echo htmlspecialchars($personal['nombre']); ?>
                </h1>
                <h2>
                    <?php echo htmlspecialchars((string) ($personal['vereda'] ?? 'Candidato Sumapaz')); ?>
                </h2>
                <div class="contact-info">
                    <span><i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($personal['email'] ?: 'No registrado'); ?>
                    </span>
                    <span><i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($personal['telefono'] ?: 'No registrado'); ?>
                    </span>
                    <span><i class="fas fa-id-card"></i>
                        <?php echo htmlspecialchars(($personal['tipo_documento'] ?? 'CC') . ': ' . $personal['documento']); ?>
                    </span>
                    <span><i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($personal['municipio_residencia'] ?? 'Sumapaz'); ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!empty($personal['perfil_profesional'])): ?>
        <div class="section">
            <div class="section-title">PERFIL PROFESIONAL</div>
            <div class="section-body">
                <?php echo nl2br(htmlspecialchars($personal['perfil_profesional'])); ?>
            </div>
        </div>
        <?php endif; ?>

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
                                    <?php echo htmlspecialchars((string) ($e['nivel_educacion'] ?? $e['titulo'] ?? '')); ?>
                                </span>
                                <span>
                                    <?php echo $e['fecha_inicio'] ?: 'N/A'; ?> —
                                    <?php echo ($e['fecha_fin'] ?: 'Actualidad'); ?>
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
                                    <?php echo $ex['fecha_inicio'] ?: 'N/A'; ?> —
                                    <?php echo ($ex['fecha_fin'] ?: 'Actualidad'); ?>
                                </span>
                            </div>
                            <div class="item-sub">
                                <?php echo htmlspecialchars((string) ($ex['empresa'] ?? '')); ?>
                            </div>
                            <div class="item-desc">
                                <?php echo nl2br(htmlspecialchars($ex['descripcion'] ?? '')); ?>
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
                                <?php echo htmlspecialchars((string) ($r['ocupacion'] ?? 'Referencia')); ?>
                            </div>
                            <div class="item-desc">Teléfono:
                                <?php echo htmlspecialchars((string) ($r['telefono'] ?? 'No registrado')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sección de Anexos y Soportes -->
        <?php 
        $has_anexos = !empty($personal['ruta_cedula']) || 
                      !empty(array_filter($education, function($e) { return !empty($e['ruta_certificado']); })) || 
                      !empty(array_filter($experience, function($ex) { return !empty($ex['ruta_experiencia']); }));
        
        if ($has_anexos): 
        ?>
        <div class="section" style="page-break-before: always; border-top: 2px solid var(--accent); padding-top: 30px; margin-top: 50px;">
            <div class="section-title" style="font-size: 1.5rem; justify-content: center; color: var(--accent); border:none;">
                <i class="fas fa-file-alt" style="margin-right: 12px;"></i> ANEXOS Y SOPORTES CONCATENADOS
            </div>
            
            <?php 
            function render_soporte($path, $title) {
                if (empty($path)) return;
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $is_img = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                ?>
                <div class="section" style="margin-top: 30px; break-inside: avoid;">
                    <div class="section-title"><?php echo $title; ?></div>
                    <div class="section-body" style="text-align: center;">
                        <?php if ($is_img): ?>
                            <img src="../<?php echo $path; ?>" style="max-width: 100%; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <iframe src="../<?php echo $path; ?>" style="width:100%; height:800px; border: 1px solid #ddd; border-radius: 8px;"></iframe>
                        <?php endif; ?>
                        <div class="no-print" style="margin-top: 10px;">
                            <a href="../<?php echo $path; ?>" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: bold;">
                                <i class="fas fa-external-link-alt"></i> Ver archivo original
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }

            render_soporte($personal['ruta_cedula'] ?? null, "DOCUMENTO DE IDENTIDAD");

            foreach ($education as $e) {
                render_soporte($e['ruta_certificado'] ?? null, "CERTIFICADO ACADÉMICO: " . ($e['nivel_educacion'] ?? $e['titulo']));
            }

            foreach ($experience as $ex) {
                render_soporte($ex['ruta_experiencia'] ?? null, "CERTIFICADO LABORAL: " . ($ex['empresa'] . " - " . $ex['cargo']));
            }
            ?>
        </div>
        <?php endif; ?>
    </div>

</body>

</html>