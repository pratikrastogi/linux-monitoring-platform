import time
import mysql.connector
import paramiko
import shlex

POLL_INTERVAL = 5

db = mysql.connector.connect(
    host="mysql",
    user="monitor",
    password="monitor123",
    database="monitoring",
    autocommit=True
)

def provision_user(target, session):
    host = target["host_ip"]
    ssh_user = target["ssh_user"]          # labprovision
    ssh_pass = target["ssh_password"]      # temporary (keys later)

    username = session["username"]
    duration = int(
        (session["access_expiry"] - session["access_start"]).total_seconds() / 60
    )

    safe_user = shlex.quote(username)

    cmd = f"sudo /opt/lab/create_lab_user.sh {safe_user} {duration}"

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

    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()

    ssh.close()

    if err:
        raise RuntimeError(err)

    return out


while True:
    try:
        cur = db.cursor(dictionary=True)

        # 1️⃣ Fetch provisioning target
        cur.execute("SELECT * FROM provision_target WHERE id=1")
        target = cur.fetchone()
        if not target:
            time.sleep(POLL_INTERVAL)
            continue

        # 2️⃣ Fetch next REQUESTED lab
        cur.execute("""
            SELECT *
            FROM lab_sessions
            WHERE status='REQUESTED'
            ORDER BY id
            LIMIT 1
            FOR UPDATE
        """)
        session = cur.fetchone()

        if not session:
            time.sleep(POLL_INTERVAL)
            continue

        session_id = session["id"]

        # 3️⃣ Mark RUNNING (lock)
        pass
        try:
            provision_user(target, session)

            # 4️⃣ Success
            cur.execute("""
                UPDATE lab_sessions
                SET status='ACTIVE'
                WHERE id=%s
            """, (session_id,))

        except Exception as e:
            # 5️⃣ Failure
            cur.execute("""
                UPDATE lab_sessions
                SET status='FAILED'
                WHERE id=%s
            """, (session_id,))
            print(f"[ERROR] Provision failed for {session['username']}: {e}")

        cur.close()

    except Exception as main_err:
        print("[FATAL] Worker error:", main_err)

    time.sleep(POLL_INTERVAL)

