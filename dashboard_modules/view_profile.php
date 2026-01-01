<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}
$loggedInId = $_SESSION['id'];
include('../config.php');

// GET THE USER ID FROM URL
if (!isset($_GET['user_id'])) {
    header("Location: user_dashboard.php");
    exit();
}
$targetUserId = (int) $_GET['user_id'];

// Redirect to personal profile page if it's the logged-in user
if ($targetUserId == $loggedInId) {
    header("Location: ../user_modules/profile.php");
    exit();
}

/* FETCH TARGET USER INFO + COUNTS */
$sql = "SELECT u.*, 
        EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?) AS is_following,
        (SELECT COUNT(*) FROM follows WHERE following_id = ?) AS followers_count,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ?) AS following_count
        FROM users u WHERE u.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iiiii", $loggedInId, $targetUserId, $targetUserId, $targetUserId, $targetUserId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$targetUser = mysqli_fetch_assoc($result);

if (!$targetUser) {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($targetUser['username']) ?> | Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #051115;
            --sidebar-bg: #030a0c;
            --card-bg: #112229;
            --accent-blue: #4db6ff;
            --text-white: #ffffff;
            --text-gray: #94a3b8;
            --border-color: #2a3f47;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --danger: #ff4d4d;
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
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
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 50px;
            padding-left: 15px;
            letter-spacing: -1px;
        }

        .sidebar-left a {
            display: block;
            width: 100%;
            padding: 12px 20px;
            margin-bottom: 15px;
            border-radius: 50px;
            text-decoration: none;
            color: var(--text-white);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-left a:hover {
            background: rgba(77, 182, 255, 0.1);
            border-color: var(--accent-blue);
            transform: translateX(5px);
        }

        /* --- MAIN CONTENT --- */
        .content {
            flex: 1;
            padding: 40px 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-header-card {
            width: 100%;
            max-width: 650px;
            background: var(--card-bg);
            border-radius: 30px;
            padding: 50px 30px;
            text-align: center;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 50px;
        }

        .profile-large-pic {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-blue);
            margin-bottom: 20px;
            padding: 3px;
            background: var(--bg-dark);
        }

        .profile-name {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
        }

        .profile-email {
            color: var(--text-gray);
            font-size: 14px;
            margin: 5px 0 20px 0;
            display: block;
        }

        .stats-glass-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 25px auto;
            padding: 18px 35px;
            background: var(--glass-bg);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--accent-blue);
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-gray);
        }

        .follow-btn-large {
            padding: 14px 50px;
            border-radius: 50px;
            border: 1px solid var(--accent-blue);
            font-weight: 700;
            cursor: pointer;
            background: var(--accent-blue);
            color: #000;
            transition: 0.3s;
        }

        .follow-btn-large.following {
            background: transparent;
            color: var(--accent-blue);
        }

        /* --- POSTS --- */
        .posts-container {
            width: 100%;
            max-width: 600px;
        }

        .posts-title {
            font-size: 18px;
            margin-bottom: 25px;
            padding-left: 15px;
            border-left: 4px solid var(--accent-blue);
            font-weight: 700;
        }

        .post-card {
            background: var(--card-bg);
            border-radius: 20px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .post-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
        }

        .post-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--accent-blue);
        }

        .post-username {
            font-weight: bold;
            color: var(--accent-blue);
        }

        .post-time {
            font-size: 12px;
            color: var(--text-gray);
            display: block;
        }

        /* --- DOTS MENU --- */
        .post-options {
            position: relative;
        }

        .dots-btn {
            background: none;
            border: none;
            color: var(--text-gray);
            cursor: pointer;
            padding: 5px;
            font-size: 18px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 30px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            min-width: 150px;
            z-index: 100;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .dropdown-menu button {
            width: 100%;
            padding: 12px 15px;
            background: none;
            border: none;
            color: var(--text-white);
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-menu button:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .dropdown-menu button.delete-btn:hover {
            background: rgba(255, 77, 77, 0.1);
            color: var(--danger);
        }

        .post-caption {
            padding: 15px 20px;
            font-size: 15px;
            line-height: 1.5;
        }

        .post-image-container {
            width: 100%;
            background: #000;
            display: flex;
            justify-content: center;
            cursor: pointer;
        }

        .post-image {
            width: 100%;
            max-height: 600px;
            object-fit: contain;
        }

        .post-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            gap: 20px;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-white);
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .action-btn.liked {
            color: var(--danger);
        }

        /* --- MODALS --- */
        .comment-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
        }

        .comment-modal-container {
            display: flex;
            width: 90%;
            max-width: 1100px;
            height: 85vh;
            margin: 7.5vh auto;
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .modal-left-post {
            flex: 1.5;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid var(--border-color);
            padding: 20px;
            color: var(--text-white);
            text-align: center;
        }

        .modal-left-post img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .modal-right-comments {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
        }

        .comments-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .comments-list {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .comment-input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
        }

        .reply-indicator {
            display: none;
            background: rgba(77, 182, 255, 0.1);
            padding: 8px 15px;
            font-size: 12px;
            color: var(--accent-blue);
            justify-content: space-between;
            align-items: center;
            border-radius: 10px 10px 0 0;
        }

        .comment-input-area input {
            width: 100%;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            padding: 12px 20px;
            color: white;
            border-radius: 25px;
            outline: none;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
        }

        .modal-content {
            background: var(--card-bg);
            margin: 8% auto;
            padding: 30px;
            width: 90%;
            max-width: 400px;
            border-radius: 25px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 25px;
            top: 25px;
            cursor: pointer;
            color: var(--text-gray);
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div class="sidebar-left">
        <div class="logo">MySocial</div>
        <a href="user_dashboard.php">Back to Feed</a>
        <a href="../user_modules/profile.php">My Profile</a>
    </div>

    <div class="content">
        <div class="profile-header-card">
            <img src="../uploads/<?= htmlspecialchars($targetUser['picture'] ?? 'default.png') ?>"
                class="profile-large-pic">
            <h1 class="profile-name"><?= htmlspecialchars($targetUser['username']) ?></h1>
            <span class="profile-email"><?= htmlspecialchars($targetUser['email']) ?></span>

            <div class="stats-glass-container">
                <div class="stat-item" onclick="openUserList('followers')">
                    <span class="stat-value"><?= number_format($targetUser['followers_count']) ?></span>
                    <span class="stat-label">Followers</span>
                </div>
                <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.1);"></div>
                <div class="stat-item" onclick="openUserList('following')">
                    <span class="stat-value"><?= number_format($targetUser['following_count']) ?></span>
                    <span class="stat-label">Following</span>
                </div>
            </div>

            <p class="profile-bio"><?= nl2br(htmlspecialchars($targetUser['bio'] ?? 'No bio yet.')) ?></p>

            <button class="follow-btn-large <?= $targetUser['is_following'] ? 'following' : '' ?>"
                onclick="toggleFollow(this, <?= $targetUserId ?>)">
                <?= $targetUser['is_following'] ? 'Following' : 'Follow' ?>
            </button>
        </div>

        <div class="posts-container">
            <div class="posts-title">Recent Posts</div>
            <?php
            $postSql = "SELECT p.*, u.username, u.picture,
                        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                        EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?) as user_liked
                        FROM posts p JOIN users u ON p.user_id = u.id
                        WHERE p.user_id = ? ORDER BY p.created_at DESC";

            $postStmt = mysqli_prepare($conn, $postSql);
            mysqli_stmt_bind_param($postStmt, "ii", $loggedInId, $targetUserId);
            mysqli_stmt_execute($postStmt);
            $postRes = mysqli_stmt_get_result($postStmt);

            while ($post = mysqli_fetch_assoc($postRes)): ?>
                <div class="post-card" id="post-<?= $post['id'] ?>">
                    <div class="post-header">
                        <div class="post-user-info">
                            <img src="../uploads/<?= htmlspecialchars($post['picture'] ?? 'default.png') ?>"
                                class="post-avatar">
                            <div class="post-user-details">
                                <span class="post-username"><?= htmlspecialchars($post['username']) ?></span>
                                <span class="post-time"><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="post-options">
                            <button class="dots-btn" onclick="toggleDropdown(event, <?= $post['id'] ?>)">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="dropdown-menu" id="dropdown-<?= $post['id'] ?>">
                                <?php if ($post['user_id'] == $loggedInId): ?>
                                    <button onclick="editPost(<?= $post['id'] ?>)">
                                        <i class="fa-regular fa-pen-to-square"></i> Edit Post
                                    </button>
                                    <button class="delete-btn" onclick="deletePost(<?= $post['id'] ?>)">
                                        <i class="fa-regular fa-trash-can"></i> Delete Post
                                    </button>
                                <?php else: ?>
                                    <button onclick="reportPost(<?= $post['id'] ?>)">
                                        <i class="fa-regular fa-flag"></i> Report Post
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="post-caption" id="caption-<?= $post['id'] ?>">
                        <?= nl2br(htmlspecialchars($post['caption'])) ?>
                    </div>

                    <?php if (!empty($post['image'])): ?>
                        <div class="post-image-container" onclick="openComments(<?= $post['id'] ?>)">
                            <img src="../user_modules/<?= htmlspecialchars($post['image']) ?>" class="post-image"
                                id="img-<?= $post['id'] ?>">
                        </div>
                    <?php endif; ?>

                    <div class="post-footer">
                        <button class="action-btn <?= $post['user_liked'] ? 'liked' : '' ?>"
                            onclick="likePost(<?= $post['id'] ?>)">
                            <i class="<?= $post['user_liked'] ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                            <span class="like-count"><?= $post['like_count'] ?></span>
                        </button>
                        <button class="action-btn" onclick="openComments(<?= $post['id'] ?>)">
                            <i class="fa-regular fa-comment"></i>
                            <span>Comment</span>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="commentModal" class="comment-modal">
        <div class="comment-modal-container">
            <div class="modal-left-post" id="modalPostContent"></div>
            <div class="modal-right-comments">
                <div class="comments-header"><span>Comments</span><i class="fa-solid fa-xmark"
                        onclick="closeCommentModal()" style="cursor:pointer;"></i></div>
                <div class="comments-list" id="modalCommentsList"></div>

                <div class="comment-input-area">
                    <div id="replyIndicator" class="reply-indicator">
                        <span id="replyText">Replying to...</span>
                        <i class="fa-solid fa-xmark" style="cursor:pointer;" onclick="cancelReply()"></i>
                    </div>
                    <input type="text" id="commentInput" placeholder="Add a comment..."
                        onkeydown="handleCommentSubmit(event)">
                </div>
            </div>
        </div>
    </div>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalTitle"
                style="font-weight:bold; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        let currentPostId = null;
        let replyToId = 0;

        function toggleDropdown(event, postId) {
            event.stopPropagation();
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu.id !== `dropdown-${postId}`) menu.style.display = 'none';
            });
            const dropdown = document.getElementById(`dropdown-${postId}`);
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = () => document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');

        function deletePost(postId) {
            if (confirm("Are you sure you want to delete this post?")) {
                fetch("../user_modules/delete_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "post_id=" + postId
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        document.getElementById(`post-${postId}`).remove();
                    } else {
                        alert(data.message || "Error deleting post");
                    }
                });
            }
        }

        function editPost(postId) {
            const currentCaption = document.getElementById(`caption-${postId}`).innerText.trim();
            const newCaption = prompt("Edit your caption:", currentCaption);
            if (newCaption !== null) {
                fetch("../user_modules/edit_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `post_id=${postId}&caption=${encodeURIComponent(newCaption)}`
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        document.getElementById(`caption-${postId}`).innerText = newCaption;
                    } else {
                        alert(data.message || "Error updating post");
                    }
                });
            }
        }

        function deleteComment(commentId) {
            if (confirm("Delete this comment?")) {
                fetch("../user_modules/delete_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "comment_id=" + commentId
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        fetchComments(currentPostId);
                    } else {
                        alert("Error deleting comment");
                    }
                });
            }
        }

        function editComment(commentId, oldText) {
            const newText = prompt("Edit comment:", oldText);
            if (newText && newText !== oldText) {
                fetch("../user_modules/edit_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `comment_id=${commentId}&content=${encodeURIComponent(newText)}`
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        fetchComments(currentPostId);
                    } else {
                        alert("Error updating comment");
                    }
                });
            }
        }

        function setReply(id, username) {
            replyToId = id;
            const indicator = document.getElementById('replyIndicator');
            const replyText = document.getElementById('replyText');
            replyText.innerText = "Replying to " + username;
            indicator.style.display = 'flex';
            document.getElementById('commentInput').focus();
        }

        function cancelReply() {
            replyToId = 0;
            document.getElementById('replyIndicator').style.display = 'none';
        }

        function reportPost(postId) {
            if (confirm("Report this post?")) {
                const form = document.createElement('form');
                form.method = 'POST'; form.action = '../user_modules/report.php';
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'post_id'; input.value = postId;
                form.appendChild(input); document.body.appendChild(form); form.submit();
            }
        }

        function openComments(postId) {
            currentPostId = postId;
            cancelReply();
            const modal = document.getElementById('commentModal');
            const postImg = document.getElementById(`img-${postId}`);
            const modalLeft = document.getElementById('modalPostContent');
            const captionElement = document.getElementById(`caption-${postId}`);
            const captionText = captionElement ? captionElement.innerText : "";

            modalLeft.innerHTML = '';
            if (postImg) {
                const newImg = document.createElement('img');
                newImg.src = postImg.src;
                modalLeft.appendChild(newImg);
            } else {
                modalLeft.innerHTML = `<div style="padding:40px; font-size:18px;">${captionText}</div>`;
            }

            modal.style.display = 'block';
            fetchComments(postId);
        }

        function closeCommentModal() {
            document.getElementById('commentModal').style.display = 'none';
            cancelReply();
        }

        function fetchComments(postId) {
            if (!postId) return;
            // Point to the correct relative path: ../user_modules/
            fetch(`../user_modules/get_comments.php?post_id=${postId}`)
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('modalCommentsList');
                    if (data.success && data.html) {
                        list.innerHTML = data.html;
                    } else {
                        list.innerHTML = '<div style="color:#71767b; padding:20px; text-align:center;">No comments yet.</div>';
                    }
                })
                .catch(err => {
                    console.error("Fetch Error:", err);
                    document.getElementById('modalCommentsList').innerHTML = "Error loading comments.";
                });
        }

        function handleCommentSubmit(e) {
            if (e.key === 'Enter' && e.target.value.trim() !== '') {
                const commentText = e.target.value;
                // Point to the correct relative path: ../user_modules/
                fetch("../user_modules/add_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `post_id=${currentPostId}&comment_text=${encodeURIComponent(commentText)}&parent_id=${replyToId}`
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        e.target.value = '';
                        cancelReply();
                        fetchComments(currentPostId);
                    } else {
                        alert(data.message || "Failed to add comment.");
                    }
                });
            }
        }

        function likePost(postId) {
            const btn = document.querySelector(`#post-${postId} .action-btn`);
            // Point to the correct relative path: ../user_modules/
            fetch("../user_modules/like_post.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "post_id=" + postId
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    btn.querySelector('.like-count').innerText = data.like_count;
                    const icon = btn.querySelector('i');
                    if (data.liked) { btn.classList.add('liked'); icon.classList.replace('fa-regular', 'fa-solid'); }
                    else { btn.classList.remove('liked'); icon.classList.replace('fa-solid', 'fa-regular'); }
                }
            });
        }

        function openUserList(type) {
            const modal = document.getElementById('userModal');
            modal.style.display = "block";
            document.getElementById('modalTitle').innerText = type.toUpperCase();
            // Point to the correct relative path: get_user_list.php is in the same folder
            fetch(`get_user_list.php?type=${type}&user_id=<?= $targetUserId ?>`)
                .then(r => r.text()).then(html => { document.getElementById('modalBody').innerHTML = html; });
        }

        function closeModal() { document.getElementById('userModal').style.display = "none"; }

        function toggleFollow(btn, targetUserId) {
            // Point to the correct relative path: ../user_modules/
            fetch("../user_modules/follow_user.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "following_id=" + targetUserId
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
            });
        }
    </script>
</body>
</html>