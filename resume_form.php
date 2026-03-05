<?php
session_start();
require_once 'database_functions.php';
include 'header.php';
check_auth();

// Get current data if exists
$user_id = $_SESSION['user_id'];
$resume = get_complete_resume($user_id) ?? [];
$hv_id = $resume['id'] ?? 0;
?>

<div class="form-wrapper">
    <div class="form-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="dashboard.php" class="back-link">← Volver al Panel</a>
            <div id="autoSaveStatus" class="auto-save-status">
                <i class="fas fa-check-circle"></i> Todo guardado
            </div>
        </div>

        <div class="form-header">
            <h2>Gestión de Hoja de Vida</h2>
            <p>Completa cada sección para potenciar tu perfil profesional.</p>
        </div>

        <!-- Progress Navigation -->
        <div class="form-navigation" id="formNavigation">
            <div class="nav-step active" data-section="personal">
                <div class="step-icon">1</div>
                <span>Personal</span>
            </div>
            <div class="nav-step" data-section="skills">
                <div class="step-icon">2</div>
                <span>Habilidades</span>
            </div>
            <div class="nav-step" data-section="education">
                <div class="step-icon">3</div>
                <span>Educación</span>
            </div>
            <div class="nav-step" data-section="experience">
                <div class="step-icon">4</div>
                <span>Experiencia</span>
            </div>
            <div class="nav-step" data-section="references">
                <div class="step-icon">5</div>
                <span>Referencias</span>
            </div>
        </div>

        <form id="resume-form" data-hv-id="<?php echo $hv_id; ?>">

            <!-- Section 1: Personal -->
            <div class="form-section-content active" id="section-personal">
                <h3>1. Información Personal</h3>
                <div class="grid-2">
                    <div class="input-group full-width" style="text-align: center; margin-bottom: 2rem;">
                        <div style="position: relative; display: inline-block;">
                            <img id="profile-preview"
                                src="<?php echo !empty($resume['foto_perfil_path']) ? $resume['foto_perfil_path'] : 'static/img/default-avatar.png'; ?>"
                                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--crema-sumapaz);">
                            <label for="photo-upload"
                                style="position: absolute; bottom: 0; right: 0; background: var(--primary-color); color: white; padding: 8px; border-radius: 50%; cursor: pointer;">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="photo-upload" name="photo" hidden accept="image/*">
                        </div>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 10px;">Subir foto profesional (JPG, PNG -
                            Max 5MB)</p>
                    </div>

                    <div class="input-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="full_name"
                            value="<?php echo htmlspecialchars($resume['nombre_completo'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email"
                            value="<?php echo htmlspecialchars($resume['email'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label>Tipo de Documento</label>
                        <select name="id_type">
                            <option value="CC" <?php echo ($resume['tipo_documento'] ?? '') == 'CC' ? 'selected' : ''; ?>>
                                Cédula de Ciudadanía</option>
                            <option value="CE" <?php echo ($resume['tipo_documento'] ?? '') == 'CE' ? 'selected' : ''; ?>>
                                Cédula de Extranjería</option>
                            <option value="TI" <?php echo ($resume['tipo_documento'] ?? '') == 'TI' ? 'selected' : ''; ?>>
                                Tarjeta de Identidad</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Número de Documento</label>
                        <input type="text" name="document_id"
                            value="<?php echo htmlspecialchars($resume['numero_documento'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label>Teléfono de Contacto</label>
                        <input type="text" name="phone"
                            value="<?php echo htmlspecialchars($resume['telefono'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label>Profesión / Cargo Actual</label>
                        <input type="text" name="niche"
                            value="<?php echo htmlspecialchars($resume['profesion'] ?? ''); ?>">
                    </div>
                    <div class="input-group full-width">
                        <label>Descripción del Perfil</label>
                        <textarea name="profile_description"
                            placeholder="Escribe un breve resumen de tu perfil profesional..."><?php echo htmlspecialchars($resume['perfil_profesional'] ?? ''); ?></textarea>
                    </div>

                    <div class="input-group">
                        <label>Documento de Identidad (PDF)</label>
                        <input type="file" name="id_file" accept=".pdf">
                        <?php if (!empty($resume['documento_pdf_path'])): ?>
                            <small><a href="<?php echo $resume['documento_pdf_path']; ?>" target="_blank">Ver archivo
                                    actual</a></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="section-footer">
                    <span></span>
                    <button type="button" class="btn-primary next-section" style="width: auto;">Siguiente: Habilidades
                        →</button>
                </div>
            </div>

            <!-- Section 2: Skills -->
            <div class="form-section-content" id="section-skills">
                <h3>2. Habilidades</h3>
                <div class="input-group">
                    <label>Tus habilidades (Separa por comas)</label>
                    <input type="text" name="skills" value="<?php echo htmlspecialchars($resume['skills'] ?? ''); ?>"
                        placeholder="Ej: PHP, Liderazgo, Gestión de proyectos...">
                </div>
                <div class="section-footer">
                    <button type="button" class="btn-secondary prev-section">← Volver</button>
                    <button type="button" class="btn-primary next-section" style="width: auto;">Siguiente: Educación
                        →</button>
                </div>
            </div>

            <!-- Section 3: Education -->
            <div class="form-section-content" id="section-education">
                <h3>3. Formación Académica</h3>
                <div id="education-items">
                    <!-- Dinámico -->
                </div>
                <button type="button" class="btn-secondary" id="add-education"><i class="fas fa-plus"></i> Agregar
                    Estudio</button>
                <div class="section-footer">
                    <button type="button" class="btn-secondary prev-section">← Volver</button>
                    <button type="button" class="btn-primary next-section" style="width: auto;">Siguiente: Experiencia
                        →</button>
                </div>
            </div>

            <!-- Section 4: Experience -->
            <div class="form-section-content" id="section-experience">
                <h3>4. Experiencia Laboral</h3>
                <div id="experience-items">
                    <!-- Dinámico -->
                </div>
                <button type="button" class="btn-secondary" id="add-experience"><i class="fas fa-plus"></i> Agregar
                    Experiencia</button>
                <div class="section-footer">
                    <button type="button" class="btn-secondary prev-section">← Volver</button>
                    <button type="button" class="btn-primary next-section" style="width: auto;">Siguiente: Referencias
                        →</button>
                </div>
            </div>

            <!-- Section 5: References -->
            <div class="form-section-content" id="section-references">
                <h3>5. Referencias (Personales y Familiares)</h3>
                <p>Se recomienda incluir al menos una referencia personal y una familiar.</p>
                <div id="reference-items">
                    <!-- Dinámico -->
                </div>
                <button type="button" class="btn-secondary" id="add-reference"><i class="fas fa-plus"></i> Agregar
                    Referencia</button>
                <div class="section-footer">
                    <button type="button" class="btn-secondary prev-section">← Volver</button>
                    <button type="button" class="btn-submit-large" id="final-save">Finalizar y Guardar Todo</button>
                </div>
            </div>

        </form>
    </div>
