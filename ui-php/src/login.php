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
  <title>Login | KubeArena Learning Platform</title>

  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600&display=swap">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    
    .login-page {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    
    /* Animated background particles */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 1;
    }
    
    .particle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 20s infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) translateX(0); }
      25% { transform: translateY(-100px) translateX(100px); }
      50% { transform: translateY(-200px) translateX(-100px); }
      75% { transform: translateY(-100px) translateX(50px); }
    }
    
    /* Kubernetes keywords background */
    .keywords-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 60%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px;
      z-index: 2;
    }
    
    .keywords-bg h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 3.5rem;
      font-weight: 900;
      color: #fff;
      margin-bottom: 20px;
      text-shadow: 0 0 20px rgba(255,255,255,0.3);
      animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
      from { text-shadow: 0 0 20px rgba(255,255,255,0.3), 0 0 30px rgba(255,255,255,0.2); }
      to { text-shadow: 0 0 30px rgba(255,255,255,0.5), 0 0 40px rgba(255,255,255,0.3); }
    }
    
    .keywords {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 30px;
    }
    
    .keyword {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 12px 24px;
      border-radius: 30px;
      color: #fff;
      font-weight: 600;
      font-size: 0.9rem;
      animation: fadeInUp 0.6s ease-out backwards;
      transition: all 0.3s ease;
    }
    
    .keyword:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .keyword:nth-child(1) { animation-delay: 0.1s; }
    .keyword:nth-child(2) { animation-delay: 0.2s; }
    .keyword:nth-child(3) { animation-delay: 0.3s; }
    .keyword:nth-child(4) { animation-delay: 0.4s; }
    .keyword:nth-child(5) { animation-delay: 0.5s; }
    .keyword:nth-child(6) { animation-delay: 0.6s; }
    .keyword:nth-child(7) { animation-delay: 0.7s; }
    .keyword:nth-child(8) { animation-delay: 0.8s; }
    .keyword:nth-child(9) { animation-delay: 0.9s; }
    .keyword:nth-child(10) { animation-delay: 1s; }
    
    .subtitle {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.2rem;
      margin-top: 20px;
      font-weight: 300;
    }
    
    /* Login panel on the right */
    .login-container {
      position: absolute;
      right: 0;
      top: 0;
      width: 40%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }
    
    .login-box {
      width: 90%;
      max-width: 450px;
      animation: slideInRight 0.8s ease-out;
    }
    
    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(100px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
    }
    
    .login-card-body {
      padding: 40px;
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .login-logo h2 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 5px;
    }
    
    .login-logo i {
      font-size: 3rem;
      color: #667eea;
      margin-bottom: 10px;
      display: block;
      animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .login-box-msg {
      font-size: 0.95rem;
      color: #666;
      margin-bottom: 25px;
      text-align: center;
    }
    
    .form-control {
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      padding: 12px 15px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
    }
    
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
      background: #fff;
    }
    
    .input-group-text {
      background: transparent;
      border: 2px solid #e0e0e0;
      border-left: none;
      border-radius: 0 10px 10px 0;
      color: #667eea;
    }
    
    .input-group .form-control {
      border-right: none;
      border-radius: 10px 0 0 10px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: none;
      padding: 12px;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    
    .social-auth-links .btn {
      margin-bottom: 10px;
      border-radius: 10px;
      padding: 12px;
      transition: all 0.3s ease;
      border: 2px solid #e0e0e0;
    }
    
    .btn-google {
      background: #fff;
      color: #444;
    }
    
    .btn-google:hover {
      background: #f8f9fa;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .icheck-primary label {
      color: #666;
      font-size: 0.9rem;
    }
    
    a {
      color: #667eea;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    a:hover {
      color: #764ba2;
      text-decoration: none;
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
    
    @media (max-width: 992px) {
      .keywords-bg {
        width: 100%;
        padding: 30px;
      }
      
      .keywords-bg h1 {
        font-size: 2rem;
      }
      
      .login-container {
        position: relative;
        width: 100%;
        margin-top: 400px;
      }
      
      .login-box {
        width: 95%;
      }
    }
  </style>
</head>
<body class="login-page">
  
  <!-- Animated particles background -->
  <div class="particles">
    <div class="particle" style="width: 80px; height: 80px; top: 10%; left: 5%; animation-delay: 0s;"></div>
    <div class="particle" style="width: 60px; height: 60px; top: 70%; left: 10%; animation-delay: 2s;"></div>
    <div class="particle" style="width: 100px; height: 100px; top: 30%; left: 15%; animation-delay: 4s;"></div>
    <div class="particle" style="width: 50px; height: 50px; top: 50%; left: 8%; animation-delay: 1s;"></div>
    <div class="particle" style="width: 70px; height: 70px; top: 85%; left: 20%; animation-delay: 3s;"></div>
  </div>
  
  <!-- Left side - Keywords background -->
  <div class="keywords-bg">
    <h1><i class="fas fa-dharmachakra"></i> Kubernetes Learning Platform</h1>
    <p class="subtitle">Master Cloud-Native Technologies & DevOps Excellence</p>
    
    <div class="keywords">
      <div class="keyword"><i class="fas fa-server"></i> Kubernetes</div>
      <div class="keyword"><i class="fas fa-docker"></i> Docker</div>
      <div class="keyword"><i class="fas fa-cloud"></i> Cloud Native</div>
      <div class="keyword"><i class="fas fa-code-branch"></i> DevOps</div>
      <div class="keyword"><i class="fas fa-infinity"></i> CI/CD</div>
      <div class="keyword"><i class="fas fa-network-wired"></i> Microservices</div>
      <div class="keyword"><i class="fas fa-shield-alt"></i> Security</div>
      <div class="keyword"><i class="fas fa-chart-line"></i> Monitoring</div>
      <div class="keyword"><i class="fab fa-linux"></i> Linux</div>
      <div class="keyword"><i class="fas fa-terminal"></i> Automation</div>
      <div class="keyword"><i class="fas fa-cubes"></i> Containers</div>
      <div class="keyword"><i class="fas fa-layer-group"></i> Orchestration</div>
    </div>
  </div>
  
  <!-- Right side - Login panel -->
  <div class="login-container">
    <div class="login-box">
      <div class="login-card card">
        <div class="card-body login-card-body">
          <div class="login-logo">
            <i class="fas fa-rocket"></i>
            <h2>KubeArena</h2>
          </div>
          
          <p class="login-box-msg">
            Sign in to access your learning dashboard
          </p>

          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?php
              switch ($_GET['error']) {
                case 'invalid':
                  echo '<i class="fas fa-exclamation-triangle"></i> Invalid username or password';
                  break;
                case 'disabled':
                  echo '<i class="fas fa-ban"></i> Account is disabled';
                  break;
                case 'expired':
                  echo '<i class="fas fa-clock"></i> Your lab access has expired';
                  break;
                case 'empty':
                  echo '<i class="fas fa-info-circle"></i> Please enter username and password';
                  break;
                default:
                  echo '<i class="fas fa-exclamation"></i> Login failed';
              }
              ?>
            </div>
          <?php endif; ?>

          <form method="post" action="login_submit.php">
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
            <p style="color: #999; font-size: 0.85rem;">- OR CONTINUE WITH -</p>
            <a href="oauth/google_login.php" class="btn btn-block btn-google">
              <i class="fab fa-google mr-2"></i> Sign in with Google
            </a>
          </div>

          <hr style="border-color: #e0e0e0;">

          <p class="mb-2 text-center">
            <a href="forgot_password.php">
              <i class="fas fa-key"></i> Forgot password?
            </a>
          </p>
          <p class="mb-0 text-center">
            <a href="register.php">
              <i class="fas fa-user-plus"></i> Create new account
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Smooth loading animation
$(document).ready(function() {
  $('form').on('submit', function() {
    $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Signing in...');
    $('button[type="submit"]').prop('disabled', true);
  });
  
  // Add floating particles animation
  const particles = $('.particle');
  particles.each(function(i) {
    $(this).css('animation-delay', (i * 1.5) + 's');
});
</script>

</body>
</html>
