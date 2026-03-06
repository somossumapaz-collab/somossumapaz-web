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
            <!-- 1 INFORMACION PERSONAL -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>1. Información Personal</h3>

                <div class="grid-2">

                    <div>
                        <label>Nombre completo</label>
                        <input type="text" name="full_name">
                    </div>

                    <div>
                        <label>Tipo documento</label>
                        <select name="id_type">
                            <option value="">Seleccione</option>
                            <option value="CC">Cédula</option>
                            <option value="CE">Cédula extranjería</option>
                            <option value="TI">Tarjeta identidad</option>
                        </select>
                    </div>

                    <div>
                        <label>Número documento</label>
                        <input type="text" name="document_id">
                    </div>

                    <div>
                        <label>Documento identidad (PDF)</label>
                        <input type="file" name="id_file" accept=".pdf">
                    </div>

                    <div>
                        <label>Fecha nacimiento</label>
                        <input type="date" name="birth_date">
                    </div>

                    <div>
                        <label>País nacimiento</label>
                        <select name="birth_country" id="birth_country">
                            <option value="">Seleccione</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label>Departamento nacimiento</label>
                        <select name="birth_department" id="birth_department">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div>
                        <label>Municipio nacimiento</label>
                        <select name="birth_city" id="birth_city">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div>
                        <label>Departamento residencia</label>
                        <select name="department" id="department">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div>
                        <label>Municipio residencia</label>
                        <select name="city" id="city">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="phone">
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>

                    <div>
                        <label>Foto perfil</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>

                </div>

                <label>Perfil profesional</label>
                <textarea name="profile_description" rows="4"></textarea>

            </div>


            <!-- ========================= -->
            <!-- 2 HABILIDADES -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>2. Habilidades</h3>

                <p>Seleccione sus habilidades principales</p>

                <div id="skills-mosaic" class="skills-mosaic"></div>

                <input type="hidden" name="skills" id="skills-input">

            </div>


            <!-- ========================= -->
            <!-- 3 EDUCACION -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>3. Formación Académica</h3>

                <div id="education-list"></div>

                <button type="button" id="add-education-btn">+ Agregar Estudio</button>

            </div>


            <!-- ========================= -->
            <!-- 4 EXPERIENCIA -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>4. Experiencia Laboral</h3>

                <div id="experience-list"></div>

                <button type="button" id="add-experience-btn">+ Agregar Experiencia</button>

            </div>


            <!-- ========================= -->
            <!-- 5 REFERENCIAS -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>5. Referencias</h3>

                <div class="grid-2">

                    <div>
                        <label>Referencia personal nombre</label>
                        <input type="text" name="ref_p1_name">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_p1_phone">
                    </div>

                    <div>
                        <label>Referencia personal nombre</label>
                        <input type="text" name="ref_p2_name">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_p2_phone">
                    </div>

                    <div>
                        <label>Referencia familiar nombre</label>
                        <input type="text" name="ref_f1_name">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_f1_phone">
                    </div>

                    <div>
                        <label>Referencia familiar nombre</label>
                        <input type="text" name="ref_f2_name">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_f2_phone">
                    </div>

                </div>

            </div>


            <button type="submit" class="btn-submit-large">
                Guardar Hoja de Vida
            </button>

        </form>

    </div>
</div>


<!-- ========================= -->
<!-- TEMPLATE EDUCACION -->
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

        <label>Fecha inicio</label>
        <input type="date" name="education_INDEX_start_date">

        <label>Fecha fin</label>
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


<!-- ========================= -->
<!-- TEMPLATE EXPERIENCIA -->
<!-- ========================= -->

<template id="experience-item-tpl">

    <div class="dynamic-item">

        <label>Empresa</label>
        <input type="text" name="experience_INDEX_company">

        <label>Cargo</label>
        <input type="text" name="experience_INDEX_role">

        <label>Descripción</label>
        <textarea name="experience_INDEX_description"></textarea>

        <label>Fecha inicio</label>
        <input type="date" name="experience_INDEX_start_date">

        <label>Fecha fin</label>
        <input type="date" name="experience_INDEX_end_date">

        <label>
            <input type="checkbox" name="experience_INDEX_is_current">
            Actualmente aquí
        </label>

        <label>Certificado laboral</label>
        <input type="file" name="experience_INDEX_file" accept=".pdf">

        <button type="button" class="remove">Eliminar</button>

    </div>

</template>


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

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
</style>


<?php include 'footer.php'; ?>