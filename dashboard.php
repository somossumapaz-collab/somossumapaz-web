<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'header.php';
?>

<div class="dashboard-wrapper">
    <header class="dashboard-header">
        <h2>Mejorando el empleo en la localidad de Sumapaz</h2>
        <a href="api/logout.php" class="logout-link">Salir</a>
    </header>

    <main class="dashboard-content">
        <a href="resume_form.php" class="action-card" id="btn-ingresar"
            style="text-decoration: none; color: inherit; display: block;">
            <div class="icon">📝</div>
            <h3>Ingresar Hoja de Vida</h3>
            <p>Registra tu perfil en nuestra base de datos.</p>
        </a>
        <div class="action-card" id="btn-consultar">
            <div class="icon">🔍</div>
            <h3>Consultar</h3>
            <p>Ver las hojas de vida registradas.</p>
        </div>

        <a href="api/download_database.php" class="action-card"
            style="text-decoration: none; color: inherit; display: block;">
            <div class="icon">💾</div>
            <h3>Descargar Base de Datos</h3>
            <p>Exportar todos los registros (CSV).</p>
        </a>
    </main>

    <!-- Modal for Listing Resumes -->
    <div id="list-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Hojas de Vida Registradas</h2>
            <div id="resumes-list">
                <!-- Resumes will be loaded here via JS -->
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>