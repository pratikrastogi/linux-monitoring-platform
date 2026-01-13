<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$case_id = isset($_GET['case_id']) ? (int)$_GET['case_id'] : 0;
$uid = $_SESSION['uid'];
$role = $_SESSION['role'] ?? 'user';

if (!$case_id) {
    echo json_encode(['error' => 'Invalid case ID']);
    exit;
}

$conn = new mysqli("mysql", "monitor", "monitor123", "monitoring");
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB Error']);
    exit;
}

// Verify user has access to this case
if ($role === 'user') {
    $verify = $conn->query("SELECT id FROM support_cases WHERE id=$case_id AND user_id=$uid");
    if (!$verify || $verify->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
}

// Fetch all messages for this case
$messages_q = $conn->query("SELECT scm.*, 
    CASE 
        WHEN scm.sender_type='USER' THEN u.username
        ELSE a.username
    END as sender_name,
    CASE 
        WHEN scm.sender_type='USER' THEN u.email
        ELSE a.email
    END as sender_email
    FROM support_case_messages scm
    LEFT JOIN users u ON scm.sender_type='USER' AND scm.sender_id = u.id
    LEFT JOIN users a ON scm.sender_type='ADMIN' AND scm.sender_id = a.id
    WHERE scm.case_id = $case_id
    ORDER BY scm.created_at ASC");

if (!$messages_q) {
    echo json_encode(['error' => 'Query error']);
    exit;
}

$html = '';
while ($msg = $messages_q->fetch_assoc()) {
    $is_user = $msg['sender_type'] === 'USER';
    $is_current_user = ($role === 'user' && $is_user) || ($role === 'admin' && !$is_user);
    $align = $is_current_user ? 'right' : 'left';
    $bg_class = $is_current_user ? 'bg-primary' : ($is_user ? 'bg-secondary' : 'bg-success');
    
    $sender_label = $is_user ? 'User' : 'Support Team';
    if ($is_current_user) {
        $sender_label = ($is_user ? 'You' : 'You (Support)');
    }
    
    $html .= '<div class="direct-chat-msg ' . $align . '" style="margin-bottom: 15px;">
        <div class="direct-chat-infos clearfix" style="margin-bottom: 5px;">
            <span class="direct-chat-name float-' . $align . '" style="font-weight: bold;">
                ' . $sender_label . '
            </span>
            <span class="direct-chat-timestamp float-' . ($align === 'right' ? 'left' : 'right') . '" style="font-size: 12px; color: #999;">
                ' . date('M d, Y H:i', strtotime($msg['created_at'])) . '
            </span>
        </div>
        <div class="direct-chat-text ' . $bg_class . '" style="' . ($is_current_user ? 'margin-left: 50px; border-radius: 8px;' : 'margin-right: 50px; border-radius: 8px;') . ' color: white; padding: 10px 15px;">
            ' . nl2br(htmlspecialchars($msg['message'] ?? '')) . '
        </div>
    </div>';
}

echo json_encode(['messages' => $html, 'count' => $messages_q->num_rows]);
?>
