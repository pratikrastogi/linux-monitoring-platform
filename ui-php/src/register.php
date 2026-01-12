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
      background: linear-gradient(135deg, #2c5364, #203a43);
      border: none;
      color: white;
    }
    
    .btn-gradient:hover {
      background: linear-gradient(135deg, #203a43, #0f2027);
      color: white;
    }
    
    .validation-feedback {
      font-size: 0.875rem;
      margin-top: 5px;
    }
    
    .validation-feedback.text-success {
      color: #28a745 !important;
    }
    
    .validation-feedback.text-danger {
      color: #dc3545 !important;
    }
  </style>
</head>
<body class="hold-transition register-page">
<div class="register-box">
  <div class="register-logo">
    <a href="#"><i class="fas fa-rocket"></i><b>Kube</b>Arena</a>
  </div>

  <div class="card">
    <div class="card-body register-card-body">
      <p class="login-box-msg">Create your KubeArena Account</p>
      <p class="text-muted text-center mb-3"><small>Free 1-Hour Kubernetes Lab Access</small></p>

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
            <button type="submit" class="btn btn-gradient btn-block">Register</button>
          </div>
        </div>
      </form>

      <hr>

      <p class="mb-0 text-center">
        <a href="login.php" class="text-center">Already have an account? Login here</a>
      </p>
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

