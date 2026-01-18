<?php
session_start();
require_once '../auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || !isset($_SESSION['uid'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Learning Paths | KubeArena Admin</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform 0.3s;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .content-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .path-item {
            padding: 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .path-item:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }

        .path-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .path-description {
            color: #666;
            margin-bottom: 1rem;
        }

        .path-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .coming-soon {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px;
            text-align: center;
            margin-top: 2rem;
        }

        .coming-soon h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .coming-soon p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-road"></i> Manage Learning Paths</h1>
            <p>Configure and customize the learning paths displayed on your platform</p>
            <div class="header-actions">
                <a href="../learning_paths.php" class="btn" target="_blank">
                    <i class="fas fa-eye"></i> Preview Public Page
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="content-card">
            <h2>Current Learning Paths</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">These are the learning paths currently visible on your platform</p>

            <div class="path-item">
                <div class="path-title">🔴 RHCSA (Red Hat Certified System Administrator)</div>
                <div class="path-description">Foundation-level Linux certification covering essential system administration skills</div>
                <div class="path-actions">
                    <button class="btn btn-small" disabled>Edit (Coming Soon)</button>
                    <button class="btn btn-secondary btn-small" disabled>Reorder</button>
                </div>
            </div>

            <div class="path-item">
                <div class="path-title">🔴 RHCE (Red Hat Certified Engineer)</div>
                <div class="path-description">Advanced Linux certification for automation and infrastructure management</div>
                <div class="path-actions">
                    <button class="btn btn-small" disabled>Edit (Coming Soon)</button>
                    <button class="btn btn-secondary btn-small" disabled>Reorder</button>
                </div>
            </div>

            <div class="path-item">
                <div class="path-title">🐳 Docker Mastery</div>
                <div class="path-description">Containerization fundamentals and advanced Docker techniques</div>
                <div class="path-actions">
                    <button class="btn btn-small" disabled>Edit (Coming Soon)</button>
                    <button class="btn btn-secondary btn-small" disabled>Reorder</button>
                </div>
            </div>

            <div class="path-item">
                <div class="path-title">☸️ Kubernetes Expert</div>
                <div class="path-description">Container orchestration and cloud-native application deployment</div>
                <div class="path-actions">
                    <button class="btn btn-small" disabled>Edit (Coming Soon)</button>
                    <button class="btn btn-secondary btn-small" disabled>Reorder</button>
                </div>
            </div>

            <div class="path-item">
                <div class="path-title">🚀 DevOps Professional</div>
                <div class="path-description">Complete DevOps pipeline with CI/CD, monitoring, and automation</div>
                <div class="path-actions">
                    <button class="btn btn-small" disabled>Edit (Coming Soon)</button>
                    <button class="btn btn-secondary btn-small" disabled>Reorder</button>
                </div>
            </div>
        </div>

        <div class="coming-soon">
            <h2>🚧 Full Management Features Coming Soon!</h2>
            <p>We're building a comprehensive interface to manage learning paths, including:</p>
            <ul style="list-style: none; margin-top: 1rem; font-size: 1.1rem;">
                <li>✨ Add/Edit/Delete learning paths</li>
                <li>📝 Customize content, prerequisites, and descriptions</li>
                <li>🔄 Reorder paths and manage visibility</li>
                <li>📊 Track student progress on each path</li>
                <li>🎯 Set milestones and achievements</li>
            </ul>
            <p style="margin-top: 2rem;">For now, learning paths are managed directly in the learning_paths.php file.</p>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
