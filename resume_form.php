<?php
session_start();
require_once 'database_functions.php';
include 'header.php';
check_auth();
?>

<div class="form-wrapper">
    <div class="form-container">
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" class="back-link">← Volver al Panel de Consulta</a>
        </div>

        <form id="resume-form" class="comprehensive-form" method="POST" enctype="multipart/form-data"
            action="api/submit_resume.php">
            <div class="form-header">
                <h2>Registrar Hoja de Vida</h2>
                <p>Completa el formulario para registrar una nueva hoja de vida. Cada envío crea un registro nuevo.</p>
            </div>

            <!-- 1. Información Personal -->
            <div class="form-section">
                <h3>1. Información Personal</h3>
                <div class="grid-2">
                    <div class="input-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="input-group">
                        <label>Tipo de Documento</label>
                        <select name="id_type" required>
                            <option value="">Seleccione...</option>
                            <option value="CC">Cédula de Ciudadanía</option>
                            <option value="CE">Cédula de Extranjería</option>
                            <option value="TI">Tarjeta de Identidad</option>
                            <option value="PEP">PEP</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Número de Documento</label>
                        <input type="text" name="document_id" required>
                    </div>
                    <div class="input-group">
                        <label>Documento de Identidad (PDF)</label>
                        <input type="file" name="id_file" accept=".pdf" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="birth_date">
                    </div>

                    <!-- Lugar de Nacimiento -->
                    <div class="input-group">
                        <label>País de Nacimiento</label>
                        <select name="birth_country" id="birth_country">
                            <option value="Colombia">Colombia</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Departamento de Nacimiento</label>
                        <select name="birth_department" id="birth_department">
                            <option value="">Seleccione Departamento...</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Municipio de Nacimiento</label>
                        <select name="birth_city" id="birth_city">
                            <option value="">Seleccione Municipio...</option>
                        </select>
                    </div>

                    <!-- Residencia -->
                    <div class="input-group">
                        <label>Departamento de Residencia</label>
                        <select name="department" id="department" required>
                            <option value="">Seleccione Departamento...</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Municipio de Residencia</label>
                        <select name="city" id="city" required>
                            <option value="">Seleccione Municipio...</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Teléfono de Contacto</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <div class="input-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label>Foto de Perfil (Opcional)</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                </div>
                <div class="input-group full-width">
                    <label>Perfil Profesional / Descripción</label>
                    <textarea name="profile_description" placeholder="Breve descripción del perfil..."
                        style="height: 100px;"></textarea>
                </div>
            </div>

            <!-- 2. Habilidades Mosaico -->
            <div class="form-section">
                <h3>2. Habilidades</h3>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">Selecciona tus principales habilidades
                    (puedes elegir varias):</p>
                <div id="skills-mosaic" class="skills-mosaic">
                    <!-- Dinámico vía JS -->
                </div>
                <!-- Hidden input to store selected skills as comma-separated string -->
                <input type="hidden" name="skills" id="skills-input">
            </div>

            <!-- 3. Formación Académica -->
            <div class="form-section">
                <h3>3. Formación Académica</h3>
                <div id="education-list">
                    <!-- Dinámico -->
                </div>
                <button type="button" class="btn-secondary" id="add-education-btn">+ Agregar Estudio</button>
            </div>

            <!-- 4. Experiencia Laboral -->
            <div class="form-section">
                <h3>4. Experiencia Laboral</h3>
                <div id="experience-list">
                    <!-- Dinámico -->
                </div>
                <button type="button" class="btn-secondary" id="add-experience-btn">+ Agregar Experiencia</button>
            </div>

            <!-- 5. Referencias -->
            <div class="form-section">
                <h3>5. Referencias</h3>
                <div class="grid-2">
                    <div class="dynamic-item">
                        <h4>Referencia Personal 1</h4>
                        <input type="text" name="ref_p1_name" placeholder="Nombre">
                        <input type="tel" name="ref_p1_phone" placeholder="Teléfono">
                        <input type="text" name="ref_p1_occupation" placeholder="Ocupación">
                    </div>
                    <div class="dynamic-item">
                        <h4>Referencia Personal 2</h4>
                        <input type="text" name="ref_p2_name" placeholder="Nombre">
                        <input type="tel" name="ref_p2_phone" placeholder="Teléfono">
                        <input type="text" name="ref_p2_occupation" placeholder="Ocupación">
                    </div>
                    <div class="dynamic-item">
                        <h4>Referencia Familiar 1</h4>
                        <input type="text" name="ref_f1_name" placeholder="Nombre">
                        <input type="tel" name="ref_f1_phone" placeholder="Teléfono">
                        <input type="text" name="ref_f1_relation" placeholder="Parentesco">
                    </div>
                    <div class="dynamic-item">
                        <h4>Referencia Familiar 2</h4>
                        <input type="text" name="ref_f2_name" placeholder="Nombre">
                        <input type="tel" name="ref_f2_phone" placeholder="Teléfono">
                        <input type="text" name="ref_f2_relation" placeholder="Parentesco">
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-submit-large">Guardar y Finalizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Plantillas para items dinámicos -->
<template id="education-item-tpl">
    <div class="dynamic-item education-entry"
        style="border: 1px solid #eee; padding: 15px; border-radius: 10px; margin-bottom: 10px;">
        <div class="grid-2">
            <input type="text" name="education_INDEX_institution" placeholder="Institución">
            <input type="text" name="education_INDEX_level" placeholder="Título / Nivel">
            <input type="date" name="education_INDEX_start_date" placeholder="Inicio">
            <input type="date" name="education_INDEX_end_date" placeholder="Fin">
        </div>
        <button type="button" class="btn-remove"
            style="color: red; background: none; border: none; cursor: pointer; margin-top: 5px;">Eliminar</button>
    </div>
</template>

<template id="experience-item-tpl">
    <div class="dynamic-item experience-entry"
        style="border: 1px solid #eee; padding: 15px; border-radius: 10px; margin-bottom: 10px;">
        <div class="grid-2">
            <input type="text" name="experience_INDEX_company" placeholder="Empresa">
            <input type="text" name="experience_INDEX_role" placeholder="Cargo">
            <input type="date" name="experience_INDEX_start_date" placeholder="Inicio">
            <input type="date" name="experience_INDEX_end_date" placeholder="Fin">
        </div>
        <button type="button" class="btn-remove"
            style="color: red; background: none; border: none; cursor: pointer; margin-top: 5px;">Eliminar</button>
    </div>
</template>

<style>
    /* Local style if not in CSS file yet */
    .skills-mosaic {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .skill-card {
        background: #fff;
        border: 1px solid #ddd;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .skill-card.selected {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
</style>

<?php include 'footer.php'; ?>