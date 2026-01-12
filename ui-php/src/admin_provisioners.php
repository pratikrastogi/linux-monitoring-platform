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
$page_title = "Server Management";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $id = $_POST['id'] ?? null;
            $hostname = $db->real_escape_string($_POST['hostname']);
            $ip_address = $db->real_escape_string($_POST['ip_address']);
            $ssh_user = $db->real_escape_string($_POST['ssh_user']);
            $ssh_password = $db->real_escape_string($_POST['ssh_password']);
            $enabled = isset($_POST['enabled']) ? 1 : 0;
            $purpose = $db->real_escape_string($_POST['purpose']);
            
            if ($id) {
                $db->query("UPDATE servers SET hostname='$hostname', ip_address='$ip_address', ssh_user='$ssh_user', 
                           ssh_password='$ssh_password', enabled=$enabled, purpose='$purpose' WHERE id=$id");
                $message = "Server updated successfully!";
            } else {
                $db->query("INSERT INTO servers (hostname, ip_address, ssh_user, ssh_password, enabled, purpose) 
                           VALUES ('$hostname', '$ip_address', '$ssh_user', '$ssh_password', $enabled, '$purpose')");
                $message = "Server added successfully!";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            
            // Check if server is mapped to any labs
            $check = $db->query("SELECT COUNT(*) as lab_count FROM labs WHERE server_id=$id");
            $result = $check->fetch_assoc();
            
            if ($result['lab_count'] > 0) {
                $error = "Cannot delete this server! It is mapped to " . $result['lab_count'] . " lab(s). Remove the server from labs first.";
            } else {
                $db->query("DELETE FROM servers WHERE id=$id");
                $message = "Server deleted successfully!";
            }
        }
    }
}

// Get server for editing
$edit_server = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $db->query("SELECT * FROM servers WHERE id = $edit_id");
    $edit_server = $result->fetch_assoc();
}

// Get all servers
$servers = $db->query("SELECT s.*, 
    (SELECT COUNT(*) FROM labs WHERE server_id = s.id) as mapped_labs
    FROM servers s ORDER BY s.added_on DESC");

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-wrapper" style="display: flex; flex-direction: column; overflow: hidden;">
  <div class="content-header" style="flex: 0 0 auto;">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-server"></i> Server Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Servers</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  
  <div class="content" style="flex: 1; overflow-y: auto;">
    <div class="container-fluid">
      <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-check"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <!-- Add/Edit Server Form -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-plus-circle"></i> <?= $edit_server ? 'Edit Server' : 'Add New Server' ?></h3>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="<?= $edit_server ? 'update' : 'create' ?>">
            <?php if ($edit_server): ?>
              <input type="hidden" name="id" value="<?= $edit_server['id'] ?>">
            <?php endif; ?>
            
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label><i class="fas fa-globe"></i> Hostname / IP *</label>
                  <input type="text" name="hostname" class="form-control" placeholder="lab-server-01"
                         value="<?= $edit_server ? htmlspecialchars($edit_server['hostname']) : '' ?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label><i class="fas fa-network-wired"></i> IP Address *</label>
                  <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.46"
                         value="<?= $edit_server ? htmlspecialchars($edit_server['ip_address']) : '' ?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label><i class="fas fa-tag"></i> Purpose</label>
                  <select name="purpose" class="form-control">
                    <option value="bastion" <?= ($edit_server && $edit_server['purpose'] === 'bastion') ? 'selected' : '' ?>>Bastion / Provisioner</option>
                    <option value="monitoring" <?= ($edit_server && $edit_server['purpose'] === 'monitoring') ? 'selected' : '' ?>>Monitoring Target</option>
                    <option value="general" <?= ($edit_server && $edit_server['purpose'] === 'general') ? 'selected' : '' ?>>General Purpose</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label><i class="fas fa-user"></i> SSH Username *</label>
                  <input type="text" name="ssh_user" class="form-control" placeholder="root"
                         value="<?= $edit_server ? htmlspecialchars($edit_server['ssh_user']) : '' ?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label><i class="fas fa-key"></i> SSH Password *</label>
                  <input type="password" name="ssh_password" class="form-control"
                         value="<?= $edit_server ? htmlspecialchars($edit_server['ssh_password']) : '' ?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="enabled" name="enabled"
                           <?= (!$edit_server || $edit_server['enabled']) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="enabled">
                      <i class="fas fa-check"></i> Enabled
                    </label>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $edit_server ? 'Update Server' : 'Add Server' ?>
              </button>
              <?php if ($edit_server): ?>
                <a href="admin_provisioners.php" class="btn btn-secondary">
                  <i class="fas fa-times"></i> Cancel
                </a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Servers List -->
      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list"></i> All Servers</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Hostname</th>
                <th>IP Address</th>
                <th>Purpose</th>
                <th>SSH User</th>
                <th>Mapped Labs</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($server = $servers->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($server['hostname']) ?></strong></td>
                <td><?= htmlspecialchars($server['ip_address']) ?></td>
                <td><span class="badge badge-info"><?= htmlspecialchars($server['purpose']) ?></span></td>
                <td><?= htmlspecialchars($server['ssh_user']) ?></td>
                <td>
                  <?php if ($server['mapped_labs'] > 0): ?>
                    <span class="badge badge-warning"><?= $server['mapped_labs'] ?> lab(s)</span>
                  <?php else: ?>
                    <span class="badge badge-light">Unmapped</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($server['enabled']): ?>
                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Enabled</span>
                  <?php else: ?>
                    <span class="badge badge-secondary"><i class="fas fa-ban"></i> Disabled</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button class="btn btn-sm btn-success" onclick="openTerminal(<?= $server['id'] ?>, '<?= htmlspecialchars($server['hostname']) ?>', '<?= htmlspecialchars($server['ssh_user']) ?>', '<?= htmlspecialchars($server['ssh_password']) ?>')" title="Open terminal access">
                    <i class="fas fa-terminal"></i> Connect
                  </button>
                  <a href="?edit=<?= $server['id'] ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <?php if ($server['mapped_labs'] == 0): ?>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $server['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this server?')">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    </form>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary" disabled title="Cannot delete - server is in use">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  <?php endif; ?>
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

<?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    // Terminal access function for admin - SECURE (no passwords in URL)
    function openTerminal(serverId, hostname, sshUser, sshPassword) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; display: flex; flex-direction: column;';
        modal.innerHTML = `
            <div style="background: #2c3e50; color: white; padding: 10px; display: flex; justify-content: space-between; align-items: center; flex: 0 0 auto;">
                <span><i class="fas fa-terminal"></i> Terminal - ${hostname}</span>
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
                
                // SECURE: server_id and user in URL, password sent in JSON message
                let ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal?server_id=' + serverId + '&user=' + encodeURIComponent(sshUser));
                
                let passwordSent = false;
                ws.onopen = () => {
                    term.write('\r\n🔗 Connecting to ' + hostname + '...\r\n');
                    // Send password as JSON
                    if (!passwordSent) {
                        ws.send(JSON.stringify({ password: sshPassword }));
                        passwordSent = true;
                    }
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
