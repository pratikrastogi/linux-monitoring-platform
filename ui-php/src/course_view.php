<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) die("Connection failed");

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$course_id) {
    header('Location: index.php');
    exit;
}

// Get course details
$course = $db->query("SELECT * FROM courses WHERE id=$course_id AND active=1")->fetch_assoc();

if (!$course) {
    header('Location: index.php');
    exit;
}

// Get labs for this course
$labs = $db->query("SELECT l.id, l.lab_name, l.duration_minutes, l.max_concurrent_users, l.active,
    s.hostname FROM labs l LEFT JOIN servers s ON l.server_id = s.id 
    WHERE l.course_id=$course_id AND l.active=1 ORDER BY l.id");

$page_title = htmlspecialchars($course['name']);
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
        <div class="col-sm-8">
          <h1 class="m-0">
            <i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($course['name']) ?>
          </h1>
          <small class="text-muted">Duration: <?= intval($course['duration_minutes']) ?> minutes</small>
        </div>
        <div class="col-sm-4">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Course</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <!-- Course Content -->
        <div class="col-md-8">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-book-open"></i> Course Overview</h3>
            </div>
            <div class="card-body" id="courseContent">
              <?= renderCourseContent($course['description']) ?>
            </div>
          </div>

          <?php if ($course['lab_guide_content']): ?>
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Lab Guide</h3>
            </div>
            <div class="card-body" id="labGuideContent">
              <?= renderCourseContent($course['lab_guide_content']) ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Sidebar: Labs -->
        <div class="col-md-4">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-flask"></i> Available Labs</h3>
            </div>
            <div class="card-body p-0">
              <?php if ($labs && $labs->num_rows > 0): ?>
              <div class="list-group">
                <?php while ($lab = $labs->fetch_assoc()): ?>
                <a href="labs.php?id=<?= $lab['id'] ?>" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1"><?= htmlspecialchars($lab['lab_name']) ?></h6>
                    <small class="text-muted"><?= $lab['duration_minutes'] ?> min</small>
                  </div>
                  <p class="mb-1"><small><i class="fas fa-server"></i> <?= htmlspecialchars($lab['hostname'] ?? 'N/A') ?></small></p>
                  <?php if ($lab['active']): ?>
                  <small class="badge badge-success">Available</small>
                  <?php else: ?>
                  <small class="badge badge-secondary">Inactive</small>
                  <?php endif; ?>
                </a>
                <?php endwhile; ?>
              </div>
              <?php else: ?>
              <p class="text-muted p-3">No labs available for this course yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
  hljs.highlightAll();

  // Copy to clipboard functionality
  document.querySelectorAll('.cmd-block').forEach(block => {
    const copyBtn = document.createElement('button');
    copyBtn.className = 'btn btn-sm btn-outline-secondary cmd-copy';
    copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
    copyBtn.onclick = (e) => {
      e.preventDefault();
      const code = block.querySelector('code').innerText;
      navigator.clipboard.writeText(code).then(() => {
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => copyBtn.innerHTML = originalText, 2000);
      });
    };
    block.appendChild(copyBtn);
  });

  // Highlight command blocks
  document.querySelectorAll('.cmd-block code').forEach(block => {
    hljs.highlightElement(block);
  });
</script>

<style>
  .cmd-block {
    position: relative;
    background: #1e1e1e;
    border: 1px solid #444;
    border-radius: 4px;
    padding: 15px;
    margin: 10px 0;
    font-family: 'Courier New', monospace;
    overflow-x: auto;
  }

  .cmd-block code {
    color: #00ff00;
    background: none;
    border: none;
    padding: 0;
    font-size: 13px;
  }

  .cmd-copy {
    position: absolute;
    top: 8px;
    right: 8px;
    opacity: 0.7;
    transition: opacity 0.2s;
  }

  .cmd-copy:hover {
    opacity: 1;
  }

  .card-body {
    line-height: 1.8;
  }

  .card-body p {
    margin-bottom: 10px;
  }

  .list-group-item {
    border: none;
    padding: 12px 15px;
    border-bottom: 1px solid #e0e0e0;
  }

  .list-group-item:last-child {
    border-bottom: none;
  }

  .list-group-item:hover {
    background-color: #f8f9fa;
  }
</style>

</body>
</html>

<?php

/**
 * Parse course content and render as formatted markdown with styled elements
 */
function renderCourseContent($content) {
    if (!$content) return '<p class="text-muted">No content available.</p>';
    
    // Use a simple markdown to HTML converter
    $html = markdownToHtml($content);
    
    return $html;
}

