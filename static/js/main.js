document.addEventListener('DOMContentLoaded', () => {

    // --- Basic UI Logic ---
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

            const div = document.createElement('div');
            div.innerHTML = html;
            const item = div.querySelector('.dynamic-item');

            item.querySelector('.btn-remove').addEventListener('click', () => {
                item.remove();
            });

            if (type === 'education') eduList.appendChild(item);
            else expList.appendChild(item);
        }

        if (addEduBtn) addEduBtn.addEventListener('click', () => addItem('education'));
        if (addExpBtn) addExpBtn.addEventListener('click', () => addItem('experience'));

        // Handle Form Submission via AJAX for better feedback
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
                    alert('¡Hoja de vida guardada exitosamente!');
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
                if (!result.data || result.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No hay hojas de vida registradas aún.</td></tr>';
                    return;
                }

                result.data.forEach(resume => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${resume.nombre}</strong></td>
                        <td>${resume.nicho_cargo || 'N/A'}</td>
                        <td>
                            <div style="font-size:0.85rem">
                                <i class="fas fa-phone"></i> ${resume.telefono}<br>
                                <i class="fas fa-envelope"></i> ${resume.email}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <a href="api/download_resume_pdf.php?id=${resume.id}" target="_blank" class="btn-action btn-view" style="text-decoration:none; padding:5px 10px; background:#3498db; color:white; border-radius:5px; font-size:0.8rem;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="api/download_resume_pdf.php?id=${resume.id}" target="_blank" class="btn-action btn-download" style="text-decoration:none; padding:5px 10px; background:#2ecc71; color:white; border-radius:5px; font-size:0.8rem;">
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