</div>

<template id="education-tpl">
    <div class="dynamic-item education-item">
        <div class="grid-2">
            <div class="input-group">
                <label>Institución</label>
                <input type="text" class="item-institution">
            </div>
            <div class="input-group">
                <label>Nivel / Título</label>
                <input type="text" class="item-level">
            </div>
            <div class="input-group">
                <label>Fecha Inicio</label>
                <input type="date" class="item-start">
            </div>
            <div class="input-group">
                <label>Fecha Fin</label>
                <input type="date" class="item-end">
            </div>
            <div class="input-group">
                <label><input type="checkbox" class="item-current"> ¿En curso?</label>
            </div>
            <div class="input-group">
                <label>Soporte (PDF)</label>
                <input type="file" class="item-file" accept=".pdf">
                <input type="hidden" class="item-file-path">
                <div class="file-status"></div>
            </div>
        </div>
        <button type="button" class="btn-remove">Eliminar</button>
    </div>
</template>

<template id="experience-tpl">
    <div class="dynamic-item experience-item">
        <div class="grid-2">
            <div class="input-group">
                <label>Empresa</label>
                <input type="text" class="item-company">
            </div>
            <div class="input-group">
                <label>Cargo</label>
                <input type="text" class="item-role">
            </div>
            <div class="input-group">
                <label>Fecha Inicio</label>
                <input type="date" class="item-start">
            </div>
            <div class="input-group">
                <label>Fecha Fin</label>
                <input type="date" class="item-end">
            </div>
            <div class="input-group">
                <label><input type="checkbox" class="item-current"> ¿Actualmente?</label>
            </div>
            <div class="input-group">
                <label>Soporte (PDF)</label>
                <input type="file" class="item-file" accept=".pdf">
                <input type="hidden" class="item-file-path">
                <div class="file-status"></div>
            </div>
        </div>
        <button type="button" class="btn-remove">Eliminar</button>
    </div>
</template>

<template id="reference-tpl">
    <div class="dynamic-item reference-item">
        <div class="grid-2">
            <div class="input-group">
                <label>Nombre</label>
                <input type="text" class="item-name">
            </div>
            <div class="input-group">
                <label>Teléfono</label>
                <input type="text" class="item-phone">
            </div>
            <div class="input-group">
                <label>Tipo</label>
                <select class="item-type">
                    <option value="Personal">Personal</option>
                    <option value="Familiar">Familiar</option>
                </select>
            </div>
            <div class="input-group">
                <label>Ocupación / Parentesco</label>
                <input type="text" class="item-occupation">
            </div>
        </div>
        <button type="button" class="btn-remove">Eliminar</button>
    </div>
</template>

<script>
    // Inyectar datos iniciales para JS
    window.INITIAL_RESUME_DATA = <?php echo json_encode($resume); ?>;
</script>

<?php include 'footer.php'; ?>