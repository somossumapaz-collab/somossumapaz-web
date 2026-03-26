import mysql.connector
import bcrypt

def create_admin():
    try:
        conn = mysql.connector.connect(
            host="15.235.82.117",
            user="somossum_admin",
            password="Talento_suma",
            database="somossum_talento",
            port=3306
        )
        cursor = conn.cursor()
        
        # Hash the password with bcrypt (default in PHP's password_hash)
        password = b"admin2026*"
        hashed = bcrypt.hashpw(password, bcrypt.gensalt())
        
        sql = "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES (%s, %s, %s, %s, %s)"
        val = ("Admin", "sotocollazos99@gmail.com", hashed.decode('utf-8'), 1, 1) # Assuming rol_id 1 is admin
        
        cursor.execute(sql, val)
        conn.commit()
        
        print("Admin user created successfully!")
        
    except mysql.connector.Error as err:
        print(f"Error: {err}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

if __name__ == "__main__":
    create_admin()
