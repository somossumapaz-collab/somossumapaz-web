<?php
session_start();
require_once 'database_functions.php';
include 'header.php';
check_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$person = null;
if ($id > 0) {
    $person = get_resume_by_id($id);
}

$veredas_list = [
    "SAN JOSE", "NUEVA GRANADA", "CONCEPCION", "TUNAL ALTO", "LAGUNITAS", "TUNAL BAJO",
    "LA UNION URBANO", "CHORRERAS", "SANTO DOMINGO", "LAS VEGAS", "SAN JUAN", "CAPITOLIO",
    "EL TOLDO", "LAS SOPAS", "LOS RIOS", "SAN ANTONIO", "LAS PALMAS", "LAS ANIMAS",
    "LAS AURAS", "NAZARETH URBANO", "TAQUECITOS", "PENALISA", "LAGUNA VERDE", "RAIZAL",
    "SANTA ROSA", "BETANIA", "EL ITSMO", "EL TABACO"
];
$current_vereda = $person['vereda'] ?? '';
$is_vereda_in_list = in_array($current_vereda, $veredas_list);
$vereda_is_other = !empty($current_vereda) && !$is_vereda_in_list;
$current_country = $person['pais_nacimiento'] ?? 'Colombia';
$country_is_other = !empty($current_country) && $current_country !== 'Colombia';
?>

