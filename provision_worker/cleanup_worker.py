import time
import subprocess
import mysql.connector
from datetime import datetime

SCRIPT_LOCAL = "/opt/lab/cleanup_lab_user.sh"
SCRIPT_REMOTE = "/opt/lab/cleanup_lab_user.sh"

db = mysql.connector.connect(
    host="mysql",
    user="monitor",
    password="monitor123",
    database="monitoring",
    autocommit=True
)

while True:
    try:
        cur = db.cursor(dictionary=True)

        # Fetch expired ACTIVE sessions
        cur.execute("""
            SELECT * FROM lab_sessions
            WHERE status='ACTIVE'
            AND access_expiry < NOW()
        """)
        sessions = cur.fetchall()

        if not sessions:
            time.sleep(120)
            continue

        # Fetch provisioning target
        cur.execute("SELECT * FROM provision_target WHERE id=1")
        target = cur.fetchone()

        if not target:
            time.sleep(120)
            continue

        host = target["host_ip"]
        ssh_user = target["ssh_user"]
        ssh_pass = target["ssh_password"]

        for s in sessions:
            sid = s["id"]
            username = s["username"]

            try:
                # Copy cleanup script
                scp_cmd = f"""
sshpass -p '{ssh_pass}' scp -o StrictHostKeyChecking=no \
{SCRIPT_LOCAL} {ssh_user}@{host}:{SCRIPT_REMOTE}
"""
                subprocess.run(scp_cmd, shell=True, check=True)

                # Execute cleanup remotely
                exec_cmd = f"""
sshpass -p '{ssh_pass}' ssh -o StrictHostKeyChecking=no \
{ssh_user}@{host} \
'bash {SCRIPT_REMOTE} {username}'
"""
                subprocess.run(exec_cmd, shell=True, check=True)

                # Mark session expired
                cur.execute("""
                    UPDATE lab_sessions
                    SET status='EXPIRED'
                    WHERE id=%s
                """, (sid,))

            except Exception as e:
                print(f"Cleanup failed for {username}: {e}")

        cur.close()

    except Exception as e:
        print("Cleanup worker error:", e)

    time.sleep(120)

