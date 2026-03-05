<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'header.php';
?>

<div class="form-wrapper">
    <div class="form-container">
        <a href="dashboard.php" class="back-link">← Volver al Panel</a>

        <form id="resume-form" class="comprehensive-form" enctype="multipart/form-data">
            <div class="form-header">
                <h2>Hoja de Vida Completa</h2>
                <p>Por favor diligencia todos los campos requeridos.</p>
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
                            <option value="NIT">NIT</option>
                            <option value="PEP">PEP</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Número de Documento</label>
                        <input type="text" name="document_id" required>
                    </div>
                    <div class="input-group">
                        <label>Cargar Documento (PDF)</label>
                        <input type="file" name="id_file" accept=".pdf" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="birth_date" required>
                    </div>
                    <div class="input-group">
                        <label>Departamento de Nacimiento</label>
                        <select name="birth_department" id="birth_department" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Municipio de Nacimiento</label>
                        <select name="birth_city" id="birth_city" disabled required>
                            <option value="">Seleccione Departamento primero</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Departamento de Residencia</label>
                        <select name="department" id="department" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Municipio de Residencia</label>
                        <select name="city" id="city" disabled required>
                            <option value="">Seleccione Departamento primero</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Teléfono (Solo números)</label>
                        <input type="tel" name="phone" pattern="[0-9]+" title="Solo números" required>
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
                    <label>Perfil Profesional</label>
                    <textarea name="profile_description" placeholder="Breve descripción de su perfil..." required
                        style="height: 100px;"></textarea>
                </div>
            </div>

            <!-- 2. Habilidades y Nicho -->
            <div class="form-section">
                <h3>2. Habilidades</h3>
                <div class="input-group full-width">
                    <label>Seleccione su Nicho / Área</label>
                    <select name="niche" id="niche-select" required>
                        <option value="">Seleccione...</option>
                        <!-- Populated by JS -->
                    </select>
                </div>
                <div id="skills-container" class="skills-mosaic">
                    <p style="color:#666; font-style:italic;">Seleccione un nicho para ver habilidades sugeridas.
                    </p>
                </div>
                <input type="hidden" name="skills" id="skills-input">
            </div>

            <!-- 3. Formación Académica -->
            <div class="form-section">
                <h3>3. Formación Académica</h3>
                <div id="education-list">
                    <!-- Dynamic Items -->
                </div>
                <button type="button" class="btn-secondary" id="add-education">+ Agregar Estudio</button>
            </div>

            <!-- 4. Experiencia Laboral -->
            <div class="form-section">
                <h3>4. Experiencia Laboral</h3>
                <div id="experience-list">
                    <!-- Dynamic Items -->
                </div>
                <button type="button" class="btn-secondary" id="add-experience">+ Agregar Experiencia</button>
                <p style="font-size:0.8rem; color:#666; margin-top:5px;">* Máximo 5 experiencias laborales.</p>
            </div>

            <!-- 5. Referencias -->
            <div class="form-section">
                <h3>5. Referencias</h3>
                <div class="grid-2">
                    <!-- Personal 1 -->
                    <div class="dynamic-item">
                        <h4>Personal 1</h4>
                        <input type="text" name="ref_p1_name" placeholder="Nombre" required>
                        <input type="tel" name="ref_p1_phone" placeholder="Teléfono" required>
                        <input type="text" name="ref_p1_occupation" placeholder="Ocupación" required>
                    </div>
                    <!-- Personal 2 -->
                    <div class="dynamic-item">
                        <h4>Personal 2</h4>
                        <input type="text" name="ref_p2_name" placeholder="Nombre" required>
                        <input type="tel" name="ref_p2_phone" placeholder="Teléfono" required>
                        <input type="text" name="ref_p2_occupation" placeholder="Ocupación" required>
                    </div>
                    <!-- Familiar 1 -->
                    <div class="dynamic-item">
                        <h4>Familiar 1</h4>
                        <input type="text" name="ref_f1_name" placeholder="Nombre" required>
                        <input type="tel" name="ref_f1_phone" placeholder="Teléfono" required>
                        <input type="text" name="ref_f1_relation" placeholder="Parentesco" required>
                    </div>
                    <!-- Familiar 2 -->
                    <div class="dynamic-item">
                        <h4>Familiar 2</h4>
                        <input type="text" name="ref_f2_name" placeholder="Nombre" required>
                        <input type="tel" name="ref_f2_phone" placeholder="Teléfono" required>
                        <input type="text" name="ref_f2_relation" placeholder="Parentesco" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit-large">Enviar Hoja de Vida</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>