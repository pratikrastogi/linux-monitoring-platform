<?php
session_start();
require_once 'auth.php';

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

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$session_id = (int)$_GET['session_id'];
$uid = $_SESSION['uid'];

// Get lab session with server details for direct SSH connection
$session = $db->query("SELECT 
    ls.id,
    ls.user_id,
    ls.username,
    ls.access_expiry,
    ls.status,
    ls.lab_id,
    l.lab_name,
    l.server_id,
    l.course_id,
    s.ip_address,
    s.ssh_user,
    s.ssh_password,
    s.ssh_port,
    c.lab_guide_content,
    c.name as course_name
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    LEFT JOIN servers s ON l.server_id = s.id
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.id=$session_id AND ls.user_id=$uid AND ls.status='ACTIVE'")->fetch_assoc();

if (!$session) die("Invalid or expired session");

// Verify lab is provisioned (provisioned=1)
if (!$session['username']) {
    die("Lab not yet provisioned. Please try again in a moment.");
}

$remaining = strtotime($session['access_expiry']) - time();
$mins = floor($remaining / 60);

$page_title = "Lab Terminal";
include 'includes/header.php';
?>
<body class="hold-transition sidebar-mini" style="overflow: hidden;">
<div class="wrapper">
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden; height: calc(100vh - 57px);">
  <!-- Lab Header -->
  <div style="flex: 0 0 auto; background: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 12px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h5 class="mb-1">
          <i class="fas fa-flask"></i> 
          <?= htmlspecialchars($session['lab_name']) ?>
          <span class="badge badge-success">ACTIVE</span>
        </h5>
        <small class="text-muted">
          <?= htmlspecialchars($session['course_name']) ?> • 
          Server: <?= htmlspecialchars($session['ip_address']) ?> • 
          Provisioned User: <code><?= htmlspecialchars($session['username']) ?></code> • 
          <i class="fas fa-clock"></i> <span id="countdown" style="font-weight: bold; color: #ff6b6b;"><?= $mins ?></span> minutes remaining
        </small>
      </div>
      <div>
        <a href="my_labs.php" class="btn btn-sm btn-secondary">
          <i class="fas fa-arrow-left"></i> Back to Labs
        </a>
      </div>
    </div>
  </div>
  
  <!-- Split View: Terminal + Guide -->
  <div style="flex: 1; display: flex; overflow: hidden; gap: 0; background: #ddd; padding-left: 5%; padding-bottom: 20%;">
    <!-- Left: Terminal (45%) -->
    <div style="flex: 0 0 45%; display: flex; flex-direction: column; background: #000; border-right: 2px solid #dee2e6; overflow: hidden; max-height: calc(100vh - 180px);">
      <div style="background: #2c3e50; color: white; padding: 8px 12px; font-weight: bold; flex: 0 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="fas fa-terminal"></i> Terminal - <?= htmlspecialchars($session['ip_address'] ?? 'Lab Server') ?></span>
        <small style="font-weight: normal; opacity: 0.8;">Provisioned User: <code><?= htmlspecialchars($session['username']) ?></code></small>
      </div>
      <div id="terminal" style="flex: 1; background: #000; overflow: auto;"></div>
    </div>
    
    <!-- Right: Lab Guide (50%) -->
    <div style="flex: 0 0 50%; display: flex; flex-direction: column; background: #f8f9fa; overflow: hidden; max-height: calc(100vh - 180px);">
      <div style="background: #f1f3f4; padding: 8px 12px; font-weight: bold; border-bottom: 1px solid #dee2e6; flex: 0 0 auto;">
        <i class="fas fa-book"></i> Lab Information & Guide
      </div>
      
      <!-- Guide Content -->
      <div style="flex: 1; overflow-y: auto; padding: 20px;" id="labGuidePanel">
        <?php if (!empty($session['lab_guide_content'])): ?>
          <!-- Lab Guide Content from Course -->
          <div class="lab-guide-content">
            <?= markdownToHtml($session['lab_guide_content']) ?>
          </div>
        <?php else: ?>
          <!-- Default Lab Information -->
          <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Lab Information</h5>
            <p>No lab guide available for this course.</p>
          </div>
        <?php endif; ?>
        
        <!-- Lab Specifications -->
        <div class="card mt-4" style="border: 1px solid #dee2e6;">
          <div class="card-header bg-light" style="background: #f1f3f4;">
            <h6 class="mb-0" style="font-size: 12px;"><i class="fas fa-cogs"></i> Lab Specifications</h6>
          </div>
          <div class="card-body" style="padding: 10px;">
            <table class="table table-sm table-borderless" style="margin: 0; font-size: 12px;">
              <tr>
                <td><strong>Lab:</strong></td>
                <td><?= htmlspecialchars($session['lab_name']) ?></td>
              </tr>
              <tr>
                <td><strong>Course:</strong></td>
                <td><?= htmlspecialchars($session['course_name'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <td><strong>Server IP:</strong></td>
                <td><code style="font-size: 11px;"><?= htmlspecialchars($session['ip_address'] ?? 'N/A') ?></code></td>
              </tr>
              <tr>
                <td><strong>Username:</strong></td>
                <td><code style="font-size: 11px;"><?= htmlspecialchars($session['username']) ?></code></td>
              </tr>
              <tr>
                <td><strong>SSH Port:</strong></td>
                <td><?= $session['ssh_port'] ?? 22 ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@4.19.0/css/xterm.css">
<script src="https://cdn.jsdelivr.net/npm/xterm@4.19.0/lib/xterm.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.5.0/lib/xterm-addon-fit.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    initializeTerminal();
});

function initializeTerminal() {
    // Terminal setup with controlled scrolling
    const term = new Terminal({
        cursorBlink: true,
        fontSize: 13,
        fontFamily: 'Courier New, monospace',
        theme: { background: '#000000', foreground: '#ffffff' },
        scrollback: 1000,  // Reduced scrollback to prevent overflow
        screenKeys: false,
        convertEol: true
    });

    const fitAddon = new FitAddon.FitAddon();
    term.loadAddon(fitAddon);
    term.open(document.getElementById('terminal'));

    // Fit terminal to container
    setTimeout(() => {
        fitAddon.fit();
        term.scrollToBottom();
    }, 100);
    
    // Responsive resize
    window.addEventListener('resize', () => {
        try {
            fitAddon.fit();
            term.scrollToBottom();
        } catch (e) {
            console.error('Fit error:', e);
        }
    });

    // Get server and authentication details
    const serverId = <?= $session['server_id'] ?>;
    const provisionedUser = "<?= htmlspecialchars($session['username']) ?>";  // The actual provisioned username (e.g., user-9-1768241321)
    const sshUser = provisionedUser;  // Use provisioned username for SSH
    const sshPassword = "k8s" + provisionedUser + "@123!";  // Default provisioner password format
    const sshHost = "<?= htmlspecialchars($session['ip_address']) ?>";
    const sshPort = <?= $session['ssh_port'] ?? 22 ?>;

    if (!serverId || !sshHost) {
        term.write('\r\n❌ No server assigned to this lab\r\n');
        return;
    }

    // SECURE: server_id and user in URL, password sent in JSON message
    let ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal?server_id=' + serverId + '&user=' + encodeURIComponent(sshUser));
    
    ws.onopen = () => {
        term.write('\r\n🔗 Connecting to lab server (' + provisionedUser + ')...\r\n');
        // Send password as JSON
        ws.send(JSON.stringify({ password: sshPassword }));
    };
    
    ws.onmessage = (event) => {
        term.write(event.data);
        // Auto-scroll to bottom when receiving data
        term.scrollToBottom();
    };
    
    ws.onerror = (err) => {
        term.write('\r\n❌ Connection error: ' + (err.message || 'Unknown error') + '\r\n');
        console.error('WebSocket error:', err);
    };
    
    ws.onclose = () => {
        term.write('\r\n❌ Connection closed\r\n');
    };
    
    // Send terminal input to gateway
    term.onData(d => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(d);
        }
    });
}

