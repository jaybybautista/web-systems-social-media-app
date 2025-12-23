<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  session_start();
  include('../config.php');

  $id = $_SESSION['id'];
  $caption = trim($_POST['caption']);
  $postImage = null;
  $target_dir = "postUploads/";

  if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
  }

  if (isset($_FILES["image"]) && !empty($_FILES["image"]["name"])) {
    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
      $postImage = $target_file;
    }
  }

  $sql = "INSERT INTO posts(user_id, caption, image) VALUES ('$id','$caption','$postImage')";

  $result = mysqli_query($conn, $sql);

  if ($result) {
    echo json_encode(['success' => true, 'message' => 'Post uploaded successfully']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload post']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
