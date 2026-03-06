<?php
session_start();
require_once 'database_functions.php';
include 'header.php';
check_auth();
?>

<div class="form-wrapper">
    <div class="form-container">

        <a href="dashboard.php" class="back-link">← Volver al Panel de Consulta</a>

        <form id="resume-form" method="POST" enctype="multipart/form-data" action="api/submit_resume.php">

            <h2>Registrar Hoja de Vida</h2>

            <!-- ========================= -->
            <!-- INFORMACIÓN PERSONAL -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>1. Información Personal</h3>

                <div class="grid-2">

                    <div class="input-group">
                        <label>Nombre completo</label>
                        <input type="text" name="full_name">
                    </div>

                    <div class="input-group">
                        <label>Tipo de documento</label>
                        <select name="id_type">
                            <option value="">Seleccione</option>
                            <option value="CC">Cédula</option>
                            <option value="CE">Cédula extranjería</option>
                            <option value="TI">Tarjeta identidad</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Número documento</label>
                        <input type="text" name="document_id">
                    </div>

                    <div class="input-group">
                        <label>Documento identidad (PDF)</label>
                        <input type="file" name="id_file" accept=".pdf">
                    </div>

                    <div class="input-group">
                        <label>Fecha nacimiento</label>
                        <input type="date" name="birth_date">
                    </div>

                    <div class="input-group">
                        <label>País nacimiento</label>
                        <select name="birth_country" id="birth_country"></select>
                    </div>

                    <div class="input-group">
                        <label>Departamento nacimiento</label>
                        <select name="birth_department" id="birth_department"></select>
                    </div>

                    <div class="input-group">
                        <label>Municipio nacimiento</label>
                        <select name="birth_city" id="birth_city"></select>
                    </div>

                    <div class="input-group">
                        <label>Departamento residencia</label>
                        <select name="department" id="department"></select>
                    </div>

                    <div class="input-group">
                        <label>Municipio residencia</label>
                        <select name="city" id="city"></select>
                    </div>

                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="phone">
                    </div>

                    <div class="input-group">
                        <label>Correo</label>
                        <input type="email" name="email">
                    </div>

                    <div class="input-group">
                        <label>Foto perfil</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>

                </div>

                <div class="input-group">
                    <label>Perfil profesional</label>
                    <textarea name="profile_description"></textarea>
                </div>

            </div>


            <!-- ========================= -->
            <!-- HABILIDADES -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>2. Habilidades</h3>

                <div id="skills-mosaic" class="skills-mosaic">

                    <div class="skill-card">Agricultura</div>
                    <div class="skill-card">Ganadería</div>
                    <div class="skill-card">Construcción</div>
                    <div class="skill-card">Electricidad</div>
                    <div class="skill-card">Carpintería</div>
                    <div class="skill-card">Ventas</div>
                    <div class="skill-card">Atención cliente</div>
                    <div class="skill-card">Programación</div>
                    <div class="skill-card">Diseño gráfico</div>
                    <div class="skill-card">Marketing</div>
                    <div class="skill-card">Logística</div>
                    <div class="skill-card">Cocina</div>

                </div>

                <input type="hidden" name="skills" id="skills-input">

            </div>


            <!-- ========================= -->
            <!-- EDUCACIÓN -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>3. Formación Académica</h3>

                <div id="education-list"></div>

                <button type="button" id="add-education-btn">+ Agregar estudio</button>

            </div>


            <!-- ========================= -->
            <!-- EXPERIENCIA -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>4. Experiencia laboral</h3>

                <div id="experience-list"></div>

                <button type="button" id="add-experience-btn">+ Agregar experiencia</button>

            </div>


            <!-- ========================= -->
            <!-- REFERENCIAS -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>5. Referencias</h3>

                <div class="grid-2">

                    <div class="input-group">
                        <label>Referencia personal</label>
                        <input type="text" name="ref_p1_name">
                    </div>

                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="ref_p1_phone">
                    </div>

                    <div class="input-group">
                        <label>Referencia personal</label>
                        <input type="text" name="ref_p2_name">
                    </div>

                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="ref_p2_phone">
                    </div>

                    <div class="input-group">
                        <label>Referencia familiar</label>
                        <input type="text" name="ref_f1_name">
                    </div>

                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="ref_f1_phone">
                    </div>

                    <div class="input-group">
                        <label>Referencia familiar</label>
                        <input type="text" name="ref_f2_name">
                    </div>

                    <div class="input-group">
                        <label>Teléfono</label>
                        <input type="tel" name="ref_f2_phone">
                    </div>

                </div>

            </div>

            <button type="submit" class="btn-submit-large">Guardar Hoja de Vida</button>

        </form>

    </div>
