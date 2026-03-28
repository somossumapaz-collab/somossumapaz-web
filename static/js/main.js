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

    function populateDepartments(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.innerHTML = '<option value="">Seleccione...</option>';

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

            }
        });

    }

    populateDepartments('birth_department');
    populateDepartments('department');

    handleDeptChange('birth_department', 'birth_city');
    handleDeptChange('department', 'city');


    /* ===================================
       2. HABILIDADES
    =================================== */

    const skillsList = [
        "Albañilería", "Carpintería", "Electricidad", "Plomería", "Pintura",
        "Soldadura", "Gestión de Proyectos", "Excel Básico", "Excel Avanzado",
        "Liderazgo", "Atención al Cliente", "Conducción (C1/C2)",
        "Seguridad Industrial", "Mantenimiento", "Limpieza", "Cocina",
        "Administración", "Contabilidad"
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

                if (skillsInput) {
                    skillsInput.value = Array.from(selectedSkills).join(',');
                }

            });

            mosaic.appendChild(card);

        });

    }


    /* ===================================
       3. EDUCACION Y EXPERIENCIA DINAMICA
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

            if (type === "education") {
                eduList.appendChild(item);
            } else {
                expList.appendChild(item);
            }

        }

        if (addEduBtn) {
            addEduBtn.addEventListener('click', () => addItem("education"));
        }

        if (addExpBtn) {
            addExpBtn.addEventListener('click', () => addItem("experience"));
        }


        /* ===================================
           4. SUBMIT FORMULARIO
        =================================== */

        resumeForm.addEventListener('submit', async (e) => {

            e.preventDefault();

            const debugBox = document.createElement("div");
            debugBox.style.background = "#fff3cd";
            debugBox.style.border = "1px solid #ffc107";
            debugBox.style.padding = "10px";
            debugBox.style.margin = "10px";
            debugBox.style.fontSize = "14px";
            debugBox.innerHTML = "<b>Debug envío formulario:</b><br>";
            document.body.prepend(debugBox);

            try {

                const btn = resumeForm.querySelector('button[type="submit"]');

                if (!btn) {
                    debugBox.innerHTML += "❌ No se encontró botón submit<br>";
                    return;
                }

                const originalText = btn.innerText;
                btn.innerText = "Guardando...";
                btn.disabled = true;

                const formData = new FormData(resumeForm);

                debugBox.innerHTML += "✔ FormData creado<br>";

                for (let pair of formData.entries()) {
                    debugBox.innerHTML += pair[0] + " = " + pair[1] + "<br>";
                }

                const response = await fetch('api/submit_resume.php', {
                    method: 'POST',
                    body: formData
                });

                debugBox.innerHTML += "✔ Respuesta recibida<br>";

                const text = await response.text();

                debugBox.innerHTML += "<b>Respuesta servidor:</b><br>" + text;

                try {

                    const result = JSON.parse(text);

                    if (result.success) {
                        alert("Hoja de vida registrada con éxito. Se abrirá la vista de impresión.");
                        window.location.href = "api/download_resume_pdf.php?id=" + result.id;
                    } else {
                        alert("Error servidor: " + result.error);
                    }

                } catch (jsonError) {

                    debugBox.innerHTML += "<br><b>⚠ Error parseando JSON:</b><br>" + jsonError;

                }

                btn.innerText = originalText;
                btn.disabled = false;

            } catch (error) {

                console.error(error);

                debugBox.innerHTML += "<br><b>❌ Error JS:</b><br>" + error;

                alert("Error detectado. Revisa el recuadro amarillo arriba.");

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

            ['filterNombre', 'filterVereda', 'filterEducacion'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', applyFilters);
            });
        } catch (e) {
            console.error("Error loading resumes:", e);
            tableBody.innerHTML = '<tr><td colspan="6">Error cargando datos</td></tr>';
        }
    }

    function applyFilters() {
        const nombreVal = (document.getElementById('filterNombre')?.value || "").toLowerCase();
        const veredaVal = document.getElementById('filterVereda')?.value || "";
        const eduVal = document.getElementById('filterEducacion')?.value || "";

        const filtered = window.allResumes.filter(r => {
            const matchNombre = (r.nombre || "").toLowerCase().includes(nombreVal);
            const matchVereda = veredaVal === "" || (r.vereda || "") === veredaVal;
            const matchEdu = eduVal === "" || (r.niveles_educacion || "").includes(eduVal);
            return matchNombre && matchVereda && matchEdu;
        });
        renderFilteredResumes(filtered);
    }

    function renderFilteredResumes(data) {
        const tableBody = document.getElementById('resumeTableBody');
        if (!tableBody) return;
        
        const countEl = document.getElementById('total-resumes-count');
        if (countEl) countEl.innerText = data.length;

        tableBody.innerHTML = "";
        data.forEach(resume => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.nombre || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.vereda || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.niveles_educacion || "No registrado"}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: var(--secondary-color);">${resume.total_experiencia || "0"}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.telefono || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">${resume.email || ""}</td>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">
                    <a href="descargar_cv.php?id=${resume.id}" target="_blank" class="btn-register" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none;">Ver</a>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    if (document.getElementById('resumeTableBody')) {
        loadResumes();
    }

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