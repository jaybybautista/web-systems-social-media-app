<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  session_start();
  include('../config.php');

  // Check if user is logged in
  if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
  }

  $id = $_SESSION['id'];
  $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
  $content = isset($_POST['content']) ? trim($_POST['content']) : '';

  // parent_id is used for replies. If not provided, it defaults to NULL or 0
  $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

  // Validation
  if ($post_id === 0 || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post ID or content is missing']);
    exit;
  }

  /* |--------------------------------------------------------------------------
  | INSERT COMMENT
  |--------------------------------------------------------------------------
  | We use prepared statements here for security.
  */
  $sql = "INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);

  // If parent_id is null, it inserts as a top-level comment
  $stmt->bind_param("iiis", $post_id, $id, $parent_id, $content);

  if ($stmt->execute()) {
    echo json_encode([
      'success' => true,
      'message' => 'Comment added successfully',
      'comment_id' => $stmt->insert_id
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment: ' . $conn->error]);
  }

  $stmt->close();
  $conn->close();

} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}