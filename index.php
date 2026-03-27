<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
include 'header.php'; 
?>

<div style="height: calc(100vh - 150px); display: flex; align-items: center; justify-content: center; text-align: center;">
    <div>
        <div style="margin-bottom: 20px;">
            <img src="static/img/logo_sumapaz.png" alt="Logo" style="height: 120px; opacity: 0.15; filter: grayscale(100%);">
        </div>
        <h1 style="font-size: 4rem; color: var(--primary-color); margin-bottom: 10px; opacity: 0.1;">Talento Sumapaz</h1>
        <p style="color: #999; font-size: 1.1rem; letter-spacing: 2px; text-transform: uppercase;">Región de Oportunidades</p>
    </div>
</div>

<?php include 'footer.php'; ?>