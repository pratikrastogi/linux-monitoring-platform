import time
import mysql.connector
import paramiko
import shlex
from datetime import datetime

POLL_INTERVAL = 10  # Check every 10 seconds for expired labs

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
def ssh_exec(host, ssh_user, ssh_pass, command, timeout=15):
    """Execute command over SSH and return output"""
    try:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect(
            hostname=host,
            username=ssh_user,
            password=ssh_pass,
            timeout=timeout,
            allow_agent=False,
            look_for_keys=False
        )
        stdin, stdout, stderr = ssh.exec_command(command, timeout=timeout)
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()
        exit_code = stdout.channel.recv_exit_status()
        ssh.close()
        
        return {
            'success': exit_code == 0,
            'output': out,
            'error': err,
            'exit_code': exit_code
        }
    except Exception as e:
        return {
            'success': False,
            'output': '',
            'error': str(e),
            'exit_code': -1
        }


# ----------------------------------------------------------
# LAB CLEANUP (EXPIRED LABS)
# ----------------------------------------------------------
def cleanup_expired_lab(session):
    """
    Cleanup an expired lab:
    1. Lock Linux user
    2. Kill active sessions
    3. Delete Kubernetes namespace
    """
    session_id = session["id"]
    username = session["username"]
    namespace = session["namespace"]
    
    host = session["ip_address"]
    ssh_user = session["ssh_user"]
    ssh_pass = session["ssh_password"]
    
    # Skip if no username (can't cleanup)
    if not username or username == '':
        print(f"[WARN] Session {session_id}: No username, skipping cleanup")
        return False
    
    if not host:
        print(f"[WARN] Session {session_id}: No server host, skipping cleanup")
        return False
    
    try:
        print(f"[INFO] Cleanup starting for session {session_id} (user: {username})")
        
        # Build cleanup command
        # This locks the user, kills sessions, and deletes namespace
        cleanup_cmd = f"""
#!/bin/bash
set -e

USERNAME="{username}"
NAMESPACE="{namespace}"

echo "▶ Locking Linux user..."
usermod -L "$USERNAME" || true
passwd -l "$USERNAME" || true

echo "▶ Killing active SSH sessions..."
pkill -u "$USERNAME" || true

echo "▶ Removing Kubernetes namespace..."
kubectl delete namespace "$NAMESPACE" --ignore-not-found=true

echo "SUCCESS|CLEANED=$USERNAME"
"""
        
        result = ssh_exec(host, ssh_user, ssh_pass, cleanup_cmd)
        
        if not result['success']:
            print(f"[ERROR] Session {session_id} cleanup failed (exit {result['exit_code']})")
            print(f"        Error: {result['error'][:200]}")
            return False
        
        print(f"[SUCCESS] Session {session_id} cleaned up")
        print(f"          Output: {result['output'][:200]}")
        return True
        
    except Exception as e:
        print(f"[ERROR] Exception during cleanup of session {session_id}: {e}")
        return False


# ----------------------------------------------------------
# MAIN CLEANUP LOOP
# ----------------------------------------------------------
print("[START] Lab Cleanup Worker started")

while True:
    try:
        cur = db.cursor(dictionary=True)
        
        # ==================================================
        # FIND EXPIRED ACTIVE LABS
        # ==================================================
        cur.execute("""
            SELECT 
                ls.id,
                ls.username,
                ls.namespace,
                ls.access_expiry,
                l.server_id,
                s.ip_address,
                s.ssh_user,
                s.ssh_password,
                s.ssh_port
            FROM lab_sessions ls
            JOIN labs l ON ls.lab_id = l.id
            JOIN servers s ON l.server_id = s.id
            WHERE ls.status = 'ACTIVE'
              AND ls.access_expiry < NOW()
            ORDER BY ls.access_expiry ASC
            LIMIT 5
        """)
        
        expired_labs = cur.fetchall()
        
        if expired_labs:
            print(f"\n[INFO] Found {len(expired_labs)} expired lab(s) to cleanup")
        
        for session in expired_labs:
            session_id = session["id"]
            username = session["username"]
            expiry_time = session["access_expiry"]
            
            print(f"\n[PROCESS] Session {session_id}: {username} (expired at {expiry_time})")
            
            # Attempt cleanup
            cleanup_success = cleanup_expired_lab(session)
            
            if cleanup_success:
                # Mark session as EXPIRED
                cur.execute("""
                    UPDATE lab_sessions
                    SET status='EXPIRED'
                    WHERE id=%s
                """, (session_id,))
                print(f"[DB] Session {session_id} marked as EXPIRED")
            else:
                # Don't mark as EXPIRED yet, retry next time
                print(f"[SKIP] Session {session_id} cleanup failed, will retry")
        
        cur.close()
        
        if not expired_labs:
            print(f"[IDLE] No expired labs to cleanup, sleeping...")
        
    except Exception as fatal_err:
        print(f"[FATAL] Cleanup worker error: {fatal_err}")
    
    # ==================================================
    # SLEEP BEFORE NEXT POLL
    # ==================================================
    time.sleep(POLL_INTERVAL)
