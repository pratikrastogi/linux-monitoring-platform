<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Pratik's Microservices Class</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .instructor-profile {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .photo-placeholder {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3em;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .instructor-info h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.8em;
        }
        
        .instructor-info p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        
        .badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-top: 10px;
        }
        
        .topics {
            margin-top: 30px;
        }
        
        .topics h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .topic-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .topic-card h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .topic-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
        }
        
        .highlight h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Pratik's Class</h1>
            <p>Master the Art of Microservices, Kubernetes & DevOps</p>
        </div>
        
        <div class="content">
            <div class="instructor-profile">
                <div class="photo-placeholder">
                    P
                </div>
                <div class="instructor-info">
                    <h2>Pratik</h2>
                    <p><strong>Kyndryl KSAT SRE - Global Team</strong></p>
                    <p>With over 10+ years of extensive experience in Site Reliability Engineering, DevOps, and Cloud Infrastructure</p>
                    <span class="badge">Senior SRE Expert</span>
                    <span class="badge">10+ Years Experience</span>
                </div>
            </div>
            
            <div class="topics">
                <h3>Today's Learning Topics</h3>
                
                <div class="topic-card">
                    <h4>🚀 Microservices Architecture</h4>
                    <p>Dive deep into microservices design patterns, best practices, and real-world implementation strategies that power modern cloud-native applications.</p>
                </div>
                
                <div class="topic-card">
                    <h4>☸️ Kubernetes Mastery</h4>
                    <p>Understand container orchestration, deployment strategies, and why Kubernetes has become the industry standard for managing containerized workloads.</p>
                </div>
                
                <div class="topic-card">
                    <h4>🛠️ DevOps Tools & Culture</h4>
                    <p>Explore essential DevOps tools, CI/CD pipelines, infrastructure as code, and learn why mastering these skills is critical in today's tech landscape.</p>
                </div>
            </div>
            
            <div class="highlight">
                <h3>⏰ Why Learning These Skills is the Need of the Hour</h3>
                <p>In today's rapidly evolving tech ecosystem, Kubernetes and DevOps practices are no longer optional—they're essential. Organizations worldwide are adopting cloud-native architectures, and professionals with these skills are in high demand.</p>
            </div>
        </div>
    </div>
</body>
</html>
