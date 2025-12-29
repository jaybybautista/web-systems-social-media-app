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
  <style>
    :root {
      --bg-dark: #051115;
      --sidebar-bg: #030a0c;
      --card-bg: #112229;
      --accent-blue: #4db6ff;
      --text-white: #ffffff;
      --text-gray: #94a3b8;
      --border-color: #2a3f47;
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

    .post-profile-pic {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      object-fit: cover;
    }

    .post-username {
      display: block;
      font-weight: bold;
      font-size: 17px;
    }

    .post-time {
      font-size: 13px;
      color: var(--text-gray);
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

    .post-actions {
      display: flex;
      gap: 25px;
      margin-top: 20px;
      padding-top: 15px;
      border-top: 1px solid var(--border-color);
    }

    .action-btn {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      font-size: 22px;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: transform 0.2s;
    }

    .action-btn:hover {
      transform: scale(1.1);
    }

    .action-btn.active {
      color: #ff4d4d;
    }

    .action-count {
      font-size: 15px;
      color: var(--text-gray);
    }

    /* --- PROFILE-STYLE POST MODAL --- */
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
    }

    .modal-left img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
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
    }

    .comment-input-area {
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

    /* --- UPLOAD FORM MODAL --- */
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
                    EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = $id) AS liked
                    FROM posts p
                    JOIN users u ON p.user_id = u.id
                    ORDER BY p.created_at DESC";
      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
        while ($post = mysqli_fetch_assoc($result)): ?>
          <div class="post-card" data-id="<?= $post['id'] ?>">
            <div class="post-header">
              <div class="user-meta">
                <img src="../uploads/<?= htmlspecialchars($post['picture'] ?? 'default.png') ?>" class="post-profile-pic">
                <div>
                  <span class="post-username"><?= htmlspecialchars($post['username']) ?></span>
                  <span class="post-time"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></span>
                </div>
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
                <span class="heart-icon"><?= $post['liked'] ? '❤️' : '♡' ?></span>
                <span class="action-count"><?= $post['like_count'] ?></span>
              </button>
              <button class="action-btn comment-btn" onclick="openPostModal(this)">
                <span>🗨️</span>
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
          <input type="text" id="commentInput" placeholder="Write a comment..." required>
          <button type="submit"
            style="background:var(--accent-blue); border:none; padding:10px 15px; border-radius:10px; cursor:pointer; color:black; font-weight:bold;">Post</button>
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
    const modal = document.getElementById('postModal');
    const modalImg = document.getElementById('modalImg');

    function openPostModal(element) {
      const card = element.closest(".post-card");
      currentPostId = card.dataset.id;
      const postImg = card.querySelector(".post-image");

      modalImg.src = postImg ? postImg.src : "";
      modal.classList.add("show");
      loadComments(currentPostId);
    }

    window.onclick = (e) => {
      if (e.target === modal) {
        modal.classList.remove("show");
        currentPostId = null;
      }
      document.querySelectorAll(".options-menu").forEach(m => m.style.display = "none");
    };

    function loadComments(postId) {
      const commentsContainer = document.getElementById('modalComments');
      commentsContainer.innerHTML = '<p style="color:gray; padding:10px;">Loading comments...</p>';

      fetch("../user_modules/get_comments.php?post_id=" + postId)
        .then(r => r.json())
        .then(data => {
          if (data.comments && data.comments.length > 0) {
            commentsContainer.innerHTML = data.comments.map(c => `
              <div style="margin-bottom:15px; font-size:14px;">
                <strong style="color:var(--accent-blue)">${c.username}</strong>
                <span style="display:block; margin-top:3px;">${c.content}</span>
              </div>
            `).join('');
          } else {
            commentsContainer.innerHTML = '<p style="color:gray; padding:10px;">No comments yet.</p>';
          }
        });
    }

    document.getElementById('commentForm').onsubmit = e => {
      e.preventDefault();
      const input = document.getElementById('commentInput');

      fetch("../user_modules/add_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${currentPostId}&comment_text=${encodeURIComponent(input.value)}`
      }).then(() => {
        input.value = "";
        loadComments(currentPostId);
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
        const heart = this.querySelector(".heart-icon");
        const count = this.querySelector(".action-count");

        fetch("../user_modules/like_post.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "post_id=" + postId
        })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              count.innerText = data.like_count;
              this.classList.toggle("active");
              heart.innerText = this.classList.contains("active") ? "❤️" : "♡";
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
      if (reason === null) return;
      if (reason.trim() === "") {
        alert("Reason is required.");
        return;
      }

      fetch("../user_modules/submit_report.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${postId}&reason=${encodeURIComponent(reason)}`
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert("Report submitted successfully.");
            document.querySelectorAll(".options-menu").forEach(m => m.style.display = "none");
          } else {
            alert("Error: " + data.message);
          }
        });
    }
  </script>
</body>

</html>