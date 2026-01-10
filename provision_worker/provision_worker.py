import time
import subprocess
import mysql.connector
import paramiko

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

        # Get provisioning target
        cur.execute("SELECT * FROM provision_target WHERE id=1")
        target = cur.fetchone()

        host = target["host_ip"]
        ssh_user = target["ssh_user"]
        ssh_pass = target["ssh_password"]

        # Get pending job
        cur.execute("""
            SELECT * FROM provisioning_queue
            WHERE status='PENDING'
            LIMIT 1
        """)
        job = cur.fetchone()

        if not job:
            time.sleep(5)
            continue

        job_id = job["id"]
        username = job["username"]
        duration = job["requested_duration"]

        cur.execute(
            "UPDATE provisioning_queue SET status='RUNNING' WHERE id=%s",
            (job_id,)
        )

        # SSH into provision node
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

        cmd = f"/opt/lab/create_lab_user.sh {username} {duration}"
        stdin, stdout, stderr = ssh.exec_command(cmd)

        out = stdout.read().decode()
        err = stderr.read().decode()

        ssh.close()

        if out.startswith("SUCCESS"):
            cur.execute("""
                UPDATE provisioning_queue
                SET status='DONE', message=%s
                WHERE id=%s
            """, (out, job_id))
        else:
            cur.execute("""
                UPDATE provisioning_queue
                SET status='FAILED', message=%s
                WHERE id=%s
            """, (err or out, job_id))

        cur.close()

    except Exception as e:
        print("Provision worker error:", e)

    time.sleep(5)

