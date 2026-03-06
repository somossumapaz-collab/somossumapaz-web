<?php
session_start();
require_once 'database_functions.php';
include 'header.php';
check_auth();
?>

<div class="form-wrapper">

    <div class="form-container">

        <a href="dashboard.php" class="back-link">← Volver al Panel</a>

        <h2>Registrar Hoja de Vida</h2>

        <div id="form-error"></div>

        <form id="resume-form" method="POST" enctype="multipart/form-data">

            <!-- ========================= -->
            <!-- INFORMACIÓN PERSONAL -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>1. Información Personal</h3>

                <div class="grid-2">

                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label>Tipo documento</label>
                        <select name="id_type" required>
                            <option value="">Seleccione</option>
                            <option value="CC">Cédula</option>
                            <option value="CE">Cédula extranjería</option>
                            <option value="TI">Tarjeta identidad</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Número documento</label>
                        <input type="text" name="document_id" required>
                    </div>

                    <div class="form-group">
                        <label>Archivo documento</label>
                        <input type="file" name="id_file" accept=".pdf">
                    </div>

                    <div class="form-group">
                        <label>Fecha nacimiento</label>
                        <input type="date" name="birth_date">
                    </div>

                    <div class="form-group">
                        <label>País nacimiento</label>
                        <select name="birth_country" id="birth_country"></select>
                    </div>

                    <div class="form-group">
                        <label>Departamento nacimiento</label>
                        <select name="birth_department" id="birth_department"></select>
                    </div>

                    <div class="form-group">
                        <label>Ciudad nacimiento</label>
                        <select name="birth_city" id="birth_city"></select>
                    </div>

                    <div class="form-group">
                        <label>Departamento residencia</label>
                        <select name="department" id="department"></select>
                    </div>

                    <div class="form-group">
                        <label>Ciudad residencia</label>
                        <select name="city" id="city"></select>
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="phone">
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="email">
                    </div>

                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>

                </div>

                <div class="form-group">
                    <label>Perfil profesional</label>
                    <textarea name="profile_description"></textarea>
                </div>

            </div>

            <!-- ========================= -->
            <!-- HABILIDADES -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>2. Habilidades</h3>

                <div id="skills-mosaic" class="skills-mosaic"></div>

                <input type="hidden" name="skills" id="skills-input">

            </div>

            <!-- ========================= -->
            <!-- EDUCACIÓN -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>3. Formación Académica</h3>

                <div id="education-list"></div>

                <button type="button" id="add-education-btn">Agregar estudio</button>

            </div>

            <!-- ========================= -->
            <!-- EXPERIENCIA -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>4. Experiencia laboral</h3>

                <div id="experience-list"></div>

                <button type="button" id="add-experience-btn">Agregar experiencia</button>

            </div>

            <button type="submit" class="btn-submit-large">
                Guardar Hoja de Vida
            </button>

        </form>

    </div>
</div>

<!-- ========================= -->
<!-- TEMPLATES -->
<!-- ========================= -->

<template id="education-item-tpl">

    <div class="dynamic-item">

        <label>Nivel educativo</label>
        <select name="education_INDEX_level">
            <option value="">Seleccione</option>
            <option>Bachiller</option>
            <option>Técnico</option>
            <option>Tecnólogo</option>
            <option>Universitario</option>
            <option>Especialización</option>
            <option>Maestría</option>
        </select>

        <label>Institución</label>
        <input type="text" name="education_INDEX_institution">

        <label>Inicio</label>
        <input type="date" name="education_INDEX_start_date">

        <label>Fin</label>
        <input type="date" name="education_INDEX_end_date">

        <label>
            <input type="checkbox" name="education_INDEX_is_current">
            En curso
        </label>

        <label>Certificado</label>
        <input type="file" name="education_INDEX_file" accept=".pdf">

        <button type="button" class="remove">Eliminar</button>

    </div>

</template>

<template id="experience-item-tpl">

    <div class="dynamic-item">

        <label>Empresa</label>
        <input type="text" name="experience_INDEX_company">

        <label>Cargo</label>
        <input type="text" name="experience_INDEX_role">

        <label>Descripción</label>
        <textarea name="experience_INDEX_description"></textarea>

        <label>Inicio</label>
        <input type="date" name="experience_INDEX_start_date">

        <label>Fin</label>
        <input type="date" name="experience_INDEX_end_date">

        <label>
            <input type="checkbox" name="experience_INDEX_is_current">
            Actualmente aquí
        </label>

        <label>Certificado</label>
        <input type="file" name="experience_INDEX_file" accept=".pdf">

        <button type="button" class="remove">Eliminar</button>

    </div>

</template>

<link rel="stylesheet" href="main.css">
<script src="main.js"></script>

<?php include 'footer.php'; ?>