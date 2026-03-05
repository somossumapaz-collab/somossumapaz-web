<?php include 'header.php'; ?>

<div
    style="max-width: 600px; margin: 20px auto; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: var(--primary-color); font-size: 2rem;">Crea tu Cuenta</h2>
        <p style="color: #666;">Únete a la red de Talento Sumapaz</p>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div
            style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; border: 1px solid #ffcdd2;">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['flash_message']);
            unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>

    <form action="api/register.php" method="POST">
        <!-- Fila 1: Nombre y Apellido -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nombre</label>
                <input type="text" name="nombre" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Apellido</label>
                <input type="text" name="apellido" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
        </div>

        <!-- Fila 2: Correo Electrónico -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Correo electrónico</label>
            <input type="email" name="email" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
        </div>

        <!-- Fila 3: Tipo Documento y Documento -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Tipo de Documento</label>
                <select name="tipo_documento" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; background: white;">
                    <option value="">Seleccione...</option>
                    <option value="CC">Cédula de Ciudadanía</option>
                    <option value="CE">Cédula de Extranjería</option>
                    <option value="TI">Tarjeta de Identidad</option>
                    <option value="PAS">Pasaporte</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Documento de Identidad</label>
                <input type="text" name="documento" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
        </div>

        <!-- Fila 4: Número de Teléfono -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Número de teléfono</label>
            <input type="tel" name="phone" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
        </div>

        <!-- Fila 5: Usuario -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Usuario</label>
            <input type="text" name="username" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
        </div>

        <!-- Fila 6: Contraseña y Confirmar -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Contraseña</label>
                <input type="password" name="password" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Confirmar contraseña</label>
                <input type="password" name="confirm_password" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
        </div>

        <div style="text-align: center;">
            <button type="submit" class="btn-register"
                style="border: none; padding: 12px 40px; font-size: 1rem; cursor: pointer; font-weight: 600; border-radius: 30px; box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);">
                Registrarme Ahora
            </button>
        </div>
    </form>

    <p style="text-align: center; margin-top: 25px; color: #666; font-size: 0.95rem;">
        ¿Ya tienes cuenta? <a href="login_page.php"
            style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Inicia sesión</a>
    </p>
</div>

<?php include 'footer.php'; ?>