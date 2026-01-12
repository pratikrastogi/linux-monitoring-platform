<?php
session_start();
require_once 'auth.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$lab_id = isset($_GET['lab_id']) ? (int)$_GET['lab_id'] : 0;

if (!$lab_id) {
    header('Location: admin_labs.php');
    exit;
}

// Get lab details
$lab = $db->query("SELECT l.id, l.lab_name, l.course_id, l.server_id, l.duration_minutes, l.max_concurrent_users, l.active, l.provision_script_path, l.cleanup_script_path,
    s.hostname, s.ip_address, s.ssh_user, s.ssh_password, s.ssh_port, c.name as course_name
    FROM labs l
    LEFT JOIN servers s ON l.server_id = s.id
    LEFT JOIN courses c ON l.course_id = c.id
    WHERE l.id = $lab_id")->fetch_assoc();

if (!$lab) {
    header('Location: admin_labs.php');
    exit;
}

$page_title = "Lab Terminal: " . $lab['lab_name'];
include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden; height: 100vh;">
        <!-- Content Header -->
        <div class="content-header" style="flex: 0 0 auto; border-bottom: 2px solid #dee2e6;">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <h1 class="m-0">
                            <i class="fas fa-flask"></i> 
                            <?= htmlspecialchars($lab['lab_name']) ?>
                        </h1>
                        <small class="text-muted ml-2">
                            <strong><?= htmlspecialchars($lab['course_name'] ?? 'N/A') ?></strong> | 
                            Server: <?= htmlspecialchars($lab['hostname'] ?? 'N/A') ?> 
                            (<?= htmlspecialchars($lab['ip_address'] ?? 'N/A') ?>)
                        </small>
                    </div>
                    <div class="col-sm-4">
                        <a href="admin_labs.php" class="btn btn-danger float-right">
                            <i class="fas fa-times"></i> Exit Lab
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content - Split View -->
        <div style="flex: 1; display: flex; overflow: hidden; gap: 0;">
            
            <!-- Left Side: Terminal (50%) -->
            <div style="flex: 1; display: flex; flex-direction: column; background: #000; border-right: 2px solid #dee2e6; overflow: hidden;">
                <div style="background: #2c3e50; color: white; padding: 8px 12px; font-weight: bold; flex: 0 0 auto; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fas fa-terminal"></i> Terminal - <?= htmlspecialchars($lab['hostname'] ?? 'Lab Server') ?></span>
                    <small style="font-weight: normal; opacity: 0.8;">Server ID: <?= $lab['server_id'] ?></small>
                </div>
                <div id="terminal" style="flex: 1; background: #000; overflow: hidden;"></div>
            </div>
            
            <!-- Right Side: Lab Guide (50%) -->
            <div style="flex: 1; display: flex; flex-direction: column; background: #f8f9fa; overflow: hidden;">
                <div style="background: #f1f3f4; padding: 8px 12px; font-weight: bold; border-bottom: 1px solid #dee2e6; flex: 0 0 auto;">
                    <i class="fas fa-book"></i> Lab Information & Guide
                </div>
                
                <!-- Guide Content -->
                <div style="flex: 1; overflow-y: auto; padding: 20px;">
                    <?php if (!empty($lab['guide_url'])): ?>
                        <!-- Embedded Guide from URL -->
                        <div class="alert alert-info mb-3">
                            <strong>Lab Guide (External):</strong>
                        </div>
                        <iframe src="<?= htmlspecialchars($lab['guide_url']) ?>" style="width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 4px;"></iframe>
                    <?php elseif (!empty($lab['lab_guide'])): ?>
                        <!-- Lab Guide Content -->
                        <div class="lab-guide-content">
                            <?= nl2br(htmlspecialchars($lab['lab_guide'])) ?>
                        </div>
                    <?php else: ?>
                        <!-- Default Lab Information -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Lab Information</h5>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Lab Specifications -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-cogs"></i> Lab Specifications</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Lab Name:</strong></td>
                                    <td><?= htmlspecialchars($lab['lab_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Course:</strong></td>
                                    <td><?= htmlspecialchars($lab['course_name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Server:</strong></td>
                                    <td><?= htmlspecialchars($lab['hostname'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Server IP:</strong></td>
                                    <td><code><?= htmlspecialchars($lab['ip_address'] ?? 'N/A') ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>SSH User:</strong></td>
                                    <td><code><?= htmlspecialchars($lab['ssh_user'] ?? 'N/A') ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>SSH Port:</strong></td>
                                    <td><?= $lab['ssh_port'] ?? 22 ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td><?= $lab['duration_minutes'] ?> minutes</td>
                                </tr>
                                <tr>
                                    <td><strong>Max Concurrent Users:</strong></td>
                                    <td><?= $lab['max_concurrent_users'] ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Scripts -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-code"></i> Configuration Scripts</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="small font-weight-bold">Provision Script:</label>
                                <code class="d-block p-2 bg-light rounded"><?= htmlspecialchars($lab['provision_script_path']) ?></code>
                            </div>
                            <div>
                                <label class="small font-weight-bold">Cleanup Script:</label>
                                <code class="d-block p-2 bg-light rounded"><?= htmlspecialchars($lab['cleanup_script_path']) ?></code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- xterm.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@4.19.0/css/xterm.css">
<script src="https://cdn.jsdelivr.net/npm/xterm@4.19.0/lib/xterm.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.5.0/lib/xterm-addon-fit.js"></script>

<script>
$(document).ready(function() {
    initializeTerminal();
});

function initializeTerminal() {
    const term = new Terminal({
        cursorBlink: true,
        fontSize: 12,
        fontFamily: 'Courier New',
        theme: {
            background: '#000000',
            foreground: '#ffffff'
        }
    });
    
    const fitAddon = new FitAddon.FitAddon();
    term.loadAddon(fitAddon);
    term.open(document.getElementById('terminal'));
    
    // Fit terminal to container
    setTimeout(() => fitAddon.fit(), 100);
    
    // Responsive resize
    window.addEventListener('resize', () => {
        try {
            fitAddon.fit();
        } catch (e) {
            console.error('Fit error:', e);
        }
    });
    
    // Connect to secure terminal gateway
    const serverId = <?= $lab['server_id'] ?>;
    const labName = "<?= htmlspecialchars($lab['lab_name']) ?>";
    const sshUser = "<?= htmlspecialchars($lab['ssh_user'] ?? 'root') ?>";
    const sshPassword = "<?= htmlspecialchars($lab['ssh_password'] ?? '') ?>";
    const sshPort = <?= $lab['ssh_port'] ?? 22 ?>;
    
    if (!serverId) {
        term.write('\r\n❌ No server assigned to this lab\r\n');
        return;
    }
    
    // SECURE: server_id and user in URL, password sent in JSON message
    let ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal?server_id=' + serverId + '&user=' + encodeURIComponent(sshUser));
    
    ws.onopen = () => {
        term.write('\r\n🔗 Connecting to lab server (' + labName + ')...\r\n');
        // Send password as JSON
        ws.send(JSON.stringify({ password: sshPassword }));
    };
    
    ws.onmessage = (e) => {
        term.write(e.data);
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
</script>

<style>
    .lab-guide-content {
        color: #333;
        line-height: 1.6;
        font-size: 14px;
    }
    
    .lab-guide-content h1,
    .lab-guide-content h2,
    .lab-guide-content h3,
    .lab-guide-content h4,
    .lab-guide-content h5,
    .lab-guide-content h6 {
        margin-top: 15px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #333;
    }
    
    .lab-guide-content code {
        background: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        color: #d63384;
    }
    
    .lab-guide-content pre {
        background: #f5f5f5;
        padding: 12px;
        border-radius: 4px;
        overflow-x: auto;
        border-left: 3px solid #007bff;
        font-size: 12px;
    }
    
    .lab-guide-content ul,
    .lab-guide-content ol {
        margin-left: 20px;
        margin-bottom: 10px;
    }
    
    .lab-guide-content li {
        margin-bottom: 5px;
    }
</style>

</body>
</html>
