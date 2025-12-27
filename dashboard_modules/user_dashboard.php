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

  <div class="report-modal" id="reportModal">
    <div class="modal-content">
      <div class="modal-icon">⚠️</div>
      <h3>Report Post</h3>
      <p>Please tell us why you're reporting this post</p>
      <form id="reportForm" action="../user_modules/report.php" method="POST">
        <input type="hidden" name="post_id" id="reportPostId">
        <textarea id="reportReason" name="reason" placeholder="Write your reason here..." class="report-textarea" required></textarea>
        <div class="modal-buttons">
          <button type="button" class="modal-cancel" id="reportCancel">Cancel</button>
          <button type="submit" class="modal-confirm" id="reportConfirm">Submit Report</button>
        </div>
      </form>
    </div>
  </div>

  <div class="post-contents">
    <?php
    $sql = "SELECT posts.id, posts.user_id, posts.caption, posts.image, posts.created_at, users.username, users.picture 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            ORDER BY posts.created_at DESC";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
      while ($post = mysqli_fetch_assoc($result)) {
        $postId = htmlspecialchars($post['id']);
        $userId = htmlspecialchars($post['user_id']);
        $username = htmlspecialchars($post['username']);
        $userPicture = htmlspecialchars($post['picture'] ?? 'default.png');
        $caption = htmlspecialchars($post['caption']);
        $postImage = htmlspecialchars($post['image']);
        $createdAt = date('M d, Y \a\t h:i A', strtotime($post['created_at']));
    ?>
        <div class="post-card">
          <div class="post-header">
            <div class="post-user-info">
              <img src="../uploads/<?= $userPicture ?>" alt="Profile" class="post-profile-pic">
              <div class="post-user-details">
                <div class="post-username"><?= $username ?></div>
                <div class="post-time"><?= $createdAt ?></div>
              </div>
            </div>
            <button class="post-report-btn" title="Report Post">⚠️</button>
          </div>

          <div class="post-content">
            <p class="post-caption"><?= nl2br($caption) ?></p>
            <?php if (!empty($postImage)) { ?>
              <img src="../user_modules/<?= $postImage ?>" alt="Post Image" class="post-image">
            <?php } ?>
          </div>

          <?php
            $likeCountQuery = mysqli_query(
            $conn, "SELECT COUNT(*) AS total FROM likes WHERE post_id = $postId");
            $likeCount = mysqli_fetch_assoc($likeCountQuery)['total'];
          ?>

            <!-- Like -->
          <div class="post-actions">
            <button class="post-action-btn like-btn" data-post-id="<?= $postId ?>">
              👍 Like
            <span class="action-count" id="like-count-<?= $postId ?>">
            <?= $likeCount ?>
            </span>
            </button>

            <!-- comment -->
             <?php
              $commentCountQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM comments WHERE post_id = $postId");
              $commentCount = mysqli_fetch_assoc($commentCountQuery)['total'];
              ?>
              
              <button class="post-action-btn comment-btn" data-post-id="<?= $postId ?>">
                <span class="action-icon">💬</span>
                <span class="action-text">Comment</span>
                <span class="action-count"><?= $commentCount ?></span>
              </button>
          </div>

          <div class="post-comments-section" id="comments-<?= $postId ?>" style="display: none;">
            <div class="comments-container">
              <div class="comments-list"></div>
            </div>
            <div class="comment-input-area">
              <img src="../uploads/<?= htmlspecialchars($user['picture'] ?? 'default.png') ?>" alt="Your Profile" class="comment-user-pic">
              <input type="text" class="comment-input" placeholder="Write a comment..." data-post-id="<?= $postId ?>">
              <button class="comment-submit-btn" data-post-id="<?= $postId ?>">Post</button>
            </div>
          </div>
        </div>
    <?php
      }
    } else {
      echo '<div class="no-posts"><p>No posts yet. Be the first to share!</p></div>';
    }
    ?>
  </div>

  <script src="../user_modules/scripts/create_post.js?v=<?= time() ?>"></script>
  <script src="../user_modules/scripts/post_interactions.js?v=<?= time() ?>"></script>

</body>

</html>