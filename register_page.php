<?php include 'header.php'; ?>

<div
    style="max-width: 600px; margin: 20px auto; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: var(--primary-color); font-size: 2rem;">Crea tu Cuenta</h2>
        <p style="color: #666;">Únete a la red de Talento Sumapaz</p>
    </div>

    <form action="api/register.php" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Usuario</label>
                <input type="text" name="username" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Correo electrónico</label>
                <input type="email" name="email" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Número de teléfono</label>
                <input type="tel" name="phone" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Dirección</label>
                <input type="text" name="address" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ciudad</label>
                <input type="text" name="city" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Departamento</label>
                <input type="text" name="department" required
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none;">
            </div>
        </div>

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

        <button type="submit" class="btn-register"
            style="width: 100%; border: none; padding: 14px; font-size: 1.1rem; cursor: pointer; font-weight: 600;">Regístrate</button>
    </form>

    <p style="text-align: center; margin-top: 25px; color: #666; font-size: 0.95rem;">
        ¿Ya tienes cuenta? <a href="login_page.php"
            style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Inicia sesión</a>
    </p>
</div>

<?php include 'footer.php'; ?>