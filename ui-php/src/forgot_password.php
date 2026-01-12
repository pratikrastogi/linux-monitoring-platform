<?php
session_start();
$conn = new mysqli("mysql","monitor","monitor123","monitoring");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    $q = $conn->prepare("SELECT username FROM users WHERE username=?");
    $q->bind_param("s",$username);
    $q->execute();

    if ($q->get_result()->num_rows === 1) {
        $token = bin2hex(random_bytes(16));
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_user']  = $username;
        $msg = "Reset token generated. Contact admin with token: $token";
    } else {
        $msg = "User not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | KubeArena</title>

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
    
    .forgot-page {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    .particles {
      position: absolute;
      width: 100%;
      height: 100%;
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
    
    .forgot-box {
      width: 90%;
      max-width: 450px;
      z-index: 10;
      animation: fadeInUp 0.8s ease-out;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .forgot-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
    }
    
    .forgot-card-body {
      padding: 40px;
    }
    
    .forgot-logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .forgot-logo i {
      font-size: 3rem;
      color: #667eea;
      margin-bottom: 15px;
      display: block;
      animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .forgot-logo h2 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 10px;
    }
    
    .forgot-box-msg {
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
    
    .alert {
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      border: none;
    }
    
    .alert-success {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
      border-left: 3px solid #28a745;
    }
    
    .alert-danger {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
      border-left: 3px solid #dc3545;
    }
    
    a {
      color: #667eea;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    
    a:hover {
      color: #764ba2;
      text-decoration: none;
    }
  </style>
</head>
<body class="forgot-page">

  <div class="particles">
    <div class="particle" style="width: 80px; height: 80px; top: 15%; left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="width: 60px; height: 60px; top: 75%; left: 80%; animation-delay: 2s;"></div>
    <div class="particle" style="width: 100px; height: 100px; top: 40%; left: 70%; animation-delay: 4s;"></div>
    <div class="particle" style="width: 50px; height: 50px; top: 60%; left: 20%; animation-delay: 1s;"></div>
  </div>

  <div class="forgot-box">
    <div class="forgot-card card">
      <div class="card-body forgot-card-body">
        <div class="forgot-logo">
          <i class="fas fa-key"></i>
          <h2>Password Reset</h2>
        </div>
        
        <p class="forgot-box-msg">
          Enter your username to generate a password reset token
        </p>

        <?php if ($msg): ?>
          <div class="alert <?= strpos($msg, 'not found') !== false ? 'alert-danger' : 'alert-success' ?>">
            <i class="fas <?= strpos($msg, 'not found') !== false ? 'fa-times-circle' : 'fa-check-circle' ?>"></i>
            <?= htmlspecialchars($msg) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="input-group mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Generate Reset Token</button>
            </div>
          </div>
        </form>

        <hr style="border-color: #e0e0e0; margin: 20px 0;">

        <p class="mb-0 text-center">
          <a href="login.php">
            <i class="fas fa-arrow-left"></i> Back to Login
          </a>
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

</body>
</html>

