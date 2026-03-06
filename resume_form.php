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
            <!-- 1. INFORMACIÓN PERSONAL -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>1. Información Personal</h3>

                <div class="grid-2">

                    <input type="text" name="full_name" placeholder="Nombre completo">

                    <select name="id_type">
                        <option value="">Tipo documento</option>
                        <option value="CC">Cédula</option>
                        <option value="CE">Cédula extranjería</option>
                        <option value="TI">Tarjeta identidad</option>
                    </select>

                    <input type="text" name="document_id" placeholder="Número documento">

                    <input type="file" name="id_file" accept=".pdf">

                    <input type="date" name="birth_date">

                    <select name="birth_country" id="birth_country"></select>

                    <select name="birth_department" id="birth_department"></select>

                    <select name="birth_city" id="birth_city"></select>

                    <select name="department" id="department"></select>

                    <select name="city" id="city"></select>

                    <input type="tel" name="phone" placeholder="Teléfono">

                    <input type="email" name="email" placeholder="Correo">

                    <input type="file" name="photo" accept="image/*">

                </div>

                <textarea name="profile_description" placeholder="Perfil profesional"></textarea>

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
                    <div class="skill-card">Atención al cliente</div>
                    <div class="skill-card">Programación</div>
                    <div class="skill-card">Diseño gráfico</div>
                    <div class="skill-card">Marketing</div>
                    <div class="skill-card">Logística</div>
                    <div class="skill-card">Cocina</div>
                    <div class="skill-card">Panadería</div>
                    <div class="skill-card">Maquinaria agrícola</div>
                    <div class="skill-card">Turismo rural</div>

                </div>

                <input type="hidden" name="skills" id="skills-input">

            </div>

            <!-- ========================= -->
            <!-- EDUCACIÓN -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>3. Formación Académica</h3>

                <div id="education-list"></div>

                <button type="button" id="add-education-btn">+ Agregar Estudio</button>

            </div>

            <!-- ========================= -->
            <!-- EXPERIENCIA -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>4. Experiencia Laboral</h3>

                <div id="experience-list"></div>

                <button type="button" id="add-experience-btn">+ Agregar Experiencia</button>

            </div>

            <!-- ========================= -->
            <!-- REFERENCIAS -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>5. Referencias</h3>

                <div class="grid-2">

                    <input type="text" name="ref_p1_name" placeholder="Referencia personal nombre">
                    <input type="tel" name="ref_p1_phone" placeholder="Teléfono">

                    <input type="text" name="ref_p2_name" placeholder="Referencia personal nombre">
                    <input type="tel" name="ref_p2_phone" placeholder="Teléfono">

                    <input type="text" name="ref_f1_name" placeholder="Referencia familiar nombre">
                    <input type="tel" name="ref_f1_phone" placeholder="Teléfono">

                    <input type="text" name="ref_f2_name" placeholder="Referencia familiar nombre">
                    <input type="tel" name="ref_f2_phone" placeholder="Teléfono">

                </div>

            </div>

            <button type="submit" class="btn-submit-large">Guardar Hoja de Vida</button>

        </form>

    </div>
</div>


<!-- ========================= -->
<!-- TEMPLATES -->
<!-- ========================= -->

<template id="education-item-tpl">

    <div class="dynamic-item">

        <select name="education_INDEX_level">
            <option value="">Nivel educativo</option>
            <option>Bachiller</option>
            <option>Técnico</option>
            <option>Tecnólogo</option>
            <option>Universitario</option>
            <option>Especialización</option>
            <option>Maestría</option>
        </select>

        <input type="text" name="education_INDEX_institution" placeholder="Institución">

        <label>Inicio</label>
        <input type="date" name="education_INDEX_start_date">

        <label>Fin</label>
        <input type="date" name="education_INDEX_end_date">

        <label>
            <input type="checkbox" name="education_INDEX_is_current">
            En curso
        </label>

        <input type="file" name="education_INDEX_file" accept=".pdf">

        <button type="button" class="remove">Eliminar</button>

    </div>

</template>


<template id="experience-item-tpl">

    <div class="dynamic-item">

        <input type="text" name="experience_INDEX_company" placeholder="Empresa">

        <input type="text" name="experience_INDEX_role" placeholder="Cargo">

        <textarea name="experience_INDEX_description" placeholder="Descripción"></textarea>

        <label>Inicio</label>
        <input type="date" name="experience_INDEX_start_date">

        <label>Fin</label>
        <input type="date" name="experience_INDEX_end_date">

        <label>
            <input type="checkbox" name="experience_INDEX_is_current">
            Actualmente aquí
        </label>

        <input type="file" name="experience_INDEX_file" accept=".pdf">

        <button type="button" class="remove">Eliminar</button>

    </div>

</template>


<!-- ========================= -->
<!-- ESTILOS -->
<!-- ========================= -->

<style>
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


<!-- ========================= -->
<!-- JAVASCRIPT -->
<!-- ========================= -->

<script>


    // =========================
    // HABILIDADES
    // =========================

    const skills = document.querySelectorAll(".skill-card");
    const hidden = document.getElementById("skills-input");

    skills.forEach(card => {
        card.addEventListener("click", () => {

            card.classList.toggle("selected");

            const selected = [...document.querySelectorAll(".skill-card.selected")]
                .map(x => x.textContent);

            hidden.value = selected.join(",");

        });
    });


    // =========================
    // EDUCACIÓN
    // =========================

    let eduIndex = 0;

    document.getElementById("add-education-btn").onclick = () => {

        let tpl = document.getElementById("education-item-tpl").innerHTML;

        tpl = tpl.replaceAll("INDEX", eduIndex);

        document.getElementById("education-list")
            .insertAdjacentHTML("beforeend", tpl);

        eduIndex++;

    };


    // =========================
    // EXPERIENCIA
    // =========================

    let expIndex = 0;

    document.getElementById("add-experience-btn").onclick = () => {

        let tpl = document.getElementById("experience-item-tpl").innerHTML;

        tpl = tpl.replaceAll("INDEX", expIndex);

        document.getElementById("experience-list")
            .insertAdjacentHTML("beforeend", tpl);

        expIndex++;

    };


    // =========================
    // PAÍSES
    // =========================

    fetch("https://restcountries.com/v3.1/all")
        .then(res => res.json())
        .then(data => {

            const select = document.getElementById("birth_country");

            data.sort((a, b) => a.name.common.localeCompare(b.name.common));

            data.forEach(c => {

                let opt = document.createElement("option");

                opt.value = c.name.common;
                opt.textContent = c.name.common;

                select.appendChild(opt);

            });

        });


    // =========================
    // DEPARTAMENTOS COLOMBIA
    // =========================

    const departamentos = [
        "Amazonas", "Antioquia", "Arauca", "Atlántico", "Bolívar",
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
        opt.textContent = d;

        depSelect.appendChild(opt);

    });

</script>

<?php include 'footer.php'; ?>