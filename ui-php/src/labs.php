<?php
session_start();
// Redirect to new admin_labs.php
header("Location: admin_labs.php");
exit;
?>

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_lab'])) {
        $course_id = (int)$_POST['course_id'];
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $difficulty = $conn->real_escape_string($_POST['difficulty']);
        $duration_minutes = (int)$_POST['duration_minutes'];
        $lab_guide_content = $conn->real_escape_string($_POST['lab_guide_content']);
        $docker_image = $conn->real_escape_string($_POST['docker_image']);
        $cpu_limit = $conn->real_escape_string($_POST['cpu_limit']);
        $memory_limit = $conn->real_escape_string($_POST['memory_limit']);
        
        $conn->query("INSERT INTO lab_templates 
                      (course_id, title, description, difficulty, duration_minutes, lab_guide_content, 
                       docker_image, cpu_limit, memory_limit, active, created_at, updated_at) 
                      VALUES ($course_id, '$title', '$description', '$difficulty', $duration_minutes, 
                              '$lab_guide_content', '$docker_image', '$cpu_limit', '$memory_limit', 1, NOW(), NOW())");
        $_SESSION['success'] = "Lab template created successfully!";
        header("Location: labs.php");
        exit;
    }
    
    if (isset($_POST['update_lab'])) {
        $id = (int)$_POST['id'];
        $course_id = (int)$_POST['course_id'];
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $difficulty = $conn->real_escape_string($_POST['difficulty']);
        $duration_minutes = (int)$_POST['duration_minutes'];
        $lab_guide_content = $conn->real_escape_string($_POST['lab_guide_content']);
        $docker_image = $conn->real_escape_string($_POST['docker_image']);
        $cpu_limit = $conn->real_escape_string($_POST['cpu_limit']);
        $memory_limit = $conn->real_escape_string($_POST['memory_limit']);
        $active = isset($_POST['active']) ? 1 : 0;
        
        $conn->query("UPDATE lab_templates SET 
                      course_id=$course_id, title='$title', description='$description', 
                      difficulty='$difficulty', duration_minutes=$duration_minutes, 
                      lab_guide_content='$lab_guide_content', docker_image='$docker_image',
                      cpu_limit='$cpu_limit', memory_limit='$memory_limit', active=$active, updated_at=NOW()
                      WHERE id=$id");
        $_SESSION['success'] = "Lab template updated successfully!";
        header("Location: labs.php");
        exit;
    }
    
    if (isset($_POST['delete_lab'])) {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE lab_templates SET active=0, updated_at=NOW() WHERE id=$id");
        $_SESSION['success'] = "Lab template archived successfully!";
        header("Location: labs.php");
        exit;
    }
}

// Get action
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? null;
$filter_course = $_GET['course'] ?? null;

