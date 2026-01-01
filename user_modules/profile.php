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

/* FOLLOWERS LIST */
$stmt_followers = $conn->prepare("
    SELECT u.id, u.username, u.picture 
    FROM follows f 
    JOIN users u ON f.follower_id = u.id 
    WHERE f.following_id = ?
    ORDER BY f.created_at DESC
");
$stmt_followers->bind_param("i", $userId);
$stmt_followers->execute();
$followers_res = $stmt_followers->get_result();

/* POSTS */
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.picture,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
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

        .sidebar-left {
            width: 250px;
            padding: 40px 20px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: sticky;
            top: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-right {
            width: 300px;
            padding: 40px 20px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: sticky;
            top: 0;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
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

        .follower-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .follower-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 15px;
            transition: 0.2s;
            cursor: pointer;
            margin-bottom: 10px;
            border: 1px solid transparent;
        }

        .follower-item:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border-color);
        }

        .follower-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .follower-item span {
            font-size: 14px;
            font-weight: 500;
        }

        .content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .profile-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .cover {
            height: 280px;
            background: url('../uploads/<?= htmlspecialchars($user['cover_picture'] ?? 'default_cover.jpg') ?>') center/cover;
            position: relative;
        }

        .cover::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to top, var(--card-bg), transparent);
        }

        .profile-header-info {
            display: flex;
            align-items: flex-end;
            padding: 0 40px;
            margin-top: -80px;
            gap: 25px;
            position: relative;
            z-index: 2;
        }

        .profile-header-info img {
            width: 160px;
            height: 160px;
            border-radius: 40px;
            border: 6px solid var(--card-bg);
            object-fit: cover;
            background: #000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .user-details {
            padding-bottom: 15px;
            flex-grow: 1;
        }

        .user-details h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .user-details p {
            margin: 5px 0;
            color: var(--accent-blue);
            font-size: 15px;
            font-weight: 500;
        }

        .profile-actions {
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .bio-text {
            color: var(--text-gray);
            font-size: 15px;
            line-height: 1.6;
            max-width: 500px;
            margin: 0;
        }

        .edit-profile-btn {
            background: var(--accent-blue);
            border: none;
            color: #000;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: 0.3s;
        }

        .edit-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(77, 182, 255, 0.4);
            filter: brightness(1.1);
        }

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
            border: 1px solid var(--border-color);
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .post-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .post-user-info strong {
            display: block;
            font-size: 16px;
        }

        .post-time {
            font-size: 13px;
            color: var(--text-gray);
        }

        .post-caption {
            margin: 15px 0;
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

        .post-actions {
            display: flex;
            gap: 25px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .action-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.1s ease;
        }

        .heart-icon {
            font-size: 22px;
            transition: all 0.2s ease;
        }

        .action-btn.active .heart-icon {
            color: var(--insta-red);
            animation: heartPop 0.3s linear;
        }

        @keyframes heartPop {
            50% {
                transform: scale(1.3);
            }

            100% {
                transform: scale(1);
            }
        }

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.show {
            display: flex;
        }

        .modal-box {
            width: 95%;
            max-width: 1200px;
            height: 85vh;
            background: var(--card-bg);
            border-radius: 20px;
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
            padding: 20px;
            display: flex;
            flex-direction: column;
            background: #08151a;
            border-left: 1px solid var(--border-color);
        }

        .comments-list {
            flex: 1;
            overflow-y: auto;
        }

        .comment-input-area {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .comment-input-area input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            padding: 12px;
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
            </div>
            <div class="profile-actions">
                <p class="bio-text"><?= htmlspecialchars($user['bio'] ?? 'Write something about yourself...') ?></p>
                <button class="edit-profile-btn" onclick="location.href='edit_profile.php'">Edit Profile</button>
            </div>
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
                                <span class="heart-icon">
                                    <i class="<?= $p['liked'] ? 'fa-solid fa-heart' : 'fa-regular fa-heart' ?>"></i>
                                </span>
                                <span class="count"><?= $p['like_count'] ?></span>
                            </button>
                            <button class="action-btn comment-btn">
                                <span><i class="fa-regular fa-comment"></i></span>
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

    <div class="sidebar-right">
        <div class="section-title">Followers</div>
        <div class="follower-list">
            <?php if ($followers_res->num_rows > 0): ?>
                <?php while ($f = $followers_res->fetch_assoc()): ?>
                    <div class="follower-item" onclick="location.href='view_profile.php?id=<?= $f['id'] ?>'">
                        <img src="../uploads/<?= htmlspecialchars($f['picture'] ?? 'default.png') ?>" alt="follower">
                        <span><?= htmlspecialchars($f['username']) ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="font-size: 14px; color: var(--text-gray);">No followers yet.</p>
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
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="commentInput" placeholder="Write a comment..." required style="flex:1;">
                        <button type="submit"
                            style="background:var(--accent-blue); border:none; padding:10px 15px; border-radius:10px; cursor:pointer; color:black; font-weight:bold;">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPost = null;
        let replyToId = 0;
        const currentUserId = <?= $userId ?>;
        const modal = document.getElementById('modal');
        const modalImg = document.getElementById('modalImg');

        // Like Logic
        document.querySelectorAll(".like-btn").forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                const card = this.closest(".post-card");
                const postId = card.dataset.id;
                const heartIconWrapper = this.querySelector(".heart-icon i");
                const countSpan = this.querySelector(".count");
                const button = this;

                fetch("like_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "post_id=" + postId
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            countSpan.innerText = data.like_count;
                            button.classList.toggle("active");
                            heartIconWrapper.className = button.classList.contains("active") ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                        }
                    });
            };
        });

        // Open Modal Fix
        document.querySelectorAll(".post-image, .comment-btn").forEach(el => {
            el.onclick = () => {
                const card = el.closest(".post-card");
                currentPost = card.dataset.id;
                const postImg = card.querySelector(".post-image");

                // If there is no image in the post, we can hide modal-left or show a placeholder
                modalImg.src = postImg ? postImg.src : "";

                modal.classList.add("show");
                loadComments(currentPost);
            };
        });

        // Close Modal
        window.onclick = e => { if (e.target === modal) modal.classList.remove("show"); };

        function loadComments(postId) {
            const container = document.getElementById('modalComments');
            container.innerHTML = '<p style="color:gray; padding:10px;">Loading...</p>';

            fetch("get_comments.php?post_id=" + postId)
                .then(r => r.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(err => {
                    container.innerHTML = '<p style="color:red; padding:10px;">Error loading comments.</p>';
                });
        }

        // Add Comment Logic
        document.getElementById('commentForm').onsubmit = e => {
            e.preventDefault();
            const input = document.getElementById('commentInput');
            if (!input.value.trim() || !currentPost) return;

            fetch("add_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `post_id=${currentPost}&comment_text=${encodeURIComponent(input.value)}&parent_id=${replyToId}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        input.value = "";
                        replyToId = 0;
                        input.placeholder = "Write a comment...";
                        loadComments(currentPost);

                        // Update local comment count on the card
                        const card = document.querySelector(`.post-card[data-id="${currentPost}"]`);
                        if (card) {
                            const countSpan = card.querySelector('.comment-btn .count');
                            countSpan.innerText = parseInt(countSpan.innerText) + 1;
                        }
                    }
                })
                .catch(err => console.error("Post Error:", err));
        };

        function setReply(id, username) {
            replyToId = id;
            const input = document.getElementById('commentInput');
            input.placeholder = "Replying to " + username + "...";
            input.focus();
        }

        function editComment(id, currentText) {
            const item = document.getElementById('comment-text-' + id);
            if (!item) return;

            item.innerHTML = `
                <div style="margin-top:5px; width: 100%;">
                    <input type="text" id="edit-input-${id}" value="${currentText}" 
                           style="width:100%; padding:8px; border-radius:8px; background:rgba(255,255,255,0.1); border:1px solid #4db6ff; color:white; outline:none; font-size:13px;">
                    <div style="display:flex; gap:10px; margin-top:8px;">
                        <button onclick="saveEdit(${id})" style="font-size:11px; cursor:pointer; background:#4db6ff; border:none; border-radius:5px; padding:4px 12px; font-weight:bold; color:black;">Save</button>
                        <button onclick="loadComments(${currentPost})" style="font-size:11px; cursor:pointer; background:rgba(255,255,255,0.1); border:none; border-radius:5px; padding:4px 12px; color:white;">Cancel</button>
                    </div>
                </div>
            `;

            setTimeout(() => {
                const input = document.getElementById(`edit-input-${id}`);
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);
            }, 10);
        }

        function saveEdit(id) {
            const inputField = document.getElementById(`edit-input-${id}`);
            const newText = inputField.value;

            if (!newText.trim()) return;

            fetch("edit_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `comment_id=${id}&content=${encodeURIComponent(newText)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        loadComments(currentPost);
                    } else {
                        alert(data.message || "Error updating comment");
                    }
                })
                .catch(err => console.error("Edit Error:", err));
        }

        function deleteComment(id) {
            if (!confirm("Are you sure?")) return;
            fetch("delete_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "comment_id=" + id
            }).then(() => loadComments(currentPost));
        }
    </script>
</body>

</html>