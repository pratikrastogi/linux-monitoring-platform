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
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(
        hostname=host,
        username=ssh_user,
        password=ssh_pass,
        timeout=15,
        allow_agent=False,
        look_for_keys=False
    )
    stdin, stdout, stderr = ssh.exec_command(command)
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    ssh.close()
    if err:
        raise RuntimeError(err)
    return out


# ----------------------------------------------------------
# NEW LAB PROVISIONING (REQUESTED → ACTIVE)
# ----------------------------------------------------------
def provision_new_lab(session):
    host = session["ip_address"]
    ssh_user = session["ssh_user"]
    ssh_pass = session["ssh_password"]

    namespace = session["namespace"]
    script = session["provision_script_path"]

    if not script:
        raise RuntimeError("Provision script path missing")

    safe_ns = shlex.quote(namespace)
    cmd = f"sudo {script} {safe_ns}"

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
                print(f"[INFO] Provisioning lab session {session_id}")
                provision_new_lab(session)

                cur.execute("""
                    UPDATE lab_sessions
                    SET status='ACTIVE', provisioned=1
                    WHERE id=%s
                """, (session_id,))

                print(f"[SUCCESS] Lab session {session_id} is ACTIVE")

            except Exception as e:
                cur.execute("""
                    UPDATE lab_sessions
                    SET status='FAILED'
                    WHERE id=%s
                """, (session_id,))

                print(f"[ERROR] Provision failed for session {session_id}: {e}")

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

