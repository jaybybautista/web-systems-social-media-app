<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

/* AUTH CHECK */
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

$user_id = (int) $_SESSION['id'];
$comment_id = (int) ($_POST['comment_id'] ?? 0);

if ($comment_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid comment ID'
    ]);
    exit;
}

/* CHECK IF COMMENT EXISTS */
$stmt = $conn->prepare("SELECT id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    echo json_encode([
        'success' => false,
        'message' => 'Comment not found'
    ]);
    exit;
}
$stmt->close();

/* CHECK IF ALREADY LIKED */
// Note: Ensure your table name is 'comment_likes'
$stmt = $conn->prepare("
    SELECT id
    FROM comment_likes
    WHERE user_id = ? AND comment_id = ?
");
$stmt->bind_param("ii", $user_id, $comment_id);
$stmt->execute();
$stmt->store_result();
$alreadyLiked = $stmt->num_rows > 0;
$stmt->close();

/* TOGGLE LIKE (TRANSACTION SAFE) */
$conn->begin_transaction();

try {
    if ($alreadyLiked) {
        /* UNLIKE */
        $stmt = $conn->prepare("
            DELETE FROM comment_likes
            WHERE user_id = ? AND comment_id = ?
        ");
        $stmt->bind_param("ii", $user_id, $comment_id);
        $stmt->execute();
        $stmt->close();
    } else {
        /* LIKE */
        $stmt = $conn->prepare("
            INSERT INTO comment_likes (user_id, comment_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $user_id, $comment_id);
        $stmt->execute();
        $stmt->close();
    }

    /* GET UPDATED LIKE COUNT */
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM comment_likes 
        WHERE comment_id = ?
    ");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->bind_result($like_count);
    $stmt->fetch();
    $stmt->close();

    $conn->commit();

    /* SUCCESS RESPONSE */
    echo json_encode([
        'success' => true,
        'liked' => !$alreadyLiked, // Returns true if it was just liked, false if unliked
        'like_count' => (int) $like_count,
        'comment_id' => $comment_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to toggle like'
    ]);
}
?>