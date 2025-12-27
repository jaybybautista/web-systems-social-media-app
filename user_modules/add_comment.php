<?php
session_start();
include('../config.php');

// Get POST data
$post_id = $_POST['post_id'] ?? null;
$comment_text = trim($_POST['comment_text'] ?? '');
$parent_id = $_POST['parent_id'] ?: null;
$user_id = $_SESSION['id'] ?? null;

header('Content-Type: application/json');

// Validate
if (!$user_id || !$post_id || empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iisi", $post_id, $user_id, $comment_text, $parent_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    // Get updated comment count for this post
    $result = $conn->query("SELECT COUNT(*) AS total FROM comments WHERE post_id = $post_id");
    $count = $result->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'comment_count' => $count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save comment']);
}
