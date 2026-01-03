<?php
session_start();
include('../config.php');

if (!isset($_SESSION['id'])) {
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit();
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$currentUserId = $_SESSION['id'];

if (empty($query) || strlen($query) < 1) {
  header('Content-Type: application/json');
  echo json_encode(['users' => []]);
  exit();
}

// Use LIKE for partial matching - case insensitive
$searchQuery = '%' . mysqli_real_escape_string($conn, $query) . '%';

$sql = "SELECT id, username, picture 
        FROM users 
        WHERE username LIKE '$searchQuery' 
        AND id != $currentUserId 
        LIMIT 10";

$result = mysqli_query($conn, $sql);
$users = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($user = mysqli_fetch_assoc($result)) {
    $users[] = $user;
  }
}

header('Content-Type: application/json');
echo json_encode(['users' => $users, 'success' => true]);
