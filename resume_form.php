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
?>

<div class="form-wrapper">
    <div class="form-container">

        <a href="dashboard.php" class="back-link">← Volver al Panel de Consulta</a>

        <form id="resume-form" method="POST" enctype="multipart/form-data" action="api/submit_resume.php">
            <?php if ($id > 0): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <h2>Editar Hoja de Vida</h2>
            <?php else: ?>
                <h2>Registrar Hoja de Vida</h2>
            <?php endif; ?>

            <!-- ========================= -->
            <!-- 1 INFORMACION PERSONAL -->
            <!-- ========================= -->

            <div class="form-section">

                <h3>1. Información Personal</h3>

                <div class="grid-2">

                    <div>
                        <label>Nombre completo</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($person['nombre'] ?? ''); ?>" <?php echo $id > 0 ? 'readonly style="background:#f9f9f9;"' : ''; ?>>
                    </div>

                    <div>
                        <label>Tipo documento</label>
                        <select name="id_type" <?php echo $id > 0 ? 'disabled style="background:#f9f9f9;"' : ''; ?>>
                            <option value="">Seleccione</option>
                            <option value="CC" <?php echo ($person['tipo_documento'] ?? '') === 'CC' ? 'selected' : ''; ?>>Cédula</option>
                            <option value="CE" <?php echo ($person['tipo_documento'] ?? '') === 'CE' ? 'selected' : ''; ?>>Cédula extranjería</option>
                            <option value="TI" <?php echo ($person['tipo_documento'] ?? '') === 'TI' ? 'selected' : ''; ?>>Tarjeta identidad</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="id_type" value="<?php echo $person['tipo_documento']; ?>"><?php endif; ?>
                    </div>

                    <div>
                        <label>Número documento</label>
                        <input type="text" name="document_id" value="<?php echo htmlspecialchars($person['documento'] ?? ''); ?>" <?php echo $id > 0 ? 'readonly style="background:#f9f9f9;"' : ''; ?>>
                    </div>

                    <div>
                        <label>Documento identidad (PDF) <?php if (!empty($person['ruta_cedula'])): ?><a href="<?php echo htmlspecialchars($person['ruta_cedula']); ?>" target="_blank" style="font-size: 0.7rem; color: #1a73e8;">(Ver actual)</a><?php endif; ?></label>
                        <?php if ($id == 0): ?>
                            <input type="file" name="id_file" accept=".pdf">
                        <?php endif; ?>
                        <input type="hidden" name="old_id_file" value="<?php echo htmlspecialchars($person['ruta_cedula'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Fecha nacimiento</label>
                        <input type="date" name="birth_date" value="<?php echo htmlspecialchars($person['fecha_nacimiento'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>País nacimiento</label>
                        <select name="birth_country" id="birth_country" <?php echo $id > 0 ? 'disabled style="background:#f9f9f9;"' : ''; ?>>
                            <option value="">Seleccione</option>
                            <option value="Colombia" <?php echo ($person['pais_nacimiento'] ?? '') === 'Colombia' || (isset($person['departamento_nacimiento']) && !empty($person['departamento_nacimiento'])) ? 'selected' : ''; ?>>Colombia</option>
                            <option value="Venezuela" <?php echo ($person['pais_nacimiento'] ?? '') === 'Venezuela' ? 'selected' : ''; ?>>Venezuela</option>
                            <option value="Otro" <?php echo ($person['pais_nacimiento'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="birth_country" value="<?php echo $person['pais_nacimiento']; ?>"><?php endif; ?>
                    </div>

                    <div>
                        <label>Departamento nacimiento</label>
                        <select name="birth_department" id="birth_department" data-selected="<?php echo htmlspecialchars($person['departamento_nacimiento'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled style="background:#f9f9f9;"' : ''; ?>>
                            <option value="">Seleccione</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="birth_department" value="<?php echo $person['departamento_nacimiento']; ?>"><?php endif; ?>
                    </div>

                    <div>
                        <label>Municipio nacimiento</label>
                        <select name="birth_city" id="birth_city" data-selected="<?php echo htmlspecialchars($person['municipio_nacimiento'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled style="background:#f9f9f9;"' : ''; ?>>
                            <option value="">Seleccione</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="birth_city" value="<?php echo $person['municipio_nacimiento']; ?>"><?php endif; ?>
                    </div>

                    <div>
                        <label>Departamento residencia</label>
                        <select name="department" id="department" data-selected="<?php echo htmlspecialchars($person['departamento_residencia'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled style="background:#f9f9f9;"' : ''; ?>>
                            <option value="">Seleccione</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="department" value="<?php echo $person['departamento_residencia']; ?>"><?php endif; ?>
                    </div>

                    <div>
                        <label>Municipio residencia</label>
                        <select name="city" id="city" data-selected="<?php echo htmlspecialchars($person['municipio_residencia'] ?? ''); ?>" <?php echo $id > 0 ? 'disabled style="background:#f9f9f9;"' : ''; ?>>
                            <option value="">Seleccione</option>
                        </select>
                        <?php if ($id > 0): ?><input type="hidden" name="city" value="<?php echo $person['municipio_residencia']; ?>"><?php endif; ?>
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($person['telefono'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($person['email'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Vereda / Barrio</label>
                        <input type="text" name="vereda" value="<?php echo htmlspecialchars($person['vereda'] ?? ''); ?>" <?php echo $id > 0 ? 'readonly style="background:#f9f9f9;"' : ''; ?>>
                    </div>

                    <div>
                        <label>Foto perfil</label>
                        <?php if (!empty($person['ruta_foto'])): ?>
                            <img src="<?php echo htmlspecialchars($person['ruta_foto']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px; display: block; margin-bottom: 5px; border: 2px solid #eee;">
                        <?php endif; ?>
                        <input type="file" name="photo" accept="image/*">
                        <input type="hidden" name="old_photo" value="<?php echo htmlspecialchars($person['ruta_foto'] ?? ''); ?>">
                    </div>

                </div>

                <label>Perfil profesional</label>
                <textarea name="profile_description" rows="4"><?php echo htmlspecialchars($person['descripcion'] ?? ($person['perfil_profesional'] ?? '')); ?></textarea>

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
                        <input type="text" name="ref_p1_name" value="<?php echo htmlspecialchars($person['referencias'][0]['nombre'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_p1_phone" value="<?php echo htmlspecialchars($person['referencias'][0]['telefono'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Referencia personal nombre</label>
                        <input type="text" name="ref_p2_name" value="<?php echo htmlspecialchars($person['referencias'][1]['nombre'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_p2_phone" value="<?php echo htmlspecialchars($person['referencias'][1]['telefono'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Referencia familiar nombre</label>
                        <input type="text" name="ref_f1_name" value="<?php echo htmlspecialchars($person['referencias'][2]['nombre'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_f1_phone" value="<?php echo htmlspecialchars($person['referencias'][2]['telefono'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Referencia familiar nombre</label>
                        <input type="text" name="ref_f2_name" value="<?php echo htmlspecialchars($person['referencias'][3]['nombre'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="tel" name="ref_f2_phone" value="<?php echo htmlspecialchars($person['referencias'][3]['telefono'] ?? ''); ?>">
                    </div>

                </div>

            </div>


            <button type="submit" class="btn-submit-large">
                <?php echo $id > 0 ? 'Actualizar Hoja de Vida' : 'Guardar Hoja de Vida'; ?>
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

    <div class="dynamic-item">

        <label>Nivel educativo</label>
        <select name="education_INDEX_level" class="education-level-select">
            <option value="">Seleccione</option>
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

        <label>Título obtenido</label>
        <input type="text" name="education_INDEX_title_obtained">

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