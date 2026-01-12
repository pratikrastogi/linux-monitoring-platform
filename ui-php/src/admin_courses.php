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
            $name = $_POST['name'];
            $description = $_POST['description'];
            $lab_guide_content = $_POST['lab_guide_content'];
            $duration_minutes = (int)$_POST['duration_minutes'];
            $active = isset($_POST['active']) ? 1 : 0;
            
            if ($id) {
                // Update
                $stmt = $db->prepare("UPDATE courses SET name=?, description=?, lab_guide_content=?, duration_minutes=?, active=? WHERE id=?");
                $stmt->bind_param("sssiii", $name, $description, $lab_guide_content, $duration_minutes, $active, $id);
                $message = "Course updated successfully!";
            } else {
                // Create
                $stmt = $db->prepare("INSERT INTO courses (name, description, lab_guide_content, duration_minutes, active) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $name, $description, $lab_guide_content, $duration_minutes, $active);
                $message = "Course created successfully!";
            }
            
            if ($stmt->execute()) {
                // Success
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM courses WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Course deleted successfully!";
            } else {
                $error = "Error deleting course: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Get course for editing
$edit_course = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $db->query("SELECT * FROM courses WHERE id = $edit_id");
    $edit_course = $result->fetch_assoc();
}

// Get all courses
$courses = $db->query("SELECT c.*, 
    (SELECT COUNT(*) FROM labs WHERE course_id = c.id) as lab_count 
    FROM courses c ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Course Management</h1>
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
                
                <!-- Course Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?= $edit_course ? 'Edit Course' : 'Create New Course' ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $edit_course ? 'update' : 'create' ?>">
                            <?php if ($edit_course): ?>
                                <input type="hidden" name="id" value="<?= $edit_course['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Course Name *</label>
                                <input type="text" name="name" class="form-control" required 
                                       value="<?= htmlspecialchars($edit_course['name'] ?? '') ?>"
                                       placeholder="e.g., Docker Basics, Kubernetes Essentials">
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                          placeholder="Brief description of the course"><?= htmlspecialchars($edit_course['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Lab Guide Content (Markdown) *</label>
                                <textarea name="lab_guide_content" class="form-control" rows="15" required 
                                          placeholder="# Lab Guide Title&#10;&#10;## Introduction&#10;Welcome to this lab...&#10;&#10;## Steps&#10;1. First step&#10;2. Second step"><?= htmlspecialchars($edit_course['lab_guide_content'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Use Markdown syntax. This will be displayed to users during lab sessions.</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default Duration (minutes) *</label>
                                        <input type="number" name="duration_minutes" class="form-control" required 
                                               value="<?= $edit_course['duration_minutes'] ?? 60 ?>" min="1" max="240">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="active" class="custom-control-input" id="active" 
                                                   <?= ($edit_course['active'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="active">Active (visible to users)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $edit_course ? 'Update Course' : 'Create Course' ?>
                            </button>
                            <?php if ($edit_course): ?>
                                <a href="admin_courses.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Courses List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Existing Courses</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Labs</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($course = $courses->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $course['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($course['name']) ?></strong></td>
                                        <td><?= htmlspecialchars(substr($course['description'], 0, 50)) ?>...</td>
                                        <td><?= $course['duration_minutes'] ?> min</td>
                                        <td><span class="badge badge-info"><?= $course['lab_count'] ?> labs</span></td>
                                        <td>
                                            <?php if ($course['active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $course['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this course?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $course['id'] ?>">
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
