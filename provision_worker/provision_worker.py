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

def ssh_exec(host, ssh_user, ssh_pass, command):
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(
        hostname=host,
        username=ssh_user,
        password=ssh_pass,
        timeout=10,
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

# ==========================================================
# EXISTING LOGIC (NEW USER PROVISIONING) - UNTOUCHED
# ==========================================================
def provision_new_user(target, session):
    host = target["host_ip"]
    ssh_user = target["ssh_user"]
    ssh_pass = target["ssh_password"]

    username = session["username"]
    duration = int(
        (session["access_expiry"] - session["access_start"]).total_seconds() / 60
    )

    safe_user = shlex.quote(username)
    cmd = f"sudo /opt/lab/create_lab_user.sh {safe_user} {duration}"

    return ssh_exec(host, ssh_user, ssh_pass, cmd)

# ==========================================================
# NEW LOGIC (EXTENSION APPROVED USERS)
# ==========================================================
def provision_extension(target, session):
    host = target["host_ip"]
    ssh_user = target["ssh_user"]
    ssh_pass = target["ssh_password"]

    username = session["username"]

    # remaining / extended minutes
    now = datetime.utcnow()
    expiry = session["access_expiry"]
    minutes = int((expiry - now).total_seconds() / 60)
    if minutes < 1:
        minutes = 1

    safe_user = shlex.quote(username)

    # SAME script – now it also unlocks + resets password + issues token
    cmd = f"sudo /opt/lab/issue_k8s_token.sh {safe_user} {minutes}"

    return ssh_exec(host, ssh_user, ssh_pass, cmd)

# ==========================================================
# MAIN LOOP
# ==========================================================
while True:
    try:
        cur = db.cursor(dictionary=True)

        # 1️⃣ Provision target
        cur.execute("SELECT * FROM provision_target WHERE id=1")
        target = cur.fetchone()
        if not target:
            cur.close()
            time.sleep(POLL_INTERVAL)
            continue

        # ==================================================
        # CASE 1: NEW USER (REQUESTED)
        # ==================================================
        cur.execute("""
            SELECT *
            FROM lab_sessions
            WHERE status='REQUESTED'
            ORDER BY id
            LIMIT 1
            FOR UPDATE
        """)
        session = cur.fetchone()

        if session:
            session_id = session["id"]
            try:
                provision_new_user(target, session)
                cur.execute("""
                    UPDATE lab_sessions
                    SET status='ACTIVE'
                    WHERE id=%s
                """, (session_id,))
            except Exception as e:
                cur.execute("""
                    UPDATE lab_sessions
                    SET status='FAILED'
                    WHERE id=%s
                """, (session_id,))
                print(f"[ERROR] Provision failed for {session['username']}: {e}")

            cur.close()
            time.sleep(POLL_INTERVAL)
            continue

        # ==================================================
        # CASE 2: EXTENSION APPROVED (EXPIRED → ACTIVE)
        # ==================================================
        cur.execute("""
            SELECT *
            FROM lab_sessions
            WHERE status='ACTIVE'
              AND plan='PAID'
              AND provisioned IS NULL
            ORDER BY id
            LIMIT 1
            FOR UPDATE
        """)
        session = cur.fetchone()

        if session:
            session_id = session["id"]
            try:
                provision_extension(target, session)
                cur.execute("""
                    UPDATE lab_sessions
                    SET provisioned=1
                    WHERE id=%s
                """, (session_id,))
            except Exception as e:
                print(f"[ERROR] Extension provision failed for {session['username']}: {e}")

            cur.close()
            time.sleep(POLL_INTERVAL)
            continue

        cur.close()

    except Exception as main_err:
        print("[FATAL] Worker error:", main_err)

    time.sleep(POLL_INTERVAL)

