<?php
session_start();

$id = $_SESSION['id'];
include('../config.php');
$sql = "SELECT * FROM users WHERE id=$id";

$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="../css/user_dashboard.css?v=<?= time() ?>">
</head>

<body>
  <div class="header">
    <div class="header-left-section">
      <div><img class="profile-pic" src="../uploads/<?= htmlspecialchars($user['picture'] ?? 'default.png') ?>" alt="Profile Picture"></div>
      <div><span class="username"><?= htmlspecialchars($user['username'] ?? 'Unknown User') ?></span></div>
    </div>
    <div class="header-middle-section">
      <div><input type="text" name="search" placeholder="search username"></div>
      <div><button class="search-button">Search</button></div>
      <button class="create-post-btn">Create Post</button>
    </div>
    <div class="header-right-section">
      <button class="logout">logout</button>
    </div>
  </div>

  <div class="upload-post">
    <div class="upload-form" style="display: none;">
      <button class="close-form-btn">&times;</button>
      <div>
        <form action="../user_modules/add_post.php" method="POST" enctype="multipart/form-data">
      </div>
      <div><textarea name="caption" class="caption" required></textarea></div>
      <div>
        <input type="file" name="image" accept="image/*" class="image-upload">
        <div class="image-preview" style="display: none;">
          <button class="remove-image-btn">&times;</button>
          <img id="preview-img" src="" alt="Preview">
        </div>
      </div>
      <div><input type="submit" value="post" class="submit-button" style="display: none;"></div>

      </form>
    </div>
  </div>

  <div class="confirmation-modal" id="confirmModal">
    <div class="modal-content">
      <div class="modal-icon">📝</div>
      <h3>Confirm Post</h3>
      <p>Are you sure you want to share this post?</p>
      <div class="modal-buttons">
        <button type="button" class="modal-cancel" id="modalCancel">Cancel</button>
        <button type="button" class="modal-confirm" id="modalConfirm">Post</button>
      </div>
    </div>
  </div>

  <script src="../user_modules/scripts/create_post.js?v=<?= time() ?>"></script>

</body>

</html>