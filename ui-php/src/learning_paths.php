<?php
// Session already started by index.php if included, or start new session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Paths - RHCSA to DevOps | KubeArena</title>
    <meta name="description" content="Structured learning paths from Linux basics to DevOps mastery. Learn RHCSA, RHCE, Docker, Kubernetes, and DevOps with hands-on labs and clear prerequisites.">
    <meta name="keywords" content="RHCSA learning path, RHCE certification, Docker training, Kubernetes course, DevOps roadmap, Linux certification path, IT career progression">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        .pathway-flow {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .flow-title {
            text-align: center;
            font-size: 1.8rem;
            color: #667eea;
            margin-bottom: 2rem;
        }

        .flow-steps {
            display: flex;
            justify-content: space-around;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .flow-step {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-weight: bold;
            position: relative;
        }

        .flow-arrow {
            font-size: 2rem;
            color: #667eea;
        }

        .path-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .path-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .path-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .path-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .path-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .path-level {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .path-description {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .prerequisites {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .prerequisites h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .prerequisites ul {
            list-style: none;
            padding-left: 0;
        }

        .prerequisites li {
            padding: 0.3rem 0;
            color: #555;
        }

        .prerequisites li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
        }

        .why-choose {
            margin-bottom: 1rem;
        }

        .why-choose h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .why-choose p {
            color: #555;
            font-size: 0.95rem;
        }

        .duration {
            background: #ffc107;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 1rem;
        }

        .cta-section {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            margin-top: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .cta-section h2 {
            color: #333;
            margin-bottom: 1rem;
        }

        .cta-section p {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 0 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes glow {
            0%, 100% {
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            50% {
                box-shadow: 0 15px 45px rgba(102, 126, 234, 0.3);
            }
        }

        @keyframes rotateIcon {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .header {
            animation: slideInDown 0.8s ease-out;
        }

        .header h1 {
            animation: slideInDown 1s ease-out 0.2s both;
        }

        .header p {
            animation: slideInDown 1s ease-out 0.4s both;
        }

        .pathway-flow {
            animation: fadeInScale 0.8s ease-out 0.3s both;
        }

        .flow-step {
            animation: pulse 2s infinite;
        }

        .flow-step:nth-child(1) { animation-delay: 0s; }
        .flow-step:nth-child(3) { animation-delay: 0.3s; }
        .flow-step:nth-child(5) { animation-delay: 0.6s; }
        .flow-step:nth-child(7) { animation-delay: 0.9s; }
        .flow-step:nth-child(9) { animation-delay: 1.2s; }

        .flow-arrow {
            animation: rotateIcon 3s ease-in-out infinite;
        }

        .path-card {
            animation: slideInUp 0.6s ease-out both;
        }

        .path-card:nth-child(1) { animation-delay: 0.2s; }
        .path-card:nth-child(2) { animation-delay: 0.4s; }
        .path-card:nth-child(3) { animation-delay: 0.6s; }
        .path-card:nth-child(4) { animation-delay: 0.8s; }
        .path-card:nth-child(5) { animation-delay: 1s; }

        .path-card {
            animation: slideInUp 0.6s ease-out both, glow 3s ease-in-out 0.5s infinite;
        }

        .path-card-title {
            transition: all 0.3s ease;
        }

        .path-card:hover .path-card-title {
            color: #667eea;
            transform: translateX(5px);
        }

        .duration {
            animation: pulse 1.5s ease-in-out infinite;
        }

        .cta-section {
            animation: slideInUp 0.8s ease-out 1.2s both;
        }

        .btn {
            transition: all 0.3s ease;
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
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .flow-steps {
                flex-direction: column;
            }

            .flow-arrow {
                transform: rotate(90deg);
            }

            .path-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/public_sidebar.php'; ?>
    <?php include 'includes/query_popup.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Your Journey to DevOps Mastery</h1>
            <p>Structured learning paths designed to take you from Linux basics to advanced DevOps engineering</p>
        </div>

        <div class="pathway-flow">
            <h2 class="flow-title">Complete Learning Pathway</h2>
            <div class="flow-steps">
                <div class="flow-step">RHCSA</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">RHCE</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">Docker</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">Kubernetes</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">DevOps</div>
            </div>
        </div>

        <div class="path-cards">
            <!-- RHCSA Card -->
            <div class="path-card">
                <div class="path-icon">🐧</div>
                <h3 class="path-title">RHCSA</h3>
                <span class="path-level">Beginner Level</span>
                <p class="path-description">
                    Red Hat Certified System Administrator (RHCSA) is the foundation of your Linux journey. 
                    Master essential system administration tasks, file management, user administration, 
                    storage management, and basic networking.
                </p>
                
                <div class="prerequisites">
                    <h4>Prerequisites</h4>
                    <ul>
                        <li>Basic computer knowledge</li>
                        <li>No prior Linux experience required</li>
                        <li>Willingness to learn command-line interface</li>
                    </ul>
                </div>

                <div class="why-choose">
                    <h4>Why Choose This Path?</h4>
                    <p>
                        RHCSA is the industry-standard certification for Linux system administrators. 
                        It opens doors to IT careers and is essential for anyone pursuing DevOps, 
                        cloud computing, or system engineering roles.
                    </p>
                </div>

                <div class="duration">⏱️ Duration: 2-3 months</div>
            </div>

            <!-- RHCE Card -->
            <div class="path-card">
                <div class="path-icon">⚙️</div>
                <h3 class="path-title">RHCE</h3>
                <span class="path-level">Intermediate Level</span>
                <p class="path-description">
                    Red Hat Certified Engineer (RHCE) builds upon RHCSA skills with advanced automation 
                    using Ansible. Learn to automate system administration tasks, configure services, 
                    and manage complex infrastructures at scale.
                </p>
                
                <div class="prerequisites">
                    <h4>Prerequisites</h4>
                    <ul>
                        <li>RHCSA certification or equivalent knowledge</li>
                        <li>6+ months Linux administration experience</li>
                        <li>Understanding of YAML syntax (helpful)</li>
                    </ul>
                </div>

                <div class="why-choose">
                    <h4>Why Choose This Path?</h4>
                    <p>
                        RHCE demonstrates advanced automation skills crucial for modern IT operations. 
                        Ansible expertise is highly sought after in DevOps roles, making this certification 
                        valuable for career advancement.
                    </p>
                </div>

                <div class="duration">⏱️ Duration: 2-3 months</div>
            </div>

            <!-- Docker Card -->
            <div class="path-card">
                <div class="path-icon">🐳</div>
                <h3 class="path-title">Docker</h3>
                <span class="path-level">Intermediate Level</span>
                <p class="path-description">
                    Master containerization with Docker. Learn to build, ship, and run applications 
                    in isolated containers. Understand Docker images, Dockerfiles, volumes, networks, 
                    and Docker Compose for multi-container applications.
                </p>
                
                <div class="prerequisites">
                    <h4>Prerequisites</h4>
                    <ul>
                        <li>RHCSA or solid Linux fundamentals</li>
                        <li>Basic networking concepts</li>
                        <li>Understanding of application deployment</li>
                        <li>Command-line proficiency</li>
                    </ul>
                </div>

                <div class="why-choose">
                    <h4>Why Choose This Path?</h4>
                    <p>
                        Docker revolutionized application deployment. It's the foundation of modern 
                        microservices architecture and essential for cloud-native development. 
                        Docker skills are mandatory for DevOps engineers.
                    </p>
                </div>

                <div class="duration">⏱️ Duration: 1-2 months</div>
            </div>

            <!-- Kubernetes Card -->
            <div class="path-card">
                <div class="path-icon">☸️</div>
                <h3 class="path-title">Kubernetes</h3>
                <span class="path-level">Advanced Level</span>
                <p class="path-description">
                    Dive into container orchestration with Kubernetes. Learn to deploy, scale, and 
                    manage containerized applications across clusters. Master pods, deployments, 
                    services, ingress, ConfigMaps, secrets, and helm charts.
                </p>
                
                <div class="prerequisites">
                    <h4>Prerequisites</h4>
                    <ul>
                        <li>Docker proficiency</li>
                        <li>Linux system administration</li>
                        <li>Networking fundamentals (DNS, load balancing)</li>
                        <li>YAML configuration experience</li>
                    </ul>
                </div>

                <div class="why-choose">
                    <h4>Why Choose This Path?</h4>
                    <p>
                        Kubernetes is the industry standard for container orchestration. Major cloud 
                        providers (AWS, Azure, GCP) offer managed Kubernetes services. K8s expertise 
                        commands premium salaries in the job market.
                    </p>
                </div>

                <div class="duration">⏱️ Duration: 3-4 months</div>
            </div>

            <!-- DevOps Card -->
            <div class="path-card">
                <div class="path-icon">🚀</div>
                <h3 class="path-title">DevOps Engineering</h3>
                <span class="path-level">Expert Level</span>
                <p class="path-description">
                    Complete your journey by integrating all skills into DevOps practices. Learn CI/CD 
                    pipelines, infrastructure as code (Terraform), monitoring (Prometheus, Grafana), 
                    GitOps, and cloud platforms (AWS/Azure/GCP).
                </p>
                
                <div class="prerequisites">
                    <h4>Prerequisites</h4>
                    <ul>
                        <li>Kubernetes hands-on experience</li>
                        <li>Docker containerization skills</li>
                        <li>Automation experience (Ansible/scripting)</li>
                        <li>Git version control proficiency</li>
                        <li>Understanding of software development lifecycle</li>
                    </ul>
                </div>

                <div class="why-choose">
                    <h4>Why Choose This Path?</h4>
                    <p>
                        DevOps engineers are among the highest-paid IT professionals. This path combines 
                        development and operations, enabling you to build automated, scalable, and reliable 
                        systems. High demand across all industries.
                    </p>
                </div>

                <div class="duration">⏱️ Duration: 4-6 months</div>
            </div>
        </div>

        <div class="cta-section">
            <h2>Ready to Start Your Learning Journey?</h2>
            <p>Join KubeArena today and get access to hands-on labs, real servers, and practical experience</p>
            <a href="register.php" class="btn">Get Started Free</a>
            <a href="browse_courses.php" class="btn btn-secondary">Explore Courses</a>
        </div>
    </div>
</body>
</html>
