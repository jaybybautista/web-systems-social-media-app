<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

$user_id = $_SESSION['id'] ?? null;
$comment_id = intval($_POST['comment_id'] ?? 0);

// Matches the JavaScript body: `comment_id=${id}&content=${encodeURIComponent(newText)}`
$content = trim($_POST['content'] ?? '');

if (!$user_id || !$comment_id || $content === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data or session expired'
    ]);
    exit;
}

/* 1. VERIFY COMMENT OWNERSHIP & EXISTENCE */
// We use a prepared statement to check if the comment exists and who it belongs to
$stmt = $conn->prepare("
    SELECT user_id 
    FROM comments 
    WHERE id = ?
");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$fetched = $stmt->fetch();
$stmt->close();

if (!$fetched) {
    echo json_encode([
        'success' => false,
        'message' => 'Comment not found'
    ]);
    exit;
}

// Security Check: Ensure the person logged in is the one who wrote the comment
if (intval($owner_id) !== intval($user_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: You do not own this comment'
    ]);
    exit;
}

/* 2. UPDATE COMMENT */
$stmt = $conn->prepare("
    UPDATE comments 
    SET content = ? 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("sii", $content, $comment_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();

    /* 3. RETURN UPDATED DATA */
    echo json_encode([
        'success' => true,
        'message' => 'Comment updated successfully',
        'comment' => [
            'id' => $comment_id,
            'content' => $content
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error during update'
    ]);
}
?>