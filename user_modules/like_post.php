<?php
session_start();
include('../config.php');

header('Content-Type: application/json');

$userId = $_SESSION['id'] ?? null;
$postId = $_POST['post_id'] ?? null;

if (!$userId || !$postId) {
    echo json_encode(['success' => false, 'message' => 'Missing user or post ID']);
    exit;
}

// Check if user already liked the post
$sqlCheck = "SELECT * FROM likes WHERE user_id=? AND post_id=?";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("ii", $userId, $postId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User already liked -> remove like
    $sqlDelete = "DELETE FROM likes WHERE user_id=? AND post_id=?";
    $stmtDel = $conn->prepare($sqlDelete);
    $stmtDel->bind_param("ii", $userId, $postId);
    $stmtDel->execute();
} else {
    // User has not liked -> insert like
    $sqlInsert = "INSERT INTO likes(user_id, post_id) VALUES(?, ?)";
    $stmtIns = $conn->prepare($sqlInsert);
    $stmtIns->bind_param("ii", $userId, $postId);
    $stmtIns->execute();
}

// Get updated like count
$sqlCount = "SELECT COUNT(*) as like_count FROM likes WHERE post_id=?";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param("i", $postId);
$stmtCount->execute();
$countResult = $stmtCount->get_result()->fetch_assoc();
$likeCount = $countResult['like_count'];

echo json_encode(['success' => true, 'like_count' => $likeCount]);
