<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user']) && isset($_SESSION['uid'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KubeArena - Free Linux Practice Labs | RHEL, RHCE, RHCSA Training</title>
  <meta name="description" content="KubeArena offers free hands-on Linux labs including RHEL, RHCE, RHCSA certification prep, Kubernetes, Docker, and DevOps training. Get interactive terminal access and real-time server monitoring for complete Linux practice.">
  <meta name="keywords" content="Linux, RHEL, RHCE, RHCSA, Kubernetes, Docker, DevOps, free Linux practice, server access, lab environment, terminal, containers, cloud, certification prep, hands-on labs, KubeArena">
  <meta name="author" content="KubeArena Team">
  <meta name="robots" content="index, follow">
  <meta name="language" content="English">
  
  <!-- Canonical URL -->
  <link rel="canonical" href="https://kubearena.pratikrastogi.co.in/">
  
  <!-- Open Graph / Social Media Tags -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://kubearena.pratikrastogi.co.in/">
  <meta property="og:title" content="KubeArena - Free Linux Practice Labs | RHEL, RHCE, RHCSA">
  <meta property="og:description" content="Access free hands-on Linux labs for RHEL, RHCE, RHCSA, Kubernetes, Docker, and DevOps with interactive terminal and real-time monitoring.">
  <meta property="og:site_name" content="KubeArena">
  
  <!-- Twitter Card Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="KubeArena - Free Linux Practice Labs | RHEL, RHCE, RHCSA">
  <meta name="twitter:description" content="Access free hands-on Linux labs for RHEL, RHCE, RHCSA, Kubernetes, Docker, and DevOps with interactive terminal and real-time monitoring.">
  
  <!-- Structured Data (JSON-LD Schema) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "EducationalOrganization",
    "name": "KubeArena",
    "url": "https://kubearena.pratikrastogi.co.in",
    "description": "Free hands-on Linux and Kubernetes lab learning platform with RHEL, RHCE, RHCSA certification prep, Docker, and DevOps training",
    "offers": {
      "@type": "EducationalOccupationalProgram",
      "name": "Linux, RHEL, Kubernetes, Docker, DevOps Labs",
      "description": "Interactive hands-on labs for Linux, RHEL, RHCE, RHCSA, Kubernetes, Docker, and DevOps with live terminal access and server management"
    }
  }
  </script>

  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600&display=swap">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      color: #333;
    }
    
    .hero {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem;
    }
    
    .hero-content {
      max-width: 800px;
    }
    
    h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 3rem;
      margin-bottom: 1.5rem;
      animation: fadeInDown 1s;
    }
    
    .subtitle {
      font-size: 1.25rem;
      margin-bottom: 2rem;
      opacity: 0.95;
    }
    
    .cta-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      margin: 2rem 0;
    }
    
    .btn {
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
      font-weight: 600;
    }
    
    .btn-primary {
      background: white;
      color: #667eea;
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .btn-secondary {
      background: transparent;
      color: white;
      border: 2px solid white;
    }
    
    .btn-secondary:hover {
      background: white;
      color: #667eea;
    }
    
    .features {
      padding: 4rem 2rem;
      background: #f8f9fa;
    }
    
    .features-grid {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
    }
    
    .feature-card {
      background: white;
      padding: 2rem;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }
    
    .feature-card:hover {
      transform: translateY(-5px);
    }
    
    .feature-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: #667eea;
    }
    
    .feature-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #333;
    }
    
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @media (max-width: 768px) {
      h1 {
        font-size: 2rem;
      }
      .cta-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>🚀 KubeArena</h1>
      <p class="subtitle">Free Hands-on Linux Labs for RHEL, RHCE, RHCSA, Kubernetes & DevOps</p>
      <p style="margin-bottom: 2rem;">Master Linux, Kubernetes, Docker, and DevOps with interactive lab environments. Get real terminal access and practice on live servers.</p>
      
      <div class="cta-buttons">
        <a href="browse_courses.php" class="btn btn-primary"><i class="fas fa-book"></i> Browse Courses</a>
        <a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Start Learning Free</a>
        <a href="login.php" class="btn btn-secondary"><i class="fas fa-sign-in-alt"></i> Login</a>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features">
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon"><i class="fab fa-linux"></i></div>
        <h3 class="feature-title">Linux & RHEL Labs</h3>
        <p>Practice RHEL, RHCE, RHCSA certification labs with real server access and hands-on terminal sessions.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-dharmachakra"></i></div>
        <h3 class="feature-title">Kubernetes Training</h3>
        <p>Deploy, manage, and scale containers with Kubernetes. Learn kubectl, deployments, services, and more.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fab fa-docker"></i></div>
        <h3 class="feature-title">Docker & Containers</h3>
        <p>Master containerization with Docker. Build images, manage containers, and orchestrate deployments.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-terminal"></i></div>
        <h3 class="feature-title">Live Terminal Access</h3>
        <p>Get real SSH access to lab servers. Practice commands, scripts, and configurations in live environments.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
        <h3 class="feature-title">Real-time Monitoring</h3>
        <p>Track server metrics, resource usage, and lab performance with integrated monitoring dashboards.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-certificate"></i></div>
        <h3 class="feature-title">Certification Prep</h3>
        <p>Prepare for RHCSA, RHCE, CKA, and other certifications with guided labs and practice exams.</p>
      </div>
    </div>
  </section>
</body>
</html>
