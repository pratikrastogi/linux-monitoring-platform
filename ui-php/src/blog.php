<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Pagination
$posts_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Get total posts count
$count_result = $db->query("SELECT COUNT(*) as total FROM blog_posts WHERE status='published'");
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Fetch published blog posts
$query = "SELECT bp.*, u.username as author_name 
          FROM blog_posts bp 
          LEFT JOIN users u ON bp.author_id = u.id 
          WHERE bp.status = 'published' 
          ORDER BY bp.published_at DESC 
          LIMIT $posts_per_page OFFSET $offset";
$result = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Latest DevOps, Kubernetes, Linux Stories | KubeArena</title>
    <meta name="description" content="Read the latest articles on DevOps, Kubernetes, Docker, Linux, RHCSA, RHCE, and cloud technologies. Expert insights and tutorials for IT professionals.">
    <meta name="keywords" content="DevOps blog, Kubernetes tutorials, Docker guides, Linux tips, RHCSA articles, RHCE certification, cloud computing, container orchestration">
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

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .blog-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .blog-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .blog-content {
            padding: 1.5rem;
        }

        .blog-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .blog-title a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .blog-title a:hover {
            color: #667eea;
        }

        .blog-meta {
            display: flex;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .blog-excerpt {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .read-more {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .read-more:hover {
            color: #764ba2;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 3rem;
        }

        .pagination a, .pagination span {
            padding: 0.75rem 1.25rem;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
        }

        .pagination .active {
            background: #667eea;
            color: white;
        }

        .no-posts {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .no-posts h2 {
            color: #667eea;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .blog-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/public_sidebar.php'; ?>
    <?php include 'includes/query_popup.php'; ?>
    
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

    <div class="container">
        <div class="header">
            <h1>📚 KubeArena Blog</h1>
            <p>Expert insights, tutorials, and stories from the world of DevOps, Kubernetes, and Linux</p>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="blog-grid">
                <?php while ($post = $result->fetch_assoc()): ?>
                    <div class="blog-card">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-image">
                        <?php else: ?>
                            <div class="blog-image"></div>
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <h2 class="blog-title">
                                <a href="blog_post.php?slug=<?php echo urlencode($post['slug']); ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            
                            <div class="blog-meta">
                                <span>👤 <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></span>
                                <span>📅 <?php echo date('M d, Y', strtotime($post['published_at'])); ?></span>
                                <span>👁️ <?php echo $post['views']; ?> views</span>
                            </div>
                            
                            <p class="blog-excerpt">
                                <?php 
                                    $excerpt = $post['excerpt'] ?: strip_tags(substr($post['content'], 0, 200));
                                    echo htmlspecialchars($excerpt) . '...'; 
                                ?>
                            </p>
                            
                            <a href="blog_post.php?slug=<?php echo urlencode($post['slug']); ?>" class="read-more">
                                Read More →
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">← Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-posts">
                <h2>No Blog Posts Yet</h2>
                <p>Check back soon for amazing content on DevOps, Kubernetes, Docker, and Linux!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $db->close(); ?>