<div class="form-wrapper">
    <div class="form-container">

        <div class="form-page-header">
            <div class="form-page-title">
                <h2><i class="fas fa-file-signature"></i> <?php echo $id > 0 ? 'Editar Hoja de Vida' : 'Ingresar Hoja de Vida'; ?></h2>
                <p class="form-page-subtitle">Diligencie los campos requeridos con su información y soportes correspondientes.</p>
            </div>
            <a href="dashboard.php" class="back-link-btn"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        </div>

        <form id="resume-form" method="POST" enctype="multipart/form-data" action="api/submit_resume.php">
            <?php if ($id > 0): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php endif; ?>

            <!-- ========================= -->
            <!-- 1 INFORMACION PERSONAL -->
            <!-- ========================= -->
            <div class="form-section-card">
                <h3 class="section-card-title"><i class="fas fa-user-circle"></i> 1. Información Personal</h3>

                <div class="form-grid-2">

                    <div class="field-group">
                        <label class="field-label">Nombre completo</label>
                        <input type="text" name="full_name" class="field-input" value="<?php echo htmlspecialchars($person['nombre'] ?? ''); ?>" <?php echo $id > 0 ? 'readonly' : ''; ?> placeholder="Ingrese nombre y apellidos">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Tipo de documento</label>
                        <select name="id_type" class="field-select" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                            <option value="CC" <?php echo ($person['tipo_documento'] ?? '') === 'CC' ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                            <option value="CE" <?php echo ($person['tipo_documento'] ?? '') === 'CE' ? 'selected' : ''; ?>>Cédula Extranjería</option>
                            <option value="TI" <?php echo ($person['tipo_documento'] ?? '') === 'TI' ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="id_type" value="<?php echo $person['tipo_documento']; ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Número de documento</label>
                        <input type="text" name="document_id" class="field-input" value="<?php echo htmlspecialchars($person['documento'] ?? ''); ?>" <?php echo $id > 0 ? 'readonly' : ''; ?> placeholder="Número de cédula o ID">
                    </div>

                    <div class="field-group">
                        <label class="field-label">
                            Documento identidad (PDF/Imagen)
                            <?php if (!empty($person['ruta_cedula'])): ?>
                                <a href="<?php echo htmlspecialchars($person['ruta_cedula']); ?>" target="_blank" class="file-current-badge"><i class="fas fa-external-link-alt"></i> Ver actual</a>
                            <?php endif; ?>
                        </label>
                        <div class="file-upload-card">
                            <i class="fas fa-file-pdf file-upload-icon"></i>
                            <div class="file-upload-info">
                                <input type="file" name="id_file" accept=".pdf,image/*">
                                <input type="hidden" name="old_id_file" value="<?php echo htmlspecialchars($person['ruta_cedula'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Fecha de nacimiento</label>
                        <input type="date" name="birth_date" class="field-input" value="<?php echo htmlspecialchars($person['fecha_nacimiento'] ?? ''); ?>">
                    </div>

                    <div class="field-group">
                        <label class="field-label">País de nacimiento</label>
                        <select name="birth_country" id="birth_country" class="field-select" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                            <option value="Colombia" <?php echo ($current_country === 'Colombia' || (isset($person['departamento_nacimiento']) && !empty($person['departamento_nacimiento']))) ? 'selected' : ''; ?>>Colombia</option>
                            <option value="Otro" <?php echo $country_is_other ? 'selected' : ''; ?>>Otro (Escribir...)</option>
                        </select>
                        <input type="text" id="birth_country_other" class="field-input" value="<?php echo htmlspecialchars($country_is_other ? $current_country : ''); ?>" placeholder="Escriba el país de nacimiento..." style="margin-top: 8px; display: <?php echo $country_is_other ? 'block' : 'none'; ?>;" <?php echo $id > 0 ? 'readonly' : ''; ?>>
                        <?php if ($id > 0): ?><input type="hidden" name="birth_country" value="<?php echo htmlspecialchars($current_country); ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Departamento de nacimiento</label>
                        <select name="birth_department" id="birth_department" class="field-select" data-selected="<?php echo htmlspecialchars($person['departamento_nacimiento'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="birth_department" value="<?php echo $person['departamento_nacimiento']; ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Municipio de nacimiento</label>
                        <select name="birth_city" id="birth_city" class="field-select" data-selected="<?php echo htmlspecialchars($person['municipio_nacimiento'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                        </select>
                        <input type="text" id="birth_city_other" class="field-input" placeholder="Escriba el municipio de nacimiento..." style="margin-top: 8px; display: none;" <?php echo $id > 0 ? 'readonly' : ''; ?>>
                        <?php if ($id > 0): ?><input type="hidden" name="birth_city" value="<?php echo $person['municipio_nacimiento']; ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Departamento de residencia</label>
                        <select name="department" id="department" class="field-select" data-selected="<?php echo htmlspecialchars($person['departamento_residencia'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="department" value="<?php echo $person['departamento_residencia']; ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Municipio de residencia</label>
                        <select name="city" id="city" class="field-select" data-selected="<?php echo htmlspecialchars($person['municipio_residencia'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione...</option>
                        </select>
                        <input type="text" id="city_other" class="field-input" placeholder="Escriba el municipio de residencia..." style="margin-top: 8px; display: none;" <?php echo $id > 0 ? 'readonly' : ''; ?>>
                        <?php if ($id > 0): ?><input type="hidden" name="city" value="<?php echo $person['municipio_residencia']; ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Teléfono de contacto</label>
                        <input type="tel" name="phone" class="field-input" value="<?php echo htmlspecialchars($person['telefono'] ?? ''); ?>" placeholder="Ej: 3001234567">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Correo electrónico</label>
                        <input type="email" name="email" class="field-input" value="<?php echo htmlspecialchars($person['email'] ?? ''); ?>" placeholder="ejemplo@correo.com">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Vereda / Barrio</label>
                        <select name="vereda" id="vereda" class="field-select" <?php echo $id > 0 ? 'disabled' : ''; ?>>
                            <option value="">Seleccione vereda / barrio...</option>
                            <?php foreach ($veredas_list as $v): ?>
                                <option value="<?php echo $v; ?>" <?php echo ($current_vereda === $v) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                            <?php endforeach; ?>
                            <option value="Otro" <?php echo $vereda_is_other ? 'selected' : ''; ?>>Otro (Escribir...)</option>
                        </select>
                        <input type="text" id="vereda_other" class="field-input" value="<?php echo htmlspecialchars($vereda_is_other ? $current_vereda : ''); ?>" placeholder="Escriba el nombre de la vereda o barrio..." style="margin-top: 8px; display: <?php echo $vereda_is_other ? 'block' : 'none'; ?>;" <?php echo $id > 0 ? 'readonly' : ''; ?>>
                        <?php if ($id > 0): ?><input type="hidden" name="vereda" value="<?php echo htmlspecialchars($current_vereda); ?>"><?php endif; ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label">
                            Foto de Perfil
                            <?php if (!empty($person['ruta_foto'])): ?>
                                <a href="<?php echo htmlspecialchars($person['ruta_foto']); ?>" target="_blank" class="file-current-badge"><i class="fas fa-image"></i> Ver actual</a>
                            <?php endif; ?>
                        </label>
                        <div class="file-upload-card">
                            <?php if (!empty($person['ruta_foto'])): ?>
                                <img src="<?php echo htmlspecialchars($person['ruta_foto']); ?>" style="width: 36px; height: 36px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <i class="fas fa-camera file-upload-icon"></i>
                            <?php endif; ?>
                            <div class="file-upload-info">
                                <input type="file" name="photo" accept="image/*">
                                <input type="hidden" name="old_photo" value="<?php echo htmlspecialchars($person['ruta_foto'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="field-group form-grid-full">
                        <label class="field-label">Perfil profesional / Resumen</label>
                        <textarea name="profile_description" class="field-textarea" placeholder="Describa brevemente su perfil, habilidades principales y aspiraciones laborales..."><?php echo htmlspecialchars($person['descripcion'] ?? ($person['perfil_profesional'] ?? '')); ?></textarea>
                    </div>

                </div>
            </div>

            <!-- ========================= -->
            <!-- 2 EDUCACION -->
            <!-- ========================= -->
            <div class="form-section-card">
                <h3 class="section-card-title"><i class="fas fa-graduation-cap"></i> 2. Formación Académica</h3>
                
                <div id="education-list"></div>

                <button type="button" id="add-education-btn" class="btn-add-dynamic">
                    <i class="fas fa-plus-circle"></i> Agregar Estudio Académico
                </button>
            </div>

            <!-- ========================= -->
            <!-- 3 EXPERIENCIA -->
            <!-- ========================= -->
            <div class="form-section-card">
                <h3 class="section-card-title"><i class="fas fa-briefcase"></i> 3. Experiencia Laboral</h3>
                
                <div id="experience-list"></div>

                <button type="button" id="add-experience-btn" class="btn-add-dynamic">
                    <i class="fas fa-plus-circle"></i> Agregar Experiencia Laboral
                </button>
            </div>

            <!-- ========================= -->
            <!-- 4 REFERENCIAS -->
            <!-- ========================= -->
            <div class="form-section-card">
                <h3 class="section-card-title"><i class="fas fa-users"></i> 4. Referencias</h3>

                <div class="form-grid-2">

                    <div class="field-group">
                        <label class="field-label">Referencia Personal 1 - Nombre</label>
                        <input type="text" name="ref_p1_name" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][0]['nombre'] ?? ''); ?>" placeholder="Nombre completo">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Teléfono Referencia Personal 1</label>
                        <input type="tel" name="ref_p1_phone" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][0]['telefono'] ?? ''); ?>" placeholder="Teléfono de contacto">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Referencia Personal 2 - Nombre</label>
                        <input type="text" name="ref_p2_name" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][1]['nombre'] ?? ''); ?>" placeholder="Nombre completo">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Teléfono Referencia Personal 2</label>
                        <input type="tel" name="ref_p2_phone" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][1]['telefono'] ?? ''); ?>" placeholder="Teléfono de contacto">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Referencia Familiar 1 - Nombre</label>
                        <input type="text" name="ref_f1_name" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][2]['nombre'] ?? ''); ?>" placeholder="Nombre completo">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Teléfono Referencia Familiar 1</label>
                        <input type="tel" name="ref_f1_phone" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][2]['telefono'] ?? ''); ?>" placeholder="Teléfono de contacto">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Referencia Familiar 2 - Nombre</label>
                        <input type="text" name="ref_f2_name" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][3]['nombre'] ?? ''); ?>" placeholder="Nombre completo">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Teléfono Referencia Familiar 2</label>
                        <input type="tel" name="ref_f2_phone" class="field-input" value="<?php echo htmlspecialchars($person['referencias'][3]['telefono'] ?? ''); ?>" placeholder="Teléfono de contacto">
                    </div>

                </div>
            </div>

            <button type="submit" class="btn-submit-large">
                <i class="fas fa-save"></i> <?php echo $id > 0 ? 'Actualizar Hoja de Vida en la Base de Datos' : 'Guardar Hoja de Vida en la Base de Datos'; ?>
            </button>

            <script>
                window.personData = <?php echo json_encode($person); ?>;
            </script>
        </form>

    </div>
