<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

/* =====================
   AUTH & INPUT
===================== */
$user_id    = $_SESSION['id'] ?? null;
$comment_id = isset($_POST['comment_id']) ? (int) $_POST['comment_id'] : 0;

/*
  IMPORTANT:
  JS minsan nagpapadala ng `content`
  minsan `comment_text`
*/
$content = trim($_POST['content'] ?? $_POST['comment_text'] ?? '');

if (!$user_id || !$comment_id || $content === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data or session expired'
    ]);
    exit;
}

/* =====================
   VERIFY COMMENT
===================== */
$stmt = $conn->prepare("
    SELECT user_id 
    FROM comments 
    WHERE id = ?
");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id);

if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode([
        'success' => false,
        'message' => 'Comment not found'
    ]);
    exit;
}
$stmt->close();

/* =====================
   OWNERSHIP CHECK
===================== */
if ((int)$owner_id !== (int)$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

/* =====================
   UPDATE COMMENT
===================== */
$stmt = $conn->prepare("
    UPDATE comments 
    SET content = ?, created_at = created_at
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("sii", $content, $comment_id, $user_id);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update comment'
    ]);
    exit;
}
$stmt->close();

/* =====================
   RETURN UPDATED COMMENT
===================== */
$stmt = $conn->prepare("
    SELECT
        c.id,
        c.content,
        c.created_at,
        u.username,
        u.picture
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.id = ?
");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* =====================
   RESPONSE
===================== */
echo json_encode([
    'success' => true,
    'comment' => [
        'id'         => $comment['id'],
        'content'    => htmlspecialchars($comment['content']),
        'created_at' => $comment['created_at'],
        'username'   => $comment['username'],
        'picture'    => $comment['picture'] ?? 'default.png'
    ]
]);
