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

            const btn = resumeForm.querySelector('button[type="submit"]');

            if (!btn) return;

            const originalText = btn.innerText;

            btn.innerText = "Guardando...";
            btn.disabled = true;

            const formData = new FormData(resumeForm);

            try {

                const response = await fetch('api/submit_resume.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {

                    alert('Hoja de vida registrada correctamente');

                    window.location.href = 'dashboard.php';

                } else {

                    alert(result.error || "Error guardando hoja de vida");

                    btn.innerText = originalText;
                    btn.disabled = false;

                }

            } catch (error) {

                console.error(error);

                alert("Error de conexión con el servidor");

                btn.innerText = originalText;
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

        tableBody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';

        try {

            const response = await fetch('api/get_resumes_dashboard.php');

            const result = await response.json();

            tableBody.innerHTML = "";

            if (!result.data || result.data.length === 0) {

                tableBody.innerHTML = '<tr><td colspan="4">No hay hojas de vida</td></tr>';
                return;

            }

            result.data.forEach(resume => {

                const row = document.createElement('tr');

                row.innerHTML = `
                <td>${resume.nombre || ""}</td>
                <td>${resume.nicho_cargo || ""}</td>
                <td>${resume.telefono || ""}<br>${resume.email || ""}</td>
                <td>
                <a href="api/download_resume_pdf.php?id=${resume.id}" target="_blank">Ver</a>
                </td>
                `;

                tableBody.appendChild(row);

            });

        } catch (e) {

            tableBody.innerHTML = '<tr><td colspan="4">Error cargando datos</td></tr>';

        }

    }

    if (document.getElementById('resumeTableBody')) {
        loadResumes();
    }

});