</div>

<!-- ========================= -->
<!-- TEMPLATE EDUCACION -->
<!-- ========================= -->
<template id="education-item-tpl">
    <div class="dynamic-card">
        <div class="dynamic-card-header">
            <span class="dynamic-card-title"><i class="fas fa-graduation-cap"></i> Registro de Estudio</span>
            <button type="button" class="btn-remove-item remove"><i class="fas fa-trash-alt"></i> Eliminar</button>
        </div>
        <div class="form-grid-2">
            <div class="field-group">
                <label class="field-label">Nivel educativo</label>
                <select name="education_INDEX_level" class="field-select education-level-select">
                    <option value="">Seleccione...</option>
                    <option>Primaria</option>
                    <option>Primaria Incompleta</option>
                    <option>Bachiller</option>
                    <option>Bachiller Incompleto</option>
                    <option>Bachiller Técnico</option>
                    <option>Técnico</option>
                    <option>Tecnólogo</option>
                    <option>Profesional</option>
                    <option>Especialización</option>
                    <option>Maestría</option>
                    <option>Autodidacta</option>
                    <option>En Formación</option>
                    <option>Sin Estudios Académicos</option>
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Título obtenido</label>
                <input type="text" name="education_INDEX_title_obtained" class="field-input" placeholder="Ej: Bachiller Académico / Ingeniero">
            </div>

            <div class="field-group">
                <label class="field-label">Institución educativa</label>
                <input type="text" name="education_INDEX_institution" class="field-input" placeholder="Nombre de la institución">
            </div>

            <div class="field-group">
                <label class="field-label">Fecha de inicio</label>
                <input type="date" name="education_INDEX_start_date" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Fecha de finalización</label>
                <input type="date" name="education_INDEX_end_date" class="field-input">
            </div>

            <div class="field-group" style="justify-content: center; margin-top: 15px;">
                <label class="field-label" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="education_INDEX_is_current" style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                    <span>Actualmente en curso</span>
                </label>
            </div>

            <div class="field-group form-grid-full">
                <label class="field-label">Certificado o Soporte Académico (PDF/Imagen)</label>
                <div class="file-upload-card">
                    <i class="fas fa-file-pdf file-upload-icon"></i>
                    <div class="file-upload-info">
                        <input type="file" name="education_INDEX_file" accept=".pdf,image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- ========================= -->
