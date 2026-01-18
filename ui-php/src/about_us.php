<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch about us content
$query = "SELECT * FROM about_us ORDER BY id DESC LIMIT 1";
$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    $about = $result->fetch_assoc();
} else {
    // Default content if nothing in database
    $about = [
        'title' => 'About KubeArena',
        'content' => '<p>Welcome to KubeArena - Your platform for mastering Linux, Kubernetes, and DevOps.</p>',
        'mission' => 'To provide free, high-quality hands-on training in cloud technologies.',
        'vision' => 'To empower learners worldwide with practical IT skills.'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($about['title']); ?> | KubeArena</title>
    <meta name="description" content="Learn about KubeArena, our mission, vision, and commitment to providing free hands-on Linux, Kubernetes, and DevOps training.">
    <meta name="keywords" content="About KubeArena, Linux training platform, Kubernetes education, DevOps learning, RHEL certification">
    
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
            line-height: 1.8;
            color: #333;
            background: #f8f9fa;
        }

        .about-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 2rem 3rem;
            text-align: center;
        }

        .about-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInDown 1s;
        }

        .about-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .about-content {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .about-content h2 {
            color: #667eea;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .about-content h3 {
            color: #764ba2;
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
        }

        .about-content p {
            margin-bottom: 1.5rem;
        }

        .about-content ul {
            margin-left: 2rem;
            margin-bottom: 1.5rem;
        }

        .about-content li {
            margin-bottom: 0.8rem;
        }

        .mission-vision-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .mission-vision-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .mission-vision-card:hover {
            transform: translateY(-10px);
        }

        .mission-vision-card h3 {
            color: #667eea;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .mission-vision-card h3 i {
            margin-right: 1rem;
            font-size: 2rem;
        }

        .back-home {
            text-align: center;
            margin-top: 3rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .about-header h1 {
                font-size: 2rem;
            }

            .about-content {
                padding: 2rem 1.5rem;
            }

            .mission-vision-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/public_sidebar.php'; ?>
    <?php include 'includes/query_popup.php'; ?>

    <div class="about-header">
        <h1><?php echo htmlspecialchars($about['title']); ?></h1>
        <p>Your trusted platform for mastering Linux, Kubernetes, Docker, and DevOps</p>
    </div>

    <div class="container">
        <div class="about-content">
            <?php echo $about['content']; ?>
        </div>

        <div class="mission-vision-grid">
            <div class="mission-vision-card">
                <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                <p><?php echo nl2br(htmlspecialchars($about['mission'])); ?></p>
            </div>

            <div class="mission-vision-card">
                <h3><i class="fas fa-eye"></i> Our Vision</h3>
                <p><?php echo nl2br(htmlspecialchars($about['vision'])); ?></p>
            </div>
        </div>

        <div class="back-home">
            <a href="/" class="btn"><i class="fas fa-home"></i> Back to Home</a>
            <a href="browse_courses.php" class="btn"><i class="fas fa-book"></i> Explore Courses</a>
        </div>
    </div>
</body>
</html>
<?php $db->close(); ?>
