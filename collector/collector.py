import time
import socket
import paramiko
import mysql.connector

#modified the collector for CI/CD Testing

# ==============================
# DB CONNECTION
# ==============================
db = mysql.connector.connect(
    host="mysql",
    user="monitor",
    password="monitor123",
    database="monitoring",
    autocommit=True
)

# ==============================
# CHECK HOST REACHABILITY
# ==============================
def is_reachable(ip, port=22, timeout=3):
    try:
        socket.create_connection((ip, port), timeout=timeout)
        return True
    except Exception:
        return False


# ==============================
# MAIN LOOP
# ==============================
while True:
    try:
        cur = db.cursor(dictionary=True)

        # ONLY ENABLED SERVERS
        cur.execute("SELECT * FROM servers WHERE enabled = 1")
        servers = cur.fetchall()

        for s in servers:
            server_id = s["id"]
            ip = s["ip_address"]
            user = s["ssh_user"]
            password = s["ssh_password"]  # 🔑 DB se password

            reachable = is_reachable(ip)
            sshd_status = "down"
            os_v = virt = uptime = "NA"
            cpu = mem = disk = 0.0

            if reachable:
                try:
                    ssh = paramiko.SSHClient()
                    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

                    ssh.connect(
                        hostname=ip,
                        username=user,
                        password=password,
                        timeout=5,
                        allow_agent=False,
                        look_for_keys=False
                    )

                    def cmd(c):
                        _, stdout, _ = ssh.exec_command(c)
                        return stdout.read().decode().strip()

                    sshd_status = cmd("systemctl is-active sshd")
                    os_v = cmd(". /etc/os-release && echo $PRETTY_NAME")
                    virt = cmd("systemd-detect-virt || echo baremetal")
                    uptime = cmd("uptime -p")

                    try:
                        cpu = float(cmd("top -bn1 | awk '/Cpu/ {print $2}'"))
                        mem = float(cmd("free | awk '/Mem/ {printf \"%.2f\", $3/$2*100}'"))
                        disk = float(cmd("df / | awk 'NR==2 {print $5}' | tr -d '%'"))
                    except Exception:
                        cpu = mem = disk = 0.0

                    ssh.close()

                except Exception as e:
                    reachable = False
                    sshd_status = "down"

            # ==============================
            # INSERT METRICS
            # ==============================
            cur.execute(
                """
                INSERT INTO server_metrics
                (server_id, os_version, virtualization, uptime,
                 sshd_status, cpu_usage, mem_usage, disk_usage, reachable)
                VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)
                """,
                (
                    server_id,
                    os_v,
                    virt,
                    uptime,
                    sshd_status,
                    cpu,
                    mem,
                    disk,
                    reachable
                )
            )

            # ==============================
            # ALERT LOGIC
            # ==============================
            if not reachable:
                cur.execute(
                    """
                    INSERT INTO alerts (server_id, alert_type, message)
                    VALUES (%s, 'HOST_DOWN', 'Server unreachable')
                    """,
                    (server_id,)
                )

            elif sshd_status != "active":
                cur.execute(
                    """
                    INSERT INTO alerts (server_id, alert_type, message)
                    VALUES (%s, 'SSHD_DOWN', 'SSHD service is down')
                    """,
                    (server_id,)
                )

        cur.close()

    except Exception as main_err:
        # collector kabhi crash nahi hona chahiye
        print("Collector error:", main_err)

    # ==============================
    # POLLING INTERVAL
    # ==============================
    time.sleep(10)

