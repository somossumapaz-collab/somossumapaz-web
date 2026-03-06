<?php
require_once 'database_functions.php';
include 'header.php';
check_auth();
?>

<div style="text-align: center; margin-bottom: 40px;">
    <h1 style="color: var(--primary-color); font-size: 2.5rem;">Talento Sumapaz – Página de Usuario</h1>
    <p style="color: #666;">Bienvenido,
        <?php echo $_SESSION['usuario']; ?>. Gestiona tu talento desde aquí.
    </p>
</div>

<div
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px;">
    <!-- Opción 1: Consulta -->
    <a href="dashboard.php" style="text-decoration: none; color: inherit;">
        <div
            style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s; border-bottom: 5px solid var(--secondary-color);">
            <i class="fas fa-search fa-3x" style="color: var(--primary-color); margin-bottom: 20px;"></i>
            <h3 style="color: var(--primary-color);">Consulta</h3>
            <p style="color: #666;">Visualiza y haz seguimiento de tus hojas de vida registradas.</p>
        </div>
    </a>

    <!-- Opción 2: Inscribir hoja de vida -->
    <a href="resume_form.php" style="text-decoration: none; color: inherit;">
        <div
            style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s; border-bottom: 5px solid var(--secondary-color);">
            <i class="fas fa-file-signature fa-3x" style="color: var(--primary-color); margin-bottom: 20px;"></i>
            <h3 style="color: var(--primary-color);">Inscribir hoja de vida</h3>
            <p style="color: #666;">Registra una nueva hoja de vida con todos tus soportes y experiencia.</p>
        </div>
    </a>

    <!-- Opción 3: Panel de información -->
    <a href="#" style="text-decoration: none; color: inherit;">
        <div
            style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s; border-bottom: 5px solid var(--secondary-color);">
            <i class="fas fa-info-circle fa-3x" style="color: var(--primary-color); margin-bottom: 20px;"></i>
            <h3 style="color: var(--primary-color);">Panel de información</h3>
            <p style="color: #666;">Consulta guías, normatividad e información relevante del proyecto.</p>
        </div>
    </a>
</div>

<style>
    .user-option-card:hover {
        transform: translateY(-10px);
    }
</style>

<?php include 'footer.php'; ?>