<!-- TEMPLATE EXPERIENCIA -->
<!-- ========================= -->
<template id="experience-item-tpl">
    <div class="dynamic-card">
        <div class="dynamic-card-header">
            <span class="dynamic-card-title"><i class="fas fa-briefcase"></i> Registro de Experiencia Laboral</span>
            <button type="button" class="btn-remove-item remove"><i class="fas fa-trash-alt"></i> Eliminar</button>
        </div>
        <div class="form-grid-2">
            <div class="field-group">
                <label class="field-label">Empresa / Organización</label>
                <input type="text" name="experience_INDEX_company" class="field-input" placeholder="Nombre de la empresa">
            </div>

            <div class="field-group">
                <label class="field-label">Cargo desempeñado</label>
                <input type="text" name="experience_INDEX_role" class="field-input" placeholder="Ej: Auxiliar / Operario / Coordinador">
            </div>

            <div class="field-group form-grid-full">
                <label class="field-label">Descripción de funciones</label>
                <textarea name="experience_INDEX_description" class="field-textarea" placeholder="Resumen de responsabilidades y logros..."></textarea>
            </div>

            <div class="field-group">
                <label class="field-label">Fecha de inicio</label>
                <input type="date" name="experience_INDEX_start_date" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Fecha de finalización</label>
                <input type="date" name="experience_INDEX_end_date" class="field-input">
            </div>

            <div class="field-group form-grid-full" style="margin-top: -5px;">
                <label class="field-label" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="experience_INDEX_is_current" style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                    <span>Actualmente laboro aquí</span>
                </label>
            </div>

            <div class="field-group form-grid-full">
                <label class="field-label">Certificado Laboral (PDF/Imagen)</label>
                <div class="file-upload-card">
                    <i class="fas fa-file-contract file-upload-icon"></i>
                    <div class="file-upload-info">
                        <input type="file" name="experience_INDEX_file" accept=".pdf,image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<?php include 'footer.php'; ?>