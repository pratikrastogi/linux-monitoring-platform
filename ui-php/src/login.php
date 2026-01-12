<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | KubeArena</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <style>
    .login-page {
      background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
      min-height: 100vh;
    }
    
    .login-box {
      width: 420px;
    }
    
    .login-card-body {
      background: rgba(255, 255, 255, 0.98);
      border-radius: 15px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
    }
    
    .login-logo a {
      color: #fff;
      font-size: 2rem;
      font-weight: 700;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .login-logo i {
      font-size: 2.5rem;
      vertical-align: middle;
      margin-right: 10px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #2c5364, #203a43);
      border: none;
      padding: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #203a43, #0f2027);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    
    .social-auth-links .btn {
      margin-bottom: 10px;
      border-radius: 25px;
      padding: 10px;
      transition: all 0.3s ease;
    }
    
    .btn-google {
      background: #fff;
      color: #444;
      border: 1px solid #ddd;
    }
    
    .btn-google:hover {
      background: #f8f9fa;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .input-group-text {
      background: transparent;
      border-right: none;
    }
    
    .form-control {
      border-left: none;
    }
    
    .form-control:focus {
      border-color: #2c5364;
      box-shadow: 0 0 0 0.2rem rgba(44, 83, 100, 0.25);
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .login-box {
      animation: fadeInUp 0.6s ease-out;
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="login-logo">
    <a href="#">
      <i class="fas fa-rocket"></i>
      <b>Kube</b>Arena
    </a>
  </div>
  
  <div class="card elevation-5">
    <div class="card-body login-card-body">
      <p class="login-box-msg">
        <strong>Enterprise Linux & Kubernetes Labs</strong><br>
        <small class="text-muted">Sign in to start your session</small>
      </p>

      <form method="post" action="auth.php">
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Username" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <div class="social-auth-links text-center mt-3 mb-3">
        <p>- OR -</p>
        <a href="oauth/google_login.php" class="btn btn-block btn-google">
          <i class="fab fa-google mr-2"></i> Sign in with Google
        </a>
        <button class="btn btn-block btn-secondary" disabled>
          <i class="fas fa-envelope mr-2"></i> Zoho (Coming Soon)
        </button>
      </div>
      <!-- /.social-auth-links -->

      <p class="mb-1">
        <a href="forgot_password.php">
          <i class="fas fa-key"></i> Forgot password?
        </a>
      </p>
      <p class="mb-0">
        <a href="register_new.php" class="text-center">
          <i class="fas fa-user-plus"></i> Register a new account
        </a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Add smooth loading animation
$(document).ready(function() {
  $('form').on('submit', function() {
    $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Signing in...');
    $('button[type="submit"]').prop('disabled', true);
  });
});
</script>

</body>
</html>
