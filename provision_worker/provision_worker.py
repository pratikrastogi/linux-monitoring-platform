import time
import mysql.connector
import paramiko
import shlex
from datetime import datetime

POLL_INTERVAL = 5

db = mysql.connector.connect(
    host="mysql",
    user="monitor",
    password="monitor123",
    database="monitoring",
    autocommit=True
)

# ----------------------------------------------------------
# SSH EXECUTION HELPER
# ----------------------------------------------------------
def ssh_exec(host, ssh_user, ssh_pass, command):
    print(f"[DEBUG] SSH connecting to {host} as {ssh_user}")
    print(f"[DEBUG] Command: {command}")
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        ssh.connect(
            hostname=host,
            username=ssh_user,
            password=ssh_pass,
            timeout=15,
            allow_agent=False,
            look_for_keys=False
        )
        print(f"[DEBUG] SSH connected successfully")
    except Exception as e:
        raise RuntimeError(f"SSH connection failed: {e}")
    
    try:
        stdin, stdout, stderr = ssh.exec_command(command)
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()
        exit_code = stdout.channel.recv_exit_status()
        
        print(f"[DEBUG] Exit code: {exit_code}")
        if out:
            print(f"[DEBUG] STDOUT: {out[:500]}")
        if err:
            print(f"[DEBUG] STDERR: {err[:500]}")
        
        ssh.close()
        
        if exit_code != 0:
            raise RuntimeError(f"Script failed with exit code {exit_code}: {err}")
        
        return out
    except Exception as e:
        ssh.close()
        raise RuntimeError(f"SSH execution failed: {e}")


# ----------------------------------------------------------
# NEW LAB PROVISIONING (REQUESTED → ACTIVE)
# ----------------------------------------------------------
def provision_new_lab(session):
    host = session["ip_address"]
    ssh_user = session["ssh_user"]
    ssh_pass = session["ssh_password"]

    namespace = session["namespace"]
    username = session["username"]
    user_id = session["user_id"]
    script = session["provision_script_path"]

    if not script:
        raise RuntimeError("Provision script path missing")
    
    print(f"[DEBUG] Session details: id={session.get('id')}, user={username}, user_id={user_id}, namespace={namespace}")
    print(f"[DEBUG] Server: {host}, script={script}")

    # Calculate duration in minutes
    # Handle both string timestamps and datetime objects
    now = datetime.utcnow()
    expiry = session["access_expiry"]
    
    if isinstance(expiry, str):
        try:
            expiry_dt = datetime.fromisoformat(expiry.replace('Z', '+00:00'))
        except:
            expiry_dt = datetime.strptime(expiry, "%Y-%m-%d %H:%M:%S")
    else:
        expiry_dt = expiry
    
    duration_minutes = int((expiry_dt - now).total_seconds() / 60)
    if duration_minutes < 1:
        duration_minutes = 1
    
    print(f"[DEBUG] Now: {now}, Expiry: {expiry_dt}, Duration: {duration_minutes} minutes")

    # Generate timestamp for unique username and namespace
    timestamp = int(now.timestamp())
    
    # Format: user-{user_id}-{timestamp} and lab-user-{user_id}-{timestamp}
    formatted_username = f"user-{user_id}-{timestamp}"
    safe_user = shlex.quote(formatted_username)
    safe_user_id = shlex.quote(str(user_id))

    # Pass formatted username, user_id, duration, and timestamp to the provision script
    cmd = f"sudo {script} {safe_user} {duration_minutes} {safe_user_id} {timestamp}"

    return ssh_exec(host, ssh_user, ssh_pass, cmd)


# ----------------------------------------------------------
# EXTENSION HANDLING (PAID EXTENSIONS – FUTURE USE)
# ----------------------------------------------------------
def provision_extension(session):
    host = session["ip_address"]
    ssh_user = session["ssh_user"]
    ssh_pass = session["ssh_password"]

    namespace = session["namespace"]

    now = datetime.utcnow()
    expiry = session["access_expiry"]
    minutes = int((expiry - now).total_seconds() / 60)
    if minutes < 1:
        minutes = 1

    safe_ns = shlex.quote(namespace)

    # example extension script (optional future use)
    cmd = f"sudo /opt/lab/extend_lab.sh {safe_ns} {minutes}"

    return ssh_exec(host, ssh_user, ssh_pass, cmd)


# ----------------------------------------------------------
# MAIN WORKER LOOP
# ----------------------------------------------------------
while True:
    try:
        cur = db.cursor(dictionary=True)

        # ==================================================
        # CASE 1: NEW LAB SESSION (REQUESTED)
        # ==================================================
        cur.execute("""
            SELECT 
                ls.*,
                l.provision_script_path,
                s.ip_address,
                s.ssh_user,
                s.ssh_password,
                s.ssh_port
            FROM lab_sessions ls
            JOIN labs l ON ls.lab_id = l.id
            JOIN servers s ON l.server_id = s.id
            WHERE ls.status = 'REQUESTED'
            ORDER BY ls.id
            LIMIT 1
            FOR UPDATE
        """)
        session = cur.fetchone()

        if session:
            session_id = session["id"]
            try:
                print(f"\n[INFO] ========== Provisioning lab session {session_id} ==========")
                print(f"[INFO] Lab ID: {session.get('lab_id')}, User ID: {session.get('user_id')}")
                provision_new_lab(session)

                cur.execute("""
                    UPDATE lab_sessions
                    SET status='ACTIVE', provisioned=1
                    WHERE id=%s
                """, (session_id,))

                print(f"[SUCCESS] Lab session {session_id} is now ACTIVE\n")

            except Exception as e:
                print(f"[ERROR] Provision failed for session {session_id}: {type(e).__name__}: {e}\n")
                cur.execute("""
                    UPDATE lab_sessions
                    SET status='FAILED'
                    WHERE id=%s
                """, (session_id,))

            cur.close()
            time.sleep(POLL_INTERVAL)
            continue

        # ==================================================
        # CASE 2: EXTENSION (OPTIONAL / FUTURE)
        # ==================================================
        cur.execute("""
            SELECT 
                ls.*,
                s.ip_address,
                s.ssh_user,
                s.ssh_password
            FROM lab_sessions ls
            JOIN labs l ON ls.lab_id = l.id
            JOIN servers s ON l.server_id = s.id
            WHERE ls.status='ACTIVE'
              AND ls.plan='PAID'
              AND ls.provisioned = 0
            ORDER BY ls.id
            LIMIT 1
            FOR UPDATE
        """)
        session = cur.fetchone()

        if session:
            session_id = session["id"]
            try:
                print(f"[INFO] Processing extension for session {session_id}")
                provision_extension(session)

                cur.execute("""
                    UPDATE lab_sessions
                    SET provisioned=1
                    WHERE id=%s
                """, (session_id,))

                print(f"[SUCCESS] Extension applied for session {session_id}")

            except Exception as e:
                print(f"[ERROR] Extension failed for session {session_id}: {e}")

            cur.close()
            time.sleep(POLL_INTERVAL)
            continue

        cur.close()

    except Exception as fatal:
        print("[FATAL] Worker crashed:", fatal)

    time.sleep(POLL_INTERVAL)
