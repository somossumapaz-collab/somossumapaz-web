from flask import Flask, render_template, request, redirect, url_for, flash, jsonify, session
import database

import os
import json
from werkzeug.utils import secure_filename

app = Flask(__name__)
app.secret_key = 'somos_sumapaz_secret_key' # Change this in production
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024 # 16MB max limit

ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'pdf'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

# Initialize database
with app.app_context():
    database.init_db()
    database.update_schema() # Ensure schema is up to date

@app.route('/')
def index():
    return render_template('login.html')

@app.route('/login', methods=['POST'])
def login():
    username = request.form.get('username')
    password = request.form.get('password')
    remember = request.form.get('remember')
    
    user = database.verify_user(username, password)
    
    if user:
        if remember:
            session.permanent = True
        else:
            session.permanent = False
            
        # Store user in session
        session['user_id'] = user['id']
        session['username'] = user['username']
        
        return redirect(url_for('dashboard'))
    else:
        flash('Usuario o contraseña incorrectos')
        return redirect(url_for('index'))

@app.route('/register', methods=['POST'])
def register():
    username = request.form.get('username')
    password = request.form.get('password')
    
    if database.create_user(username, password):
        flash('Usuario creado exitosamente. Por favor ingresa.')
    else:
        flash('El nombre de usuario ya existe.')
    
    return redirect(url_for('index'))

@app.route('/google_login')
def google_login():
    # Mock Google Login
    session['user_id'] = 999
    session['username'] = 'Google User'
    session.permanent = True
    flash('Ingreso con Google exitoso (Simulado)')
    return redirect(url_for('dashboard'))

@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('index'))

@app.route('/dashboard')
def dashboard():
    if 'user_id' not in session:
        return redirect(url_for('index'))
    return render_template('dashboard.html', username=session.get('username'))

@app.route('/resume-form')
def resume_form():
    return render_template('resume_form.html')

@app.route('/api/submit_resume', methods=['POST'])
def submit_resume():
    try:
        # Extract main fields
        data = {
            'full_name': request.form.get('full_name'),
            'document_id': request.form.get('document_id'),
            'id_type': request.form.get('id_type'),
            'email': request.form.get('email'),
            'phone': request.form.get('phone'),
            'birth_date': request.form.get('birth_date'),
            'birth_department': request.form.get('birth_department'),
            'birth_city': request.form.get('birth_city'),
            'department': request.form.get('department'),
            'city': request.form.get('city'),
            'profile_description': request.form.get('profile_description'),
            'niche': request.form.get('niche'),
            'skills': request.form.get('skills'), # Comma separated
            'personal_references_json': request.form.get('personal_references_json'),
            'family_references_json': request.form.get('family_references_json')
        }

        # Handle Files
        def save_file(file_key, prefix=''):
            file = request.files.get(file_key)
            if file and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                filename = f"{prefix}_{int(time.time())}_{filename}"
                file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
                return filename
            return None

        data['photo'] = save_file('photo', 'photo')
        data['id_file_path'] = save_file('id_file', 'id')
        
        # Education List
        education_list = []
        edu_count = 0
        while True:
            if f'education_{edu_count}_institution' not in request.form:
                break
            
            edu_item = {
                'level': request.form.get(f'education_{edu_count}_level'),
                'institution': request.form.get(f'education_{edu_count}_institution'),
                'start_date': request.form.get(f'education_{edu_count}_start_date'),
                'end_date': request.form.get(f'education_{edu_count}_end_date'),
                'is_current': request.form.get(f'education_{edu_count}_is_current') == 'on'
            }
            edu_item['certificate_path'] = save_file(f'education_{edu_count}_file', 'edu')
            education_list.append(edu_item)
            edu_count += 1
            
        # Experience List
        experience_list = []
        exp_count = 0
        while True:
            if f'experience_{exp_count}_company' not in request.form:
                break
                
            exp_item = {
                'role': request.form.get(f'experience_{exp_count}_role'),
                'company': request.form.get(f'experience_{exp_count}_company'),
                'start_date': request.form.get(f'experience_{exp_count}_start_date'),
                'end_date': request.form.get(f'experience_{exp_count}_end_date'),
                'is_current': request.form.get(f'experience_{exp_count}_is_current') == 'on'
            }
            exp_item['certificate_path'] = save_file(f'experience_{exp_count}_file', 'exp')
            experience_list.append(exp_item)
            exp_count += 1

        resume_id = database.add_resume_comprehensive(data, education_list, experience_list)
        return jsonify({'status': 'success', 'message': 'Hoja de vida guardada exitosamente', 'resume_id': resume_id})
    except Exception as e:
        print(e)
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route('/api/resumes')
def get_resumes():
    resumes = database.get_all_resumes()
    return jsonify(resumes)

