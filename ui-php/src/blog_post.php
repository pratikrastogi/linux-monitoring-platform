<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get blog post by slug
$slug = isset($_GET['slug']) ? $db->real_escape_string($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: blog.php");
    exit;
}

// Fetch blog post with author information
$query = "SELECT bp.*, u.username as author_name 
          FROM blog_posts bp 
          LEFT JOIN users u ON bp.author_id = u.id 
          WHERE bp.slug = '$slug' AND bp.status = 'published'";
$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    header("Location: blog.php");
    exit;
}

$post = $result->fetch_assoc();

// Increment view count
$db->query("UPDATE blog_posts SET views = views + 1 WHERE id = " . $post['id']);

// Get related posts
$related_query = "SELECT id, title, slug, excerpt, featured_image, published_at 
                  FROM blog_posts 
                  WHERE status = 'published' AND id != " . $post['id'] . " 
                  ORDER BY published_at DESC 
                  LIMIT 3";
$related_result = $db->query($related_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | KubeArena Blog</title>
    <meta name="description" content="<?php echo htmlspecialchars($post['excerpt'] ?: strip_tags(substr($post['content'], 0, 160))); ?>">
    <meta name="keywords" content="DevOps, Kubernetes, Docker, Linux, RHCSA, RHCE, cloud computing">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://kubearena.pratikrastogi.co.in/blog_post.php?slug=<?php echo urlencode($post['slug']); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['excerpt'] ?: strip_tags(substr($post['content'], 0, 160))); ?>">
    <?php if ($post['featured_image']): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($post['featured_image']); ?>">
    <?php endif; ?>
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://kubearena.pratikrastogi.co.in/blog_post.php?slug=<?php echo urlencode($post['slug']); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($post['excerpt'] ?: strip_tags(substr($post['content'], 0, 160))); ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.8;
            color: #333;
            background: #f5f7fa;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .article-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0 2rem;
        }

        .article-header-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .article-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .article-meta {
            display: flex;
            gap: 2rem;
            font-size: 1rem;
            opacity: 0.95;
        }

        .featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            margin-top: 2rem;
            border-radius: 15px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .article-content {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            font-size: 1.1rem;
            line-height: 1.9;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 2rem 0;
        }

        .article-content h2 {
            color: #667eea;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .article-content h3 {
            color: #764ba2;
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
            font-size: 1.4rem;
        }

        .article-content p {
            margin-bottom: 1.5rem;
        }

        .article-content ul, .article-content ol {
            margin-left: 2rem;
            margin-bottom: 1.5rem;
        }

        .article-content li {
            margin-bottom: 0.5rem;
        }

        .article-content code {
            background: #f5f7fa;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }

        .article-content pre {
            background: #2d3748;
            color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1.5rem 0;
        }

        .article-content pre code {
            background: none;
            color: #fff;
            padding: 0;
        }

        .article-content blockquote {
            border-left: 4px solid #667eea;
            padding-left: 1.5rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #555;
        }

        .related-posts {
            margin-top: 4rem;
        }

        .related-posts h2 {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .related-card:hover {
            transform: translateY(-5px);
        }

        .related-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .related-content {
            padding: 1.5rem;
        }

        .related-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .related-title a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .related-title a:hover {
            color: #667eea;
        }

        .back-to-blog {
            display: inline-block;
            margin-bottom: 2rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .back-to-blog:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .article-title {
                font-size: 1.8rem;
            }

            .article-content {
                padding: 1.5rem;
                font-size: 1rem;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="logo">KubeArena</a>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="learning_paths.php">Learning Paths</a></li>
                <li><a href="browse_courses.php">Courses</a></li>
                <li><a href="blog.php">Blog</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="article-header">
        <div class="article-header-content">
            <h1 class="article-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="article-meta">
                <span>👤 <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></span>
                <span>📅 <?php echo date('F d, Y', strtotime($post['published_at'])); ?></span>
                <span>👁️ <?php echo $post['views']; ?> views</span>
            </div>
            <?php if ($post['featured_image']): ?>
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="featured-image">
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <a href="blog.php" class="back-to-blog">← Back to Blog</a>

        <article class="article-content">
            <?php echo $post['content']; ?>
        </article>

        <?php if ($related_result && $related_result->num_rows > 0): ?>
            <div class="related-posts">
                <h2>Related Articles</h2>
                <div class="related-grid">
                    <?php while ($related = $related_result->fetch_assoc()): ?>
                        <div class="related-card">
                            <?php if ($related['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="related-image">
                            <?php else: ?>
                                <div class="related-image"></div>
                            <?php endif; ?>
                            <div class="related-content">
                                <h3 class="related-title">
                                    <a href="blog_post.php?slug=<?php echo urlencode($related['slug']); ?>">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </h3>
                                <small><?php echo date('M d, Y', strtotime($related['published_at'])); ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $db->close(); ?>
