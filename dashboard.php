<?php
include 'header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit;
}
?>

<div
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid var(--primary-color); padding-bottom: 15px;">
    <h2 style="color: var(--primary-color); margin: 0;">Panel de Consulta - Tablero de Control</h2>
    <div style="display: flex; gap: 15px;">
        <a href="api/download_database.php" class="btn-login" style="font-size: 0.9rem; text-decoration: none;"><i
                class="fas fa-database"></i> Exportar Base de Datos (CSV)</a>
        <a href="resume_form.php" class="btn-register" style="font-size: 0.9rem; text-decoration: none;"><i
                class="fas fa-plus"></i> Nueva Hoja de Vida</a>
    </div>
</div>

<div style="background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 25px; display: flex; gap: 20px; align-items: center;">
        <div style="flex: 1; position: relative;">
            <i class="fas fa-search"
                style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
            <input type="text" id="searchInput" placeholder="Filtrar por nombre, cargo o habilidad..."
                style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
        </div>
        <button onclick="window.location.reload()" class="btn-login" style="border: none; cursor: pointer;"><i
                class="fas fa-sync-alt"></i> Refrescar</button>
    </div>

    <div id="resumes-list" class="table-container" style="overflow-x: auto;">
        <!-- Carga dinámica vía JavaScript -->
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #eee;">Nombre</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee;">Nicho/Cargo</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee;">Contacto</th>
                    <th style="padding: 15px; border-bottom: 2px solid #eee;">Acciones</th>
                </tr>
            </thead>
            <tbody id="resumeTableBody">
                <!-- Se llenará con main.js -->
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // El script main.js ya contiene la lógica de carga, 
        // solo aseguramos que el buscador filtre localmente
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#resumeTableBody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    });
</script>

<?php include 'footer.php'; ?>