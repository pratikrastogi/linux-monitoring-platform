<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");

// Get search/filter
$course_filter = $_GET['course'] ?? '';
$where = $course_filter ? "AND c.id = $course_filter" : '';

$labs = $db->query("SELECT c.*, 
    (SELECT COUNT(*) FROM labs WHERE course_id = c.id AND active=1) as lab_count
    FROM courses c WHERE c.status='published' $where ORDER BY c.name");

$page_title = "Browse Labs";
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
          <h1 class="m-0"><i class="fas fa-search"></i> Browse Labs</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Browse Labs</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  
  <section class="content">
    <div class="container-fluid">
      <?php while ($course = $labs->fetch_assoc()): ?>
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book"></i> <?= htmlspecialchars($course['title'] ?? 'N/A') ?></h3>
          </div>
          <div class="card-body">
            <p class="text-muted"><?= htmlspecialchars($course['description'] ?? 'No description available') ?></p>
            <div class="row">
              <?php
              $course_labs = $db->query("SELECT * FROM labs WHERE course_id={$course['id']} AND active=1");
              while ($lab = $course_labs->fetch_assoc()):
              ?>
                <div class="col-md-6 col-lg-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h5 class="card-title"><i class="fas fa-flask"></i> <?= htmlspecialchars($lab['lab_name']) ?></h5>
                      <p class="card-text"><i class="fas fa-clock"></i> Duration: <?= $lab['duration_minutes'] ?> minutes</p>
                      <p class="card-text"><i class="fas fa-users"></i> Max Users: <?= $lab['max_concurrent_users'] ?></p>
                      <a href="request_lab.php?lab_id=<?= $lab['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-play"></i> Request Access
                      </a>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
  $(document).ready(function() {
    $('[data-widget="pushmenu"]').PushMenu();
  });
</script>
</body>
</html>
```
```php
<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$lab_id = (int)$_GET['lab_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $justification = $_POST['justification'];
    $stmt = $db->prepare("INSERT INTO lab_requests (user_id, lab_id, justification, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iis", $_SESSION['uid'], $lab_id, $justification);
    if ($stmt->execute()) {
        header('Location: my_labs.php?msg=requested');
        exit;
    }
}

$lab = $db->query("SELECT l.*, c.name as course_name, c.lab_guide_content FROM labs l JOIN courses c ON l.course_id=c.id WHERE l.id=$lab_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Lab</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="content-header"><h1>Request Lab Access</h1></div>
        
        <div class="content"><div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3>Request Form</h3></div>
                        <div class="card-body">
                            <form method="POST">
                                <h4><?= htmlspecialchars($lab['course_name']) ?></h4>
                                <h5><?= htmlspecialchars($lab['lab_name']) ?></h5>
                                <p><i class="fas fa-clock"></i> Duration: <?= $lab['duration_minutes'] ?> minutes</p>
                                
                                <div class="form-group">
                                    <label>Why do you need this lab?</label>
                                    <textarea name="justification" class="form-control" rows="4" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg">Submit Request</button>
                                <a href="browse_labs.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3>Lab Guide Preview</h3></div>
                        <div class="card-body" id="lab-guide"></div>
                    </div>
                </div>
            </div>
        </div></div>
    </div>
</div>

<script>
document.getElementById('lab-guide').innerHTML = marked.parse(<?= json_encode($lab['lab_guide_content']) ?>);
</script>
</body>
</html>
```
```php
<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$uid = $_SESSION['uid'];

// Get active sessions
$active = $db->query("SELECT ls.*, l.lab_name, c.name as course_name 
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.user_id=$uid AND ls.status='ACTIVE' AND ls.access_expiry > NOW()");

// Get pending requests
$pending = $db->query("SELECT lr.*, l.lab_name, c.name as course_name 
    FROM lab_requests lr 
    JOIN labs l ON lr.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE lr.user_id=$uid AND lr.status='pending'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Labs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="content-header"><h1>My Labs</h1></div>
        
        <div class="content"><div class="container-fluid">
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'requested'): ?>
                <div class="alert alert-success">Request submitted! Waiting for admin approval.</div>
            <?php endif; ?>
            
            <!-- Active Labs -->
            <div class="card">
                <div class="card-header bg-success"><h3>Active Lab Sessions</h3></div>
                <div class="card-body">
                    <?php if ($active->num_rows === 0): ?>
                        <p>No active labs. <a href="browse_labs.php">Browse available labs</a></p>
                    <?php else: ?>
                        <?php while ($lab = $active->fetch_assoc()): 
                            $remaining = strtotime($lab['access_expiry']) - time();
                            $mins = floor($remaining / 60);
                        ?>
                            <div class="alert alert-success">
                                <h4><?= htmlspecialchars($lab['course_name']) ?> - <?= htmlspecialchars($lab['lab_name']) ?></h4>
                                <p><i class="fas fa-clock"></i> <strong><?= $mins ?> minutes remaining</strong></p>
                                <a href="lab_terminal.php?session_id=<?= $lab['id'] ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-terminal"></i> Launch Terminal
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pending Requests -->
            <div class="card">
                <div class="card-header bg-warning"><h3>Pending Requests</h3></div>
                <div class="card-body">
                    <?php if ($pending->num_rows === 0): ?>
                        <p>No pending requests.</p>
                    <?php else: ?>
                        <table class="table">
                            <tr><th>Course</th><th>Lab</th><th>Requested</th></tr>
                            <?php while ($req = $pending->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($req['course_name']) ?></td>
                                    <td><?= htmlspecialchars($req['lab_name']) ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($req['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div></div>
    </div>
</div>
</body>
</html>
```
```php
<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$session_id = (int)$_GET['session_id'];
$uid = $_SESSION['uid'];

// Verify session belongs to user and is active
$session = $db->query("SELECT ls.*, l.bastion_host, l.bastion_user, l.bastion_password, c.lab_guide_content, c.name as course_name, l.lab_name 
    FROM lab_sessions ls 
    JOIN labs l ON ls.lab_id = l.id 
    JOIN courses c ON l.course_id = c.id 
    WHERE ls.id=$session_id AND ls.user_id=$uid AND ls.status='ACTIVE'")->fetch_assoc();

if (!$session) die("Invalid or expired session");

$remaining = strtotime($session['access_expiry']) - time();
$mins = floor($remaining / 60);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lab Terminal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@4.19.0/css/xterm.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/xterm@4.19.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.5.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { margin: 0; padding: 0; overflow: hidden; }
        .lab-container { display: flex; height: 100vh; }
        .lab-guide { width: 30%; overflow-y: auto; padding: 20px; background: #f4f4f4; border-right: 1px solid #ccc; }
        .terminal-container { flex: 1; padding: 20px; }
        #terminal { height: calc(100% - 60px); }
        .lab-header { background: #28a745; color: white; padding: 10px; }
    </style>
</head>
<body>
<div class="lab-container">
    <div class="lab-guide">
        <div class="lab-header">
            <h4><?= htmlspecialchars($session['course_name']) ?></h4>
            <h5><?= htmlspecialchars($session['lab_name']) ?></h5>
            <p><i class="fas fa-clock"></i> <?= $mins ?> minutes remaining</p>
        </div>
        <div id="guide-content"></div>
    </div>
    
    <div class="terminal-container">
        <div class="alert alert-info">
            <strong>Connected to:</strong> <?= htmlspecialchars($session['bastion_host']) ?><br>
            <strong>Session expires in:</strong> <span id="countdown"><?= $mins ?></span> minutes
            <button onclick="location.href='my_labs.php'" class="btn btn-sm btn-secondary float-right">Back to My Labs</button>
        </div>
        <div id="terminal"></div>
    </div>
</div>

<script>
// Render lab guide
document.getElementById('guide-content').innerHTML = marked.parse(<?= json_encode($session['lab_guide_content']) ?>);

// Terminal setup (uses your existing WebSocket connection)
const term = new Terminal({
    cursorBlink: true,
    fontSize: 14
});

const fitAddon = new FitAddon.FitAddon();
term.loadAddon(fitAddon);
term.open(document.getElementById('terminal'));
fitAddon.fit();

// Connect to existing terminal WebSocket
const ws = new WebSocket('wss://kubearena.pratikrastogi.co.in/terminal');
ws.onopen = () => {
    ws.send(JSON.stringify({
        type: 'auth',
        host: '<?= $session['bastion_host'] ?>',
        user: '<?= $session['bastion_user'] ?>',
        password: '<?= $session['bastion_password'] ?>'
    }));
};

ws.onmessage = (event) => {
    term.write(event.data);
};

term.onData((data) => {
    ws.send(JSON.stringify({ type: 'data', data: data }));
});

// Countdown timer
setInterval(() => {
    let remaining = <?= $remaining ?> - (Date.now() - <?= time() * 1000 ?>) / 1000;
    let mins = Math.floor(remaining / 60);
    document.getElementById('countdown').textContent = mins;
    if (mins <= 0) {
        alert('Session expired!');
        location.href = 'my_labs.php';
    }
}, 60000);
</script>
</body>
</html>
```
```php
<!-- For Admin -->
<li class="nav-item"><a href="admin_courses.php" class="nav-link"><i class="fas fa-book"></i> <p>Courses</p></a></li>
<li class="nav-item"><a href="admin_labs.php" class="nav-link"><i class="fas fa-flask"></i> <p>Labs</p></a></li>
<li class="nav-item"><a href="admin_requests.php" class="nav-link"><i class="fas fa-clipboard-check"></i> <p>Lab Requests</p></a></li>

<!-- For Users -->
<li class="nav-item"><a href="browse_labs.php" class="nav-link"><i class="fas fa-search"></i> <p>Browse Labs</p></a></li>
<li class="nav-item"><a href="my_labs.php" class="nav-link"><i class="fas fa-laptop-code"></i> <p>My Labs</p></a></li>