</div>


<style>
    .input-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 10px;
    }

    .skills-mosaic {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .skill-card {
        border: 1px solid #ccc;
        padding: 8px 12px;
        border-radius: 20px;
        cursor: pointer;
    }

    .skill-card.selected {
        background: #2e7d32;
        color: white;
    }

    .dynamic-item {
        border: 1px solid #eee;
        padding: 15px;
        margin-top: 10px;
        border-radius: 10px;
    }
</style>


<script>


    /* =========================
    HABILIDADES
    ========================= */

    const skills = document.querySelectorAll(".skill-card");
    const hidden = document.getElementById("skills-input");

    skills.forEach(card => {
        card.onclick = () => {

            card.classList.toggle("selected");

            const selected = [...document.querySelectorAll(".skill-card.selected")]
                .map(x => x.textContent);

            hidden.value = selected.length ? selected.join(",") : "";

        };
    });


    /* =========================
    EDUCACIÓN
    ========================= */

    let eduIndex = 0;

    document.getElementById("add-education-btn").onclick = () => {

        const html = `
<div class="dynamic-item">

<label>Nivel educativo</label>
<select name="education_${eduIndex}_level">
<option value="">Seleccione</option>
<option>Bachiller</option>
<option>Técnico</option>
<option>Tecnólogo</option>
<option>Universitario</option>
</select>

<label>Institución</label>
<input type="text" name="education_${eduIndex}_institution">

<label>Inicio</label>
<input type="date" name="education_${eduIndex}_start_date">

<label>Fin</label>
<input type="date" name="education_${eduIndex}_end_date">

<label>Certificado PDF</label>
<input type="file" name="education_${eduIndex}_file">

<button type="button" onclick="this.parentNode.remove()">Eliminar</button>

</div>
`;

        document.getElementById("education-list").insertAdjacentHTML("beforeend", html);

        eduIndex++;

    };


    /* =========================
    EXPERIENCIA
    ========================= */

    let expIndex = 0;

    document.getElementById("add-experience-btn").onclick = () => {

        const html = `
<div class="dynamic-item">

<label>Empresa</label>
<input type="text" name="experience_${expIndex}_company">

<label>Cargo</label>
<input type="text" name="experience_${expIndex}_role">

<label>Descripción</label>
<textarea name="experience_${expIndex}_description"></textarea>

<label>Inicio</label>
<input type="date" name="experience_${expIndex}_start_date">

<label>Fin</label>
<input type="date" name="experience_${expIndex}_end_date">

<label>Soporte PDF</label>
<input type="file" name="experience_${expIndex}_file">

<button type="button" onclick="this.parentNode.remove()">Eliminar</button>

</div>
`;

        document.getElementById("experience-list").insertAdjacentHTML("beforeend", html);

        expIndex++;

    };


    /* =========================
    PAÍSES
    ========================= */

    const countries = [
        "", "Colombia", "Argentina", "Bolivia", "Brasil", "Chile", "Ecuador",
        "Perú", "Uruguay", "Paraguay", "Venezuela", "México", "España",
        "Estados Unidos", "Canadá", "Panamá"
    ];

    const countrySelect = document.getElementById("birth_country");

    countries.forEach(c => {
        let opt = document.createElement("option");
        opt.value = c;
        opt.textContent = c || "Seleccione";
        countrySelect.appendChild(opt);
    });


    /* =========================
    DEPARTAMENTOS
    ========================= */

    const departamentos = [
        "", "Amazonas", "Antioquia", "Arauca", "Atlántico", "Bolívar",
        "Boyacá", "Caldas", "Caquetá", "Casanare", "Cauca",
        "Cesar", "Chocó", "Córdoba", "Cundinamarca", "Guainía",
        "Guaviare", "Huila", "La Guajira", "Magdalena", "Meta",
        "Nariño", "Norte de Santander", "Putumayo", "Quindío",
        "Risaralda", "San Andrés", "Santander", "Sucre",
        "Tolima", "Valle del Cauca", "Vaupés", "Vichada"
    ];

    const depSelect = document.getElementById("department");

    departamentos.forEach(d => {
        let opt = document.createElement("option");
        opt.value = d;
        opt.textContent = d || "Seleccione";
        depSelect.appendChild(opt);
    });


    /* =========================
    NORMALIZAR CAMPOS VACÍOS
    ========================= */

    document.getElementById("resume-form").addEventListener("submit", function () {

        this.querySelectorAll("input,textarea,select").forEach(field => {
            if (!field.value) field.value = "";
        });

    });

</script>

<?php include 'footer.php'; ?>