/**
 * Convert markdown to HTML with Bootstrap styling
 */
function markdownToHtml($markdown) {
    // Escape HTML special characters first but preserve markdown
    $markdown = htmlspecialchars($markdown, ENT_QUOTES);
    
    // Convert headers
    $markdown = preg_replace('/^### (.*?)$/m', '<h3 style="color: #0056b3; margin-top: 15px; font-weight: 600;">$1</h3>', $markdown);
    $markdown = preg_replace('/^## (.*?)$/m', '<h2 style="border-bottom: 1px solid #0056b3; padding-bottom: 8px; margin-top: 20px; font-weight: 600;">$1</h2>', $markdown);
    $markdown = preg_replace('/^# (.*?)$/m', '<h1 style="border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-top: 25px; font-weight: 700;">$1</h1>', $markdown);
    
    // Convert bold
    $markdown = preg_replace('/\*\*(.*?)\*\*/s', '<strong style="color: #333; font-weight: 600;">$1</strong>', $markdown);
    
    // Convert italic
    $markdown = preg_replace('/_([^_]+)_/', '<em style="color: #666;">$1</em>', $markdown);
    
    // Convert code blocks (```code```)
    $markdown = preg_replace_callback('/```(.*?)```/s', function($matches) {
        $code = trim($matches[1]);
        $html = '<div style="background: #f5f5f5; padding: 12px; border-radius: 4px; border-left: 3px solid #007bff; margin: 10px 0; overflow-x: auto;">';
        $html .= '<code style="color: #333; font-family: monospace; font-size: 13px; line-height: 1.4;">' . $code . '</code>';
        $html .= '</div>';
        return $html;
    }, $markdown);
    
    // Convert inline code
    $markdown = preg_replace('/`([^`]+)`/', '<code style="background: #f1f1f1; padding: 2px 6px; border-radius: 3px; color: #d63384; font-family: monospace; font-size: 13px;">$1</code>', $markdown);
    
    // Convert tables
    $markdown = preg_replace_callback('/(\|.*?\|.*?\n\|[\s\-\|:]*\n(?:\|.*?\n)*)/s', function($matches) {
        $table = $matches[0];
        $rows = explode("\n", trim($table));
        $html = '<table class="table table-bordered table-striped" style="margin: 15px 0;">';
        $isHeader = true;
        
        foreach ($rows as $row) {
            if (preg_match('/^[\s\|-]*$/', $row)) continue; // Skip separator
            
            $cells = array_filter(explode('|', $row), function($cell) { return trim($cell) !== ''; });
            if (empty($cells)) continue;
            
            $tag = $isHeader ? 'th' : 'td';
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<' . $tag . ' style="' . ($isHeader ? 'background: #343a40; color: white; font-weight: 600;' : '') . '">' . trim($cell) . '</' . $tag . '>';
            }
            $html .= '</tr>';
            $isHeader = false;
        }
        
        $html .= '</table>';
        return $html;
    }, $markdown);
    
    // Convert unordered lists
    $markdown = preg_replace_callback('/^(\s*-\s+.*?)(?=^[^-]|\Z)/ms', function($matches) {
        $items = preg_split('/^\s*-\s+/m', $matches[1]);
        $html = '<ul style="margin-left: 20px; margin: 10px 0;">';
        foreach ($items as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $html .= '<li style="margin-bottom: 5px;">' . $item . '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }, $markdown);
    
    // Convert ordered lists
    $markdown = preg_replace_callback('/^(\s*\d+\.\s+.*?)(?=^[^\d]|\Z)/ms', function($matches) {
        $items = preg_split('/^\s*\d+\.\s+/m', $matches[1]);
        $html = '<ol style="margin-left: 20px; margin: 10px 0;">';
        foreach ($items as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $html .= '<li style="margin-bottom: 5px;">' . $item . '</li>';
            }
        }
        $html .= '</ol>';
        return $html;
    }, $markdown);
    
    // Convert line breaks to paragraphs
    $html = '';
    $paragraphs = preg_split('/\n\n+/', $markdown);
    foreach ($paragraphs as $para) {
        $para = trim($para);
        if (!empty($para)) {
            // Check if paragraph contains block-level elements
            if (preg_match('/<(h[1-6]|div|table|ul|ol)/', $para)) {
                $html .= $para . "\n";
            } else {
                $html .= '<p style="margin-bottom: 12px; line-height: 1.6;">' . nl2br($para) . '</p>';
            }
        }
    }
    
    return $html;
}

?>
