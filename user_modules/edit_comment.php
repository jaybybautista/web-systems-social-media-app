<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

$user_id    = $_SESSION['id'] ?? null;
$comment_id = intval($_POST['comment_id'] ?? 0);
$content    = trim($_POST['content'] ?? '');

if (!$user_id || !$comment_id || $content === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data'
    ]);
    exit;
}

/* VERIFY COMMENT OWNERSHIP */
$stmt = $conn->prepare("
    SELECT user_id 
    FROM comments 
    WHERE id = ?
");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if ($owner_id !== $user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

/* UPDATE COMMENT */
$stmt = $conn->prepare("
    UPDATE comments 
    SET content = ? 
    WHERE id = ?
");
$stmt->bind_param("si", $content, $comment_id);
$stmt->execute();
$stmt->close();

/* RETURN UPDATED COMMENT */
echo json_encode([
    'success' => true,
    'comment' => [
        'id' => $comment_id,
        'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
    ]
]);
