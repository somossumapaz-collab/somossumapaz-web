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

    // --- Professional Resume System Logic ---
    const resumeForm = document.getElementById('resume-form');
    let currentSection = 'personal';

    if (resumeForm) {
        const navSteps = document.querySelectorAll('.nav-step');
        const sections = document.querySelectorAll('.form-section-content');
        const hvId = resumeForm.getAttribute('data-hv-id');

        // 1. Navigation Logic
        function showSection(sectionId) {
            sections.forEach(s => s.classList.remove('active'));
            navSteps.forEach(n => n.classList.remove('active'));

            document.getElementById(`section-${sectionId}`).classList.add('active');
            document.querySelector(`.nav-step[data-section="${sectionId}"]`).classList.add('active');
            currentSection = sectionId;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        navSteps.forEach(step => {
            step.addEventListener('click', () => {
                const target = step.getAttribute('data-section');
                saveCurrentSection().then(() => showSection(target));
            });
        });

        document.querySelectorAll('.next-section').forEach(btn => {
            btn.addEventListener('click', () => {
                const currentIndex = Array.from(navSteps).findIndex(s => s.getAttribute('data-section') === currentSection);
                if (currentIndex < navSteps.length - 1) {
                    const next = navSteps[currentIndex + 1].getAttribute('data-section');
                    saveCurrentSection().then(() => showSection(next));
                }
            });
        });

        document.querySelectorAll('.prev-section').forEach(btn => {
            btn.addEventListener('click', () => {
                const currentIndex = Array.from(navSteps).findIndex(s => s.getAttribute('data-section') === currentSection);
                if (currentIndex > 0) {
                    const prev = navSteps[currentIndex - 1].getAttribute('data-section');
                    showSection(prev);
                }
            });
        });

        // 2. Auto-Save Logic
        async function saveCurrentSection() {
            const statusEl = document.getElementById('autoSaveStatus');
            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            statusEl.className = 'auto-save-status saving';

            const formData = new FormData();
            formData.append('section', currentSection);
            formData.append('hoja_vida_id', resumeForm.getAttribute('data-hv-id'));

            // Gather data based on section
            if (currentSection === 'personal') {
                formData.append('full_name', resumeForm.full_name.value);
                formData.append('email', resumeForm.email.value);
                formData.append('id_type', resumeForm.id_type.value);
                formData.append('document_id', resumeForm.document_id.value);
                formData.append('phone', resumeForm.phone.value);
                formData.append('niche', resumeForm.niche.value);
                formData.append('profesion', resumeForm.profesion.value);
                formData.append('profile_description', resumeForm.profile_description.value);
            } else if (currentSection === 'skills') {
                formData.append('skills', resumeForm.skills.value);
            } else if (['education', 'experience', 'references'].includes(currentSection)) {
                const items = [];
                const itemEls = document.querySelectorAll(`.${currentSection}-item`);
                itemEls.forEach(el => {
                    const item = {};
                    if (currentSection === 'education') {
                        item.institution = el.querySelector('.item-institution').value;
                        item.level = el.querySelector('.item-level').value;
                        item.start_date = el.querySelector('.item-start').value;
                        item.end_date = el.querySelector('.item-end').value;
                        item.is_current = el.querySelector('.item-current').checked ? 1 : 0;
                        item.file_path = el.querySelector('.item-file-path').value;
                    } else if (currentSection === 'experience') {
                        item.company = el.querySelector('.item-company').value;
                        item.role = el.querySelector('.item-role').value;
                        item.start_date = el.querySelector('.item-start').value;
                        item.end_date = el.querySelector('.item-end').value;
                        item.is_current = el.querySelector('.item-current').checked ? 1 : 0;
                        item.file_path = el.querySelector('.item-file-path').value;
                    } else if (currentSection === 'references') {
                        item.name = el.querySelector('.item-name').value;
                        item.phone = el.querySelector('.item-phone').value;
                        item.type = el.querySelector('.item-type').value;
                        item.occupation = el.querySelector('.item-occupation').value;
                        item.relation = el.querySelector('.item-occupation').value; // Unified field
                    }
                    items.push(item);
                });
                formData.append('items', JSON.stringify(items));
            }

            try {
                const res = await fetch('api/save_section.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Guardado';
                    statusEl.className = 'auto-save-status saved';
                    if (result.hoja_vida_id) resumeForm.setAttribute('data-hv-id', result.hoja_vida_id);
                    document.querySelector(`.nav-step[data-section="${currentSection}"]`).classList.add('completed');
                } else {
                    statusEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al guardar';
                    statusEl.className = 'auto-save-status error';
                }
            } catch (e) {
                statusEl.innerHTML = '<i class="fas fa-wifi"></i> Error de red';
            }
        }

        // 3. Dynamic Items (Education, Experience, References)
        function addItem(type, data = null) {
            const container = document.getElementById(`${type}-items`);
            const template = document.getElementById(`${type}-tpl`);
            const clone = template.content.cloneNode(true);
            const itemDiv = clone.querySelector('.dynamic-item');

            if (data) {
                if (type === 'education') {
                    itemDiv.querySelector('.item-institution').value = data.institucion || '';
                    itemDiv.querySelector('.item-level').value = data.nivel_educativo || '';
                    itemDiv.querySelector('.item-start').value = data.fecha_inicio || '';
                    itemDiv.querySelector('.item-end').value = data.fecha_fin || '';
                    itemDiv.querySelector('.item-current').checked = data.en_curso == 1;
                    itemDiv.querySelector('.item-file-path').value = data.soporte_path || '';
                    if (data.soporte_path) itemDiv.querySelector('.file-status').innerHTML = '<small style="color:green">Archivo cargado</small>';
                } else if (type === 'experience') {
                    itemDiv.querySelector('.item-company').value = data.empresa || '';
                    itemDiv.querySelector('.item-role').value = data.cargo || '';
                    itemDiv.querySelector('.item-start').value = data.fecha_inicio || '';
                    itemDiv.querySelector('.item-end').value = data.fecha_fin || '';
                    itemDiv.querySelector('.item-current').checked = data.actualmente == 1;
                    itemDiv.querySelector('.item-file-path').value = data.soporte_path || '';
                    if (data.soporte_path) itemDiv.querySelector('.file-status').innerHTML = '<small style="color:green">Archivo cargado</small>';
                } else if (type === 'references') {
                    itemDiv.querySelector('.item-name').value = data.nombre || '';
                    itemDiv.querySelector('.item-phone').value = data.telefono || '';
                    itemDiv.querySelector('.item-type').value = data.tipo || 'Personal';
                    itemDiv.querySelector('.item-occupation').value = data.ocupacion || data.parentesco || '';
                }
            }

            itemDiv.querySelector('.btn-remove').addEventListener('click', () => {
                if (confirm('¿Eliminar este elemento?')) {
                    itemDiv.remove();
                    saveCurrentSection();
                }
            });

            // File upload listener for dynamic items
            const fileInput = itemDiv.querySelector('.item-file');
            if (fileInput) {
                fileInput.addEventListener('change', async () => {
                    const status = itemDiv.querySelector('.file-status');
                    status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';

                    const fd = new FormData();
                    fd.append('file', fileInput.files[0]);
                    fd.append('type', type === 'education' ? 'edu_cert' : 'exp_cert');
                    fd.append('hoja_vida_id', resumeForm.getAttribute('data-hv-id'));

                    try {
                        const r = await fetch('api/upload_file.php', { method: 'POST', body: fd });
                        const res = await r.json();
                        if (res.success) {
                            itemDiv.querySelector('.item-file-path').value = res.path;
                            status.innerHTML = '<small style="color:green">✓ Subido</small>';
                            saveCurrentSection();
                        } else {
                            status.innerHTML = '<small style="color:red">Error</small>';
                            alert(res.error);
                        }
                    } catch (e) { status.innerHTML = 'Error'; }
                });
            }

            container.appendChild(itemDiv);
        }

        document.getElementById('add-education').addEventListener('click', () => addItem('education'));
        document.getElementById('add-experience').addEventListener('click', () => addItem('experience'));
        document.getElementById('add-reference').addEventListener('click', () => addItem('references'));

        // 4. File Upload (Photo & ID)
        document.getElementById('photo-upload').addEventListener('change', async (e) => {
            const fd = new FormData();
            fd.append('file', e.target.files[0]);
            fd.append('type', 'photo');
            fd.append('hoja_vida_id', resumeForm.getAttribute('data-hv-id'));

            try {
                const r = await fetch('api/upload_file.php', { method: 'POST', body: fd });
                const res = await r.json();
                if (res.success) {
                    document.getElementById('profile-preview').src = res.path;
                    saveCurrentSection();
                } else alert(res.error);
            } catch (e) { alert('Error al subir foto'); }
        });

        // 5. Initial Data Load
        if (window.INITIAL_RESUME_DATA) {
            const d = window.INITIAL_RESUME_DATA;
            if (d.education) d.education.forEach(item => addItem('education', item));
            if (d.experiencia) d.experiencia.forEach(item => addItem('experience', item));
            if (d.referencias) d.referencias.forEach(item => addItem('references', item));
        }

        // 6. Final Save
        document.getElementById('final-save').addEventListener('click', async () => {
            await saveCurrentSection();
            const id = resumeForm.getAttribute('data-hv-id');
            alert('¡Hoja de vida finalizada con éxito!');
            window.location.href = `api/download_resume_pdf.php?user_id=${window.INITIAL_RESUME_DATA.usuario_id || ''}`;
        });
    }

    // --- Admin Dashboard Logic ---
    async function loadResumes() {
        const tableBody = document.getElementById('resumeTableBody');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Cargando Dashboard...</td></tr>';

        try {
            const response = await fetch('api/get_resumes_dashboard.php');
            const result = await response.json();

            if (result.success) {
                tableBody.innerHTML = '';
                if (result.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No hay postulantes aún.</td></tr>';
                    return;
                }

                result.data.forEach(resume => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${resume.nombre_completo || (resume.nombre + ' ' + resume.apellido)}</strong></td>
                        <td>${resume.profesion_display || 'N/A'}</td>
                        <td>
                            <div style="font-size:0.85rem">
                                <i class="fas fa-phone"></i> ${resume.telefono}<br>
                                <i class="fas fa-envelope"></i> ${resume.email}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <a href="api/download_resume_pdf.php?user_id=${resume.usuario_id}" target="_blank" class="btn-action btn-view">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="api/download_resume_pdf.php?user_id=${resume.usuario_id}" target="_blank" class="btn-action btn-download">
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

    // Load resumes if on dashboard
    if (document.getElementById('resumeTableBody')) {
        loadResumes();
    }
});
