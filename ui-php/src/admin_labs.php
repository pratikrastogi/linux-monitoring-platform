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
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $id = $_POST['id'] ?? null;
            $course_id = (int)$_POST['course_id'];
            $lab_name = $_POST['lab_name'];
            $bastion_host = $_POST['bastion_host'];
            $bastion_user = $_POST['bastion_user'];
            $bastion_password = $_POST['bastion_password'];
            $provision_script_path = $_POST['provision_script_path'];
            $cleanup_script_path = $_POST['cleanup_script_path'];
            $duration_minutes = (int)$_POST['duration_minutes'];
            $max_concurrent_users = (int)$_POST['max_concurrent_users'];
            $active = isset($_POST['active']) ? 1 : 0;
            
            if ($id) {
                // Update
                $stmt = $db->prepare("UPDATE labs SET course_id=?, lab_name=?, bastion_host=?, bastion_user=?, bastion_password=?, provision_script_path=?, cleanup_script_path=?, duration_minutes=?, max_concurrent_users=?, active=? WHERE id=?");
                $stmt->bind_param("issssssiiii", $course_id, $lab_name, $bastion_host, $bastion_user, $bastion_password, $provision_script_path, $cleanup_script_path, $duration_minutes, $max_concurrent_users, $active, $id);
                $message = "Lab updated successfully!";
            } else {
                // Create
                $stmt = $db->prepare("INSERT INTO labs (course_id, lab_name, bastion_host, bastion_user, bastion_password, provision_script_path, cleanup_script_path, duration_minutes, max_concurrent_users, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssiii", $course_id, $lab_name, $bastion_host, $bastion_user, $bastion_password, $provision_script_path, $cleanup_script_path, $duration_minutes, $max_concurrent_users, $active);
                $message = "Lab created successfully!";
            }
            
            if ($stmt->execute()) {
                // Success
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM labs WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Lab deleted successfully!";
            } else {
                $error = "Error deleting lab: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Get lab for editing
$edit_lab = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $db->query("SELECT * FROM labs WHERE id = $edit_id");
    $edit_lab = $result->fetch_assoc();
}

// Get all courses for dropdown
$courses = $db->query("SELECT id, name FROM courses WHERE active=1 ORDER BY name");

// Get all labs with course names
$labs = $db->query("SELECT l.*, c.name as course_name, 
    (SELECT COUNT(*) FROM lab_sessions WHERE lab_id = l.id AND status='ACTIVE') as active_sessions 
    FROM labs l 
    LEFT JOIN courses c ON l.course_id = c.id 
    ORDER BY l.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Labs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Lab Management</h1>
            </div>
        </div>
        
        <div class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Lab Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?= $edit_lab ? 'Edit Lab' : 'Create New Lab' ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $edit_lab ? 'update' : 'create' ?>">
                            <?php if ($edit_lab): ?>
                                <input type="hidden" name="id" value="<?= $edit_lab['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Course *</label>
                                        <select name="course_id" class="form-control" required>
                                            <option value="">Select Course</option>
                                            <?php 
                                            $courses_copy = $db->query("SELECT id, name FROM courses WHERE active=1 ORDER BY name");
                                            while ($c = $courses_copy->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $c['id'] ?>" 
                                                    <?= ($edit_lab && $edit_lab['course_id'] == $c['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lab Name *</label>
                                        <input type="text" name="lab_name" class="form-control" required 
                                               value="<?= htmlspecialchars($edit_lab['lab_name'] ?? '') ?>"
                                               placeholder="e.g., Lab 1 - Introduction">
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-3">Bastion Server Configuration</h5>
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Bastion Host (IP/Hostname) *</label>
                                        <input type="text" name="bastion_host" class="form-control" required 
                                               value="<?= htmlspecialchars($edit_lab['bastion_host'] ?? '192.168.1.46') ?>"
                                               placeholder="192.168.1.46 or master.local">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Username *</label>
                                        <input type="text" name="bastion_user" class="form-control" required 
                                               value="<?= htmlspecialchars($edit_lab['bastion_user'] ?? 'root') ?>"
                                               placeholder="root">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Password *</label>
                                        <input type="password" name="bastion_password" class="form-control" required 
                                               value="<?= htmlspecialchars($edit_lab['bastion_password'] ?? '') ?>"
                                               placeholder="Enter SSH password">
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-3">Provisioning Scripts</h5>
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Provision Script Path *</label>
                                        <input type="text" name="provision_script_path" class="form-control" required 
                                               value="<?= htmlspecialchars($edit_lab['provision_script_path'] ?? '/opt/lab/create_lab_user.sh') ?>"
                                               placeholder="/opt/lab/create_lab_user.sh">
                                        <small class="form-text text-muted">Script to create lab environment for user</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cleanup Script Path *</label>
                                        <input type="text" name="cleanup_script_path" class="form-control" required 
                                               value="<?= htmlspecialchars($edit_lab['cleanup_script_path'] ?? '/opt/lab/cleanup_lab_user.sh') ?>"
                                               placeholder="/opt/lab/cleanup_lab_user.sh">
                                        <small class="form-text text-muted">Script to cleanup after lab expires</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Duration (minutes) *</label>
                                        <input type="number" name="duration_minutes" class="form-control" required 
                                               value="<?= $edit_lab['duration_minutes'] ?? 60 ?>" min="1" max="240">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Max Concurrent Users</label>
                                        <input type="number" name="max_concurrent_users" class="form-control" 
                                               value="<?= $edit_lab['max_concurrent_users'] ?? 10 ?>" min="1" max="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="active" class="custom-control-input" id="active" 
                                                   <?= ($edit_lab['active'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="active">Active (visible to users)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $edit_lab ? 'Update Lab' : 'Create Lab' ?>
                            </button>
                            <?php if ($edit_lab): ?>
                                <a href="admin_labs.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Labs List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Existing Labs</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Course</th>
                                    <th>Lab Name</th>
                                    <th>Bastion Host</th>
                                    <th>Duration</th>
                                    <th>Active Sessions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lab = $labs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $lab['id'] ?></td>
                                        <td><span class="badge badge-primary"><?= htmlspecialchars($lab['course_name']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($lab['lab_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($lab['bastion_user']) ?>@<?= htmlspecialchars($lab['bastion_host']) ?></td>
                                        <td><?= $lab['duration_minutes'] ?> min</td>
                                        <td><span class="badge badge-info"><?= $lab['active_sessions'] ?></span></td>
                                        <td>
                                            <?php if ($lab['active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $lab['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this lab?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $lab['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
