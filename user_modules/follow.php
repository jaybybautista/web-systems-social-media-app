<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

$follower_id  = $_SESSION['id'] ?? null;
$following_id = intval($_POST['user_id'] ?? 0);

/* BASIC VALIDATION */
if (!$follower_id || !$following_id || $follower_id === $following_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

/* CHECK IF ALREADY FOLLOWING */
$stmt = $conn->prepare("
    SELECT id FROM follows
    WHERE follower_id = ? AND following_id = ?
");
$stmt->bind_param("ii", $follower_id, $following_id);
$stmt->execute();
$stmt->store_result();

$isFollowing = $stmt->num_rows > 0;
$stmt->close();

/* TOGGLE FOLLOW */
if ($isFollowing) {

    /* UNFOLLOW */
    $stmt = $conn->prepare("
        DELETE FROM follows
        WHERE follower_id = ? AND following_id = ?
    ");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $stmt->close();

    $status = 'unfollowed';

} else {

    /* FOLLOW */
    $stmt = $conn->prepare("
        INSERT INTO follows (follower_id, following_id, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $stmt->close();

    $status = 'followed';
}

/* GET UPDATED COUNTS */
$stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM follows WHERE following_id = ?) AS followers,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ?) AS following
");
$stmt->bind_param("ii", $following_id, $following_id);
$stmt->execute();
$stmt->bind_result($followers_count, $following_count);
$stmt->fetch();
$stmt->close();

/* RESPONSE */
echo json_encode([
    'success' => true,
    'status' => $status,            // followed | unfollowed
    'followers' => $followers_count,
    'following' => $following_count
]);
