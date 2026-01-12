# Auto-Provisioning Collector - Quick Guide

## Overview
Enhanced collector that combines server monitoring with automatic lab provisioning.

## Features
✅ **Auto-Provisioning**: Automatically provisions labs with `status='REQUESTED'`  
✅ **Server Monitoring**: Continues to monitor all enabled servers  
✅ **Smart Retry**: Skips temporarily unreachable hosts without marking as failed  
✅ **Logging**: Provides detailed console output for debugging  

## How It Works

### 1. Lab Assignment Flow
```
Admin assigns lab → Status: REQUESTED → Collector picks up → Executes provision script → Status: ACTIVE
```

### 2. Provisioning Logic
- Checks `lab_sessions` table for `status='REQUESTED'` AND `provisioned=0`
- Fetches server/bastion details from `labs` and `servers` tables
- Connects via SSH to target host
- Executes: `bash {provision_script_path} {username} {namespace}`
- Updates status based on exit code:
  - **Exit 0**: `status='ACTIVE', provisioned=1`
  - **Exit ≠ 0**: `status='FAILED'`
  - **SSH Error**: Skips (retry next loop)

### 3. Monitoring Loop
- **Every 10 seconds**: 
  1. Check for new provisioning requests
  2. Monitor all enabled servers
  3. Insert metrics and alerts

## Deployment

### Option 1: Replace Existing Collector
```bash
ssh root@192.168.1.46
cd /root/linux-monitoring-platform/collector
cp collector.py collector.py.backup
cp collector_with_provisioning.py collector.py
# Restart collector pod/container
```

### Option 2: Run Side-by-Side
```bash
# Keep existing collector running
# Deploy new one with different name
kubectl apply -f collector-provisioning-deployment.yaml
```

## Testing

### 1. Assign a Lab
```sql
-- Via admin UI or direct SQL
INSERT INTO lab_sessions 
(user_id, username, lab_id, namespace, access_start, access_expiry, status, session_token, provisioned)
VALUES (1, 'testuser', 1, 'user-1-12345', NOW(), DATE_ADD(NOW(), INTERVAL 8 HOUR), 'REQUESTED', 'abc123', 0);
```

### 2. Monitor Collector Logs
```bash
kubectl logs -n linux-monitoring -f <collector-pod-name>
```

You should see:
```
🚀 Provisioning session 123: bash /opt/lab/create_lab_user.sh testuser user-1-12345
✅ Session 123 provisioned successfully
```

### 3. Verify Database
```bash
kubectl exec -n linux-monitoring -it mysql-0 -- mysql -u monitor -pmonitor123 monitoring

mysql> SELECT id, username, status, provisioned FROM lab_sessions WHERE status='ACTIVE';
```

## Database Schema Requirements

Ensure these columns exist in `lab_sessions`:
- `status` ENUM including 'REQUESTED', 'ACTIVE', 'FAILED'
- `provisioned` TINYINT (0 or 1)
- `namespace` VARCHAR for unique lab identifier

## Provisioning Script Requirements

The provision script (e.g., `/opt/lab/create_lab_user.sh`) should:
1. Accept 2 arguments: `$1=username`, `$2=namespace`
2. Exit with code 0 on success
3. Exit with non-zero on failure

Example:
```bash
#!/bin/bash
USERNAME=$1
NAMESPACE=$2

# Create user, setup environment, etc.
useradd -m "$USERNAME-$NAMESPACE"
# ... setup lab environment ...

exit 0  # Success
```

## Troubleshooting

### Labs stuck in REQUESTED
**Check:**
1. Collector is running: `kubectl get pods -n linux-monitoring`
2. Server/bastion is reachable
3. Provision script exists on target server
4. SSH credentials are correct

### Labs marked as FAILED
**Check collector logs for:**
- Exit code from provision script
- Error output from script
- SSH connection errors

### Manual Status Update
```sql
-- Reset to retry provisioning
UPDATE lab_sessions SET status='REQUESTED', provisioned=0 WHERE id=<session_id>;
```

## Key Improvements Over Manual Provisioning

| Aspect | Manual | Auto-Provisioning |
|--------|--------|-------------------|
| Speed | Minutes | Seconds (10s polling) |
| Reliability | Human error | Automated with logging |
| Scalability | Limited | Handles multiple requests |
| Tracking | Manual | Database status tracking |

## Next Steps

1. **Deploy to Production**: Replace or run alongside existing collector
2. **Monitor Performance**: Check logs for any errors
3. **Scale if Needed**: Reduce polling interval or run multiple collectors
4. **Add Cleanup**: Extend for auto-cleanup of expired labs
