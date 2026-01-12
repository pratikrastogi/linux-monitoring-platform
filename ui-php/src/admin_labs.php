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
            $lab_name = $db->real_escape_string($_POST['lab_name']);
            $bastion_host = $db->real_escape_string($_POST['bastion_host']);
            $bastion_user = $db->real_escape_string($_POST['bastion_user']);
            $bastion_password = $db->real_escape_string($_POST['bastion_password']);
            $provision_script_path = $db->real_escape_string($_POST['provision_script_path']);
            $cleanup_script_path = $db->real_escape_string($_POST['cleanup_script_path']);
            $duration_minutes = (int)$_POST['duration_minutes'];
            $max_concurrent_users = (int)$_POST['max_concurrent_users'];
            $active = isset($_POST['active']) ? 1 : 0;
            
            if ($id) {
                $db->query("UPDATE labs SET course_id=$course_id, lab_name='$lab_name', bastion_host='$bastion_host', 
                           bastion_user='$bastion_user', bastion_password='$bastion_password', 
                           provision_script_path='$provision_script_path', cleanup_script_path='$cleanup_script_path',
                           duration_minutes=$duration_minutes, max_concurrent_users=$max_concurrent_users, 
                           active=$active WHERE id=$id");
                $message = "Lab updated successfully!";
            } else {
                $db->query("INSERT INTO labs (course_id, lab_name, bastion_host, bastion_user, bastion_password, 
                           provision_script_path, cleanup_script_path, duration_minutes, max_concurrent_users, active) 
                           VALUES ($course_id, '$lab_name', '$bastion_host', '$bastion_user', '$bastion_password', 
                           '$provision_script_path', '$cleanup_script_path', $duration_minutes, $max_concurrent_users, $active)");
                $message = "Lab created successfully!";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM labs WHERE id=$id");
            $message = "Lab deleted successfully!";
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
    <title>Lab Management | KubeArena</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">
    
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark navbar-lightblue">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        
        <div class="navbar-nav ml-auto">
            <div class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user']) ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-flask"></i> Lab Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Labs</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <div class="container-fluid">
                
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Lab Form Card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-<?= $edit_lab ? 'edit' : 'plus' ?>"></i>
                            <?= $edit_lab ? 'Edit Lab' : 'Create New Lab' ?>
                        </h3>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <input type="hidden" name="action" value="<?= $edit_lab ? 'update' : 'create' ?>">
                            <?php if ($edit_lab): ?>
                                <input type="hidden" name="id" value="<?= $edit_lab['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-book"></i> Course *</label>
                                        <select name="course_id" class="form-control" required>
                                            <option value="">-- Select Course --</option>
                                            <?php 
                                            $courses = $db->query("SELECT id, name FROM courses WHERE active=1 ORDER BY name");
                                            while ($c = $courses->fetch_assoc()): 
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
                                        <label><i class="fas fa-flask"></i> Lab Name *</label>
                                        <input type="text" name="lab_name" class="form-control" 
                                               value="<?= $edit_lab ? htmlspecialchars($edit_lab['lab_name']) : '' ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            <h5><i class="fas fa-server"></i> Bastion Server Configuration</h5>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Host (IP/Hostname) *</label>
                                        <input type="text" name="bastion_host" class="form-control" placeholder="192.168.1.46"
                                               value="<?= $edit_lab ? htmlspecialchars($edit_lab['bastion_host']) : '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Username *</label>
                                        <input type="text" name="bastion_user" class="form-control"
                                               value="<?= $edit_lab ? htmlspecialchars($edit_lab['bastion_user']) : '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Password *</label>
                                        <input type="password" name="bastion_password" class="form-control"
                                               value="<?= $edit_lab ? htmlspecialchars($edit_lab['bastion_password']) : '' ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            <h5><i class="fas fa-cogs"></i> Provisioning Scripts</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Provision Script Path *</label>
                                        <input type="text" name="provision_script_path" class="form-control" 
                                               placeholder="/opt/lab/create_lab_user.sh"
                                               value="<?= $edit_lab ? htmlspecialchars($edit_lab['provision_script_path']) : '' ?>" required>
                                        <small class="text-muted">Script to create lab environment</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cleanup Script Path *</label>
                                        <input type="text" name="cleanup_script_path" class="form-control"
                                               placeholder="/opt/lab/cleanup_lab_user.sh"
                                               value="<?= $edit_lab ? htmlspecialchars($edit_lab['cleanup_script_path']) : '' ?>" required>
                                        <small class="text-muted">Script to cleanup when lab expires</small>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            <h5><i class="fas fa-sliders-h"></i> Lab Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><i class="fas fa-clock"></i> Duration (minutes) *</label>
                                        <input type="number" name="duration_minutes" class="form-control" min="10" max="1440"
                                               value="<?= $edit_lab ? $edit_lab['duration_minutes'] : 60 ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><i class="fas fa-users"></i> Max Concurrent Users *</label>
                                        <input type="number" name="max_concurrent_users" class="form-control" min="1" max="100"
                                               value="<?= $edit_lab ? $edit_lab['max_concurrent_users'] : 10 ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="active" name="active"
                                                   <?= (!$edit_lab || $edit_lab['active']) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="active">
                                                <i class="fas fa-check"></i> Active (visible to users)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $edit_lab ? 'Update Lab' : 'Create Lab' ?>
                            </button>
                            <?php if ($edit_lab): ?>
                                <a href="admin_labs.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Labs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Available Labs</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Lab Name</th>
                                    <th>Course</th>
                                    <th>Duration</th>
                                    <th>Max Users</th>
                                    <th>Active Sessions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lab = $labs->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($lab['lab_name']) ?></strong></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($lab['course_name'] ?? 'N/A') ?></span></td>
                                    <td><span class="badge badge-secondary"><?= $lab['duration_minutes'] ?> min</span></td>
                                    <td><?= $lab['max_concurrent_users'] ?></td>
                                    <td><span class="badge badge-success"><?= $lab['active_sessions'] ?></span></td>
                                    <td>
                                        <?php if ($lab['active']): ?>
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><i class="fas fa-ban"></i> Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?edit=<?= $lab['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $lab['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Delete this lab?')">
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
    
    <!-- Footer -->
    <footer class="main-footer">
        <strong>KubeArena</strong> Learning Platform &copy; 2026
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 2.0
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    $(document).ready(function() {
        $('[data-widget="pushmenu"]').PushMenu();
    });
</script>
</body>
</html>