// Fetch lab for editing
$edit_lab = null;
if ($action === 'edit' && $edit_id) {
    $result = $conn->query("SELECT * FROM lab_templates WHERE id=$edit_id");
    $edit_lab = $result->fetch_assoc();
}

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-flask"></i> Lab Templates Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Lab Templates</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
      <?php endif; ?>

      <?php if ($action === 'create' || $action === 'edit'): ?>
      <!-- Create/Edit Form -->
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-<?= $action === 'create' ? 'plus' : 'edit' ?>"></i>
            <?= $action === 'create' ? 'Create New Lab Template' : 'Edit Lab Template' ?>
          </h3>
        </div>
        <form method="POST">
          <div class="card-body">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $edit_lab['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
              <label>Course <span class="text-danger">*</span></label>
              <select name="course_id" class="form-control" required>
                <option value="">-- Select Course --</option>
                <?php
                $courses = $conn->query("SELECT id, title FROM courses WHERE active=1 ORDER BY title");
                while($c = $courses->fetch_assoc()):
                ?>
                <option value="<?= $c['id'] ?>" <?= ($edit_lab['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['title']) ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Lab Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" 
                     value="<?= htmlspecialchars($edit_lab['title'] ?? '') ?>" 
                     placeholder="e.g., Deploy Your First Pod" required>
            </div>

            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="3" required 
                        placeholder="What will students do in this lab?"><?= htmlspecialchars($edit_lab['description'] ?? '') ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Difficulty Level <span class="text-danger">*</span></label>
                  <select name="difficulty" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="Beginner" <?= ($edit_lab['difficulty'] ?? '') === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="Intermediate" <?= ($edit_lab['difficulty'] ?? '') === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="Advanced" <?= ($edit_lab['difficulty'] ?? '') === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Duration (Minutes) <span class="text-danger">*</span></label>
                  <input type="number" name="duration_minutes" class="form-control" 
                         value="<?= $edit_lab['duration_minutes'] ?? 60 ?>" min="15" max="480" required>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Lab Guide (Markdown) <span class="text-danger">*</span></label>
              <textarea name="lab_guide_content" class="form-control" rows="10" required 
                        placeholder="# Lab Guide&#10;&#10;## Objective&#10;Learn to...&#10;&#10;## Steps&#10;1. First step..."><?= htmlspecialchars($edit_lab['lab_guide_content'] ?? '') ?></textarea>
              <small class="text-muted">Use Markdown syntax. This will be displayed in the terminal guide panel.</small>
            </div>

            <div class="card card-secondary collapsed-card">
              <div class="card-header">
                <h3 class="card-title">Resource Configuration</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label>Docker Image</label>
                  <input type="text" name="docker_image" class="form-control" 
                         value="<?= htmlspecialchars($edit_lab['docker_image'] ?? 'ubuntu:22.04') ?>" 
                         placeholder="ubuntu:22.04">
                  <small class="text-muted">Container image for the lab environment</small>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>CPU Limit</label>
                      <input type="text" name="cpu_limit" class="form-control" 
                             value="<?= htmlspecialchars($edit_lab['cpu_limit'] ?? '1') ?>" 
                             placeholder="1">
                      <small class="text-muted">e.g., "1" or "500m"</small>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Memory Limit</label>
                      <input type="text" name="memory_limit" class="form-control" 
                             value="<?= htmlspecialchars($edit_lab['memory_limit'] ?? '512Mi') ?>" 
                             placeholder="512Mi">
                      <small class="text-muted">e.g., "512Mi" or "1Gi"</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($action === 'edit'): ?>
            <div class="form-group mt-3">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="active" name="active" 
                       <?= ($edit_lab['active'] ?? 1) ? 'checked' : '' ?>>
                <label class="custom-control-label" for="active">Active (visible to users)</label>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div class="card-footer">
            <button type="submit" name="<?= $action === 'create' ? 'create_lab' : 'update_lab' ?>" 
                    class="btn btn-success">
              <i class="fas fa-save"></i> <?= $action === 'create' ? 'Create' : 'Update' ?> Lab Template
            </button>
            <a href="labs.php" class="btn btn-default">
              <i class="fas fa-times"></i> Cancel
            </a>
          </div>
        </form>
      </div>

      <?php else: ?>
      <!-- List View -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list"></i> All Lab Templates</h3>
          <div class="card-tools">
            <?php if ($filter_course): ?>
            <a href="labs.php" class="btn btn-default btn-sm mr-2">
              <i class="fas fa-times"></i> Clear Filter
            </a>
            <?php endif; ?>
            <a href="labs.php?action=create" class="btn btn-success btn-sm">
              <i class="fas fa-plus"></i> Create New Lab
            </a>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Lab Title</th>
                <th>Course</th>
                <th>Difficulty</th>
                <th>Duration</th>
                <th>Active Sessions</th>
                <th>Total Completions</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT lt.*, c.title as course_title 
                      FROM lab_templates lt
                      JOIN courses c ON lt.course_id = c.id";
              if ($filter_course) {
                $sql .= " WHERE lt.course_id = $filter_course";
              }
              $sql .= " ORDER BY c.title, lt.created_at DESC";
              
              $result = $conn->query($sql);
              while($lab = $result->fetch_assoc()):
                // Count active sessions
                $sessions_q = $conn->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE lab_template_id={$lab['id']} AND status='ACTIVE'");
                $active_sessions = $sessions_q->fetch_assoc()['cnt'];
                
                // Count completions
                $completions_q = $conn->query("SELECT COUNT(*) as cnt FROM lab_progress WHERE lab_template_id={$lab['id']} AND status='completed'");
                $completions = $completions_q->fetch_assoc()['cnt'];
              ?>
              <tr>
                <td><?= $lab['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($lab['title']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars(substr($lab['description'], 0, 50)) ?>...</small>
                </td>
                <td><?= htmlspecialchars($lab['course_title']) ?></td>
                <td>
                  <?php
                  $badge_class = $lab['difficulty'] === 'Beginner' ? 'success' : ($lab['difficulty'] === 'Intermediate' ? 'warning' : 'danger');
                  ?>
                  <span class="badge badge-<?= $badge_class ?>"><?= $lab['difficulty'] ?></span>
                </td>
                <td><?= $lab['duration_minutes'] ?> min</td>
                <td><?= $active_sessions ?></td>
                <td><?= $completions ?></td>
                <td>
                  <?php if($lab['active']): ?>
                  <span class="badge badge-success">Active</span>
                  <?php else: ?>
                  <span class="badge badge-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="labs.php?action=edit&id=<?= $lab['id'] ?>" class="btn btn-xs btn-info">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this lab?');">
                    <input type="hidden" name="id" value="<?= $lab['id'] ?>">
                    <button type="submit" name="delete_lab" class="btn btn-xs btn-danger">
                      <i class="fas fa-archive"></i>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

</div>

<!-- Markdown Preview Script -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

</body>
</html>
