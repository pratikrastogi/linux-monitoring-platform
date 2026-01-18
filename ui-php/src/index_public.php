<?php
// Session already started by index.php

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
      overflow-x: hidden;
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
      position: relative;
      overflow: hidden;
    }
    
    /* Animated background particles */
    .hero::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      top: -50%;
      left: -50%;
      background: 
        radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px),
        radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
      background-size: 80px 80px;
      background-position: 0 0, 40px 40px;
      animation: particleMove 20s linear infinite;
    }
    
    @keyframes particleMove {
      0% { transform: translate(0, 0); }
      100% { transform: translate(80px, 80px); }
    }
    
    /* Floating shapes */
    .hero::after {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
      top: 10%;
      right: 5%;
      animation: floatShape 15s ease-in-out infinite;
    }
    
    @keyframes floatShape {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      33% { transform: translate(30px, -30px) rotate(120deg); }
      66% { transform: translate(-30px, 30px) rotate(240deg); }
    }
    
    .hero-content {
      max-width: 800px;
      position: relative;
      z-index: 1;
    }
    
    h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 3rem;
      margin-bottom: 1.5rem;
      animation: fadeInDown 1s ease-out;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      position: relative;
      display: inline-block;
    }
    
    h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 3px;
      background: white;
      animation: expandLine 1.5s ease-out 0.5s forwards;
    }
    
    @keyframes expandLine {
      to { width: 80%; }
    }
    
    .subtitle {
      font-size: 1.25rem;
      margin-bottom: 2rem;
      opacity: 0;
      animation: fadeInUp 1s ease-out 0.3s forwards;
    }
    
    .hero-content > p {
      opacity: 0;
      animation: fadeInUp 1s ease-out 0.5s forwards;
    }
    
    .cta-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      margin: 2rem 0;
      opacity: 0;
      animation: fadeInUp 1s ease-out 0.7s forwards;
    }
    
    .btn {
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      font-weight: 600;
      position: relative;
      overflow: hidden;
    }
    
    .btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn-primary {
      background: white;
      color: #667eea;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .btn-primary:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    }
    
    .btn-secondary {
      background: transparent;
      color: white;
      border: 2px solid white;
    }
    
    .btn-secondary:hover {
      background: white;
      color: #667eea;
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 15px 35px rgba(255,255,255,0.3);
    }
    
    .btn i {
      transition: transform 0.3s;
      position: relative;
      z-index: 1;
    }
    
    .btn:hover i {
      transform: scale(1.2) rotate(5deg);
    }
    
    .features {
      padding: 4rem 2rem;
      background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
      position: relative;
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
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 0.8s ease-out forwards;
      position: relative;
      overflow: hidden;
    }
    
    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
      transition: left 0.6s;
    }
    
    .feature-card:hover::before {
      left: 100%;
    }
    
    .feature-card:nth-child(1) { animation-delay: 0.1s; }
    .feature-card:nth-child(2) { animation-delay: 0.2s; }
    .feature-card:nth-child(3) { animation-delay: 0.3s; }
    .feature-card:nth-child(4) { animation-delay: 0.4s; }
    .feature-card:nth-child(5) { animation-delay: 0.5s; }
    .feature-card:nth-child(6) { animation-delay: 0.6s; }
    
    .feature-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
    }
    
    .feature-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      transition: all 0.4s;
      display: inline-block;
    }
    
    .feature-card:hover .feature-icon {
      transform: scale(1.2) rotate(360deg);
    }
    
    .feature-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #333;
      transition: color 0.3s;
    }
    
    .feature-card:hover .feature-title {
      color: #667eea;
    }
    
    .feature-card p {
      transition: transform 0.3s;
    }
    
    .feature-card:hover p {
      transform: translateX(5px);
    }
    
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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
    
    /* Pulse animation for rocket emoji */
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    h1:hover {
      animation: pulse 0.6s ease-in-out;
    }
    
    /* Scroll reveal effect */
    @media (prefers-reduced-motion: no-preference) {
      .feature-card {
        will-change: transform, opacity;
      }
    }
    
    @media (max-width: 768px) {
      h1 {
        font-size: 2rem;
      }
      .cta-buttons {
        flex-direction: column;
      }
      .btn {
        width: 100%;
      }
      .hero::after {
        width: 150px;
        height: 150px;
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
        <a href="learning_paths.php" class="btn btn-primary"><i class="fas fa-route"></i> Learning Paths</a>
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
