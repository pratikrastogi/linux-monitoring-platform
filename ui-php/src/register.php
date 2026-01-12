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
  <title>Register | KubeArena Learning Platform</title>

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
    }
    
    .register-page {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    
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
    
    /* Keywords background on left */
    .keywords-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 55%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px;
      z-index: 2;
    }
    
    .keywords-bg h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 3rem;
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
    
    .subtitle {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.2rem;
      margin-top: 20px;
      font-weight: 300;
    }
    
    .register-container {
      position: absolute;
      right: 0;
      top: 0;
      width: 45%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }
    
    .register-box {
      width: 90%;
      max-width: 500px;
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
    
    .register-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .register-card-body {
      padding: 35px;
    }
    
    .register-logo {
      text-align: center;
      margin-bottom: 25px;
    }
    
    .register-logo h2 {
      font-family: 'Orbitron', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    .register-logo i {
      font-size: 2.5rem;
      color: #667eea;
      margin-bottom: 10px;
      display: block;
      animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .btn-gradient {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: none;
      padding: 12px;
      font-weight: 600;
      border-radius: 10px;
      color: white;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .btn-gradient:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
      color: white;
    }
    
    .validation-feedback {
      font-size: 0.8rem;
      margin-top: 5px;
    }
    
    .validation-feedback.text-success {
      color: #28a745 !important;
    }
    
    .validation-feedback.text-danger {
      color: #dc3545 !important;
    }
    
    .login-box-msg {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 20px;
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
    
    a {
      color: #667eea;
      transition: all 0.3s ease;
    }
    
    a:hover {
      color: #764ba2;
      text-decoration: none;
    }
    
    .info-text {
      background: rgba(102, 126, 234, 0.1);
      padding: 12px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 0.85rem;
      color: #555;
      border-left: 3px solid #667eea;
    }
    
    @media (max-width: 992px) {
      .keywords-bg {
        width: 100%;
        padding: 30px;
      }
      
      .keywords-bg h1 {
        font-size: 2rem;
      }
      
      .register-container {
        position: relative;
        width: 100%;
        margin-top: 400px;
      }
      
      .register-box {
        width: 95%;
      }
    }
  </style>
</head>
<body class="register-page">

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
    <h1><i class="fas fa-graduation-cap"></i> Join the Cloud Revolution</h1>
    <p class="subtitle">Start Your Kubernetes & DevOps Journey Today</p>
    
    <div class="keywords">
      <div class="keyword"><i class="fas fa-cloud"></i> Cloud Native</div>
      <div class="keyword"><i class="fas fa-dharmachakra"></i> Kubernetes</div>
      <div class="keyword"><i class="fab fa-docker"></i> Docker</div>
      <div class="keyword"><i class="fas fa-infinity"></i> CI/CD Pipeline</div>
      <div class="keyword"><i class="fas fa-code-branch"></i> GitOps</div>
      <div class="keyword"><i class="fas fa-shield-alt"></i> Security</div>
      <div class="keyword"><i class="fas fa-network-wired"></i> Microservices</div>
      <div class="keyword"><i class="fas fa-rocket"></i> Automation</div>
    </div>
  </div>

  <!-- Right side - Register panel -->
  <div class="register-container">
<div class="register-box">
  <div class="register-card card">
    <div class="card-body register-card-body">
      <div class="register-logo">
        <i class="fas fa-rocket"></i>
        <h2>KubeArena</h2>
      </div>
      
      <p class="login-box-msg">Create your account and start learning</p>
      
      <div class="info-text">
        <i class="fas fa-gift"></i> Get <strong>FREE 1-Hour Kubernetes Lab Access</strong> upon registration!
      </div>

      <form method="post" action="register_submit.php">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div id="uStatus" class="validation-feedback"></div>

        <div class="input-group mb-3">
          <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div id="eStatus" class="validation-feedback"></div>

        <div class="input-group mb-3">
          <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-phone"></span>
            </div>
          </div>
        </div>
        <div id="mStatus" class="validation-feedback"></div>

        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div id="pStatus" class="validation-feedback"></div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-gradient btn-block">Create Account</button>
          </div>
        </div>
      </form>

      <hr style="border-color: #e0e0e0; margin: 20px 0;">

      <p class="mb-0 text-center">
        <a href="login.php">
          <i class="fas fa-sign-in-alt"></i> Already have an account? Login here
        </a>
      </p>
    </div>
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
// Real-time validation
document.getElementById('username').addEventListener('input', function() {
  checkAvailability('username', this.value, 'uStatus');
});

document.getElementById('email').addEventListener('input', function() {
  checkAvailability('email', this.value, 'eStatus');
});

document.getElementById('mobile').addEventListener('input', function() {
  checkAvailability('mobile', this.value, 'mStatus');
});

document.getElementById('password').addEventListener('input', function() {
  const pwd = this.value;
  const statusDiv = document.getElementById('pStatus');
  
  if (pwd.length < 6) {
    statusDiv.className = 'validation-feedback text-danger';
    statusDiv.innerHTML = '<i class="fas fa-times-circle"></i> Password must be at least 6 characters';
  } else {
    statusDiv.className = 'validation-feedback text-success';
    statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Password strength: Good';
  }
});

function checkAvailability(field, value, statusId) {
  const statusDiv = document.getElementById(statusId);
  
  if (value.length < 3) {
    statusDiv.innerHTML = '';
    return;
  }
  
  fetch(`api/check_availability.php?field=${field}&value=${encodeURIComponent(value)}`)
    .then(r => r.json())
    .then(data => {
      if (data.available) {
        statusDiv.className = 'validation-feedback text-success';
        statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Available';
      } else {
        statusDiv.className = 'validation-feedback text-danger';
        statusDiv.innerHTML = '<i class="fas fa-times-circle"></i> Already taken';
      }
    })
    .catch(err => {
      statusDiv.innerHTML = '';
    });
}
</script>

</body>
</html>

