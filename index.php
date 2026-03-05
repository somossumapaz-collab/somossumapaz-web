<?php
session_start();
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <p>
            Nuestra marca se construye desde la comunidad, desde el somos: somos una comunidad perseverante que habita
            un territorio único y hemos mantenido una forma de vida que busca honrarlo.
            <br><br>
            Somos habitantes, somos productores y somos guardianes de este lugar. Somos Bogotá rural y creemos
            firmemente que podemos proveerle productos con la historia de nuestra gente, nuestra tierra y nuestro aire.
            <br><br>
            Somos la comunidad productora que cuida el páramo más grande del mundo.
        </p>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="about-grid">
        <div class="about-text">
            <h2>¿QUIÉNES SOMOS?</h2>
            <p>Somos un colectivo que impulsa y promueve las diversas iniciativas productivas de nuestra localidad,
                Sumapaz.</p>
            <p>Habitamos y producimos en lo más alto de Bogotá, asumiendo la importante responsabilidad de proteger y
                cuidar el páramo más grande del mundo.</p>
            <p>Esto nos convierte en una comunidad de altura, personas comprometidas en producir y proveer productos de
                altura y con historia a toda la región.</p>
        </div>
        <div class="about-img">
            <img src="static/img/about_image.png" alt="Comunidad Sumapaz">
        </div>
    </div>
</section>

<!-- Logic container for the side panel -->
<div class="login-sidebar" id="login-sidebar">
    <!-- Login Form -->
    <div class="login-content" id="login-form-container">
        <button class="close-sidebar" id="close-login">&times;</button>
        <h1>Somos Sumapaz</h1>
        <p>Bienvenido al portal de empleo</p>
        <form action="api/login.php" method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Usuario" required>
            </div>
            <div class="input-group password-group">
                <input type="password" name="password" id="password-input" placeholder="Contraseña" required>
                <span class="toggle-password" id="toggle-password">👁️</span>
            </div>

            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Recordar usuario
                </label>
                <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-primary">Ingresar</button>

            <div class="divider">o</div>

            <a href="#" class="btn-google" style="text-decoration: none;" onclick="alert('Google Login Simulado')">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg"
                    alt="Google Logo">
                Ingresar con Google
            </a>

            <div class="create-account">
                ¿No tienes cuenta? <a href="#" id="show-register">Crear usuario</a>
            </div>
        </form>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="flash-messages">
                <p style="color: red;">
                    <?php echo $_SESSION['flash_error'];
                    unset($_SESSION['flash_error']); ?>
                </p>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="flash-messages">
                <p style="color: green;">
                    <?php echo $_SESSION['flash_success'];
                    unset($_SESSION['flash_success']); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Register Form (Hidden by default) -->
    <div class="login-content" id="register-form-container" style="display: none;">
        <button class="close-sidebar" id="close-register">&times;</button>
        <h1>Crear Cuenta</h1>
        <p>Únete a Somos Sumapaz</p>
        <form action="api/register.php" method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Elige un Usuario" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Elige una Contraseña" required>
            </div>

            <button type="submit" class="btn-primary">Registrarse</button>

            <div class="create-account" style="margin-top: 2rem;">
                ¿Ya tienes cuenta? <a href="#" id="show-login">Ingresar</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>