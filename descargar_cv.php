<?php
// descargar_cv.php
// Proxy to the actual API to allow cleaner URLs without depending solely on .htaccess
if (isset($_GET['id'])) {
    require_once __DIR__ . '/api/download_resume_pdf.php';
} else {
    header("Location: dashboard.php");
    exit;
}
