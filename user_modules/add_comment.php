<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

/* =====================
   AUTH & INPUT
===================== */
$user_id = $_SESSION['id'] ?? null;
$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$content = trim($_POST['comment_text'] ?? '');
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== ''
    ? (int) $_POST['parent_id']
    : null;

if (!$user_id || !$post_id || $content === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid comment data'
    ]);
    exit;
}

/* =====================
   VERIFY POST EXISTS
===================== */
$stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Post not found'
    ]);
    exit;
}
$stmt->close();

/* =====================
   VERIFY PARENT COMMENT (IF REPLY)
===================== */
if ($parent_id !== null) {
    $stmt = $conn->prepare("
        SELECT id FROM comments 
        WHERE id = ? AND post_id = ?
    ");
    $stmt->bind_param("ii", $parent_id, $post_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Parent comment not found'
        ]);
        exit;
    }
    $stmt->close();
}

/* =====================
   INSERT COMMENT
===================== */
$stmt = $conn->prepare("
    INSERT INTO comments (post_id, user_id, content, parent_id, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iisi", $post_id, $user_id, $content, $parent_id);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save comment'
    ]);
    exit;
}

$comment_id = $stmt->insert_id;
$stmt->close();

/* =====================
   FETCH NEW COMMENT
===================== */
$stmt = $conn->prepare("
    SELECT
        c.id,
        c.content,
        c.created_at,
        c.parent_id,
        u.id AS user_id,
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
   UPDATED COMMENT COUNT
===================== */
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM comments WHERE post_id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($comment_count);
$stmt->fetch();
$stmt->close();

/* =====================
   RESPONSE
===================== */
echo json_encode([
    'success' => true,
    'comment' => [
        'id' => $comment['id'],
        'content' => htmlspecialchars($comment['content']),
        'created_at' => $comment['created_at'],
        'parent_id' => $comment['parent_id'],
        'user_id' => $comment['user_id'],
        'username' => $comment['username'],
        'picture' => $comment['picture'] ?? 'default.png'
    ],
    'comment_count' => $comment_count
]);
