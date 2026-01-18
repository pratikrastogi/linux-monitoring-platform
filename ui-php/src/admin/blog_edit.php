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

// Get post ID
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header("Location: blog_manage.php");
    exit;
}

// Fetch post
$query = "SELECT * FROM blog_posts WHERE id = $post_id";
$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    header("Location: blog_manage.php");
    exit;
}

$post = $result->fetch_assoc();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $db->real_escape_string($_POST['title']);
    $content = $_POST['content'];
    $excerpt = $db->real_escape_string($_POST['excerpt']);
    $status = $db->real_escape_string($_POST['status']);
    $featured_image = $db->real_escape_string($_POST['featured_image'] ?? '');
    
    // Generate new slug if title changed
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    // Check if slug exists (excluding current post)
    $slug_check = $db->query("SELECT id FROM blog_posts WHERE slug = '$slug' AND id != $post_id");
    if ($slug_check->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    $published_at = ($status === 'published' && !$post['published_at']) ? date('Y-m-d H:i:s') : $post['published_at'];
    
    $query = "UPDATE blog_posts SET 
              title = '$title', 
              slug = '$slug', 
              content = '$content', 
              excerpt = '$excerpt', 
              featured_image = '$featured_image', 
              status = '$status', 
              published_at = " . ($status === 'published' && $published_at ? "'$published_at'" : 'NULL') . "
              WHERE id = $post_id";
    
    if ($db->query($query)) {
        $success_message = "Blog post updated successfully!";
        if ($status === 'published') {
            $success_message .= " <a href='../blog_post.php?slug=$slug' target='_blank'>View Post</a>";
        }
        // Refresh post data
        $result = $db->query("SELECT * FROM blog_posts WHERE id = $post_id");
        $post = $result->fetch_assoc();
    } else {
        $error_message = "Error updating post: " . $db->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post | KubeArena Admin</title>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 600,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
            ],
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | link image media codesample | code fullscreen preview',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; font-size: 16px; line-height: 1.8; }',
            image_title: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_url: '../api/upload_blog_image.php',
            images_upload_handler: function (blobInfo, success, failure) {
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '../api/upload_blog_image.php');
                xhr.onload = function() {
                    var json;
                    if (xhr.status != 200) {
                        failure('HTTP Error: ' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    if (!json || typeof json.location != 'string') {
                        failure('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    success(json.location);
                };
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            }
        });
    </script>
    
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
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="url"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="url"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .image-preview {
            margin-top: 1rem;
            max-width: 300px;
        }

        .image-preview img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .post-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .post-info p {
            margin: 0.5rem 0;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Blog Post</h1>
            <div class="header-actions">
                <a href="blog_manage.php" class="btn btn-secondary">Back to Manage</a>
                <a href="../dashboard.php" class="btn">Dashboard</a>
            </div>
        </div>

        <div class="form-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="post-info">
                <p><strong>Created:</strong> <?php echo date('F d, Y H:i', strtotime($post['created_at'])); ?></p>
                <p><strong>Last Updated:</strong> <?php echo date('F d, Y H:i', strtotime($post['updated_at'])); ?></p>
                <?php if ($post['published_at']): ?>
                    <p><strong>Published:</strong> <?php echo date('F d, Y H:i', strtotime($post['published_at'])); ?></p>
                <?php endif; ?>
                <p><strong>Views:</strong> <?php echo $post['views']; ?></p>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Post Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($post['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="excerpt">Excerpt (Summary) *</label>
                    <textarea id="excerpt" name="excerpt" required><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="featured_image">Featured Image URL</label>
                    <input type="url" id="featured_image" name="featured_image" 
                           value="<?php echo htmlspecialchars($post['featured_image']); ?>"
                           onchange="previewImage(this.value)">
                    <?php if ($post['featured_image']): ?>
                        <div id="image-preview" class="image-preview">
                            <img id="preview-img" src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Preview">
                        </div>
                    <?php else: ?>
                        <div id="image-preview" class="image-preview" style="display: none;">
                            <img id="preview-img" src="" alt="Preview">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Update Blog Post</button>
                    <a href="blog_manage.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(url) {
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('preview-img');
            
            if (url) {
                img.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php $db->close(); ?>
