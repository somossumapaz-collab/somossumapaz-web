import pandas as pd
import zipfile
import os
import shutil

def create_sample_zip():
    temp_dir = 'temp_test_zip'
    if os.path.exists(temp_dir):
        shutil.rmtree(temp_dir)
    os.makedirs(temp_dir)

    # 1. Datos Sheet
    datos_data = [
        ['Nombre', 'Juan Perez'],
        ['Documento', '12345678'],
        ['Tipo Documento', 'CC'],
        ['Fecha Nacimiento', '1990-01-01'],
        ['Departamento Nacimien', 'Cundinamarca'],
        ['Municipio Nacimien', 'Bogotá'],
        ['Telefono', '3101234567'],
        ['Email', 'juan.perez@example.com'],
        ['Vereda', 'Vereda Central']
    ]
    df_datos = pd.DataFrame(datos_data)

    # 2. Educacion Sheet
    edu_data = [
        ['Título', 'Bachiller', 'Institución', 'Colegio Nacional', 'Fecha Inicio', '2000-01-01', 'Fecha Fin', '2005-12-31', 'ID', '1'],
        ['Título', 'Ingeniero', 'Institución', 'Universidad X', 'Fecha Inicio', '2006-01-01', 'Fecha Fin', '2011-12-31', 'ID', '2']
    ]
    df_edu = pd.DataFrame(edu_data[1:], columns=edu_data[0])

    # 3. Experiencia Sheet
    exp_data = [
        ['Cargo', 'Desarrollador', 'Empresa', 'Tech Corp', 'Fecha Inicio', '2012-01-01', 'Fecha Fin', '2015-12-31', 'Descripción', 'Desarrollo web', 'ID', '1'],
        ['Cargo', 'Lider Tecnico', 'Empresa', 'Soft Solutions', 'Fecha Inicio', '2016-01-01', 'Fecha Fin', '2020-12-31', 'Descripción', 'Liderazgo de equipo', 'ID', '2']
    ]
    df_exp = pd.DataFrame(exp_data[1:], columns=exp_data[0])

    # 4. Referencias Sheet
    ref_data = [
        ['Nombre', 'Maria Lopez', 'Teléfono', '3209876543', 'Ocupación', 'Gerente'],
        ['Nombre', 'Carlos Ruiz', 'Teléfono', '3151234567', 'Ocupación', 'Director']
    ]
    df_ref = pd.DataFrame(ref_data[1:], columns=ref_data[0])

    # Save to Excel
    excel_path = os.path.join(temp_dir, 'datos.xlsx')
    with pd.ExcelWriter(excel_path) as writer:
        df_datos.to_excel(writer, sheet_name='Datos', index=False, header=False)
        df_edu.to_excel(writer, sheet_name='Educacion', index=False)
        df_exp.to_excel(writer, sheet_name='Experiencia', index=False)
        df_ref.to_excel(writer, sheet_name='Referencias', index=False)

    # Create dummy files
    open(os.path.join(temp_dir, 'foto.jpg'), 'a').close()
    open(os.path.join(temp_dir, 'cedula.pdf'), 'a').close()
    open(os.path.join(temp_dir, 'educacion_1.pdf'), 'a').close()
    open(os.path.join(temp_dir, 'experiencia_1.pdf'), 'a').close()

    # Create ZIP
    zip_path = 'sample_resumes.zip'
    with zipfile.ZipFile(zip_path, 'w') as zipf:
        for root, dirs, files in os.walk(temp_dir):
            for file in files:
                zipf.write(os.path.join(root, file), file)

    shutil.rmtree(temp_dir)
    print(f"ZIP created: {zip_path}")

if __name__ == "__main__":
    create_sample_zip()
