document.addEventListener('DOMContentLoaded', () => {

    // Hero Text Animation (Typewriter Effect)
    const heroParagraph = document.querySelector('.hero-content p');
    if (heroParagraph) {
        const originalText = heroParagraph.innerHTML; // Keep HTML tags
        heroParagraph.innerHTML = ''; // Clear content

        let i = 0;

        function typeWriter() {
            if (i < originalText.length) {
                let char = originalText.charAt(i);

                if (char === '<') {
                    let nextSegment = originalText.substring(i);
                    let tagEndIndex = nextSegment.indexOf('>');
                    if (tagEndIndex !== -1) {
                        i += tagEndIndex + 1;
                        heroParagraph.innerHTML = originalText.substring(0, i) + '<span class="cursor">|</span>';
                        setTimeout(typeWriter, 0);
                        return;
                    }
                }

                heroParagraph.innerHTML = originalText.substring(0, i + 1) + '<span class="cursor">|</span>';
                i++;
                setTimeout(typeWriter, 30);
            } else {
                heroParagraph.innerHTML = originalText + '<span class="cursor">|</span>';
            }
        }

        // Start after a small delay
        setTimeout(typeWriter, 500);
    }

    // Password Toggle
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password-input');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Toggle icon (optional, simplistic text swap or icon swap)
        });
    }

    // Modal Logic
    const consultarBtn = document.getElementById('btn-consultar');
    const resumeModal = document.getElementById('resume-modal');
    const listModal = document.getElementById('list-modal');
    const closeButtons = document.querySelectorAll('.close-modal');

    // btn-ingresar is now a direct link, so strict modal logic is removed for it.

    if (consultarBtn) {
        consultarBtn.addEventListener('click', () => {
            loadResumes();
            listModal.style.display = 'flex';
        });
    }

    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            resumeModal.style.display = 'none';
            listModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === resumeModal) resumeModal.style.display = 'none';
        if (e.target === listModal) listModal.style.display = 'none';
    });

    // Login Sidebar Logic
    const loginBtn = document.getElementById('nav-login-link');
    const loginSidebar = document.getElementById('login-sidebar');
    const closeLoginBtn = document.getElementById('close-login');
    const closeRegisterBtn = document.getElementById('close-register'); // New
    const showRegisterBtn = document.getElementById('show-register');
    const showLoginBtn = document.getElementById('show-login');
    const loginFormContainer = document.getElementById('login-form-container');
    const registerFormContainer = document.getElementById('register-form-container');

    if (loginBtn && loginSidebar) {
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginSidebar.classList.add('active');
            // Reset to login view when opening
            loginFormContainer.style.display = 'block';
            registerFormContainer.style.display = 'none';
        });
    }

    const closeSidebar = () => {
        loginSidebar.classList.remove('active');
    };

    if (closeLoginBtn) closeLoginBtn.addEventListener('click', closeSidebar);
    if (closeRegisterBtn) closeRegisterBtn.addEventListener('click', closeSidebar);

    // Toggle Forms
    if (showRegisterBtn && showLoginBtn) {
        showRegisterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginFormContainer.style.display = 'none';
            registerFormContainer.style.display = 'block';
        });

        showLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            registerFormContainer.style.display = 'none';
            loginFormContainer.style.display = 'block';
        });
    }

    // Close on click outside
    window.addEventListener('click', (e) => {
        if (loginSidebar && loginSidebar.classList.contains('active')) {
            // If click is not inside sidebar and not on the login button
            if (!loginSidebar.contains(e.target) && e.target !== loginBtn) {
                loginSidebar.classList.remove('active');
            }
        }
    });

    // Comprehensive Form Logic

    // Data definition
    const departments = {
        "Bogotá D.C.": ["Bogotá D.C."],
        "Antioquia": ["Medellín", "Bello", "Itagüí", "Envigado"],
        "Cundinamarca": ["Soacha", "Zipaquirá", "Facatativá", "Chía"],
        "Valle del Cauca": ["Cali", "Palmira", "Buenaventura"]
        // Add more as needed or load from JSON
    };

    const niches = {
        "Agricultura": ["Cultivos", "Riego", "Cosecha", "Manejo de Plagas"],
        "Construcción": ["Albañilería", "Pintura", "Electricidad", "Plomería"],
        "Ventas": ["Atención al Cliente", "Caja", "Inventarios", "Negociación"],
        "Administración": ["Archivo", "Digitación", "Contabilidad Básica", "Recepción"],
        "Tecnología": ["Soporte Técnico", "Redes", "Programación", "Diseño Gráfico"]
    };

    // 1. Geography Dropdowns
    const deptSelects = ['birth_department', 'department'];
    const citySelects = ['birth_city', 'city'];

    deptSelects.forEach((id, index) => {
        const select = document.getElementById(id);
        const citySelect = document.getElementById(citySelects[index]);

        if (select && citySelect) {
            // Populate Departments
            select.innerHTML = '<option value="">Seleccione...</option>';
            Object.keys(departments).sort().forEach(dept => {
                select.innerHTML += `<option value="${dept}">${dept}</option>`;
            });

            // Handle Change
            select.addEventListener('change', () => {
                const dept = select.value;
                citySelect.innerHTML = '<option value="">Seleccione...</option>';

                if (dept && departments[dept]) {
                    departments[dept].sort().forEach(city => {
                        citySelect.innerHTML += `<option value="${city}">${city}</option>`;
                    });
                    citySelect.disabled = false;
                } else {
                    citySelect.disabled = true;
                    citySelect.innerHTML = '<option value="">Seleccione Departamento primero</option>';
                }
            });
        }
    });

    // 2. Skills Mosaic
    const nicheSelect = document.getElementById('niche-select');
    const skillsContainer = document.getElementById('skills-container');
    const skillsInput = document.getElementById('skills-input');
    let selectedSkills = new Set();

    if (nicheSelect && skillsContainer) {
        // Populate Niches
        Object.keys(niches).sort().forEach(niche => {
            nicheSelect.innerHTML += `<option value="${niche}">${niche}</option>`;
        });

        nicheSelect.addEventListener('change', () => {
            const niche = nicheSelect.value;
            selectedSkills.clear();
            updateSkillsInput();

            if (niche && niches[niche]) {
                skillsContainer.innerHTML = '';
                niches[niche].forEach(skill => {
                    const div = document.createElement('div');
                    div.className = 'skill-item';
                    div.textContent = skill;
                    div.onclick = () => toggleSkill(div, skill);
                    skillsContainer.appendChild(div);
                });
            } else {
                skillsContainer.innerHTML = '<p style="color:#666; font-style:italic;">Seleccione un nicho para ver habilidades sugeridas.</p>';
            }
        });
    }

    function toggleSkill(element, skill) {
        if (selectedSkills.has(skill)) {
            selectedSkills.delete(skill);
            element.classList.remove('selected');
        } else {
            selectedSkills.add(skill);
            element.classList.add('selected');
        }
        updateSkillsInput();
    }

    function updateSkillsInput() {
        if (skillsInput) {
            skillsInput.value = Array.from(selectedSkills).join(', ');
        }
    }

    // 3. Dynamic Rows (Education & Experience)
    function createRow(type, index) {
        const div = document.createElement('div');
        div.className = 'dynamic-item';
        div.id = `${type}-${index}`;

        let content = '';
        if (type === 'education') {
            content = `
                <h4>Estudio ${index + 1}</h4>
                <div class="grid-2">
                    <div class="input-group">
                        <label>Nivel Educativo</label>
                        <select name="education_${index}_level" required>
                            <option value="Bachiller">Bachiller</option>
                            <option value="Técnico">Técnico</option>
                            <option value="Tecnólogo">Tecnólogo</option>
                            <option value="Profesional">Profesional</option>
                            <option value="Postgrado">Postgrado</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Institución</label>
                        <input type="text" name="education_${index}_institution" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="education_${index}_start_date" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="education_${index}_end_date" id="edu_end_${index}" required>
                         <label style="display:inline-flex; align-items:center; margin-top:5px; font-weight:normal;">
                            <input type="checkbox" name="education_${index}_is_current" onchange="toggleEndDate('edu_end_${index}', this)"> 
                            En curso (Cargar certificado de estudios)
                        </label>
                    </div>
                    <div class="input-group full-width">
                        <label>Cargar Soporte (Diploma o Certificado)</label>
                        <input type="file" name="education_${index}_file" accept=".pdf,.jpg,.png" required>
                    </div>
                </div>
                <button type="button" class="btn-remove" onclick="removeRow(this)">Eliminar</button>
            `;
        } else if (type === 'experience') {
            content = `
                <h4>Experiencia ${index + 1}</h4>
                <div class="grid-2">
                    <div class="input-group">
                        <label>Cargo / Rol</label>
                        <input type="text" name="experience_${index}_role" required>
                    </div>
                    <div class="input-group">
                        <label>Empresa</label>
                        <input type="text" name="experience_${index}_company" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="experience_${index}_start_date" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="experience_${index}_end_date" id="exp_end_${index}" required>
                         <label style="display:inline-flex; align-items:center; margin-top:5px; font-weight:normal;">
                            <input type="checkbox" name="experience_${index}_is_current" onchange="toggleEndDate('exp_end_${index}', this)"> 
                            Actualmente (No requiere certificado laboral)
                        </label>
                    </div>
                    <div class="input-group full-width" id="exp_cert_${index}_container">
                        <label>Cargar Certificación Laboral</label>
                        <input type="file" name="experience_${index}_file" accept=".pdf,.jpg,.png" required>
                    </div>
                </div>
                <button type="button" class="btn-remove" onclick="removeRow(this)">Eliminar</button>
            `;
        }

        div.innerHTML = content;
        return div;
    }

    // Global toggle function
    window.toggleEndDate = function (inputId, checkbox) {
        const input = document.getElementById(inputId);
        if (input) {
            input.disabled = checkbox.checked;
            input.required = !checkbox.checked;
            if (checkbox.checked) input.value = '';
        }

        // Specific logic for Experience Certificate
        if (inputId.startsWith('exp_end_')) {
            const index = inputId.split('_')[2];
            const certContainer = document.getElementById(`exp_cert_${index}_container`);
            const certInput = certContainer.querySelector('input');

            if (checkbox.checked) {
                // If current, cert NOT required
                certContainer.style.display = 'none';
                certInput.required = false;
            } else {
                // If finished, cert REQUIRED
                certContainer.style.display = 'block';
                certInput.required = true;
            }
        }
    };

    window.removeRow = function (btn) {
        btn.parentElement.remove();
    };

    const addEduBtn = document.getElementById('add-education');
    const eduList = document.getElementById('education-list');
    let eduCount = 0;

    if (addEduBtn && eduList) {
        addEduBtn.addEventListener('click', () => {
            eduList.appendChild(createRow('education', eduCount++));
        });
        // Add one initial row
        // eduList.appendChild(createRow('education', eduCount++)); 
    }

    const addExpBtn = document.getElementById('add-experience');
    const expList = document.getElementById('experience-list');
    let expCount = 0;

    if (addExpBtn && expList) {
        addExpBtn.addEventListener('click', () => {
            if (expCount < 5) {
                expList.appendChild(createRow('experience', expCount++));
            } else {
                alert('Máximo 5 experiencias permitidas.');
            }
        });
    }

    // Form Submission (Updated)
    const resumeForm = document.getElementById('resume-form');
    if (resumeForm) {
        resumeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Collect References as JSON
            const p1 = {
                name: resumeForm.ref_p1_name.value,
                phone: resumeForm.ref_p1_phone.value,
                occupation: resumeForm.ref_p1_occupation.value
            };
            const p2 = {
                name: resumeForm.ref_p2_name.value,
                phone: resumeForm.ref_p2_phone.value,
                occupation: resumeForm.ref_p2_occupation.value
            };
            const f1 = {
                name: resumeForm.ref_f1_name.value,
                phone: resumeForm.ref_f1_phone.value,
                relation: resumeForm.ref_f1_relation.value
            };
            const f2 = {
                name: resumeForm.ref_f2_name.value,
                phone: resumeForm.ref_f2_phone.value,
                relation: resumeForm.ref_f2_relation.value
            };

            const formData = new FormData(resumeForm);
            formData.append('personal_references_json', JSON.stringify([p1, p2]));
            formData.append('family_references_json', JSON.stringify([f1, f2]));

            try {
                const response = await fetch('api/submit_resume.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (response.ok) {
                    alert('¡Hoja de vida completa registrada con éxito!');
                    if (result.resume_id) {
                        window.location.href = `resume_preview.php?resume_id=${result.resume_id}`;
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                    resumeForm.reset();
                    // Clear dynamic lists
                    eduList.innerHTML = '';
                    expList.innerHTML = '';
                    eduCount = 0;
                    expCount = 0;
                    if (skillsContainer) skillsContainer.innerHTML = '';
                    if (skillsInput) skillsInput.value = '';
                    selectedSkills.clear();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Hubo un error al enviar el formulario.');
            }
        });
    }

    // Load Resumes
    async function loadResumes() {
        const tableBody = document.getElementById('resumeTableBody');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="4" style="padding:20px; text-align:center;">Cargando...</td></tr>';

        try {
            const response = await fetch('api/get_resumes.php');
            const data = await response.json();

            tableBody.innerHTML = '';
            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="padding:20px; text-align:center;">No hay registros encontrados.</td></tr>';
                return;
            }

            data.forEach(resume => {
                const row = document.createElement('tr');
                row.style.borderBottom = '1px solid #eee';
                row.innerHTML = `
                    <td style="padding:15px;">
                        <strong>${resume.full_name}</strong><br>
                        <small style="color:#777;">${resume.email}</small>
                    </td>
                    <td style="padding:15px;">${resume.niche || 'N/A'}</td>
                    <td style="padding:15px;">${resume.phone}</td>
                    <td style="padding:15px;">
                        <div style="display:flex; gap:10px;">
                            <a href="resume_preview.php?resume_id=${resume.id}" class="btn-login" style="text-decoration:none; padding:5px 12px; font-size:0.8rem; border:1px solid var(--primary-color); border-radius:15px; color:var(--primary-color);">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="api/download_resume.php?resume_id=${resume.id}" class="btn-secondary" style="text-decoration:none; padding:5px 12px; font-size:0.8rem; background:#8d6e63; color:#fff; border-radius:15px;">
                                <i class="fas fa-download"></i> ZIP
                            </a>
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Error fetching resumes:', error);
            tableBody.innerHTML = '<tr><td colspan="4" style="padding:20px; text-align:center; color:red;">Error al cargar datos.</td></tr>';
        }
    }
});
