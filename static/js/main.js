document.addEventListener('DOMContentLoaded', () => {

    const errorBox = document.getElementById("form-error");

    function showError(msg) {

        errorBox.style.display = "block";
        errorBox.innerHTML = msg;

    }

    /* =======================
    DEPARTAMENTOS
    ======================= */

    const departments = [
        "Amazonas", "Antioquia", "Arauca", "Atlántico", "Bolívar", "Boyacá",
        "Caldas", "Caquetá", "Casanare", "Cauca", "Cesar", "Chocó", "Córdoba",
        "Cundinamarca", "Guainía", "Guaviare", "Huila", "La Guajira",
        "Magdalena", "Meta", "Nariño", "Norte de Santander", "Putumayo",
        "Quindío", "Risaralda", "San Andrés", "Santander", "Sucre",
        "Tolima", "Valle del Cauca", "Vaupés", "Vichada"
    ];

    const cities = {
        "Cundinamarca": ["Fusagasugá", "Arbeláez", "Pasca", "Tibacuy", "Bogotá"],
        "Meta": ["Villavicencio", "Acacías", "Granada"],
        "Antioquia": ["Medellín", "Envigado", "Itagüí"]
    };

    function populateDepartments(id) {

        const select = document.getElementById(id);

        if (!select) return;

        select.innerHTML = '<option value="">Seleccione...</option>';

        departments.forEach(d => {

            let opt = document.createElement("option");

            opt.value = d;
            opt.textContent = d;

            select.appendChild(opt);

        });

    }

    function handleDeptChange(deptId, cityId) {

        const dept = document.getElementById(deptId);
        const city = document.getElementById(cityId);

        if (!dept || !city) return;

        dept.addEventListener("change", () => {

            city.innerHTML = '<option value="">Seleccione...</option>';

            const list = cities[dept.value] || [];

            list.forEach(c => {

                let opt = document.createElement("option");
                opt.value = c;
                opt.textContent = c;
                city.appendChild(opt);

            });

        });

    }

    populateDepartments("birth_department");
    populateDepartments("department");

    handleDeptChange("birth_department", "birth_city");
    handleDeptChange("department", "city");


    /* =======================
    HABILIDADES
    ======================= */

    const skillsList = [
        "Agricultura", "Ganadería", "Construcción",
        "Electricidad", "Carpintería", "Ventas",
        "Atención al cliente", "Programación",
        "Diseño gráfico", "Marketing", "Logística"
    ];

    const mosaic = document.getElementById("skills-mosaic");
    const skillsInput = document.getElementById("skills-input");

    const selected = new Set();

    skillsList.forEach(skill => {

        let card = document.createElement("div");

        card.className = "skill-card";
        card.textContent = skill;

        card.onclick = () => {

            if (selected.has(skill)) {

                selected.delete(skill);
                card.classList.remove("selected");

            } else {

                selected.add(skill);
                card.classList.add("selected");

            }

            skillsInput.value = [...selected].join(",");

        };

        mosaic.appendChild(card);

    });


    /* =======================
    EDUCACION / EXPERIENCIA
    ======================= */

    let eduCount = 0;
    let expCount = 0;

    function addItem(type) {

        const index = type === "education" ? eduCount++ : expCount++;

        const tpl = document.getElementById(type + "-item-tpl");

        let html = tpl.innerHTML.replace(/INDEX/g, index);

        let wrapper = document.createElement("div");
        wrapper.innerHTML = html;

        let item = wrapper.firstElementChild;

        item.querySelector(".remove").onclick = () => item.remove();

        if (type === "education") {

            document.getElementById("education-list").appendChild(item);

        } else {

            document.getElementById("experience-list").appendChild(item);

        }

    }

    document.getElementById("add-education-btn")
        .onclick = () => addItem("education");

    document.getElementById("add-experience-btn")
        .onclick = () => addItem("experience");


    /* =======================
    SUBMIT
    ======================= */

    const form = document.getElementById("resume-form");

    form.addEventListener("submit", async (e) => {

        e.preventDefault();

        errorBox.style.display = "none";

        const btn = form.querySelector("button[type=submit]");

        btn.disabled = true;
        btn.innerText = "Guardando...";

        try {

            const data = new FormData(form);

            const response = await fetch("api/submit_resume.php", {
                method: "POST",
                body: data
            });

            const text = await response.text();

            let json;

            try {

                json = JSON.parse(text);

            } catch {

                showError("Error servidor:<br>" + text);
                btn.disabled = false;
                btn.innerText = "Guardar Hoja de Vida";
                return;

            }

            if (json.success) {

                alert("Hoja de vida registrada");

                window.location = "dashboard.php";

            } else {

                showError("Error: " + json.error);

            }

        } catch (err) {

            showError("Error conexión: " + err);

        }

        btn.disabled = false;
        btn.innerText = "Guardar Hoja de Vida";

    });

});