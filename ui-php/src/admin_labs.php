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
$page_title = "Lab Management";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $id = $_POST['id'] ?? null;
            $course_id = (int)$_POST['course_id'];
            $lab_name = $db->real_escape_string($_POST['lab_name']);
            $server_id = (int)$_POST['server_id'];
            $provision_script_path = $db->real_escape_string($_POST['provision_script_path']);
            $cleanup_script_path = $db->real_escape_string($_POST['cleanup_script_path']);
            $duration_minutes = (int)$_POST['duration_minutes'];
            $max_concurrent_users = (int)$_POST['max_concurrent_users'];
            $active = isset($_POST['active']) ? 1 : 0;
            
            if ($id) {
                $db->query("UPDATE labs SET course_id=$course_id, lab_name='$lab_name', server_id=$server_id, 
                           provision_script_path='$provision_script_path', cleanup_script_path='$cleanup_script_path',
                           duration_minutes=$duration_minutes, max_concurrent_users=$max_concurrent_users, 
                           active=$active WHERE id=$id");
                $message = "Lab updated successfully!";
            } else {
                $db->query("INSERT INTO labs (course_id, lab_name, server_id, 
                           provision_script_path, cleanup_script_path, duration_minutes, max_concurrent_users, active) 
                           VALUES ($course_id, '$lab_name', $server_id, 
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

// Get all labs with course names and server details
$labs = $db->query("SELECT l.*, c.name as course_name, s.ip_address, s.ssh_user, s.ssh_password,
    (SELECT COUNT(*) FROM lab_sessions WHERE lab_id = l.id AND status='ACTIVE') as active_sessions 
    FROM labs l 
    LEFT JOIN courses c ON l.course_id = c.id 
    LEFT JOIN servers s ON l.server_id = s.id 
    ORDER BY l.created_at DESC");

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden;">
        <!-- Content Header -->
        <div class="content-header" style="flex: 0 0 auto;">
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
        <div class="content" style="flex: 1; overflow-y: auto;">
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
                            <h5><i class="fas fa-server"></i> Select Server (Provisioner/Bastion)</h5>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Server *</label>
                                        <select name="server_id" class="form-control" required>
                                            <option value="">-- Select a Server --</option>
                                            <?php 
                                            $servers = $db->query("SELECT id, hostname, ip_address, ssh_user FROM servers WHERE enabled = 1 ORDER BY hostname");
                                            while ($server = $servers->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $server['id'] ?>" <?= ($edit_lab && $edit_lab['server_id'] == $server['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($server['hostname']) ?> (<?= htmlspecialchars($server['ip_address']) ?>) - <?= htmlspecialchars($server['ssh_user']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <small class="text-muted">Choose a server from <a href="admin_provisioners.php" target="_blank">Server Management</a></small>
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
                                        <a href="?edit=<?= $lab['id'] ?>" class="btn btn-sm btn-info" title="Edit lab">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-success" onclick="openTerminal('<?= htmlspecialchars($lab['ip_address']) ?>', '<?= htmlspecialchars($lab['ssh_user']) ?>', '<?= htmlspecialchars($lab['ssh_password']) ?>')" title="Open terminal access">
                                            <i class="fas fa-terminal"></i> Terminal
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $lab['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Delete this lab?')" title="Delete lab">
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
    <?php include 'includes/footer.php'; ?>
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
    
    // Terminal access function for admin
    function openTerminal(host, user, password) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; display: flex; flex-direction: column;';
        modal.innerHTML = `
            <div style="background: #2c3e50; color: white; padding: 10px; display: flex; justify-content: space-between; flex: 0 0 auto;">
                <span><i class="fas fa-terminal"></i> Terminal - ${host}</span>
                <button onclick="this.closest('[data-modal]').remove()" style="background: #e74c3c; border: none; color: white; padding: 5px 15px; cursor: pointer; border-radius: 3px;">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
            <div id="terminal" style="flex: 1; background: #000; overflow: hidden;"></div>
        `;
        modal.setAttribute('data-modal', '1');
        document.body.appendChild(modal);
        
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/xterm@4.19.0/css/xterm.css';
        document.head.appendChild(link);
        
        const s1 = document.createElement('script');
        s1.src = 'https://cdn.jsdelivr.net/npm/xterm@4.19.0/lib/xterm.js';
        s1.onload = () => {
            const s2 = document.createElement('script');
            s2.src = 'https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.5.0/lib/xterm-addon-fit.js';
            s2.onload = () => {
                const term = new Terminal({cursorBlink: true, fontSize: 12, fontFamily: 'Courier New'});
                const fitAddon = new FitAddon.FitAddon();
                term.loadAddon(fitAddon);
                term.open(document.getElementById('terminal'));
                setTimeout(() => fitAddon.fit(), 100);
                window.addEventListener('resize', () => fitAddon.fit());
                
                let ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal?' + 
                    'host=' + encodeURIComponent(host) + 
                    '&user=' + encodeURIComponent(user) +
                    '&password=' + encodeURIComponent(password));
                
                ws.onopen = () => {
                    term.write('\r\n🔗 Connecting to ' + host + '...\r\n');
                };
                ws.onmessage = (e) => term.write(e.data);
                ws.onerror = (err) => {
                    term.write('\r\n❌ Connection error: ' + (err.message || 'Unknown error') + '\r\n');
                    console.error('WebSocket error:', err);
                };
                ws.onclose = () => term.write('\r\n❌ Connection closed\r\n');
                
                term.onData(d => {
                    if (ws.readyState === WebSocket.OPEN) {
                        ws.send(d);
                    }
                });
            };
            document.head.appendChild(s2);
        };
        document.head.appendChild(s1);
    }
</script>
</body>
</html>
