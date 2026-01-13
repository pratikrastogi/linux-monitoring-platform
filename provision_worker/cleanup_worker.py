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
# LAB CLEANUP (EXPIRED or REVOKED LABS)
# ----------------------------------------------------------
def cleanup_lab_session(session):
    """
    Cleanup a lab session (expired or revoked):
    Executes the lab's cleanup script with the provisioned username
    """
    session_id = session["id"]
    username = session["username"]
    status = session["status"]
    
    host = session["ip_address"]
    ssh_user = session["ssh_user"]
    ssh_pass = session["ssh_password"]
    script = session["cleanup_script_path"]
    
    # Skip if no username (can't cleanup)
    if not username or username == '':
        print(f"[WARN] Session {session_id}: No username, skipping cleanup")
        return False
    
    if not host:
        print(f"[WARN] Session {session_id}: No server host, skipping cleanup")
        return False
    
    # Script path is required
    if not script:
        print(f"[WARN] Session {session_id}: No cleanup script defined, skipping cleanup")
        return False
    
    try:
        print(f"[INFO] Cleanup starting for session {session_id} ({status}): user={username}")
        print(f"[INFO] Using cleanup script: {script}")
        
        safe_user = shlex.quote(username)
        
        # Execute the lab-specific cleanup script with username as parameter
        cmd = f"sudo {script} {safe_user}"
        
        result = ssh_exec(host, ssh_user, ssh_pass, cmd)
        
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
        # FIND EXPIRED or REVOKED ACTIVE LABS
        # ==================================================
        # Case 1: Sessions that are ACTIVE but have expired (access_expiry < NOW)
        # Case 2: Sessions that have been manually REVOKED
        # Only cleanup sessions where provisioned=1 (means they were provisioned and need cleanup)
        cur.execute("""
            SELECT 
                ls.id,
                ls.username,
                ls.access_expiry,
                ls.status,
                l.server_id,
                l.cleanup_script_path,
                s.ip_address,
                s.ssh_user,
                s.ssh_password,
                s.ssh_port
            FROM lab_sessions ls
            JOIN labs l ON ls.lab_id = l.id
            JOIN servers s ON l.server_id = s.id
            WHERE ls.provisioned = 1
            AND (
                (ls.status = 'ACTIVE' AND ls.access_expiry < NOW())
                OR ls.status = 'REVOKED'
            )
            ORDER BY ls.access_expiry ASC
            LIMIT 5
        """)
        
        sessions_to_cleanup = cur.fetchall()
        
        if sessions_to_cleanup:
            print(f"\n[INFO] Found {len(sessions_to_cleanup)} session(s) to cleanup")
        
        for session in sessions_to_cleanup:
            session_id = session["id"]
            username = session["username"]
            status = session["status"]
            expiry_time = session["access_expiry"]
            
            print(f"\n[PROCESS] Session {session_id}: {username} (status={status}, expired={expiry_time})")
            
            # Attempt cleanup
            # Attempt cleanup
            cleanup_success = cleanup_lab_session(session)
            
            if cleanup_success:
                # Mark session status and set provisioned=0 to indicate cleanup is done
                if status == 'REVOKED':
                    final_status = 'REVOKED'
                else:
                    final_status = 'EXPIRED'
                
                cur.execute("""
                    UPDATE lab_sessions
                    SET status=%s, provisioned=0
                    WHERE id=%s
                """, (final_status, session_id))
                print(f"[DB] Session {session_id} marked as {final_status} with provisioned=0 (cleanup complete)")
            else:
                # Don't mark, retry next time
                print(f"[SKIP] Session {session_id} cleanup failed, will retry")
        
        cur.close()
        
        if not sessions_to_cleanup:
            print(f"[IDLE] No expired/revoked labs to cleanup, sleeping...")
        
    except Exception as fatal_err:
        print(f"[FATAL] Cleanup worker error: {fatal_err}")
    
    # ==================================================
    # SLEEP BEFORE NEXT POLL
    # ==================================================
    time.sleep(POLL_INTERVAL)
