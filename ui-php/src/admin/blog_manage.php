<?php
session_start();
require_once '../auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || !isset($_SESSION['uid'])) {
    header("Location: ../login.php");
    exit;
}

// Database connection
$db = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$user_id = $_SESSION['uid'];

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    $db->query("DELETE FROM blog_posts WHERE id = $post_id");
    header("Location: blog_manage.php");
    exit;
}

// Handle publish/unpublish action
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    $result = $db->query("SELECT status FROM blog_posts WHERE id = $post_id");
    if ($row = $result->fetch_assoc()) {
        $new_status = ($row['status'] === 'published') ? 'draft' : 'published';
        $published_at = ($new_status === 'published') ? date('Y-m-d H:i:s') : 'NULL';
        $db->query("UPDATE blog_posts SET status = '$new_status', published_at = " . 
                   ($new_status === 'published' ? "'$published_at'" : 'NULL') . " WHERE id = $post_id");
    }
    header("Location: blog_manage.php");
    exit;
}

// Fetch all blog posts
$query = "SELECT bp.*, u.username as author_name 
          FROM blog_posts bp 
          LEFT JOIN users u ON bp.author_id = u.id 
          ORDER BY bp.created_at DESC";
$result = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts | KubeArena Admin</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #667eea;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #333;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .posts-table {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        th:first-child {
            border-radius: 8px 0 0 0;
        }

        th:last-child {
            border-radius: 0 8px 0 0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f5f7fa;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-published {
            background: #d4edda;
            color: #155724;
        }

        .status-draft {
            background: #fff3cd;
            color: #856404;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .post-title {
            color: #333;
            font-weight: 600;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .no-posts {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .confirm-delete {
            display: inline;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Manage Blog Posts</h1>
            <div class="header-actions">
                <a href="blog_create.php" class="btn btn-success">Create New Post</a>
                <a href="../dashboard.php" class="btn">Back to Dashboard</a>
            </div>
        </div>

        <?php
        // Get statistics
        $stats_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) as published,
                        SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) as drafts,
                        SUM(views) as total_views
                        FROM blog_posts";
        $stats_result = $db->query($stats_query);
        $stats = $stats_result->fetch_assoc();
        ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['published']; ?></div>
                <div class="stat-label">Published</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['drafts']; ?></div>
                <div class="stat-label">Drafts</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_views']); ?></div>
                <div class="stat-label">Total Views</div>
            </div>
        </div>

        <div class="posts-table">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="post-title" title="<?php echo htmlspecialchars($post['title']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $post['status']; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $post['views']; ?></td>
                                <td>
                                    <?php echo $post['published_at'] ? date('M d, Y', strtotime($post['published_at'])) : 'Not published'; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($post['status'] === 'published'): ?>
                                            <a href="../blog_post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                               class="btn btn-sm" target="_blank">View</a>
                                        <?php endif; ?>
                                        <a href="blog_edit.php?id=<?php echo $post['id']; ?>" 
                                           class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?action=toggle_status&id=<?php echo $post['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <?php echo $post['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $post['id']; ?>" 
                                           class="btn btn-danger btn-sm confirm-delete"
                                           onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-posts">
                    <h2>No blog posts yet</h2>
                    <p>Create your first blog post to get started!</p>
                    <a href="blog_create.php" class="btn btn-success" style="margin-top: 1rem;">Create First Post</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $db->close(); ?>
