<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login_modules/login.php");
  exit();
}
$id = $_SESSION['id'];
include('../config.php');

/* FETCH USER INFO */
$sql = "SELECT * FROM users WHERE id=$id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Newsfeed | MySocial</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    :root {
      --bg-dark: #051115;
      --sidebar-bg: #030a0c;
      --card-bg: #112229;
      --accent-blue: #4db6ff;
      --text-white: #ffffff;
      --text-gray: #94a3b8;
      --border-color: #2a3f47;
      --glass-bg: rgba(255, 255, 255, 0.03);
      --glass-border: rgba(255, 255, 255, 0.1);
      --insta-red: #ff3040;
    }

    * {
      box-sizing: border-box;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    body {
      margin: 0;
      display: flex;
      background-color: var(--bg-dark);
      color: var(--text-white);
      min-height: 100vh;
    }

    /* --- SIDEBAR --- */
    .sidebar-left {
      width: 250px;
      padding: 40px 20px;
      background-color: var(--sidebar-bg);
      height: 100vh;
      position: sticky;
      top: 0;
      border-right: 1px solid rgba(255, 255, 255, 0.05);
    }

    .logo {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 50px;
      padding-left: 15px;
    }

    .sidebar-left a,
    .create-post-btn {
      display: block;
      width: 100%;
      padding: 12px 20px;
      margin-bottom: 15px;
      border-radius: 50px;
      text-decoration: none;
      color: var(--text-white);
      border: 1px solid var(--border-color);
      background: transparent;
      transition: all 0.3s ease;
      text-align: center;
      font-size: 15px;
      cursor: pointer;
    }

    .sidebar-left a:hover,
    .create-post-btn:hover {
      background: rgba(77, 182, 255, 0.1);
      border-color: var(--accent-blue);
    }

    /* --- MAIN CONTENT --- */
    .content {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }

    /* --- SEARCH BAR --- */
    .search-container {
      max-width: 700px;
      margin: 0 auto 30px auto;
      display: flex;
      align-items: center;
      gap: 12px;
      width: 100%;
    }

    .search-container input {
      flex: 1;
      background: var(--card-bg);
      border: 2px solid var(--border-color);
      padding: 12px 25px;
      border-radius: 50px;
      color: white;
      font-size: 15px;
      outline: none;
    }

    .search-btn {
      background: rgba(77, 182, 255, 0.1);
      border: 1px solid var(--border-color);
      color: var(--text-white);
      padding: 12px 25px;
      border-radius: 50px;
      cursor: pointer;
      white-space: nowrap;
    }

    /* --- TOP FEED CREATE POST TRIGGER --- */
    .feed-create-post {
      max-width: 700px;
      margin: 0 auto 40px auto;
      background: var(--card-bg);
      border-radius: 25px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 15px;
      border: 2px solid var(--border-color);
      cursor: pointer;
      transition: border-color 0.3s;
    }

    .feed-create-post:hover {
      border-color: var(--accent-blue);
    }

    .feed-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      object-fit: cover;
    }

    .feed-placeholder {
      flex: 1;
      background: rgba(0, 0, 0, 0.2);
      padding: 12px 20px;
      border-radius: 50px;
      color: var(--text-gray);
      font-size: 15px;
    }

    /* --- POST CARD --- */
    .posts-container {
      max-width: 700px;
      margin: 0 auto;
    }

    .post-card {
      background: var(--card-bg);
      border-radius: 25px;
      padding: 25px;
      margin-bottom: 30px;
      border: 2px solid var(--border-color);
      position: relative;
    }

    .post-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .user-meta {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .profile-link {
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .profile-link:hover .post-username {
      color: var(--accent-blue);
    }

    .post-profile-pic {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      object-fit: cover;
    }

    .post-username {
      font-weight: bold;
      font-size: 17px;
      transition: color 0.2s;
    }

    .follow-btn {
      background: transparent;
      border: 1px solid var(--accent-blue);
      color: var(--accent-blue);
      border-radius: 20px;
      padding: 2px 12px;
      font-size: 12px;
      cursor: pointer;
      margin-left: 10px;
      transition: 0.3s;
    }

    .follow-btn.following {
      background: var(--accent-blue);
      color: black;
    }

    .post-time {
      font-size: 13px;
      color: var(--text-gray);
      display: block;
    }

    /* --- OPTIONS MENU (THREE DOTS) --- */
    .options-container {
      position: relative;
    }

    .three-dots-btn {
      background: none;
      border: none;
      color: var(--text-gray);
      font-size: 22px;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 50%;
      transition: 0.3s;
    }

    .three-dots-btn:hover {
      background: rgba(255, 255, 255, 0.05);
      color: white;
    }

    .options-menu {
      position: absolute;
      right: 0;
      top: 100%;
      background: var(--sidebar-bg);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      width: 120px;
      display: none;
      z-index: 10;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    .options-menu button {
      width: 100%;
      padding: 10px;
      background: none;
      border: none;
      color: var(--text-white);
      text-align: left;
      cursor: pointer;
      font-size: 14px;
    }

    .options-menu button:hover {
      background: rgba(255, 77, 77, 0.1);
      color: #ff4d4d;
    }

    .post-caption {
      margin: 15px 0;
      line-height: 1.5;
      font-size: 16px;
      cursor: pointer;
    }

    .post-image {
      width: 100%;
      border-radius: 20px;
      margin-top: 10px;
      border: 1px solid rgba(255, 255, 255, 0.05);
      cursor: pointer;
    }

    /* --- IMPROVED HEART & ACTIONS --- */
    .post-actions {
      display: flex;
      gap: 20px;
      margin-top: 20px;
      padding-top: 15px;
      border-top: 1px solid var(--border-color);
    }

    .action-btn {
      background: none;
      border: none;
      color: var(--text-white);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 5px;
      transition: transform 0.1s ease;
    }

    .action-btn i {
      font-size: 24px;
      transition: color 0.3s ease;
    }

    /* Instagram Heart Pop Animation */
    @keyframes heart-pop {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.3);
      }

      100% {
        transform: scale(1);
      }
    }

    .like-btn.active i {
      color: var(--insta-red);
      animation: heart-pop 0.3s linear;
    }

    .like-btn.active .heart-icon::before {
      content: "\f004";
      font-weight: 900;
    }

    .comment-btn:hover i {
      color: var(--accent-blue);
    }

    .action-count {
      font-size: 15px;
      font-weight: 600;
      color: var(--text-white);
    }

    /* --- MODAL --- */
    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.9);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .modal.show {
      display: flex;
    }

    .modal-box-post {
      width: 90%;
      max-width: 1100px;
      height: 80vh;
      background: var(--card-bg);
      border-radius: 25px;
      display: flex;
      overflow: hidden;
      border: 1px solid var(--border-color);
    }

    .modal-left {
      flex: 2;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .modal-left img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }

    .modal-right {
      flex: 1;
      width: 400px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      background: #08151a;
      border-left: 1px solid var(--border-color);
    }

    .comments-list {
      flex: 1;
      overflow-y: auto;
      margin-bottom: 15px;
      padding-right: 5px;
    }

    .comment-input-area {
      display: flex;
      flex-direction: column;
      gap: 10px;
      padding-top: 10px;
      border-top: 1px solid var(--border-color);
    }

    #replying-to-info {
      display: none;
      font-size: 12px;
      color: var(--accent-blue);
      background: rgba(77, 182, 255, 0.1);
      padding: 5px 10px;
      border-radius: 5px;
      justify-content: space-between;
      align-items: center;
    }

    .comment-input-area .input-row {
      display: flex;
      gap: 10px;
    }

    .comment-input-area input {
      flex: 1;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      padding: 10px 15px;
      border-radius: 10px;
      color: white;
    }

    /* --- GLASS REPLIES & THREADING --- */
    .comment-container {
      margin-bottom: 15px;
    }

    .comment-item {
      padding: 12px;
      border-radius: 15px;
      position: relative;
      background: var(--glass-bg);
      backdrop-filter: blur(5px);
      border: 1px solid var(--glass-border);
      margin-bottom: 8px;
    }

    .replies-wrapper {
      margin-left: 30px;
      border-left: 1px solid var(--border-color);
      padding-left: 15px;
    }

    .comment-actions {
      display: flex;
      gap: 12px;
      margin-top: 8px;
      align-items: center;
    }

    .comment-actions span {
      cursor: pointer;
      font-size: 11px;
      color: var(--text-gray);
      letter-spacing: 0.5px;
      transition: color 0.2s;
    }

    .comment-actions span:hover {
      color: var(--accent-blue);
    }

    .delete-action:hover {
      color: #ff4d4d !important;
    }

    .comment-react-btn {
      font-size: 12px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .comment-react-btn.active {
      color: var(--insta-red) !important;
    }

    .upload-form {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.85);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .upload-modal-box {
      background: var(--card-bg);
      width: 90%;
      max-width: 500px;
      padding: 30px;
      border-radius: 30px;
      border: 2px solid var(--border-color);
      position: relative;
    }
  </style>
</head>

<body>

  <div class="sidebar-left">
    <div class="logo">MySocial</div>
    <a href="../user_modules/profile.php">Profile</a>
    <a href="user_dashboard.php"
      style="background: rgba(77, 182, 255, 0.1); border-color: var(--accent-blue);">Newsfeed</a>
    <button class="create-post-btn">Create Post</button>
    <a href="../logout.php" class="logout" style="margin-top: 50px;">Logout</a>
  </div>

  <div class="content">
    <div class="search-container">
      <input type="text" placeholder="Search username..." id="searchBox">
      <button class="search-btn">Search</button>
    </div>

    <div class="feed-create-post create-post-btn">
      <img src="../uploads/<?= htmlspecialchars($user['picture'] ?? 'default.png') ?>" class="feed-avatar">
      <div class="feed-placeholder">What's on your mind, <?= htmlspecialchars($user['username']) ?>?</div>
    </div>

    <div class="posts-container">
      <?php
      $sql = "SELECT p.*, u.username, u.picture,
                    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                    EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = $id) AS liked,
                    EXISTS(SELECT 1 FROM follows WHERE follower_id = $id AND following_id = p.user_id) AS is_following
                    FROM posts p
                    JOIN users u ON p.user_id = u.id
                    ORDER BY p.created_at DESC";
      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
        while ($post = mysqli_fetch_assoc($result)): ?>
          <div class="post-card" data-id="<?= $post['id'] ?>">
            <div class="post-header">
              <div class="user-meta">
                <a href="view_profile.php?user_id=<?= $post['user_id'] ?>" class="profile-link">
                  <img src="../uploads/<?= htmlspecialchars($post['picture'] ?? 'default.png') ?>" class="post-profile-pic">
                  <div>
                    <span class="post-username"><?= htmlspecialchars($post['username']) ?></span>
                    <span class="post-time"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></span>
                  </div>
                </a>

                <?php if ($post['user_id'] != $id): ?>
                  <button class="follow-btn <?= $post['is_following'] ? 'following' : '' ?>"
                    onclick="toggleFollow(this, <?= $post['user_id'] ?>)">
                    <?= $post['is_following'] ? 'Following' : 'Follow' ?>
                  </button>
                <?php endif; ?>
              </div>
              <div class="options-container">
                <button class="three-dots-btn">⋮</button>
                <div class="options-menu">
                  <button onclick="reportPost(<?= $post['id'] ?>)">Report Post</button>
                </div>
              </div>
            </div>

            <div class="post-caption" onclick="openPostModal(this)">
              <?= nl2br(htmlspecialchars($post['caption'])) ?>
            </div>

            <?php if (!empty($post['image'])): ?>
              <img src="../user_modules/<?= htmlspecialchars($post['image']) ?>" class="post-image"
                onclick="openPostModal(this)">
            <?php endif; ?>

            <div class="post-actions">
              <button class="action-btn like-btn <?= $post['liked'] ? 'active' : '' ?>">
                <i class="fa-<?= $post['liked'] ? 'solid' : 'regular' ?> fa-heart heart-icon"></i>
                <span class="action-count"><?= $post['like_count'] ?></span>
              </button>
              <button class="action-btn comment-btn" onclick="openPostModal(this)">
                <i class="fa-regular fa-comment"></i>
                <span class="action-count"><?= $post['comment_count'] ?></span>
              </button>
            </div>
          </div>
        <?php endwhile;
      } else {
        echo '<p style="text-align:center; color:var(--text-gray);">No posts yet.</p>';
      }
      ?>
    </div>
  </div>

  <div class="modal" id="postModal">
    <div class="modal-box-post">
      <div class="modal-left">
        <img id="modalImg" src="" alt="Post">
      </div>
      <div class="modal-right">
        <div class="comments-list" id="modalComments"></div>
        <form class="comment-input-area" id="commentForm">
          <div id="replying-to-info">
            <span>Replying to <b id="reply-username"></b></span>
            <span onclick="cancelReply()" style="cursor:pointer;">&times;</span>
          </div>
          <div class="input-row">
            <input type="text" id="commentInput" placeholder="Write a comment..." required>
            <button type="submit"
              style="background:var(--accent-blue); border:none; padding:10px 15px; border-radius:10px; cursor:pointer; color:black; font-weight:bold;">Post</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="upload-form">
    <div class="upload-modal-box">
      <button class="close-form-btn"
        style="position:absolute; top:15px; right:20px; background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
      <h3 style="margin-top:0">Create Post</h3>
      <form id="addPostForm" action="../user_modules/add_post.php" method="POST" enctype="multipart/form-data">
        <textarea name="caption"
          style="width:100%; height:120px; background:rgba(0,0,0,0.2); border:1px solid var(--border-color); border-radius:15px; color:white; padding:15px; margin:15px 0; resize:none;"
          placeholder="What's on your mind?" required></textarea>
        <input type="file" name="image" accept="image/*" style="margin-bottom:20px; display:block;">
        <button type="submit"
          style="background:var(--accent-blue); color:black; border:none; width: 100%; padding:12px; border-radius: 50px; font-weight: bold; cursor: pointer;">Post</button>
      </form>
    </div>
  </div>

  <script src="../user_modules/scripts/create_post.js"></script>

  <script>
    let currentPostId = null;
    let currentParentId = null;
    const loggedInUserId = <?= $id ?>;
    const modal = document.getElementById('postModal');
    const modalImg = document.getElementById('modalImg');

    function openPostModal(element) {
      const card = element.closest(".post-card");
      currentPostId = card.dataset.id;
      const postImg = card.querySelector(".post-image");

      modalImg.src = postImg ? postImg.src : "";

      if (!modalImg.src || modalImg.src.includes('undefined') || modalImg.src === window.location.href) {
        document.querySelector('.modal-left').style.display = 'none';
      } else {
        document.querySelector('.modal-left').style.display = 'flex';
      }

      modal.classList.add("show");
      cancelReply();
      loadComments(currentPostId);
    }

    window.onclick = (e) => {
      if (e.target === modal) {
        modal.classList.remove("show");
        currentPostId = null;
        cancelReply();
      }
      document.querySelectorAll(".options-menu").forEach(m => m.style.display = "none");
    };

    /**
     * LOAD COMMENTS: 
     * Modified to receive HTML directly from get_comments.php
     */
    function loadComments(postId) {
      const commentsContainer = document.getElementById('modalComments');
      commentsContainer.innerHTML = '<p style="color:gray; padding:10px;">Loading comments...</p>';

      fetch("../user_modules/get_comments.php?post_id=" + postId)
        .then(r => r.text()) // Changed from r.json() to r.text()
        .then(html => {
          commentsContainer.innerHTML = html;
        })
        .catch(err => {
          console.error(err);
          commentsContainer.innerHTML = '<p style="color:red; padding:10px;">Error loading comments.</p>';
        });
    }

    // Note: The renderComments JS function is no longer needed because 
    // the backend get_comments.php is now doing the HTML rendering. 

    function reactToComment(commentId, btn) {
      fetch("../user_modules/like_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `comment_id=${commentId}`
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            btn.classList.toggle('active', data.liked);
            const iconClass = data.liked ? 'fa-solid' : 'fa-regular';
            btn.innerHTML = `<i class="${iconClass} fa-heart"></i> <span class="react-count">${data.like_count}</span>`;
          }
        });
    }

    function setReply(commentId, username) {
      currentParentId = commentId;
      document.getElementById('reply-username').innerText = username;
      document.getElementById('replying-to-info').style.display = 'flex';
      document.getElementById('commentInput').focus();
    }

    function cancelReply() {
      currentParentId = null;
      document.getElementById('replying-to-info').style.display = 'none';
      document.getElementById('commentInput').value = "";
    }

    function deleteComment(commentId) {
      if (!confirm("Are you sure? This will delete all replies under this comment too.")) return;
      fetch("../user_modules/delete_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `comment_id=${commentId}`
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) loadComments(currentPostId);
          else alert(data.message);
        });
    }

    function editComment(commentId, oldContent) {
      const newContent = prompt("Edit your comment:", oldContent);
      if (!newContent || newContent.trim() === "" || newContent === oldContent) return;
      fetch("../user_modules/edit_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `comment_id=${commentId}&comment_text=${encodeURIComponent(newContent)}`
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) loadComments(currentPostId);
        });
    }

    document.getElementById('commentForm').onsubmit = e => {
      e.preventDefault();
      const input = document.getElementById('commentInput');
      const commentText = input.value;

      let bodyData = `post_id=${currentPostId}&comment_text=${encodeURIComponent(commentText)}`;
      if (currentParentId) {
        bodyData += `&parent_id=${currentParentId}`;
      }

      fetch("../user_modules/add_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: bodyData
      }).then(r => r.json()).then(data => {
        if (data.success) {
          cancelReply();
          loadComments(currentPostId);
        } else {
          alert(data.message);
        }
      });
    };

    document.querySelectorAll(".three-dots-btn").forEach(btn => {
      btn.onclick = function (e) {
        e.stopPropagation();
        const menu = this.nextElementSibling;
        document.querySelectorAll(".options-menu").forEach(m => {
          if (m !== menu) m.style.display = "none";
        });
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
      }
    });

    document.querySelectorAll(".like-btn").forEach(btn => {
      btn.onclick = function (e) {
        e.stopPropagation();
        const card = this.closest(".post-card");
        const postId = card.dataset.id;
        const icon = this.querySelector("i");
        const count = this.querySelector(".action-count");

        fetch("../user_modules/like_post.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "post_id=" + postId
        }).then(r => r.json()).then(data => {
          if (data.success) {
            count.innerText = data.like_count;
            this.classList.toggle("active");
            if (this.classList.contains("active")) {
              icon.classList.remove("fa-regular");
              icon.classList.add("fa-solid");
            } else {
              icon.classList.remove("fa-solid");
              icon.classList.add("fa-regular");
            }
          }
        });
      };
    });

    document.querySelectorAll(".create-post-btn").forEach(btn => {
      btn.onclick = () => document.querySelector(".upload-form").style.display = "flex";
    });
    document.querySelector(".close-form-btn").onclick = () => document.querySelector(".upload-form").style.display = "none";

    function reportPost(postId) {
      const reason = prompt("Why are you reporting this post?");
      if (!reason || reason.trim() === "") return;
      fetch("../user_modules/submit_report.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${postId}&reason=${encodeURIComponent(reason)}`
      }).then(r => r.json()).then(data => {
        if (data.success) alert("Report submitted.");
      });
    }

    function toggleFollow(btn, targetUserId) {
      fetch("../user_modules/follow_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "following_id=" + targetUserId
      }).then(r => r.json()).then(data => {
        if (data.success) {
          btn.classList.toggle('following');
          btn.innerText = btn.classList.contains('following') ? 'Following' : 'Follow';
        }
      });
    }
  </script>
</body>

</html>