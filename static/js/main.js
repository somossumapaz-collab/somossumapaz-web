document.addEventListener('DOMContentLoaded', () => {

    /* ===================================
       1. DEPARTAMENTOS Y MUNICIPIOS
    =================================== */

    const departments = [
        "Amazonas", "Antioquia", "Arauca", "Atlántico", "Bolívar", "Boyacá", "Caldas",
        "Caquetá", "Casanare", "Cauca", "Cesar", "Chocó", "Córdoba", "Cundinamarca",
        "Guainía", "Guaviare", "Huila", "La Guajira", "Magdalena", "Meta", "Nariño",
        "Norte de Santander", "Putumayo", "Quindío", "Risaralda",
        "San Andrés y Providencia", "Santander", "Sucre", "Tolima",
        "Valle del Cauca", "Vaupés", "Vichada"
    ];

    const cities = {
        "Cundinamarca": ["Fusagasugá", "Arbeláez", "Pandi", "San Bernardo", "Venecia", "Pasca", "Tibacuy", "Cabrera", "Bogotá"],
        "Meta": ["Villavicencio", "Acacías", "Granada"],
        "Antioquia": ["Medellín", "Envigado", "Itagüí"],
        "Atlántico": ["Barranquilla", "Soledad", "Puerto Colombia"],
        "Valle del Cauca": ["Cali", "Palmira", "Buenaventura"]
    };

    function setupSelectOtherToggle(selectId, otherInputId, fieldName) {
        const selectEl = document.getElementById(selectId);
        const otherInput = document.getElementById(otherInputId);
        if (!selectEl || !otherInput) return;

        function updateToggle() {
            if (selectEl.value === 'Otro') {
                otherInput.style.display = 'block';
                if (!selectEl.disabled) {
                    otherInput.name = fieldName;
                    selectEl.removeAttribute('name');
                }
            } else {
                otherInput.style.display = 'none';
                if (!selectEl.disabled) {
                    selectEl.name = fieldName;
                    otherInput.removeAttribute('name');
                }
            }
        }

        selectEl.addEventListener('change', updateToggle);
        updateToggle();
    }

    function populateDepartments(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.innerHTML = '<option value="">Seleccione...</option>';

        const selectedVal = select.getAttribute('data-selected');
        departments.sort().forEach(dept => {
            const opt = document.createElement('option');
            opt.value = dept;
            opt.textContent = dept;
            if (dept === selectedVal) opt.selected = true;
            select.appendChild(opt);
        });

        if (selectedVal) {
            select.dispatchEvent(new Event('change'));
        }
    }

    function handleDeptChange(deptSelectId, citySelectId) {
        const deptSelect = document.getElementById(deptSelectId);
        const citySelect = document.getElementById(citySelectId);
        const otherInput = document.getElementById(citySelectId + '_other');

        if (!deptSelect || !citySelect) return;

        deptSelect.addEventListener('change', () => {
            const dept = deptSelect.value;
            citySelect.innerHTML = '<option value="">Seleccione Municipio...</option>';

            const selectedCity = citySelect.getAttribute('data-selected') || '';
            let matched = false;

            if (cities[dept]) {
                cities[dept].sort().forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city;
                    opt.textContent = city;
                    if (city === selectedCity) {
                        opt.selected = true;
                        matched = true;
                    }
                    citySelect.appendChild(opt);
                });
            }

            const optOtro = document.createElement('option');
            optOtro.value = "Otro";
            optOtro.textContent = "Otro (Escribir...)";
            citySelect.appendChild(optOtro);

            if (selectedCity && !matched) {
                optOtro.selected = true;
                if (otherInput) {
                    otherInput.value = selectedCity;
                }
            }

            citySelect.dispatchEvent(new Event('change'));
        });

        setupSelectOtherToggle(citySelectId, citySelectId + '_other', citySelectId);

        if (deptSelect.value) {
            deptSelect.dispatchEvent(new Event('change'));
        }
    }

    populateDepartments('birth_department');
    populateDepartments('department');

    handleDeptChange('birth_department', 'birth_city');
    handleDeptChange('department', 'city');

    setupSelectOtherToggle('vereda', 'vereda_other', 'vereda');
    setupSelectOtherToggle('birth_country', 'birth_country_other', 'birth_country');


    /* ===================================
       2. EDUCACION Y EXPERIENCIA DINAMICA
    =================================== */

    const resumeForm = document.getElementById('resume-form');

    if (resumeForm) {

        let eduCount = 0;
        let expCount = 0;

        const addEduBtn = document.getElementById('add-education-btn');
        const addExpBtn = document.getElementById('add-experience-btn');

        const eduList = document.getElementById('education-list');
        const expList = document.getElementById('experience-list');

        function addItem(type) {

            const index = type === "education" ? eduCount++ : expCount++;

            const tpl = document.getElementById(`${type}-item-tpl`);

            if (!tpl) return;

            const html = tpl.innerHTML.replace(/INDEX/g, index);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;

            const item = wrapper.firstElementChild;

            const removeBtn = item.querySelector('.remove');

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    item.remove();
                });
            }

            // Logic for "En curso" / "Actualmente aquí"
            const currentCheckbox = item.querySelector('input[type="checkbox"]');
            const endTimeInput = item.querySelector('input[type="date"][name*="end_date"]');
            
            if (currentCheckbox && endTimeInput) {
                const updateCurrentState = () => {
                    if (currentCheckbox.checked) {
                        endTimeInput.value = "2999-01-01";
                        endTimeInput.style.display = "none";
                        // Also hide label if possible
                        if (endTimeInput.previousElementSibling && endTimeInput.previousElementSibling.tagName === "LABEL") {
                            endTimeInput.previousElementSibling.style.display = "none";
                        }
                    } else {
                        if (endTimeInput.value === "2999-01-01") endTimeInput.value = "";
                        endTimeInput.style.display = "block";
                        if (endTimeInput.previousElementSibling && endTimeInput.previousElementSibling.tagName === "LABEL") {
                            endTimeInput.previousElementSibling.style.display = "block";
                        }
                    }
                };
                currentCheckbox.addEventListener('change', updateCurrentState);
                // Initial check for pre-filled data
                if (endTimeInput.value === "2999-01-01") {
                    currentCheckbox.checked = true;
                    updateCurrentState();
                }
            }

            // Logic for Academic Level conditional fields
            if (type === "education") {
                const levelSelect = item.querySelector('select');
                const startDateInput = item.querySelector('input[type="date"][name*="start_date"]');
                const fileInput = item.querySelector('input[type="file"]');
                const titleInput = item.querySelector('input[name*="title_obtained"]');

                if (levelSelect) {
                    const updateLevelFields = () => {
                        const level = levelSelect.value;
                        const hideAll = ["Autodidacta", "Sin Estudios Académicos"].includes(level);
                        const hideFile = ["Bachiller Incompleto", "Primaria Incompleta"].includes(level) || hideAll;

                        const dateRow = [startDateInput, endTimeInput];
                        dateRow.forEach(el => {
                            if (el) {
                                el.style.display = hideAll ? "none" : "block";
                                if (el.previousElementSibling && el.previousElementSibling.tagName === "LABEL") {
                                    el.previousElementSibling.style.display = hideAll ? "none" : "block";
                                }
                            }
                        });

                        if (fileInput) {
                            fileInput.style.display = hideFile ? "none" : "block";
                            if (fileInput.previousElementSibling && fileInput.previousElementSibling.tagName === "LABEL") {
                                fileInput.previousElementSibling.style.display = hideFile ? "none" : "block";
                            }
                        }

                        // Bonus: if autodidacta/sin estudios, maybe title is also hidden?
                        // User didn't say, but usually it is. I'll leave it for now.
                    };
                    levelSelect.addEventListener('change', updateLevelFields);
                    updateLevelFields(); // Initial run
                }
            }

            if (type === "education") {
                eduList.appendChild(item);
            } else {
                expList.appendChild(item);
            }

            return item;
        }

        if (addEduBtn) {
            addEduBtn.addEventListener('click', () => addItem("education"));
        }

        if (addExpBtn) {
            addExpBtn.addEventListener('click', () => addItem("experience"));
        }

        // Prefill dynamic items if editing
        if (window.personData) {
            if (window.personData.formacion) {
                window.personData.formacion.forEach(edu => {
                    const item = addItem("education");
                    const idx = item.querySelector('[name^="education_"]').name.split('_')[1];
                    if (item.querySelector(`[name="education_${idx}_level"]`)) item.querySelector(`[name="education_${idx}_level"]`).value = edu.nivel_educacion || '';
                    if (item.querySelector(`[name="education_${idx}_title_obtained"]`)) item.querySelector(`[name="education_${idx}_title_obtained"]`).value = edu.titulo_obtenido || '';
                    if (item.querySelector(`[name="education_${idx}_institution"]`)) item.querySelector(`[name="education_${idx}_institution"]`).value = edu.institucion || '';
                    if (item.querySelector(`[name="education_${idx}_start_date"]`)) item.querySelector(`[name="education_${idx}_start_date"]`).value = edu.fecha_inicio || '';
                    if (item.querySelector(`[name="education_${idx}_end_date"]`)) item.querySelector(`[name="education_${idx}_end_date"]`).value = edu.fecha_fin || '';

                    const fileCard = item.querySelector('.file-upload-card') || item.querySelector('label:last-of-type');
                    if (edu.ruta_soporte && fileCard) {
                        const link = document.createElement('a');
                        link.href = edu.ruta_soporte;
                        link.target = "_blank";
                        link.className = "file-current-badge";
                        link.innerHTML = '<i class="fas fa-external-link-alt"></i> (Ver certificado actual)';
                        fileCard.appendChild(link);

                        const hiddenFile = document.createElement('input');
                        hiddenFile.type = "hidden";
                        hiddenFile.name = `education_${idx}_old_file`;
                        hiddenFile.value = edu.ruta_soporte;
                        fileCard.appendChild(hiddenFile);
                    }
                });
            }

            if (window.personData.experiencia) {
                window.personData.experiencia.forEach(exp => {
                    const item = addItem("experience");
                    const idx = item.querySelector('[name^="experience_"]').name.split('_')[1];
                    if (item.querySelector(`[name="experience_${idx}_company"]`)) item.querySelector(`[name="experience_${idx}_company"]`).value = exp.empresa || '';
                    if (item.querySelector(`[name="experience_${idx}_role"]`)) item.querySelector(`[name="experience_${idx}_role"]`).value = exp.cargo || '';
                    if (item.querySelector(`[name="experience_${idx}_description"]`)) item.querySelector(`[name="experience_${idx}_description"]`).value = exp.descripcion || '';
                    if (item.querySelector(`[name="experience_${idx}_start_date"]`)) item.querySelector(`[name="experience_${idx}_start_date"]`).value = exp.fecha_inicio || '';
                    if (item.querySelector(`[name="experience_${idx}_end_date"]`)) item.querySelector(`[name="experience_${idx}_end_date"]`).value = exp.fecha_fin || '';

                    const fileCard = item.querySelector('.file-upload-card') || item.querySelector('label:last-of-type');
                    if (exp.ruta_soporte && fileCard) {
                        const link = document.createElement('a');
                        link.href = exp.ruta_soporte;
                        link.target = "_blank";
                        link.className = "file-current-badge";
                        link.innerHTML = '<i class="fas fa-external-link-alt"></i> (Ver certificado actual)';
                        fileCard.appendChild(link);

                        const hiddenFile = document.createElement('input');
                        hiddenFile.type = "hidden";
                        hiddenFile.name = `experience_${idx}_old_file`;
                        hiddenFile.value = exp.ruta_soporte;
                        fileCard.appendChild(hiddenFile);
                    }
                });
            }
        }


        /* ===================================
           3. SUBMIT FORMULARIO
        =================================== */

        resumeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = resumeForm.querySelector('button[type="submit"]');
            if (!btn) return;

            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando Hoja de Vida en la base de datos...';
            btn.disabled = true;

            try {
                const formData = new FormData(resumeForm);
                const response = await fetch('api/submit_resume.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (jsonError) {
                    console.error("Error parseando respuesta JSON:", text);
                    alert("Error en la respuesta del servidor: " + text);
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                    return;
                }

                if (result.success) {
                    alert("¡Hoja de vida guardada con éxito en la base de datos! Serás redirigido al tablero de consulta.");
                    window.location.href = "dashboard.php";
                } else {
                    alert("Error al guardar en base de datos: " + (result.error || "Ocurrió un error inesperado."));
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error("Error en la petición:", error);
                alert("Error de conexión al intentar guardar la hoja de vida.");
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        });

    }


    /* ===================================
       5. DASHBOARD
    =================================== */

    async function loadResumes() {
        const tableBody = document.getElementById('resumeTableBody');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';

        try {
            const response = await fetch('api/get_resumes_dashboard.php');
            const result = await response.json();

            if (!result.data || result.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6">No hay hojas de vida</td></tr>';
                return;
            }

            window.allResumes = result.data;
            
            const veredas = new Set();
            const educaciones = new Set();
            result.data.forEach(r => {
                if (r.vereda) veredas.add(r.vereda.trim());
                if (r.niveles_educacion) {
                    r.niveles_educacion.split(',').forEach(edu => educaciones.add(edu.trim()));
                }
            });

            const selVereda = document.getElementById('filterVereda');
            const selEdu = document.getElementById('filterEducacion');
            if (selVereda) {
                selVereda.innerHTML = '<option value="">Todas</option>';
                Array.from(veredas).sort().forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v; opt.textContent = v;
                    selVereda.appendChild(opt);
                });
            }
            if (selEdu) {
                selEdu.innerHTML = '<option value="">Todos</option>';
                Array.from(educaciones).sort().forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e; opt.textContent = e;
                    selEdu.appendChild(opt);
                });
            }

            renderFilteredResumes(result.data);

            ['filterNombre', 'filterVereda', 'filterEducacion', 'filterCompletitud'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', applyFilters);
                    el.addEventListener('change', applyFilters);
                }
            });
        } catch (e) {
            console.error("Error loading resumes:", e);
            tableBody.innerHTML = '<tr><td colspan="9">Error cargando datos</td></tr>';
        }
    }

    function applyFilters() {
        const nombreVal = (document.getElementById('filterNombre')?.value || "").toLowerCase();
        const veredaVal = document.getElementById('filterVereda')?.value || "";
        const eduVal = document.getElementById('filterEducacion')?.value || "";
        const compVal = document.getElementById('filterCompletitud')?.value || "";

        const filtered = (window.allResumes || []).filter(r => {
            const matchNombre = (r.nombre || "").toLowerCase().includes(nombreVal);
            const matchVereda = veredaVal === "" || (r.vereda || "") === veredaVal;
            const matchEdu = eduVal === "" || (r.niveles_educacion || "").includes(eduVal);
            let matchComp = true;
            if (compVal === 'completo') {
                matchComp = (r.completitud || 0) === 100;
            } else if (compVal === 'incompleto') {
                matchComp = (r.completitud || 0) < 100;
            }
            return matchNombre && matchVereda && matchEdu && matchComp;
        });
        window.filteredResumes = filtered;
        renderFilteredResumes(filtered);
    }

    function renderFilteredResumes(data) {
        const tableBody = document.getElementById('resumeTableBody');
        if (!tableBody) return;
        
        const countTotal = document.getElementById('total-resumes-count');
        const countComplete = document.getElementById('complete-resumes-count');
        const countIncomplete = document.getElementById('incomplete-resumes-count');

        let completeCount = 0;
        let incompleteCount = 0;

        (window.allResumes || data).forEach(r => {
            if ((r.completitud || 0) === 100) {
                completeCount++;
            } else {
                incompleteCount++;
            }
        });

        if (countTotal) countTotal.innerText = data.length;
        if (countComplete) countComplete.innerText = completeCount;
        if (countIncomplete) countIncomplete.innerText = incompleteCount;

        if (!data || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:20px; color:#666;">No se encontraron hojas de vida para este criterio.</td></tr>';
            return;
        }

        tableBody.innerHTML = "";
        data.forEach(resume => {
            const pct = resume.completitud || 0;
            let color = '#dc2626';
            if (pct >= 90) {
                color = '#16a34a';
            } else if (pct >= 60) {
                color = '#d97706';
            }

            const completitudHtml = `
                <div style="min-width: 140px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-weight: 700; font-size: 0.85rem; color: ${color};">${pct}%</span>
                        ${pct === 100 ? '<span style="font-size:0.7rem; background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:10px; font-weight:bold;"><i class="fas fa-check"></i> OK</span>' : ''}
                    </div>
                    <div style="width: 100%; background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                        <div style="width: ${pct}%; background: ${color}; height: 100%; transition: width 0.4s ease;"></div>
                    </div>
                    ${resume.faltantes ? `<div style="font-size: 0.72rem; color: #64748b; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Falta: ${resume.faltantes}"><i class="fas fa-info-circle"></i> Falta: ${resume.faltantes}</div>` : ''}
                </div>
            `;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: 600;">${resume.nombre || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.documento || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${completitudHtml}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.vereda || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.niveles_educacion || "No registrado"}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: var(--secondary-color);">${resume.total_experiencia || "0"}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.telefono || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.email || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee; display: flex; gap: 5px;">
                    <a href="descargar_cv.php?id=${resume.id}" target="_blank" class="btn-register" style="padding: 6px 12px; font-size: 0.8rem; text-decoration: none;" title="Ver PDF"><i class="fas fa-eye"></i> Ver</a>
                    <a href="resume_form.php?id=${resume.id}" class="btn-login" style="padding: 6px 12px; font-size: 0.8rem; text-decoration: none; background: var(--secondary-color); color: white;" title="Editar y completar perfil"><i class="fas fa-edit"></i> Editar</a>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    if (document.getElementById('resumeTableBody')) {
        loadResumes();
    }

    window.exportTableToExcel = function() {
        const dataToExport = window.filteredResumes || window.allResumes;
        if (!dataToExport || dataToExport.length === 0) {
            alert("No hay datos para exportar.");
            return;
        }

        let table = '<table border="1"><tr><th>Nombre</th><th>Cédula</th><th>Fecha de Nacimiento</th><th>Vereda</th><th>Nivel Educativo</th><th>Exp. (Años)</th><th>Telefono</th><th>Email</th></tr>';
        dataToExport.forEach(r => {
            table += `<tr>
                <td>${r.nombre || ""}</td>
                <td>${r.documento || ""}</td>
                <td>${r.fecha_nacimiento || ""}</td>
                <td>${r.vereda || ""}</td>
                <td>${r.niveles_educacion || "No registrado"}</td>
                <td>${r.total_experiencia || "0"}</td>
                <td>${r.telefono || ""}</td>
                <td>${r.email || ""}</td>
            </tr>`;
        });
        table += '</table>';

        const blob = new Blob(['\ufeff', table], { type: 'application/vnd.ms-excel' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", "Resultados_Filtro.xls");
        link.style.display = "none";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

});

function toggleLoginDrawer() {
    const drawer = document.getElementById('loginDrawer');
    const overlay = document.getElementById('loginDrawerOverlay');
    if (drawer && overlay) {
        drawer.classList.toggle('active');
        overlay.classList.toggle('active');
        if (drawer.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
}