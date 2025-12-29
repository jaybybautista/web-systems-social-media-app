<?php
session_start();
include '../config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}

$userId = $_SESSION['id'];

/* USER INFO */
$stmt = $conn->prepare("
    SELECT id, email, username, picture, cover_picture, bio, created_at
    FROM users WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* POSTS */
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.picture,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
        EXISTS(
            SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?
        ) AS liked
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$posts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | MySocial</title>

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

        .sidebar-left a {
            display: block;
            padding: 12px 20px;
            margin-bottom: 15px;
            border-radius: 50px;
            text-decoration: none;
            color: var(--text-white);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            text-align: center;
            font-size: 15px;
        }

        .sidebar-left a:hover {
            background: rgba(77, 182, 255, 0.1);
            border-color: var(--accent-blue);
        }

        /* --- CONTENT AREA --- */
        .content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        /* --- PROFILE SECTION --- */
        .profile-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 30px;
            overflow: hidden;
            border: 2px solid var(--border-color);
            position: relative;
            padding-bottom: 30px;
        }

        .cover {
            height: 240px;
            background: url('../uploads/<?= htmlspecialchars($user['cover_picture'] ?? 'default_cover.jpg') ?>') center/cover;
            border-bottom: 1px solid var(--border-color);
        }

        .profile-header-info {
            display: flex;
            align-items: flex-end;
            padding: 0 40px;
            margin-top: -60px;
            gap: 20px;
            position: relative;
        }

        .profile-header-info img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 5px solid var(--card-bg);
            object-fit: cover;
            background: #000;
        }

        .user-details {
            padding-bottom: 15px;
            flex-grow: 1;
        }

        .user-details h2 {
            margin: 0;
            font-size: 22px;
        }

        .user-details p {
            margin: 5px 0;
            color: var(--text-gray);
            font-size: 14px;
        }

        .edit-profile-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
        }

        .edit-profile-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .bio-display {
            text-align: center;
            margin-top: 30px;
            color: var(--text-gray);
            font-style: italic;
        }

        /* --- POSTS SECTION --- */
        .posts-container {
            max-width: 700px;
            margin: 50px auto;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .post-card {
            background: var(--card-bg);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid var(--border-color);
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .post-header img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
        }

        .post-user-info strong {
            display: block;
            font-size: 17px;
        }

        .post-time {
            font-size: 13px;
            color: var(--text-gray);
        }

        .post-caption {
            margin: 20px 0;
            line-height: 1.5;
            font-size: 16px;
        }

        .post-image {
            width: 100%;
            border-radius: 20px;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: opacity 0.2s;
        }

        .post-image:hover {
            opacity: 0.9;
        }

        .post-actions {
            display: flex;
            gap: 25px;
            margin-top: 20px;
            padding-top: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 24px;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn:hover {
            transform: scale(1.05);
        }

        .action-btn.active {
            color: #ff4d4d;
        }

        .action-btn .count {
            font-size: 16px;
            color: var(--text-gray);
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

        .modal-box {
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
    </style>
</head>

<body>

    <div class="sidebar-left">
        <div class="logo">MySocial</div>
        <a href="profile.php">Profile</a>
        <a href="../dashboard_modules/user_dashboard.php">Newsfeed</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="content">

        <div class="profile-card">
            <div class="cover"></div>
            <div class="profile-header-info">
                <img src="../uploads/<?= htmlspecialchars($user['picture']) ?>" alt="Avatar">
                <div class="user-details">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <button class="edit-profile-btn" onclick="location.href='edit_profile.php'">Edit Profile</button>
            </div>
            <div class="bio-display"><?= htmlspecialchars($user['bio'] ?? 'Bio') ?></div>
        </div>

        <div class="posts-container">
            <div class="section-title">My Posts</div>

            <?php if ($posts->num_rows > 0): ?>
                <?php while ($p = $posts->fetch_assoc()): ?>
                    <div class="post-card" data-id="<?= $p['id'] ?>">
                        <div class="post-header">
                            <img src="../uploads/<?= htmlspecialchars($p['picture']) ?>">
                            <div class="post-user-info">
                                <strong><?= htmlspecialchars($p['username']) ?></strong>
                                <span class="post-time"><?= date("M d, Y h:i A", strtotime($p['created_at'])) ?></span>
                            </div>
                        </div>

                        <div class="post-caption"><?= nl2br(htmlspecialchars($p['caption'])) ?></div>

                        <?php if ($p['image']): ?>
                            <img src="../user_modules/<?= htmlspecialchars($p['image']) ?>" class="post-image">
                        <?php endif; ?>

                        <div class="post-actions">
                            <button class="action-btn like-btn <?= $p['liked'] ? 'active' : '' ?>">
                                <span class="heart-icon"><?= $p['liked'] ? '❤️' : '♡' ?></span>
                                <span class="count"><?= $p['like_count'] ?></span>
                            </button>
                            <button class="action-btn comment-btn">
                                <span>🗨️</span>
                                <span class="count"><?= $p['comment_count'] ?></span>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--text-gray);">No posts yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal" id="modal">
        <div class="modal-box">
            <div class="modal-left">
                <img id="modalImg" src="" alt="Post Detail">
            </div>
            <div class="modal-right">
                <div class="comments-list" id="modalComments"></div>
                <form class="comment-input-area" id="commentForm">
                    <input type="text" id="commentInput" placeholder="Write a comment..." required>
                    <button type="submit"
                        style="background:var(--accent-blue); border:none; padding:10px 15px; border-radius:10px; cursor:pointer; color:white;">Post</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPost = null;
        const modal = document.getElementById('modal');
        const modalImg = document.getElementById('modalImg');

        // FIXED: Like Functionality
        document.querySelectorAll(".like-btn").forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                const card = this.closest(".post-card");
                const postId = card.dataset.id;
                const heartIcon = this.querySelector(".heart-icon");
                const countSpan = this.querySelector(".count");
                const button = this;

                fetch("../user_modules/like_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "post_id=" + postId
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            countSpan.innerText = data.like_count;
                            button.classList.toggle("active");
                            heartIcon.innerText = button.classList.contains("active") ? "❤️" : "♡";
                        }
                    })
                    .catch(err => console.error("Error:", err));
            };
        });

        // Open Modal Handler
        document.querySelectorAll(".post-image, .comment-btn").forEach(el => {
            el.onclick = () => {
                const card = el.closest(".post-card");
                currentPost = card.dataset.id;
                const postImg = card.querySelector(".post-image");

                modalImg.src = postImg ? postImg.src : "";
                modal.classList.add("show");
                loadComments(currentPost);
            };
        });

        // Close Modal
        window.onclick = e => {
            if (e.target === modal) {
                modal.classList.remove("show");
                currentPost = null;
            }
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
                body: `post_id=${currentPost}&comment_text=${encodeURIComponent(input.value)}`
            }).then(() => {
                input.value = "";
                loadComments(currentPost);
            });
        };
    </script>
</body>

</html>