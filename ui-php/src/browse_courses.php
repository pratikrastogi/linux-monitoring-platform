<?php
session_start();
$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");

// Get all active courses
$courses_query = $db->query("SELECT * FROM courses WHERE active=1 ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Browse Courses | KubeArena - Free Linux & Kubernetes Labs</title>
  <meta name="description" content="Explore free hands-on Linux, RHEL, RHCE, RHCSA, Kubernetes, and Docker courses with interactive lab environments.">
  <meta name="keywords" content="Linux courses, RHEL training, Kubernetes labs, Docker tutorials, DevOps courses, free Linux practice">
  
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8f9fa;
    }
    
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    
    .header h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
    }
    
    .nav-bar {
      background: white;
      padding: 1rem 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .nav-links a {
      margin: 0 1rem;
      text-decoration: none;
      color: #667eea;
      font-weight: 600;
    }
    
    .nav-links a:hover {
      text-decoration: underline;
    }
    
    .btn {
      padding: 0.6rem 1.5rem;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-primary {
      background: #667eea;
      color: white;
    }
    
    .btn-primary:hover {
      background: #5568d3;
      transform: translateY(-2px);
    }
    
    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
    }
    
    .courses-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }
    
    .course-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .course-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .course-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.5rem;
    }
    
    .course-header h3 {
      margin: 0;
      font-size: 1.5rem;
    }
    
    .course-body {
      padding: 1.5rem;
    }
    
    .course-description {
      color: #666;
      margin-bottom: 1rem;
      line-height: 1.6;
    }
    
    .course-meta {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
      color: #888;
      font-size: 0.9rem;
    }
    
    .course-meta i {
      color: #667eea;
    }
    
    .login-prompt {
      background: #fff3cd;
      padding: 1rem;
      border-radius: 8px;
      border-left: 4px solid #ffc107;
      margin-top: 1rem;
      text-align: center;
    }
    
    .login-prompt a {
      color: #667eea;
      font-weight: 600;
      text-decoration: none;
    }
    
    .login-prompt a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <div class="nav-bar">
    <div class="nav-links">
      <a href="index.php"><i class="fas fa-home"></i> Home</a>
      <a href="browse_courses.php"><i class="fas fa-book"></i> Courses</a>
    </div>
    <div>
      <?php if (isset($_SESSION['user'])): ?>
        <a href="index.php" class="btn btn-primary"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Header -->
  <div class="header">
    <h1><i class="fas fa-graduation-cap"></i> Browse Courses</h1>
    <p>Explore free hands-on Linux, Kubernetes, and DevOps training</p>
  </div>

  <!-- Courses Grid -->
  <div class="container">
    <?php if (!isset($_SESSION['user'])): ?>
    <div class="login-prompt">
      <i class="fas fa-info-circle"></i> To access interactive labs and request course enrollment, please <a href="register.php">register</a> or <a href="login.php">login</a>
    </div>
    <?php endif; ?>

    <div class="courses-grid">
      <?php while ($course = $courses_query->fetch_assoc()): ?>
      <div class="course-card">
        <div class="course-header">
          <h3><?php echo htmlspecialchars($course['name']); ?></h3>
        </div>
        <div class="course-body">
          <p class="course-description">
            <?php echo htmlspecialchars($course['description'] ?? 'Hands-on practical training'); ?>
          </p>
          
          <div class="course-meta">
            <span><i class="fas fa-clock"></i> <?php echo $course['duration_minutes']; ?> mins</span>
            <span><i class="fas fa-flask"></i> Interactive Labs</span>
          </div>
          
          <?php if (isset($_SESSION['user'])): ?>
            <a href="course_view.php?id=<?php echo $course['id']; ?>" class="btn btn-primary" style="display: block; text-align: center;">
              <i class="fas fa-arrow-right"></i> View Course
            </a>
          <?php else: ?>
            <a href="login.php" class="btn btn-primary" style="display: block; text-align: center; background: #ccc;">
              <i class="fas fa-lock"></i> Login to Access
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>
