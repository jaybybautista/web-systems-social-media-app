<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

$user_id = $_SESSION['id'] ?? null;

/**
 * We handle both 'id' (GET/REQUEST) and 'comment_id' (POST) 
 * to ensure compatibility with your JavaScript fetch call.
 */
$comment_id = intval($_POST['comment_id'] ?? $_REQUEST['id'] ?? 0);

if (!$user_id || !$comment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request or session expired']);
    exit;
}

/* 1. VERIFY OWNERSHIP */
// We check if the comment exists and belongs to the logged-in user
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$fetched = $stmt->fetch();
$stmt->close();

if (!$fetched) {
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit;
}

// Check ownership (using != to allow for string/int comparison flexibility)
if ($owner_id != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/* 2. DELETE */
// Note: This targets the specific comment ID. 
// If your database uses 'ON DELETE CASCADE' for a parent_id column, replies will also be removed.
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
exit;