<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'database_functions.php';

$resume_id = $_GET['resume_id'] ?? null;
if (!$resume_id) {
    die("ID de resume no proporcionado");
}

$resumes = get_all_resumes();
$resume = null;
foreach ($resumes as $r) {
    if ($r['id'] == $resume_id) {
        $resume = $r;
        break;
    }
}

if (!$resume) {
    die("Resumen no encontrado");
}

$personal_refs = json_decode($resume['personal_references_json'] ?? '[]', true);
$family_refs = json_decode($resume['family_references_json'] ?? '[]', true);

include 'header.php';
?>

<div class="preview-wrapper">
    <div class="resume-preview-container">
        <a href="dashboard.php" class="back-link">← Volver al Panel</a>

        <div class="preview-header">
            <?php if ($resume['photo_path']): ?>
                <img src="uploads/<?php echo $resume['photo_path']; ?>" alt="Foto de Perfil" class="preview-photo">
            <?php endif; ?>
            <div class="header-text">
                <h1>
                    <?php echo $resume['full_name']; ?>
                </h1>
                <p>
                    <?php echo $resume['niche']; ?>
                </p>
            </div>
        </div>

        <div class="preview-section">
            <h3>Perfil Profesional</h3>
            <p>
                <?php echo $resume['profile_description']; ?>
            </p>
        </div>

        <div class="preview-grid">
            <div class="preview-left">
                <div class="preview-section">
                    <h3>Contacto</h3>
                    <p><strong>Email:</strong>
                        <?php echo $resume['email']; ?>
                    </p>
                    <p><strong>Teléfono:</strong>
                        <?php echo $resume['phone']; ?>
                    </p>
                    <p><strong>Ubicación:</strong>
                        <?php echo $resume['city']; ?>,
                        <?php echo $resume['department']; ?>
                    </p>
                </div>

                <div class="preview-section">
                    <h3>Habilidades</h3>
                    <p>
                        <?php echo $resume['skills']; ?>
                    </p>
                </div>
            </div>

            <div class="preview-right">
                <div class="preview-section">
                    <h3>Formación Académica</h3>
                    <?php foreach ($resume['education'] as $edu): ?>
                        <div class="item">
                            <p><strong>
                                    <?php echo $edu['level']; ?>
                                </strong> -
                                <?php echo $edu['institution']; ?>
                            </p>
                            <p class="date">
                                <?php echo $edu['start_date']; ?> a
                                <?php echo ($edu['is_current'] ? 'Presente' : $edu['end_date']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="preview-section">
                    <h3>Experiencia Laboral</h3>
                    <?php foreach ($resume['experience'] as $exp): ?>
                        <div class="item">
                            <p><strong>
                                    <?php echo $exp['role']; ?>
                                </strong> -
                                <?php echo $exp['company']; ?>
                            </p>
                            <p class="date">
                                <?php echo $exp['start_date']; ?> a
                                <?php echo ($exp['is_current'] ? 'Presente' : $exp['end_date']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="preview-section">
            <h3>Referencias</h3>
            <div class="grid-2">
                <div>
                    <h4>Personales</h4>
                    <?php foreach ($personal_refs as $ref): ?>
                        <p>
                            <?php echo $ref['name']; ?> -
                            <?php echo $ref['phone']; ?> (
                            <?php echo $ref['occupation']; ?>)
                        </p>
                    <?php endforeach; ?>
                </div>
                <div>
                    <h4>Familiares</h4>
                    <?php foreach ($family_refs as $ref): ?>
                        <p>
                            <?php echo $ref['name']; ?> -
                            <?php echo $ref['phone']; ?> (
                            <?php echo $ref['relation']; ?>)
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="preview-actions" style="margin-top: 20px;">
            <a href="api/download_resume.php?resume_id=<?php echo $resume['id']; ?>" class="btn-primary">Descargar
                Documentos (ZIP)</a>
            <button onclick="window.print()" class="btn-secondary">Imprimir / Guardar como PDF</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>