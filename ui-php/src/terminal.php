<?php
session_start();

if (!isset($_SESSION['user'], $_SESSION['uid'])) {
    die("Login required");
}

$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB error");

$user = $_SESSION['user'];
$uid  = (int)$_SESSION['uid'];
$role = $_SESSION['role'];
$page_title = "Lab Terminal";

/* ===============================
   FETCH LATEST LAB SESSION
================================ */
$q = $conn->prepare("
    SELECT *
    FROM lab_sessions
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$q->bind_param("i", $uid);
$q->execute();
$res = $q->get_result();
$lab = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;

/* fixed server id (OLD LOGIC – DO NOT TOUCH) */
$server_id = 1;

/* ===============================
   HANDLE EXTENSION REQUEST FORM
================================ */
$msg = "";

if (isset($_POST['request_extension'])) {

    $hours = (int)$_POST['hours'];
    $exp   = trim($_POST['experience']);
    $dom   = trim($_POST['domain']);
    $fb    = trim($_POST['feedback']);
    $sug   = trim($_POST['suggestion']);

    if ($hours && $exp && $dom && $fb && $sug) {

        $stmt = $conn->prepare("
            INSERT INTO lab_extension_requests
            (user_id, username, hours, status)
            VALUES (?, ?, ?, 'PENDING')
        ");
        $stmt->bind_param("isi", $uid, $user, $hours);
        $stmt->execute();
        $stmt->close();

        $msg = "✅ Request submitted. Please wait for admin approval.";
    } else {
        $msg = "❌ All fields are mandatory.";
    }
}

/* ===============================
   IST TIME FORMATTER
================================ */
function toIST($utc) {
    $dt = new DateTime($utc, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
    return $dt->format('d M Y, h:i A') . " IST";
}

include 'includes/header.php';
?>

<!-- xterm.js for terminal emulator -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm/css/xterm.css">
<script src="https://cdn.jsdelivr.net/npm/xterm/lib/xterm.js"></script>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-terminal"></i> Kubernetes Lab Terminal</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Lab Terminal</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

<?php if (!$lab): ?>

      <div class="row">
        <div class="col-md-6 offset-md-3">
          <div class="card">
            <div class="card-body text-center">
              <i class="fas fa-flask fa-4x mb-3" style="color: #667eea;"></i>
              <h4>Welcome to Kubernetes Lab</h4>
              <p class="text-muted">You haven't used your free lab access yet. Start your learning journey now!</p>
              <a class="btn btn-primary btn-lg" href="generate_free_access.php">
                <i class="fas fa-rocket"></i> Request Free Lab Access (60 min)
              </a>
            </div>
          </div>
        </div>
      </div>

<?php elseif ($lab['status'] === 'REQUESTED'): ?>

      <div class="row">
        <div class="col-md-6 offset-md-3">
          <div class="card">
            <div class="card-body text-center">
              <i class="fas fa-cog fa-spin fa-4x mb-3" style="color: #667eea;"></i>
              <h4>Provisioning Your Lab Environment</h4>
              <p class="text-muted">Please wait while we set up your Kubernetes lab...</p>
              <div class="progress mt-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 75%; background: linear-gradient(135deg, #667eea, #764ba2);">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>setTimeout(() => location.reload(), 5000);</script>

<?php elseif ($lab['status'] === 'ACTIVE'): ?>

      <style>
        #terminal { 
          background: #000; 
          height: 500px; 
          border-radius: 10px; 
          padding: 10px;
        }
        .cmd-block { 
          background: #1e1e1e; 
          border-left: 3px solid #667eea;
          padding: 12px; 
          margin: 10px 0; 
          color: #00ff00; 
          font-family: 'Courier New', monospace;
          font-size: 13px;
          border-radius: 5px;
        }
        .lab-note { 
          color: #666; 
          font-size: 14px; 
          margin: 8px 0;
        }
      </style>

      <div class="row">
        <!-- Lab Info Card -->
        <div class="col-12 mb-3">
          <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
              <h3 class="card-title"><i class="fas fa-check-circle"></i> Lab Active</h3>
              <div class="card-tools">
                <span class="badge badge-light">
                  <i class="far fa-clock"></i> Expires: <?= toIST($lab['access_expiry']) ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- TERMINAL PANEL -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-terminal"></i> Interactive Terminal</h3>
              <div class="card-tools">
                <button class="btn btn-sm btn-primary" onclick="connectTerminal()">
                  <i class="fas fa-plug"></i> Connect
                </button>
              </div>
            </div>
            <div class="card-body" style="background: #000;">
              <div id="terminal"></div>
            </div>
          </div>
        </div>

        <!-- GUIDED LAB PANEL -->
        <div class="col-lg-5">
          <div class="card">
            <div class="card-header" style="background: rgba(102, 126, 234, 0.1);">
              <h3 class="card-title"><i class="fas fa-book"></i> LAB-1: Docker & Container Basics</h3>
            </div>
            <div class="card-body" style="max-height: 550px; overflow-y: auto;">
              <p class="lab-note">
                <i class="fas fa-info-circle text-info"></i> 
                Containers are the foundation of Kubernetes. This lab ensures you understand containers before moving to Pods.
              </p>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 1. Check Runtime</h5>
              <p class="lab-note">Verify Docker or Podman installation.</p>
              <div class="cmd-block">docker --version</div>
              <div class="cmd-block">podman --version</div>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 2. Images</h5>
              <p class="lab-note">Images are templates used to create containers.</p>
              <div class="cmd-block">docker images</div>
              <div class="cmd-block">docker pull nginx</div>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 3. Run Container</h5>
              <p class="lab-note">Run nginx in background.</p>
              <div class="cmd-block">docker run -d --name web nginx</div>
              <div class="cmd-block">docker ps</div>
              <div class="cmd-block">docker ps -a</div>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 4. Port Mapping</h5>
              <p class="lab-note">Expose container port to host.</p>
              <div class="cmd-block">docker run -d -p 8080:80 nginx</div>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 5. Logs & Inspect</h5>
              <p class="lab-note">Inspect container details and logs.</p>
              <div class="cmd-block">docker logs web</div>
              <div class="cmd-block">docker inspect web</div>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 6. Persistent Volume</h5>
              <p class="lab-note">Persist data beyond container lifecycle.</p>
              <div class="cmd-block">docker volume create my-vol</div>
              <div class="cmd-block">docker run -d -v my-vol:/data nginx</div>

              <h5 class="mt-3"><i class="fas fa-check-circle text-success"></i> 7. Clean Up</h5>
              <p class="lab-note">Remove containers and images.</p>
              <div class="cmd-block">docker stop web</div>
              <div class="cmd-block">docker rm web</div>
              <div class="cmd-block">docker rmi nginx</div>
            </div>
          </div>
        </div>
      </div>

      <script>
        let term = null;
        let ws = null;

        function connectTerminal() {
          // Prevent multiple connections
          if (ws && ws.readyState === WebSocket.OPEN) {
            console.log('Already connected');
            return;
          }

          const username = "<?= $user ?>";
          const password = "k8s" + username + "@123!";

          // Initialize terminal if not already done
          if (!term) {
            term = new Terminal({ 
              cursorBlink: true,
              fontSize: 14,
              fontFamily: 'Courier New, monospace',
              theme: {
                background: '#000000',
                foreground: '#00ff00'
              }
            });
            term.open(document.getElementById("terminal"));
          }

          // Connect WebSocket
          term.write("🔌 Connecting to terminal...\r\n");
          
          ws = new WebSocket(
            "wss://kubearena.pratikrastogi.co.in/terminal?" +
            "server_id=<?= $server_id ?>&user=<?= $user ?>"
          );

          ws.onopen = () => {
            ws.send(JSON.stringify({ password }));
            term.write("🔐 Authenticating...\r\n");
          };

          ws.onmessage = e => term.write(e.data);
          
          ws.onerror = (error) => {
            term.write("\r\n❌ Connection error. Please try again.\r\n");
            console.error('WebSocket error:', error);
          };
          
          ws.onclose = () => { 
            term.write("\r\n❌ Connection closed\r\n"); 
            ws = null; 
          };
          
          term.onData(d => ws && ws.readyState === WebSocket.OPEN && ws.send(d));
        }

        // Auto-connect on page load
        window.addEventListener('load', function() {
          connectTerminal();
        });
      </script>

<?php elseif ($lab['status'] === 'EXPIRED'): ?>

    <!-- EXPIRED LAB STATE -->
    <div class="row">
      <div class="col-lg-8 offset-lg-2">
        <div class="card">
          <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-hourglass-end"></i> Lab Session Expired</h3>
          </div>
          <div class="card-body">
            <p class="text-muted">Your lab session has ended. Request an extension to continue learning!</p>
            
            <?php if ($msg): ?>
              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($msg) ?>
              </div>
            <?php endif; ?>

            <form method="post">
              <div class="form-group">
                <label>Your Experience <span class="text-danger">*</span></label>
                <textarea name="experience" class="form-control" rows="3" placeholder="Describe what you've learned..." required></textarea>
              </div>

              <div class="form-group">
                <label>Domain/Focus Area <span class="text-danger">*</span></label>
                <input name="domain" class="form-control" placeholder="e.g., DevOps, Cloud, Backend..." required>
              </div>

              <div class="form-group">
                <label>Feedback <span class="text-danger">*</span></label>
                <textarea name="feedback" class="form-control" rows="2" placeholder="How was your experience?" required></textarea>
              </div>

              <div class="form-group">
                <label>Suggestions <span class="text-danger">*</span></label>
                <textarea name="suggestion" class="form-control" rows="2" placeholder="How can we improve?" required></textarea>
              </div>

              <div class="form-group">
                <label>Extension Duration <span class="text-danger">*</span></label>
                <select name="hours" class="form-control">
                  <option value="1">1 Hour</option>
                  <option value="2">2 Hours</option>
                  <option value="4">4 Hours</option>
                </select>
              </div>

              <button class="btn btn-primary btn-block" name="request_extension">
                <i class="fas fa-paper-plane"></i> Submit Extension Request
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

  <?php endif; ?>

  </div>
</section>
</div>

<?php include 'includes/footer.php'; ?>

