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
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
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
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <img src="static/img/logo.png" alt="Talento Sumapaz" class="logo-img">
            <span class="logo-text">Talento Sumapaz</span>
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="blog.php">Noticias</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="user_panel.php">Panel de Usuario</a></li>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a href="admin_create_user.php" style="color:var(--primary-color); font-weight:bold;">Crear Usuario</a></li>
                <?php endif; ?>
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
                <a href="login_page.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">