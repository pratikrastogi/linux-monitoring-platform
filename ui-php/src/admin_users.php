<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$page_title = "User Management";
$conn = new mysqli("mysql","monitor","monitor123","monitoring");
if ($conn->connect_error) die("DB Error");

/*
|--------------------------------------------------------------------------
| HANDLE LAB ASSIGNMENT (ASYNC PROVISIONING)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_lab'])) {

    $user_id        = (int)$_POST['user_id'];
    $lab_id         = (int)$_POST['lab_id'];
    $validity_hours = (int)$_POST['validity_hours'];
    $notes          = $conn->real_escape_string($_POST['notes'] ?? '');

    // Fetch user
    $user_q = $conn->query("SELECT email FROM users WHERE id=$user_id");
    if ($user_q->num_rows === 0) {
        $_SESSION['error'] = "User not found!";
        header("Location: admin_users.php");
        exit;
    }
    $username = $user_q->fetch_assoc()['email'];

    // Fetch lab
    $lab_q = $conn->query("SELECT id FROM labs WHERE id=$lab_id AND active=1");
    if ($lab_q->num_rows === 0) {
        $_SESSION['error'] = "Invalid or inactive lab!";
        header("Location: admin_users.php");
        exit;
    }

    // Calculate timings
    $access_start  = date('Y-m-d H:i:s');
    $access_expiry = date('Y-m-d H:i:s', time() + ($validity_hours * 3600));
    $namespace     = "user-{$user_id}-" . time();
    $session_token = bin2hex(random_bytes(16));

    // 🔴 IMPORTANT: STATUS MUST BE REQUESTED (NOT ACTIVE)
    $sql = "
        INSERT INTO lab_sessions
        (user_id, username, lab_id, namespace, access_start, access_expiry, status, plan, provisioned, session_token)
        VALUES
        ($user_id, '$username', $lab_id, '$namespace', '$access_start', '$access_expiry',
         'REQUESTED', 'FREE', 0, '$session_token')
    ";

    if (!$conn->query($sql)) {
        $_SESSION['error'] = "DB Error: " . $conn->error;
        header("Location: admin_users.php");
        exit;
    }

    $session_id = $conn->insert_id;

    // Log for worker visibility
    error_log(
        "[" . date('c') . "] Lab session {$session_id} queued for provisioning (lab_id={$lab_id}, user={$username})\n",
        3,
        "/tmp/lab_provisioning.log"
    );

    $_SESSION['success'] = "Lab assigned successfully. Provisioning in progress.";
    header("Location: admin_users.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| HANDLE ROLE UPDATE
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_role'])) {

    $user_id = (int)$_POST['user_id'];
    $role    = $conn->real_escape_string($_POST['role']);

    $conn->query("UPDATE users SET role='$role' WHERE id=$user_id");

    $_SESSION['success'] = "User role updated.";
    header("Location: admin_users.php");
    exit;
}

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid">

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="card">
<div class="card-header"><h3 class="card-title">Users</h3></div>
<div class="card-body table-responsive p-0">
<table class="table table-hover">
<thead>
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Role</th>
    <th>Current Lab</th>
    <th>Expires</th>
    <th>Sessions</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
while ($u = $users->fetch_assoc()):

$active_q = $conn->query("
    SELECT * FROM lab_sessions
    WHERE user_id={$u['id']} AND status='ACTIVE'
    ORDER BY access_expiry DESC LIMIT 1
");
$active = $active_q->fetch_assoc();

$count_q = $conn->query("SELECT COUNT(*) c FROM lab_sessions WHERE user_id={$u['id']}");
$total_sessions = $count_q->fetch_assoc()['c'];
?>

<tr>
<td><?= $u['id'] ?></td>
<td><b><?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></b></td>
<td>
<form method="POST">
<input type="hidden" name="user_id" value="<?= $u['id'] ?>">
<select name="role" onchange="this.form.submit()">
<option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
<option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
</select>
<button type="submit" name="update_user_role" hidden></button>
</form>
</td>

<td>
<?php if ($active): ?>
<span class="badge badge-success">ACTIVE</span>
<?php else: ?>
<span class="text-muted">None</span>
<?php endif; ?>
</td>

<td>
<?php if ($active): ?>
<?= date('M j H:i', strtotime($active['access_expiry'])) ?>
<?php else: ?> - <?php endif; ?>
</td>

<td><?= $total_sessions ?></td>

<td>
<button class="btn btn-sm btn-primary assign-lab-btn"
        data-user-id="<?= $u['id'] ?>"
        data-user-name="<?= htmlspecialchars($u['email']) ?>">
Assign Lab
</button>
</td>
</tr>

<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

<!-- ASSIGN LAB MODAL -->
<div class="modal fade" id="assignLabModal">
<div class="modal-dialog">
<form method="POST" class="modal-content">
<div class="modal-header bg-primary">
<h5 class="modal-title">Assign Lab</h5>
</div>

<div class="modal-body">
<input type="hidden" name="user_id" id="modalUserId">

<label>Lab</label>
<select name="lab_id" class="form-control" required>
<option value="">Select Lab</option>
<?php
$labs = $conn->query("SELECT id, lab_name FROM labs WHERE active=1");
while ($l = $labs->fetch_assoc()):
?>
<option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['lab_name']) ?></option>
<?php endwhile; ?>
</select>

<label class="mt-2">Validity (hours)</label>
<input type="number" name="validity_hours" class="form-control" value="2" min="1" max="168">
</div>

<div class="modal-footer">
<button type="submit" name="assign_lab" class="btn btn-success">Assign</button>
</div>
</form>
</div>
</div>

</div>
</section>
</div>

<?php include 'includes/footer.php'; ?>
</div>

<script>
document.querySelectorAll('.assign-lab-btn').forEach(btn=>{
    btn.onclick=()=>{
        document.getElementById('modalUserId').value=btn.dataset.userId;
        $('#assignLabModal').modal('show');
    };
});
</script>

</body>
</html>

