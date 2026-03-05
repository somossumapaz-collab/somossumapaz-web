<?php include 'header.php'; ?>

<div style="text-align: center; margin: 20px 0;">
    <h1 style="color: var(--primary-color); font-size: 2.5rem;">Blog de Talento Sumapaz</h1>
    <p style="color: #666;">Las últimas novedades de nuestro proyecto y la región</p>
</div>

<div
    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-bottom: 60px;">
    <!-- Mock Blog Post 1 -->
    <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div
            style="height: 200px; background: var(--accent-color); display: flex; align-items: center; justify-content: center; color: #fff;">
            <i class="fas fa-mountain fa-4x"></i>
        </div>
        <div style="padding: 25px;">
            <span
                style="color: var(--secondary-color); font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">Evento</span>
            <h3 style="margin: 10px 0; color: var(--primary-color);">Lanzamiento de Talento Sumapaz en Fusagasugá</h3>
            <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">Gran acogida del proyecto en la región,
                conectando a expertos con nuevas oportunidades laborales.</p>
            <a href="#" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Leer más →</a>
        </div>
    </div>

    <!-- Mock Blog Post 2 -->
    <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div
            style="height: 200px; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: #fff;">
            <i class="fas fa-leaf fa-4x"></i>
        </div>
        <div style="padding: 25px;">
            <span
                style="color: var(--secondary-color); font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">Capacitación</span>
            <h3 style="margin: 10px 0; color: var(--primary-color);">Talleres de Sostenibilidad en el Páramo</h3>
            <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">Nuevos talleres gratuitos para los inscritos
                en Talento Sumapaz sobre prácticas agrícolas sostenibles.</p>
            <a href="#" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Leer más →</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>