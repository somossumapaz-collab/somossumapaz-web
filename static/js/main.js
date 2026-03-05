document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Colombian Locations Logic ---
    const departments = ["Amazonas", "Antioquia", "Arauca", "Atlántico", "Bolívar", "Boyacá", "Caldas", "Caquetá", "Casanare", "Cauca", "Cesar", "Chocó", "Córdoba", "Cundinamarca", "Guainía", "Guaviare", "Huila", "La Guajira", "Magdalena", "Meta", "Nariño", "Norte de Santander", "Putumayo", "Quindío", "Risaralda", "San Andrés y Providencia", "Santander", "Sucre", "Tolima", "Valle del Cauca", "Vaupés", "Vichada"];

    // Simplified city mapping (top 3 per department for demonstration/original feel)
    const cities = {
        "Cundinamarca": ["Fusagasugá", "Arbeláez", "Pandi", "San Bernardo", "Venecia", "Pasca", "Tibacuy", "Cabrera", "Bogotá"],
        "Meta": ["Villavicencio", "Acacías", "Granada"],
        "Antioquia": ["Medellín", "Envigado", "Itagüí"],
        "Atlántico": ["Barranquilla", "Soledad", "Puerto Colombia"],
        "Valle del Cauca": ["Cali", "Palmira", "Buenaventura"]
    };

    function populateDepartments(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;
        departments.sort().forEach(dept => {
            const opt = document.createElement('option');
            opt.value = dept;
            opt.textContent = dept;
            select.appendChild(opt);
        });
    }

    function handleDeptChange(deptSelectId, citySelectId) {
        const deptSelect = document.getElementById(deptSelectId);
        const citySelect = document.getElementById(citySelectId);
        if (!deptSelect || !citySelect) return;

        deptSelect.addEventListener('change', () => {
            const dept = deptSelect.value;
            citySelect.innerHTML = '<option value="">Seleccione Municipio...</option>';
            if (cities[dept]) {
                cities[dept].sort().forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city;
                    opt.textContent = city;
                    citySelect.appendChild(opt);
                });
            } else if (dept) {
                // Fallback text if cities not in list
                const opt = document.createElement('option');
                opt.value = "Otro";
                opt.textContent = "Otro (Especifique en observaciones)";
                citySelect.appendChild(opt);
            }
        });
    }

    populateDepartments('birth_department');
    populateDepartments('department');
    handleDeptChange('birth_department', 'birth_city');
    handleDeptChange('department', 'city');

    // --- 2. Skills Mosaic Logic ---
    const skillsList = [
        "Albañilería", "Carpintería", "Electricidad", "Plomería", "Pintura",
        "Soldadura", "Gestión de Proyectos", "Excel Básico", "Excel Avanzado",
        "Liderazgo", "Atención al Cliente", "Conducción (C1/C2)", "Seguridad Industrial",
        "Mantenimiento", "Limpieza", "Cocina", "Administración", "Contabilidad"
    ];

    const mosaic = document.getElementById('skills-mosaic');
    const skillsInput = document.getElementById('skills-input');
    const selectedSkills = new Set();

    if (mosaic) {
        skillsList.sort().forEach(skill => {
            const card = document.createElement('div');
            card.className = 'skill-card';
            card.textContent = skill;
            card.addEventListener('click', () => {
                if (selectedSkills.has(skill)) {
                    selectedSkills.delete(skill);
                    card.classList.remove('selected');
                } else {
                    selectedSkills.add(skill);
                    card.classList.add('selected');
                }
                skillsInput.value = Array.from(selectedSkills).join(',');
            });
            mosaic.appendChild(card);
        });
    }

    // --- 3. Dynamic Items (Education/Experience) ---
    const resumeForm = document.getElementById('resume-form');
    if (resumeForm) {
        let eduCount = 0;
        let expCount = 0;

        const addEduBtn = document.getElementById('add-education-btn');
        const addExpBtn = document.getElementById('add-experience-btn');
        const eduList = document.getElementById('education-list');
        const expList = document.getElementById('experience-list');

        function addItem(type) {
            const index = type === 'education' ? eduCount++ : expCount++;
            const tpl = document.getElementById(`${type}-item-tpl`).innerHTML;
            const html = tpl.replace(/INDEX/g, index);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            const item = wrapper.firstElementChild;

            item.querySelector('.btn-remove').addEventListener('click', () => {
                item.remove();
            });

            if (type === 'education') eduList.appendChild(item);
            else expList.appendChild(item);
        }

        if (addEduBtn) addEduBtn.addEventListener('click', () => addItem('education'));
        if (addExpBtn) addExpBtn.addEventListener('click', () => addItem('experience'));

        // Handle Form Submission
        resumeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = resumeForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerText = 'Guardando...';
            btn.disabled = true;

            const formData = new FormData(resumeForm);

            try {
                const response = await fetch('api/submit_resume.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert('¡Hoja de vida registrada exitosamente!');
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Error: ' + result.error);
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Error de conexión con el servidor.');
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }

    // --- 4. Admin Dashboard Logic ---
    async function loadResumes() {
        const tableBody = document.getElementById('resumeTableBody');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Cargando Dashboard...</td></tr>';

        try {
            const response = await fetch('api/get_resumes_dashboard.php');
            const result = await response.json();

            if (result.success) {
                tableBody.innerHTML = '';
                if (!result.data || result.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No hay hojas de vida registradas aún.</td></tr>';
                    return;
                }

                result.data.forEach(resume => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${resume.nombre || 'Sin nombre'}</strong></td>
                        <td>${resume.nicho_cargo || 'N/A'}</td>
                        <td>
                            <div style="font-size:0.85rem">
                                <i class="fas fa-phone"></i> ${resume.telefono || 'N/A'}<br>
                                <i class="fas fa-envelope"></i> ${resume.email || 'N/A'}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <a href="api/download_resume_pdf.php?id=${resume.id}" target="_blank" class="btn-action" style="text-decoration:none; padding:5px 10px; background:#3498db; color:white; border-radius:5px; font-size:0.8rem;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="api/download_resume_pdf.php?id=${resume.id}" target="_blank" class="btn-action" style="text-decoration:none; padding:5px 10px; background:#2ecc71; color:white; border-radius:5px; font-size:0.8rem;">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `<tr><td colspan="4" style="color:red; text-align:center;">${result.error}</td></tr>`;
            }
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="4" style="color:red; text-align:center;">Error de conexión con el tablero.</td></tr>';
        }
    }

    if (document.getElementById('resumeTableBody')) {
        loadResumes();
    }
});