@app.route('/api/download_resume/<int:resume_id>')
def download_resume(resume_id):
    import io
    import zipfile
    
    # Get resume data
    resume = None
    all_resumes = database.get_all_resumes()
    for r in all_resumes:
        if r['id'] == resume_id:
            resume = r
            break
    
    if not resume:
        return "Resumen no encontrado", 404

    # Create Zip in memory
    memory_file = io.BytesIO()
    with zipfile.ZipFile(memory_file, 'w') as zf:
        # 1. Add Info Text File
        info_text = f"""
        Nombre: {resume['full_name']}
        Documento: {resume['document_id']}
        Email: {resume['email']}
        Teléfono: {resume['phone']}
        Ciudad: {resume['city']}
        Departamento: {resume['department']}
        Perfil: {resume['profile_description']}
        
        Habilidades:
        {resume['skills']}
        
        Experiencia:
        {resume['experience']}
        """
        zf.writestr(f"{resume['full_name']}_info.txt", info_text)
        
        # 2. Add Files if they exist
        upload_folder = app.config['UPLOAD_FOLDER']
        
        files_to_add = [
            ('Foto_Perfil', resume.get('photo_path')),
            ('Documento_Identidad', resume.get('id_file_path') or resume.get('document_path')),
            ('Diploma_Respaldo', resume.get('diploma_path'))
        ]
        
        # Add Education Certificates
        for i, edu in enumerate(resume.get('education', [])):
            if edu.get('certificate_path'):
                 files_to_add.append((f"Certificado_Estudio_{i+1}", edu['certificate_path']))
                 
        # Add Experience Certificates
        for i, exp in enumerate(resume.get('experience', [])):
            if exp.get('certificate_path'):
                 files_to_add.append((f"Certificado_Laboral_{i+1}", exp['certificate_path']))
        
        for label, filename in files_to_add:
            if filename:
                file_path = os.path.join(upload_folder, filename)
                if os.path.exists(file_path):
                    # Add file to zip
                    # Extract extension
                    ext = filename.rsplit('.', 1)[1] if '.' in filename else ''
                    safe_label = "".join([c for c in label if c.isalnum() or c=='_'])
                    arcname = f"{safe_label}_{resume['full_name'].replace(' ', '_')}.{ext}" if ext else f"{safe_label}_{resume['full_name']}"
                    zf.write(file_path, arcname)

    memory_file.seek(0)
    
    from flask import send_file
    return send_file(
        memory_file,
        mimetype='application/zip',
        as_attachment=True,
        download_name=f"HojaDeVida_{resume['full_name']}.zip"
    )

@app.route('/api/download_database')
def download_database():
    import csv
    import io
    
    # Get all resumes
    resumes = database.get_all_resumes()
    
    if not resumes:
        return "No hay datos para exportar", 404
        
    # Create CSV in memory
    proxy = io.StringIO()
    writer = csv.writer(proxy)
    
    # Write Header
    # Get keys from first dictionary for header
    header = list(resumes[0].keys())
    writer.writerow(header)
    
    # Write Data
    for resume in resumes:
        writer.writerow([resume.get(col, '') for col in header])
        
    # Create bytes buffer for download
    mem = io.BytesIO()
    mem.write(proxy.getvalue().encode('utf-8-sig')) # utf-8-sig for Excel compatibility
    mem.seek(0)
    proxy.close()
    
    from flask import send_file
    return send_file(
        mem,
        mimetype='text/csv',
        as_attachment=True,
        download_name='base_de_datos_empleo.csv'
    )

@app.route('/resume/<int:resume_id>/preview')
def resume_preview(resume_id):
    resume = None
    all_resumes = database.get_all_resumes()
    for r in all_resumes:
        if r['id'] == resume_id:
            resume = r
            break
            
    if not resume:
        return "Resumen no encontrado", 404
        
    # Parse JSON references
    try:
        personal_refs = json.loads(resume['personal_references_json']) if resume['personal_references_json'] else []
        family_refs = json.loads(resume['family_references_json']) if resume['family_references_json'] else []
    except:
        personal_refs = []
        family_refs = []

    return render_template('resume_preview.html', resume=resume, personal_refs=personal_refs, family_refs=family_refs)

@app.route('/api/download_pdf/<int:resume_id>')
def download_pdf(resume_id):
    from xhtml2pdf import pisa
    import io
    
    # 1. Get Resume Logic (Duplicate of preview, could be refactored)
    resume = None
    all_resumes = database.get_all_resumes()
    for r in all_resumes:
        if r['id'] == resume_id:
            resume = r
            break
            
    if not resume:
        return "Resumen no encontrado", 404

    # Parse JSON references
    try:
        personal_refs = json.loads(resume['personal_references_json']) if resume['personal_references_json'] else []
        family_refs = json.loads(resume['family_references_json']) if resume['family_references_json'] else []
    except:
        personal_refs = []
        family_refs = []
        
    # 2. Render HTML
    html_content = render_template('resume_preview.html', resume=resume, personal_refs=personal_refs, family_refs=family_refs)
    
    # 3. Create PDF
    pdf_buffer = io.BytesIO()
    pisa_status = pisa.CreatePDF(
       io.StringIO(html_content),
       dest=pdf_buffer
    )
    
    if pisa_status.err:
       return f"Error generando PDF: {pisa_status.err}", 500
       
    pdf_buffer.seek(0)
    
    from flask import send_file
    return send_file(
        pdf_buffer,
        mimetype='application/pdf',
        as_attachment=True,
        download_name=f"HojaDeVida_{resume['full_name'].replace(' ', '_')}.pdf"
    )

if __name__ == '__main__':
    app.run(debug=True)
