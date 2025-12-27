<?php
session_start();
include('../config.php');

$post_id = $_GET['post_id'] ?? null;
header('Content-Type: application/json');

if (!$post_id) {
    echo json_encode(['comments'=>[]]);
    exit;
}

$sql = "SELECT c.id, c.content, c.created_at, u.username, u.picture AS user_picture
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = $post_id
        ORDER BY c.created_at ASC";

$result = $conn->query($sql);
$comments = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}

echo json_encode(['comments'=>$comments]);
