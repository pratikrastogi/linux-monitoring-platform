<?php
session_start();
require_once 'auth.php';

$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
$uid = $_SESSION['uid'];
$role = $_SESSION['role'];
$page_title = "Dashboard";

include 'includes/header.php';
?>

<body class="hold-transition sidebar-mini">
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
                        <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <div class="container-fluid">
                
                <?php if ($role === 'admin'): ?>
                    <!-- ADMIN DASHBOARD -->
                    <div class="row">
                        <!-- Total Users -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <?php
                                    $total_users = $db->query("SELECT COUNT(*) as cnt FROM users WHERE role='user'")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $total_users ?></h3>
                                    <p>Total Users</p>
                                </div>
                                <div class="icon"><i class="fas fa-users"></i></div>
                                <a href="admin_users.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Users with Active Labs -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <?php
                                    $active_users = $db->query("SELECT COUNT(DISTINCT user_id) as cnt FROM lab_sessions WHERE status='ACTIVE' AND access_expiry > NOW()")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $active_users ?></h3>
                                    <p>Users in Labs</p>
                                </div>
                                <div class="icon"><i class="fas fa-user-check"></i></div>
                                <a href="admin_users.php" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Active Courses -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <?php
                                    $courses = $db->query("SELECT COUNT(*) as cnt FROM courses WHERE active=1")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $courses ?></h3>
                                    <p>Active Courses</p>
                                </div>
                                <div class="icon"><i class="fas fa-book"></i></div>
                                <a href="courses.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Live Labs -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <?php
                                    $active_labs = $db->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE status='ACTIVE' AND access_expiry > NOW()")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $active_labs ?></h3>
                                    <p>Live Lab Sessions</p>
                                </div>
                                <div class="icon"><i class="fas fa-laptop-code"></i></div>
                                <a href="admin_lab_requests.php" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Pending Requests -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <?php
                                    $pending = $db->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE status='pending'")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $pending ?></h3>
                                    <p>Pending Requests</p>
                                </div>
                                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                                <a href="admin_lab_requests.php" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Server Count -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <?php
                                    $servers = $db->query("SELECT COUNT(*) as cnt FROM servers WHERE enabled=1")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $servers ?></h3>
                                    <p>Available Servers</p>
                                </div>
                                <div class="icon"><i class="fas fa-server"></i></div>
                                <a href="admin_provisioners.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Total Labs -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <?php
                                    $total_labs = $db->query("SELECT COUNT(*) as cnt FROM labs WHERE active=1")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $total_labs ?></h3>
                                    <p>Total Labs</p>
                                </div>
                                <div class="icon"><i class="fas fa-flask"></i></div>
                                <a href="admin_labs.php" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- USER DASHBOARD -->
                    <div class="row">
                        <!-- Active Sessions -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <?php
                                    $my_active = $db->query("SELECT COUNT(*) as cnt FROM lab_sessions WHERE user_id=$uid AND status='ACTIVE' AND access_expiry > NOW()")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $my_active ?></h3>
                                    <p>Active Lab Sessions</p>
                                </div>
                                <div class="icon"><i class="fas fa-laptop-code"></i></div>
                                <a href="my_labs.php" class="small-box-footer">Launch <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        
                        <!-- Available Courses -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <?php
                                    $avail_courses = $db->query("SELECT COUNT(*) as cnt FROM courses WHERE active=1")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $avail_courses ?></h3>
                                    <p>Available Courses</p>
                                </div>
                                <div class="icon"><i class="fas fa-book"></i></div>
                                <a href="browse_labs.php" class="small-box-footer">Browse <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        
                        <!-- Pending Requests -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <?php
                                    $my_pending = $db->query("SELECT COUNT(*) as cnt FROM lab_requests WHERE user_id=$uid AND status='pending'")->fetch_assoc()['cnt'];
                                    ?>
                                    <h3><?= $my_pending ?></h3>
                                    <p>Pending Requests</p>
                                </div>
                                <div class="icon"><i class="fas fa-clock"></i></div>
                                <a href="my_labs.php" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Sessions Alert -->
                    <?php
                    $active_sessions = $db->query("SELECT ls.*, l.lab_name, c.name as course_name 
                        FROM lab_sessions ls 
                        JOIN labs l ON ls.lab_id = l.id 
                        JOIN courses c ON l.course_id = c.id 
                        WHERE ls.user_id=$uid AND ls.status='ACTIVE' AND ls.access_expiry > NOW()");
                    
                    while ($session = $active_sessions->fetch_assoc()):
                        $remaining = strtotime($session['access_expiry']) - time();
                        $mins = floor($remaining / 60);
                    ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h5><i class="icon fas fa-check"></i> Active Lab Session!</h5>
                            <strong><?= htmlspecialchars($session['course_name']) ?> - <?= htmlspecialchars($session['lab_name']) ?></strong><br>
                            Time remaining: <strong><?= $mins ?> minutes</strong><br>
                            <a href="lab_terminal.php?session_id=<?= $session['id'] ?>" class="btn btn-sm btn-success mt-2">
                                <i class="fas fa-terminal"></i> Launch Terminal
                            </a>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Available Courses -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Available Courses</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                $courses_list = $db->query("SELECT c.*, (SELECT COUNT(*) FROM labs WHERE course_id=c.id AND active=1) as lab_count FROM courses c WHERE c.active=1 ORDER BY c.created_at DESC LIMIT 6");
                                while ($course = $courses_list->fetch_assoc()):
                                ?>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h5><?= htmlspecialchars($course['name']) ?></h5>
                                                <p class="text-muted"><?= htmlspecialchars(substr($course['description'] ?? '', 0, 80)) ?>...</p>
                                                <p><i class="fas fa-flask"></i> <?= $course['lab_count'] ?> labs available</p>
                                                <a href="request_lab.php?course=<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-check-circle"></i> Request Access
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    // Initialize sidebar toggle
    $(document).ready(function() {
        $('[data-widget="pushmenu"]').PushMenu();
    });
</script>
</body>
</html>

