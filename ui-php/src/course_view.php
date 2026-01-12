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
 * Parse course content and convert commands to copyable command blocks
 * Commands are sections enclosed in backticks, code blocks, or ending with semicolon
 */
function renderCourseContent($content) {
    if (!$content) return '<p class="text-muted">No content available.</p>';
    
    // Escape HTML but preserve line breaks
    $content = htmlspecialchars($content, ENT_QUOTES);
    
    // Convert markdown-style code blocks (```command```) to copyable blocks
    $content = preg_replace_callback('/```(.+?)```/s', function($matches) {
        $cmd = trim($matches[1]);
        // Detect if it looks like a command (starts with common command keywords)
        $cmdKeywords = ['docker', 'kubectl', 'npm', 'git', 'curl', 'wget', 'ssh', 'scp', 'helm', 'make', 'python', 'node', 'java', 'grep', 'sed', 'awk', 'find', 'cat', 'echo', 'apt', 'yum', 'brew', 'systemctl', 'service'];
        $isCommand = false;
        foreach ($cmdKeywords as $kw) {
            if (stripos($cmd, $kw) === 0) {
                $isCommand = true;
                break;
            }
        }
        
        if ($isCommand) {
            return '<div class="cmd-block"><code>' . $cmd . '</code></div>';
        }
        return '<pre><code>' . $cmd . '</code></pre>';
    }, $content);
    
    // Convert inline backtick commands (single line ending with ; or containing typical command chars)
    $content = preg_replace_callback('/`([^`]+)`/', function($matches) {
        $text = $matches[1];
        // Check if it looks like a command
        if (strpos($text, ';') !== false || 
            preg_match('/^[a-z\-]+\s+/', $text) || 
            strpos($text, '|') !== false ||
            strpos($text, '>') !== false) {
            return '<div class="cmd-block" style="display:inline-block; width:100%; margin: 8px 0;"><code>' . $text . '</code></div>';
        }
        return '<code class="bg-light" style="padding: 2px 6px; border-radius: 3px;">' . $text . '</code>';
    }, $content);
    
    // Convert lines ending with semicolon to command blocks (if indented or prefixed with common commands)
    $lines = explode("\n", $content);
    $output = [];
    $inCommandBlock = false;
    $commandBuffer = '';
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Check if line looks like a command (starts with command keyword or contains pipes/redirects)
        if (preg_match('/^(docker|kubectl|npm|git|curl|wget|ssh|scp|helm|make|python|node|java|grep|sed|awk|find|cat|echo|apt|yum|brew|systemctl)[\s\-]/i', $trimmed)) {
            if (!$inCommandBlock && $commandBuffer) {
                $output[] = $commandBuffer;
                $commandBuffer = '';
            }
            $inCommandBlock = true;
            $commandBuffer .= $line . "\n";
        } elseif ($inCommandBlock && (empty($trimmed) || substr($trimmed, -1) === ';')) {
            $commandBuffer .= $line . "\n";
            if (substr($trimmed, -1) === ';' || empty($trimmed)) {
                $cmd = trim($commandBuffer);
                $output[] = '<div class="cmd-block"><code>' . $cmd . '</code></div>';
                $commandBuffer = '';
                $inCommandBlock = false;
            }
        } else {
            if ($inCommandBlock && !empty($trimmed)) {
                $commandBuffer .= $line . "\n";
            } else {
                if ($commandBuffer) {
                    $cmd = trim($commandBuffer);
                    $output[] = '<div class="cmd-block"><code>' . $cmd . '</code></div>';
                    $commandBuffer = '';
                    $inCommandBlock = false;
                }
                $output[] = $line;
            }
        }
    }
    
    if ($commandBuffer) {
        $cmd = trim($commandBuffer);
        $output[] = '<div class="cmd-block"><code>' . $cmd . '</code></div>';
    }
    
    $html = implode("\n", $output);
    
    // Convert line breaks to <br> for display
    $html = nl2br($html);
    
    // Convert paragraphs (double line breaks)
    $html = str_replace("<br />\n<br />", "</p>\n<p>", $html);
    
    if (strpos($html, '<p>') === false && strpos($html, '<div') === false) {
        $html = '<p>' . $html . '</p>';
    }
    
    return $html;
}

?>