// Countdown timer
let startTime = Date.now();
let totalRemaining = <?= $remaining ?>;
setInterval(() => {
    let elapsed = (Date.now() - startTime) / 1000;
    let remaining = totalRemaining - elapsed;
    let mins = Math.floor(remaining / 60);
    let secs = Math.floor(remaining % 60);
    
    const countdownEl = document.getElementById('countdown');
    if (countdownEl) {
        countdownEl.textContent = Math.max(0, mins);
        // Change color to red when less than 10 minutes
        if (mins < 10) {
            countdownEl.style.color = '#ff6b6b';
        }
    }
    
    if (mins <= 0 && secs <= 0) {
        alert('Session expired! Redirecting to My Labs...');
        location.href = 'my_labs.php';
    }
}, 5000);
</script>

<style>
.lab-guide-content {
    color: #333;
    line-height: 1.8;
    font-size: 14px;
}

.lab-guide-content p {
    margin-bottom: 12px;
    line-height: 1.6;
}

.lab-guide-content h1 {
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-top: 25px;
    font-weight: 700;
    font-size: 24px;
}

.lab-guide-content h2 {
    border-bottom: 1px solid #0056b3;
    padding-bottom: 8px;
    margin-top: 20px;
    font-weight: 600;
    font-size: 20px;
    color: #0056b3;
}

.lab-guide-content h3 {
    color: #0056b3;
    margin-top: 15px;
    font-weight: 600;
    font-size: 18px;
}

.lab-guide-content h4,
.lab-guide-content h5,
.lab-guide-content h6 {
    margin-top: 15px;
    margin-bottom: 8px;
    font-weight: 600;
    color: #222;
}

.lab-guide-content code {
    background: #f1f1f1;
    padding: 2px 6px;
    border-radius: 3px;
    color: #d63384;
    font-family: monospace;
    font-size: 13px;
}

.lab-guide-content pre {
    background: #f5f5f5;
    padding: 12px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
    margin: 10px 0;
    overflow-x: auto;
}

.lab-guide-content pre code {
    color: #333;
    background: none;
    padding: 0;
    font-size: 13px;
    line-height: 1.4;
}

.lab-guide-content ul, .lab-guide-content ol {
    margin-left: 20px;
    margin: 10px 0;
}

.lab-guide-content li {
    margin-bottom: 5px;
}

.lab-guide-content strong {
    color: #333;
    font-weight: 600;
}

.lab-guide-content em {
    color: #666;
}

.lab-guide-content table {
    margin: 15px 0;
}

.lab-guide-content table th {
    background: #343a40;
    color: white;
    font-weight: 600;
}

/* Ensure terminal stays within bounds */
#terminal {
    max-height: 100%;
    overflow-y: auto !important;
}

.xterm-viewport {
    overflow-y: auto !important;
}
</style>
