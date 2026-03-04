import sqlite3
from flask import g

DATABASE = 'sumapaz.db'

def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        db = g._database = sqlite3.connect(DATABASE)
        db.row_factory = sqlite3.Row
    return db

def close_db(e=None):
    db = getattr(g, '_database', None)
    if db is not None:
        db.close()

def init_db():
    with sqlite3.connect(DATABASE) as conn:
        cursor = conn.cursor()
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS resumes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                document_id TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT NOT NULL,
                skills TEXT,
                experience TEXT, -- Legacy text blob
                
                -- New Fields
                birth_date TEXT,
                city TEXT,
                department TEXT,
                profile_description TEXT,
                photo_path TEXT,
                document_path TEXT,
                diploma_path TEXT,
                
                -- Additional Fields
                id_type TEXT,
                niche TEXT,
                birth_department TEXT,
                birth_city TEXT,
                id_file_path TEXT,
                personal_references_json TEXT,
                family_references_json TEXT
            )
        ''')
        
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS education (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                resume_id INTEGER,
                level TEXT,
                institution TEXT,
                start_date TEXT,
                end_date TEXT,
                is_current BOOLEAN,
                certificate_path TEXT,
                FOREIGN KEY(resume_id) REFERENCES resumes(id)
            )
        ''')

        cursor.execute('''
            CREATE TABLE IF NOT EXISTS experience (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                resume_id INTEGER,
                role TEXT,
                company TEXT,
                start_date TEXT,
                end_date TEXT,
                is_current BOOLEAN,
                certificate_path TEXT,
                FOREIGN KEY(resume_id) REFERENCES resumes(id)
            )
        ''')
        
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT,
                google_id TEXT
            )
        ''')
        
        cursor.execute("INSERT OR IGNORE INTO users (username, password) VALUES ('admin', 'admin')")
        
        conn.commit()

def create_user(username, password):
    try:
        with sqlite3.connect(DATABASE) as conn:
            cursor = conn.cursor()
            cursor.execute("INSERT INTO users (username, password) VALUES (?, ?)", (username, password))
            conn.commit()
            return True
    except sqlite3.IntegrityError:
        return False

def verify_user(username, password):
    with sqlite3.connect(DATABASE) as conn:
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM users WHERE username = ?", (username,))
        user = cursor.fetchone()
        if user and user['password'] == password:
            return user
        return None

def update_schema():
    """Adds new columns if they don't exist (migrations)"""
    with sqlite3.connect(DATABASE) as conn:
        cursor = conn.cursor()
        try:
            # Check/Add Resume columns
            cols = [
                ('birth_date', 'TEXT'), ('city', 'TEXT'), ('department', 'TEXT'), 
                ('profile_description', 'TEXT'), ('photo_path', 'TEXT'), 
                ('document_path', 'TEXT'), ('diploma_path', 'TEXT'),
                ('id_type', 'TEXT'), ('niche', 'TEXT'), 
                ('birth_department', 'TEXT'), ('birth_city', 'TEXT'),
                ('id_file_path', 'TEXT'), ('personal_references_json', 'TEXT'),
                ('family_references_json', 'TEXT')
            ]
            for col, type_ in cols:
                try:
                    cursor.execute(f"ALTER TABLE resumes ADD COLUMN {col} {type_}")
                except sqlite3.OperationalError:
                    pass

            # Create child tables if they don't exist
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS education (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    resume_id INTEGER,
                    level TEXT,
                    institution TEXT,
                    start_date TEXT,
                    end_date TEXT,
                    is_current BOOLEAN,
                    certificate_path TEXT,
                    FOREIGN KEY(resume_id) REFERENCES resumes(id)
                )
            ''')

            cursor.execute('''
                CREATE TABLE IF NOT EXISTS experience (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    resume_id INTEGER,
                    role TEXT,
                    company TEXT,
                    start_date TEXT,
                    end_date TEXT,
                    is_current BOOLEAN,
                    certificate_path TEXT,
                    FOREIGN KEY(resume_id) REFERENCES resumes(id)
                )
            ''')
            
        except sqlite3.OperationalError:
            pass 
        
        # Ensure users table exists
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT,
                google_id TEXT
            )
        ''')

def add_resume_comprehensive(data, education_list=[], experience_list=[]):
    with sqlite3.connect(DATABASE) as conn:
        cursor = conn.cursor()
        
        # Insert Main Resume
        cursor.execute('''
            INSERT INTO resumes (
                full_name, document_id, email, phone, skills, experience,
                birth_date, city, department, profile_description,
                photo_path, document_path, diploma_path,
                id_type, niche, birth_department, birth_city, id_file_path,
                personal_references_json, family_references_json
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ''', (
            data.get('full_name'), 
            data.get('document_id'), 
            data.get('email'), 
            data.get('phone'), 
            data.get('skills', ''), 
            data.get('experience_summary', ''), # Generic text summary
            data.get('birth_date'),
            data.get('city'),
            data.get('department'),
            data.get('profile_description'),
            data.get('photo'),
            data.get('document_file'), # Keeping as backup or remove?
            data.get('diploma_file'), # Keeping as backup
            data.get('id_type'),
            data.get('niche'),
            data.get('birth_department'),
            data.get('birth_city'),
            data.get('id_file_path'),
            data.get('personal_references_json', '[]'),
            data.get('family_references_json', '[]')
        ))
        
        resume_id = cursor.lastrowid
        
        # Insert Education
        for edu in education_list:
            cursor.execute('''
                INSERT INTO education (resume_id, level, institution, start_date, end_date, is_current, certificate_path)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ''', (
                resume_id,
                edu.get('level'),
                edu.get('institution'),
                edu.get('start_date'),
                edu.get('end_date'),
                edu.get('is_current'),
                edu.get('certificate_path')
            ))
            
        # Insert Experience
        for exp in experience_list:
            cursor.execute('''
                INSERT INTO experience (resume_id, role, company, start_date, end_date, is_current, certificate_path)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ''', (
                resume_id,
                exp.get('role'),
                exp.get('company'),
                exp.get('start_date'),
                exp.get('end_date'),
                exp.get('is_current'),
                exp.get('certificate_path')
            ))
            
        conn.commit()
        return resume_id

def get_all_resumes():
    with sqlite3.connect(DATABASE) as conn:
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM resumes ORDER BY id DESC')
        resumes = [dict(row) for row in cursor.fetchall()]
        
        # Attach education and experience
        for resume in resumes:
            cursor.execute('SELECT * FROM education WHERE resume_id = ?', (resume['id'],))
            resume['education'] = [dict(r) for r in cursor.fetchall()]
            
            cursor.execute('SELECT * FROM experience WHERE resume_id = ?', (resume['id'],))
            resume['experience'] = [dict(r) for r in cursor.fetchall()]
            
        return resumes
