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
                <h2>Cargar Hoja de Vida</h2>
                <p>Ingresa la información para registrar la hoja de vida en el sistema.</p>
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
                    <div class="input-group">
                        <label>Departamento de Residencia</label>
                        <input type="text" name="department" placeholder="Ej: Cundinamarca">
                    </div>
                    <div class="input-group">
                        <label>Municipio de Residencia</label>
                        <input type="text" name="city" placeholder="Ej: Fusagasugá">
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
                        <label>Nicho / Área de Interés</label>
                        <input type="text" name="niche" placeholder="Ej: Construcción, Administrativo...">
                    </div>
                    <div class="input-group">
                        <label>Profesión u Oficio Específico</label>
                        <input type="text" name="profesion" placeholder="Ej: Maestro de obra, Secretaria...">
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

            <!-- 2. Habilidades -->
            <div class="form-section">
                <h3>2. Habilidades</h3>
                <div class="input-group full-width">
                    <label>Habilidades (Separa por comas)</label>
                    <input type="text" name="skills" placeholder="Ej: Albañilería, Carpintería, Excel...">
                </div>
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

<?php include 'footer.php'; ?>