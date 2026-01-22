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
$success_message = '';
$error_message = '';

// Fetch current about us content
$query = "SELECT * FROM about_us ORDER BY id DESC LIMIT 1";
$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    $about = $result->fetch_assoc();
} else {
    $about = [
        'title' => 'About KubeArena',
        'content' => '',
        'mission' => '',
        'vision' => ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $db->real_escape_string($_POST['title']);
    $content = $_POST['content']; // Don't escape HTML content
    $mission = $db->real_escape_string($_POST['mission']);
    $vision = $db->real_escape_string($_POST['vision']);
    
    // Check if record exists
    if (isset($about['id'])) {
        // Update existing
        $query = "UPDATE about_us SET 
                  title = '$title',
                  content = '$content',
                  mission = '$mission',
                  vision = '$vision',
                  updated_by = $user_id
                  WHERE id = " . $about['id'];
    } else {
        // Insert new
        $query = "INSERT INTO about_us (title, content, mission, vision, updated_by) 
                  VALUES ('$title', '$content', '$mission', '$vision', $user_id)";
    }
    
    if ($db->query($query)) {
        $success_message = "About Us page updated successfully! <a href='../about_us.php' target='_blank'>View Page</a>";
        // Refresh data
        $result = $db->query("SELECT * FROM about_us ORDER BY id DESC LIMIT 1");
        $about = $result->fetch_assoc();
    } else {
        $error_message = "Error updating page: " . $db->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Us | KubeArena Admin</title>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/bixmwajb9c8cblg6z3c2go7z33k4y7mrasbl69008q34qb6w/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 600,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic underline | ' +
                'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | link image | code preview',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; font-size: 16px; line-height: 1.8; }'
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
            border: none;
            cursor: pointer;
            transition: transform 0.3s;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .form-container {
            background: white;
            padding: 2.5rem;
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
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus {
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

        .info-text {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit About Us Page</h1>
            <div class="header-actions">
                <a href="../about_us.php" class="btn" target="_blank">Preview Page</a>
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <div class="form-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Page Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($about['title']); ?>">
                    <p class="info-text">Main heading that appears on the about us page</p>
                </div>

                <div class="form-group">
                    <label for="content">Main Content *</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($about['content']); ?></textarea>
                    <p class="info-text">Full HTML content for the about us page</p>
                </div>

                <div class="form-group">
                    <label for="mission">Our Mission *</label>
                    <textarea id="mission" name="mission" required rows="4"><?php echo htmlspecialchars($about['mission']); ?></textarea>
                    <p class="info-text">Brief statement about KubeArena's mission</p>
                </div>

                <div class="form-group">
                    <label for="vision">Our Vision *</label>
                    <textarea id="vision" name="vision" required rows="4"><?php echo htmlspecialchars($about['vision']); ?></textarea>
                    <p class="info-text">Brief statement about KubeArena's vision</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="../about_us.php" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php $db->close(); ?>
