<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talento Sumapaz</title>
    <link rel="stylesheet" href="static/css/style.css?v=<?php echo file_exists('static/css/style.css') ? filemtime('static/css/style.css') : '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Drawer Styles */
        .login-drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .login-drawer {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            max-width: 90%;
            height: 100%;
            background: #fff;
            z-index: 2001;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease-in-out;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }

        .login-drawer.active {
            right: 0;
        }

        .login-drawer-overlay.active {
            display: block;
            opacity: 1;
        }

        .close-drawer {
            position: absolute;
            top: 20px;
            left: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #999;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-drawer:hover {
            color: var(--primary-color);
        }

        :root {
            --primary-color: #2e7d32;
            /* Deep Green */
            --secondary-color: #f57c00;
            /* Vibrant Orange */
            --accent-color: #8d6e63;
            /* Earthy Brown */
            --text-color: #333;
            --bg-color: #f9fbf9;
            --card-bg: #fff;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding-top: 70px;
            /* Space for the fixed navbar */
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 5%;
            height: 70px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .logo-img {
            height: 50px;
            margin-right: 15px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 25px;
            margin: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 400;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-auth {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-login {
            text-decoration: none;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .btn-register {
            text-decoration: none;
            background: var(--primary-color);
            color: #fff;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: #1b5e20;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 20px 20px;
        }
    </style>
</head>

<body>
    <!-- Login Drawer -->
    <div id="loginDrawerOverlay" class="login-drawer-overlay" onclick="toggleLoginDrawer()"></div>
    <div id="loginDrawer" class="login-drawer">
        <button class="close-drawer" onclick="toggleLoginDrawer()"><i class="fas fa-times"></i></button>
        <div style="text-align: center; margin-top: 20px; margin-bottom: 25px;">
            <img src="static/img/logo_sumapaz.png" alt="Logo" style="height: 80px; margin-bottom: 15px;">
            <h2 style="color: var(--primary-color); font-size: 2rem; margin-bottom: 10px;">Iniciar Sesión</h2>
            <p style="color: #666;">Ingresa tus credenciales para acceder</p>
        </div>

        <form action="api/login.php" method="POST">
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Usuario</label>
                <input type="text" name="username" required placeholder="Nombre de usuario"
                    style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; outline: none; transition: border-color 0.3s;">
            </div>
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••"
                    style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; outline: none;">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; font-size: 0.9rem;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="remember"> Recordar usuario
                </label>
                <a href="#" style="color: var(--secondary-color); text-decoration: none;">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-register"
                style="width: 100%; border: none; padding: 16px; font-size: 1.1rem; cursor: pointer; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);">Entrar</button>
        </form>

        <div style="margin-top: auto; text-align: center; font-size: 0.9rem; color: #666;">
            ¿No tienes una cuenta? <a href="register_page.php" style="color: var(--primary-color); font-weight: bold; text-decoration: none;">Contáctanos</a>
        </div>
    </div>
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <img src="static/img/logo_sumapaz.png" alt="Logo" style="height: 40px; margin-right: 12px;">
            <span class="logo-text">Talento Sumapaz</span>
        </a>
        <ul class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php" style="color:var(--secondary-color); font-weight:bold;">Tablero</a></li>
            <?php endif; ?>
        </ul>
        <div class="nav-auth">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Hola, <strong>
                        <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Usuario'); ?>
                    </strong></span>
                <a href="api/logout.php" class="btn-login">Cerrar Sesión</a>
            <?php else: ?>
                <a href="javascript:void(0)" class="btn-login" onclick="toggleLoginDrawer()">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">