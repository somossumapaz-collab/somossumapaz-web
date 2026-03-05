<?php include 'header.php'; ?>

<div class="hero-section"
    style="text-align: center; padding: 40px 20px 80px; background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%); border-radius: 20px; margin-bottom: 40px;">
    <h1 style="font-size: 3.5rem; color: var(--primary-color); margin-bottom: 20px;">Conecta tu Talento con el Sumapaz
    </h1>
    <p style="font-size: 1.2rem; color: #666; max-width: 700px; margin: 0 auto 40px;">Una plataforma diseñada para
        potenciar las capacidades de nuestra región y facilitar el encuentro entre oportunidades y habilidades.</p>
    <div style="display: flex; gap: 20px; justify-content: center;">
        <a href="register_page.php" class="btn-register" style="padding: 15px 40px; font-size: 1.1rem;">Empezar
            Ahora</a>
        <a href="blog.php" class="btn-login" style="padding: 15px 40px; font-size: 1.1rem;">Ver Noticias</a>
    </div>
</div>

<div class="features-grid"
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 60px;">
    <div class="feature-card"
        style="background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
        <i class="fas fa-file-invoice" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
        <h3 style="color: var(--primary-color);">Registro de Hojas de Vida</h3>
        <p>Inscribe tu perfil profesional y académico de manera sencilla y segura.</p>
    </div>
    <div class="feature-card"
        style="background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
        <i class="fas fa-chart-line" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
        <h3 style="color: var(--primary-color);">Panel de Consulta</h3>
        <p>Haz seguimiento de tu información y descarga tus certificados registrados.</p>
    </div>
    <div class="feature-card"
        style="background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
        <i class="fas fa-newspaper" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
        <h3 style="color: var(--primary-color);">Noticias Regionales</h3>
        <p>Mantente al día con las últimas novedades del proyecto y la región del Sumapaz.</p>
    </div>
</div>

<?php include 'footer.php'; ?>