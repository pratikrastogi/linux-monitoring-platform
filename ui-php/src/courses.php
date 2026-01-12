<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Course Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_course'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $lab_guide_content = $conn->real_escape_string($_POST['lab_guide_content']);
        $duration_minutes = (int)$_POST['duration_minutes'];
        
        $conn->query("INSERT INTO courses (name, description, lab_guide_content, duration_minutes, active, created_at, updated_at) 
                      VALUES ('$name', '$description', '$lab_guide_content', $duration_minutes, 1, NOW(), NOW())");
        $_SESSION['success'] = "Course created successfully!";
        header("Location: courses.php");
        exit;
    }
    
    if (isset($_POST['update_course'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $lab_guide_content = $conn->real_escape_string($_POST['lab_guide_content']);
        $duration_minutes = (int)$_POST['duration_minutes'];
        $active = isset($_POST['active']) ? 1 : 0;
        
        $conn->query("UPDATE courses SET 
                      name='$name', description='$description', lab_guide_content='$lab_guide_content',
                      duration_minutes=$duration_minutes, active=$active, updated_at=NOW()
                      WHERE id=$id");
        $_SESSION['success'] = "Course updated successfully!";
        header("Location: courses.php");
        exit;
    }
    
    if (isset($_POST['delete_course'])) {
        $id = (int)$_POST['id'];
        // Soft delete - set active to 0
        $conn->query("UPDATE courses SET active=0, updated_at=NOW() WHERE id=$id");
        $_SESSION['success'] = "Course archived successfully!";
        header("Location: courses.php");
        exit;
    }
}

// Get action
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? null;

// Fetch course for editing
$edit_course = null;
if ($action === 'edit' && $edit_id) {
    $result = $conn->query("SELECT * FROM courses WHERE id=$edit_id");
    $edit_course = $result->fetch_assoc();
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
          <h1 class="m-0"><i class="fas fa-graduation-cap"></i> Course Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Courses</li>
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
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-<?= $action === 'create' ? 'plus' : 'edit' ?>"></i>
            <?= $action === 'create' ? 'Create New Course' : 'Edit Course' ?>
          </h3>
        </div>
        <form method="POST">
          <div class="card-body">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $edit_course['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
              <label>Course Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" 
                     value="<?= htmlspecialchars($edit_course['name'] ?? '') ?>" 
                     placeholder="e.g., Kubernetes Fundamentals" required>
            </div>

            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="4" required 
                        placeholder="Describe what students will learn..."><?= htmlspecialchars($edit_course['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label>Duration (Minutes) <span class="text-danger">*</span></label>
              <input type="number" name="duration_minutes" class="form-control" 
                     value="<?= $edit_course['duration_minutes'] ?? 60 ?>" min="1" max="10000" required>
            </div>

            <div class="form-group">
              <label>Lab Guide Content <span class="text-muted">(Optional - supports code blocks with ```command``` or single-line commands)</span></label>
              <textarea name="lab_guide_content" class="form-control" rows="6" 
                        placeholder="Add lab guide content here. Wrap commands in backticks: `docker run ...` or code blocks &#10;```&#10;docker run -it ubuntu&#10;```"><?= htmlspecialchars($edit_course['lab_guide_content'] ?? '') ?></textarea>
              <small class="form-text text-muted mt-2">
                <strong>Command Format Examples:</strong><br>
                • Inline: `docker ps`<br>
                • Code block: ```docker run -it ubuntu```<br>
                • Multi-line: Commands starting with docker, kubectl, npm, git, etc. will be auto-detected
              </small>
            </div>

            <?php if ($action === 'edit'): ?>
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="active" name="active" 
                       <?= ($edit_course['active'] ?? 1) ? 'checked' : '' ?>>
                <label class="custom-control-label" for="active">Active (visible to users)</label>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div class="card-footer">
            <button type="submit" name="<?= $action === 'create' ? 'create_course' : 'update_course' ?>" 
                    class="btn btn-primary">
              <i class="fas fa-save"></i> <?= $action === 'create' ? 'Create' : 'Update' ?> Course
            </button>
            <a href="courses.php" class="btn btn-default">
              <i class="fas fa-times"></i> Cancel
            </a>
          </div>
        </form>
      </div>

      <?php else: ?>
      <!-- List View -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list"></i> All Courses</h3>
          <div class="card-tools">
            <a href="courses.php?action=create" class="btn btn-primary btn-sm">
              <i class="fas fa-plus"></i> Create New Course
            </a>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Duration</th>
                <th>Labs</th>
                <th>Enrollments</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $result = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
              while($course = $result->fetch_assoc()):
                // Count labs
                $labs_q = $conn->query("SELECT COUNT(*) as cnt FROM labs WHERE course_id={$course['id']} AND active=1");
                $labs_count = $labs_q->fetch_assoc()['cnt'];
                
                // Count unique users who accessed labs
                $enroll_q = $conn->query("
                  SELECT COUNT(DISTINCT ls.user_id) as cnt 
                  FROM lab_sessions ls
                  JOIN labs l ON ls.lab_id = l.id
                  WHERE l.course_id = {$course['id']}
                ");
                $enrollments = $enroll_q->fetch_assoc()['cnt'];
              ?>
              <tr>
                <td><?= $course['id'] ?></td>
                <td>
                  <strong><?= htmlspecialchars($course['name']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars(substr($course['description'] ?? '', 0, 60)) ?>...</small>
                </td>
                <td><?= intval($course['duration_minutes'] ?? 60) ?> min</td>
                <td><?= $labs_count ?></td>
                <td><?= $enrollments ?></td>
                <td>
                  <?php if($course['active']): ?>
                  <span class="badge badge-success">Active</span>
                  <?php else: ?>
                  <span class="badge badge-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-xs btn-primary" title="View Course">
                    <i class="fas fa-eye"></i> View
                  </a>
                  <a href="courses.php?action=edit&id=<?= $course['id'] ?>" class="btn btn-xs btn-info" title="Edit Course">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="labs.php?course=<?= $course['id'] ?>" class="btn btn-xs btn-success" title="View Labs">
                    <i class="fas fa-flask"></i> Labs
                  </a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this course?');">
                    <input type="hidden" name="id" value="<?= $course['id'] ?>">
                    <button type="submit" name="delete_course" class="btn btn-xs btn-danger" title="Archive Course">
                      <i class="fas fa-archive"></i> Archive
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
</body>
</html>
