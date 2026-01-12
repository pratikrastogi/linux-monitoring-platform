<?php
session_start();
require_once 'auth.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) die("Connection failed");

$message = '';

// Handle approve/deny
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    if ($_POST['action'] === 'approve') {
        // Get request details
        $req = $db->query("SELECT lr.*, l.duration_minutes, l.bastion_host, l.bastion_user, l.bastion_password, l.provision_script_path, u.email 
            FROM lab_requests lr 
            JOIN labs l ON lr.lab_id = l.id 
            JOIN users u ON lr.user_id = u.id 
            WHERE lr.id = $request_id")->fetch_assoc();
        
        if ($req) {
            // Create lab session
            $expires_at = date('Y-m-d H:i:s', time() + ($req['duration_minutes'] * 60));
            $session_token = bin2hex(random_bytes(32));
            
            $stmt = $db->prepare("INSERT INTO lab_sessions (user_id, username, lab_id, namespace, access_start, access_expiry, status, session_token) VALUES (?, ?, ?, ?, NOW(), ?, 'ACTIVE', ?)");
            $namespace = 'lab-' . $req['user_id'];
            $username = $req['email'];
            $stmt->bind_param("isisss", $req['user_id'], $username, $req['lab_id'], $namespace, $expires_at, $session_token);
            $stmt->execute();
            
            // Update request status
            $db->query("UPDATE lab_requests SET status='approved', reviewed_by={$_SESSION['uid']}, reviewed_at=NOW(), admin_notes='$admin_notes' WHERE id=$request_id");
            
            // Trigger provisioning
            $provision_cmd = "sshpass -p '{$req['bastion_password']}' ssh -o StrictHostKeyChecking=no {$req['bastion_user']}@{$req['bastion_host']} 'bash {$req['provision_script_path']} {$req['email']} {$req['duration_minutes']} lab'";
            exec($provision_cmd . " > /dev/null 2>&1 &");
            
            $message = "Lab approved and provisioned!";
        }
    } elseif ($_POST['action'] === 'deny') {
        $db->query("UPDATE lab_requests SET status='denied', reviewed_by={$_SESSION['uid']}, reviewed_at=NOW(), admin_notes='$admin_notes' WHERE id=$request_id");
        $message = "Request denied.";
    }
}

// Get pending requests
$pending = $db->query("SELECT lr.*, u.email, u.username as user_name, c.name as course_name, l.lab_name 
    FROM lab_requests lr 
    JOIN users u ON lr.user_id = u.id 
    JOIN labs l ON lr.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE lr.status='pending' 
    ORDER BY lr.created_at ASC");

// Get recent history
$history = $db->query("SELECT lr.*, u.email, c.name as course_name, l.lab_name,  admin.username as reviewed_by_name
    FROM lab_requests lr 
    JOIN users u ON lr.user_id = u.id 
    JOIN labs l ON lr.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    LEFT JOIN users admin ON lr.reviewed_by = admin.id 
    WHERE lr.status IN ('approved','denied') 
    ORDER BY lr.reviewed_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Requests | KubeArena</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-list"></i> Lab Access Requests</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Requests</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <!-- Pending Requests -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">Pending Requests</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Lab</th>
                                    <th>Justification</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($r = $pending->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['email']) ?></td>
                                        <td><?= htmlspecialchars($r['course_name']) ?></td>
                                        <td><?= htmlspecialchars($r['lab_name']) ?></td>
                                        <td><?= htmlspecialchars($r['justification']) ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="approveRequest(<?= $r['id'] ?>)">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="denyRequest(<?= $r['id'] ?>)">
                                                <i class="fas fa-times"></i> Deny
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- History -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Request History</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Course/Lab</th>
                                    <th>Status</th>
                                    <th>Reviewed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($h = $history->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($h['email']) ?></td>
                                        <td><?= htmlspecialchars($h['course_name']) ?> - <?= htmlspecialchars($h['lab_name']) ?></td>
                                        <td>
                                            <?php if ($h['status'] === 'approved'): ?>
                                                <span class="badge badge-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Denied</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($h['reviewed_by_name'] ?? 'N/A') ?></td>
                                        <td><?= date('M d, H:i', strtotime($h['reviewed_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Approve Lab Request</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="request_id" id="approve_request_id">
                    <div class="form-group">
                        <label>Admin Notes (optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="3"></textarea>
                    </div>
                    <p><strong>This will:</strong></p>
                    <ul>
                        <li>Create a 60-minute lab session</li>
                        <li>Trigger auto-provisioning on bastion host</li>
                        <li>Notify the user (if email configured)</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Approve & Provision</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deny Modal -->
<div class="modal fade" id="denyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">Deny Lab Request</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="deny">
                    <input type="hidden" name="request_id" id="deny_request_id">
                    <div class="form-group">
                        <label>Reason for Denial *</label>
                        <textarea name="admin_notes" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Deny Request</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    $('[data-widget="pushmenu"]').PushMenu();
});

    $('#approveModal').modal('show');
}

function denyRequest(id) {
    $('#deny_request_id').val(id);
    $('#denyModal').modal('show');
}
</script>
</body>
</html>
