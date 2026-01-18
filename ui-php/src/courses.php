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

<?php include 'includes/admin_sidebar.php'; ?>
<?php include 'includes/admin_topbar.php'; ?>

<div class="content-wrapper app-shell">
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
              <label>Lab Guide Content <span class="text-muted">(Optional - Supports formatting, code blocks, and tables)</span></label>
              
              <!-- Formatting Toolbar -->
              <div class="btn-toolbar mb-2" role="toolbar">
                <div class="btn-group btn-group-sm mr-2" role="group">
                  <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('# ', '')" title="Add Heading 1">
                    <i class="fas fa-heading"></i> H1
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('## ', '')" title="Add Heading 2">
                    <i class="fas fa-heading"></i> H2
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('### ', '')" title="Add Heading 3">
                    <i class="fas fa-heading"></i> H3
                  </button>
                </div>
                
                <div class="btn-group btn-group-sm mr-2" role="group">
                  <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('**', '**')" title="Bold">
                    <i class="fas fa-bold"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('_', '_')" title="Italic">
                    <i class="fas fa-italic"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('`', '`')" title="Inline Code">
                    <i class="fas fa-code"></i>
                  </button>
                </div>
                
                <div class="btn-group btn-group-sm mr-2" role="group">
                  <button type="button" class="btn btn-outline-secondary" onclick="insertCodeBlock()" title="Code Block">
                    <i class="fas fa-code"></i> Block
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertTopic()" title="Add Topic Section">
                    <i class="fas fa-list"></i> Topic
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertCommandSection()" title="Add Command Section">
                    <i class="fas fa-terminal"></i> Command
                  </button>
                </div>
                
                <div class="btn-group btn-group-sm mr-2" role="group">
                  <button type="button" class="btn btn-outline-secondary" onclick="insertTable()" title="Insert Table">
                    <i class="fas fa-table"></i> Table
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertList('ul')" title="Unordered List">
                    <i class="fas fa-list-ul"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertList('ol')" title="Ordered List">
                    <i class="fas fa-list-ol"></i>
                  </button>
                </div>
                
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#previewModal" title="Preview">
                    <i class="fas fa-eye"></i> Preview
                  </button>
                </div>
              </div>
              
              <!-- Textarea -->
              <textarea id="lab_guide_content" name="lab_guide_content" class="form-control" rows="10" 
                        placeholder="Use formatting buttons above or write markdown. Examples:&#10;# Main Topic&#10;## Subtopic&#10;### Lesson&#10;&#10;```command&#10;docker run -it ubuntu&#10;```&#10;&#10;| Column 1 | Column 2 |&#10;|----------|----------|&#10;| Data 1   | Data 2   |"><?= htmlspecialchars($edit_course['lab_guide_content'] ?? '') ?></textarea>
              
              <small class="form-text text-muted mt-2">
                <strong>Quick Tips:</strong><br>
                • Use <code>#</code> for headings (H1), <code>##</code> for H2, <code>###</code> for H3<br>
                • Use <code>\`command\`</code> for inline commands<br>
                • Use code blocks with <code>\`\`\`</code> for multi-line commands<br>
                • Create tables with <code>| Column 1 | Column 2 |</code><br>
                • Use lists with <code>- Item</code> (unordered) or <code>1. Item</code> (ordered)<br>
                • Click Preview to see formatted output
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" size="lg">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-eye"></i> Lab Guide Preview</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="previewContent" style="max-height: 600px; overflow-y: auto;">
        <p class="text-muted">Preview loading...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
// Insert formatted text into textarea
function insertFormat(before, after) {
    const textarea = document.getElementById('lab_guide_content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end) || 'text';
    const beforeText = textarea.value.substring(0, start);
    const afterText = textarea.value.substring(end);
    
    textarea.value = beforeText + before + selectedText + after + afterText;
    textarea.focus();
    textarea.selectionStart = start + before.length;
    textarea.selectionEnd = start + before.length + selectedText.length;
}

// Insert code block
function insertCodeBlock() {
    const textarea = document.getElementById('lab_guide_content');
    const codeBlock = '\n```\ncommand here\n```\n';
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + codeBlock + textarea.value.substring(start);
    textarea.focus();
}

// Insert topic section
function insertTopic() {
    const textarea = document.getElementById('lab_guide_content');
    const topic = '\n## Topic Title\n**Description or introduction goes here.**\n\n### Subtopic\nDetails here...\n';
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + topic + textarea.value.substring(start);
    textarea.focus();
}

// Insert command section
function insertCommandSection() {
    const textarea = document.getElementById('lab_guide_content');
    const command = '\n### Command: Description\n**Purpose:** Explain what this command does\n\n```\ncommand-syntax here\n```\n\n**Output:**\n```\nExpected output\n```\n';
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + command + textarea.value.substring(start);
    textarea.focus();
}

// Insert table
function insertTable() {
    const textarea = document.getElementById('lab_guide_content');
    const table = '\n| Column 1 | Column 2 | Column 3 |\n|----------|----------|----------|\n| Data 1   | Data 2   | Data 3   |\n| Data 4   | Data 5   | Data 6   |\n\n';
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + table + textarea.value.substring(start);
    textarea.focus();
}

// Insert list
function insertList(type) {
    const textarea = document.getElementById('lab_guide_content');
    let list = '';
    if (type === 'ul') {
        list = '\n- Item 1\n- Item 2\n- Item 3\n';
    } else {
        list = '\n1. First item\n2. Second item\n3. Third item\n';
    }
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + list + textarea.value.substring(start);
    textarea.focus();
}

// Parse and display markdown with custom styling
function parseMarkdownToHTML(markdown) {
    // Configure marked options
    marked.setOptions({
        breaks: true,
        gfm: true,
        tables: true
    });
    
    let html = marked.parse(markdown);
    
    // Add Bootstrap styling to tables
    html = html.replace(/<table>/g, '<table class="table table-bordered table-striped">');
    html = html.replace(/<thead>/g, '<thead class="table-dark">');
    
    // Add Bootstrap styling to code blocks
    html = html.replace(/<pre><code/g, '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; border-left: 3px solid #007bff;"><code style="color: #333;"');
    html = html.replace(/<code>/g, '<code style="background: #f1f1f1; padding: 2px 4px; border-radius: 3px; color: #d63384; font-family: monospace;">');
    
    // Add styling to headings
    html = html.replace(/<h1>/g, '<h1 style="border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-top: 20px;">');
    html = html.replace(/<h2>/g, '<h2 style="border-bottom: 1px solid #0056b3; padding-bottom: 8px; margin-top: 18px;">');
    html = html.replace(/<h3>/g, '<h3 style="color: #0056b3; margin-top: 15px;">');
    
    // Add styling to blockquotes
    html = html.replace(/<blockquote>/g, '<blockquote style="border-left: 4px solid #ffc107; padding: 10px 15px; background: #fffbf0; margin: 10px 0;">');
    
    // Add styling to lists
    html = html.replace(/<li>/g, '<li style="margin-bottom: 5px;">');
    
    return html;
}

// Show preview
document.addEventListener('DOMContentLoaded', function() {
    // Check if preview modal exists
    const previewBtn = document.querySelector('[data-target="#previewModal"]');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const content = document.getElementById('lab_guide_content').value;
            const previewContent = document.getElementById('previewContent');
            if (content.trim()) {
                previewContent.innerHTML = parseMarkdownToHTML(content);
            } else {
                previewContent.innerHTML = '<p class="text-muted"><i class="fas fa-info-circle"></i> No content to preview</p>';
            }
        });
    }
});
</script>

</html>
