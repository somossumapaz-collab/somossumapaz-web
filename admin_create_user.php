<?php
require_once 'database_functions.php';
check_auth();

// Only admin allowed
if ($_SESSION['rol'] !== 'admin') {
    header('Location: user_panel.php');
    exit;
}

include 'header.php';
?>

<div style="max-width: 600px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: var(--primary-color); font-size: 2rem;">Crear Usuario</h2>
        <p style="color: #666;">Administración de cuentas</p>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; border: 1px solid #c8e6c9;">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_SESSION['flash_message']); unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; border: 1px solid #ffcdd2;">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <form action="api/admin_create_user.php" method="POST">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nombre Completo</label>
            <input type="text" name="nombre" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; transition: border-color 0.3s;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Correo Electrónico</label>
            <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Contraseña</label>
            <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Rol</label>
            <select name="rol_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; background: #fff;">
                <option value="2">Usuario Normal</option>
                <option value="1">Administrador</option>
            </select>
        </div>

        <button type="submit" class="btn-register" style="width: 100%; border: none; padding: 14px; font-size: 1.1rem; cursor: pointer; font-weight: 600; margin-top: 10px;">Crear Usuario</button>
    </form>
</div>

<?php include 'footer.php'; ?>
