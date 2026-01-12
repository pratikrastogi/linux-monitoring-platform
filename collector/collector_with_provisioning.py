import time
import socket
import paramiko
import mysql.connector

#modified the collector for CI/CD Testing + Auto-Provisioning

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
# AUTO-PROVISIONING LOGIC
# ==============================
def provision_labs():
    """
    Auto-provision labs with status='REQUESTED'
    """
    try:
        cur = db.cursor(dictionary=True)
        
        # Get all REQUESTED lab sessions
        cur.execute("""
            SELECT 
                ls.id as session_id,
                ls.user_id,
                ls.username,
                ls.lab_id,
                ls.namespace,
                l.provision_script_path,
                l.server_id,
                l.bastion_host,
                l.bastion_user,
                l.bastion_password,
                s.ip_address,
                s.ssh_user,
                s.ssh_password
            FROM lab_sessions ls
            JOIN labs l ON ls.lab_id = l.id
            LEFT JOIN servers s ON l.server_id = s.id
            WHERE ls.status = 'REQUESTED' AND ls.provisioned = 0
            ORDER BY ls.created_at ASC
            LIMIT 10
        """)
        
        requested_labs = cur.fetchall()
        
        for lab in requested_labs:
            session_id = lab['session_id']
            username = lab['username']
            namespace = lab['namespace']
            provision_script = lab['provision_script_path']
            
            # Determine target host (bastion or direct server)
            target_ip = lab['bastion_host'] if lab['bastion_host'] else lab['ip_address']
            target_user = lab['bastion_user'] if lab['bastion_user'] else lab['ssh_user']
            target_password = lab['bastion_password'] if lab['bastion_password'] else lab['ssh_password']
            
            if not target_ip:
                print(f"⚠️ Session {session_id}: No server/bastion configured, marking FAILED")
                cur.execute("""
                    UPDATE lab_sessions 
                    SET status = 'FAILED' 
                    WHERE id = %s
                """, (session_id,))
                continue
            
            # Check if host is reachable
            if not is_reachable(target_ip):
                print(f"⚠️ Session {session_id}: Host {target_ip} unreachable, skipping")
                continue
            
            # Execute provisioning
            try:
                ssh = paramiko.SSHClient()
                ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
                
                ssh.connect(
                    hostname=target_ip,
                    username=target_user,
                    password=target_password,
                    timeout=10,
                    allow_agent=False,
                    look_for_keys=False
                )
                
                # Execute provisioning script
                provision_cmd = f"bash {provision_script} {username} {namespace}"
                print(f"🚀 Provisioning session {session_id}: {provision_cmd}")
                
                stdin, stdout, stderr = ssh.exec_command(provision_cmd, timeout=60)
                exit_code = stdout.channel.recv_exit_status()
                output = stdout.read().decode().strip()
                errors = stderr.read().decode().strip()
                
                ssh.close()
                
                if exit_code == 0:
                    # Provisioning successful
                    print(f"✅ Session {session_id} provisioned successfully")
                    cur.execute("""
                        UPDATE lab_sessions 
                        SET status = 'ACTIVE', provisioned = 1
                        WHERE id = %s
                    """, (session_id,))
                    
                    # Log success
                    print(f"   Output: {output[:200]}")
                else:
                    # Provisioning failed
                    print(f"❌ Session {session_id} provisioning failed (exit code: {exit_code})")
                    print(f"   Error: {errors[:200]}")
                    cur.execute("""
                        UPDATE lab_sessions 
                        SET status = 'FAILED'
                        WHERE id = %s
                    """, (session_id,))
                    
            except paramiko.SSHException as ssh_err:
                print(f"❌ SSH Error for session {session_id}: {ssh_err}")
                # Don't mark as FAILED yet, might be temporary network issue
                
            except Exception as prov_err:
                print(f"❌ Provisioning error for session {session_id}: {prov_err}")
                cur.execute("""
                    UPDATE lab_sessions 
                    SET status = 'FAILED'
                    WHERE id = %s
                """, (session_id,))
        
        cur.close()
        
    except Exception as e:
        print(f"❌ provision_labs() error: {e}")


# ==============================
# MAIN LOOP
# ==============================
loop_count = 0

while True:
    try:
        loop_count += 1
        
        # ==============================
        # PROVISIONING CHECK (every 10 seconds)
        # ==============================
        print(f"\n=== Loop {loop_count} - Checking for labs to provision ===")
        provision_labs()
        
        # ==============================
        # SERVER MONITORING
        # ==============================
        print(f"\n=== Loop {loop_count} - Monitoring servers ===")
        
        cur = db.cursor(dictionary=True)

        # ONLY ENABLED SERVERS
        cur.execute("SELECT * FROM servers WHERE enabled = 1")
        servers = cur.fetchall()

        for s in servers:
            server_id = s["id"]
            ip = s["ip_address"]
            user = s["ssh_user"]
            password = s["ssh_password"]

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
        print(f"❌ Collector error: {main_err}")

    # ==============================
    # POLLING INTERVAL
    # ==============================
    print(f"\n💤 Sleeping for 10 seconds...\n")
    time.sleep(10)
