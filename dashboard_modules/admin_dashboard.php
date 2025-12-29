<?php
session_start();
include('../config.php');

// Ensure only admins can access
if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}

$admin_id = $_SESSION['id'];
$query = "SELECT * FROM users WHERE id = $admin_id AND type = 'admin'";
$res = mysqli_query($conn, $query);
if (mysqli_num_rows($res) == 0) {
    echo "Access Denied. Admins only.";
    exit();
}

$admin = mysqli_fetch_assoc($res);

// Fetch Statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_posts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM posts"))['count'];
$total_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reports"))['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MySocial</title>
    <style>
        :root {
            --bg-dark: #051115;
            --header-bg: #030a0c;
            --card-bg: #112229;
            --accent-blue: #4db6ff;
            --danger: #ff4d4d;
            --text-white: #ffffff;
            --text-gray: #94a3b8;
            --border-color: #2a3f47;
            --success: #22c55e;
        }

        body {
            margin: 0;
            background-color: var(--bg-dark);
            color: var(--text-white);
            font-family: 'Inter', sans-serif;
        }

        header {
            background: var(--header-bg);
            padding: 0 40px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: var(--accent-blue);
        }

        nav {
            display: flex;
            gap: 30px;
        }

        nav a {
            text-decoration: none;
            color: var(--text-gray);
            font-size: 15px;
            transition: 0.3s;
        }

        nav a:hover,
        nav a.active {
            color: var(--accent-blue);
        }

        .container {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            margin-top: 50px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        /* --- BUTTONS --- */
        .btn-delete,
        .btn-edit {
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            font-size: 13px;
            border: 1px solid transparent;
        }

        .btn-delete {
            background: rgba(255, 77, 77, 0.1);
            color: var(--danger);
            border-color: var(--danger);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        .btn-edit {
            background: rgba(77, 182, 255, 0.1);
            color: var(--accent-blue);
            border-color: var(--accent-blue);
            margin-right: 5px;
        }

        .btn-edit:hover {
            background: var(--accent-blue);
            color: #000;
        }

        /* --- TABLES --- */
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-gray);
            font-weight: 500;
        }

        /* --- STATS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .stat-card p {
            margin: 10px 0 0;
            font-size: 32px;
            font-weight: bold;
            color: var(--accent-blue);
        }

        /* --- MODAL --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            width: 400px;
            border: 1px solid var(--border-color);
        }

        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">MySocial <span style="font-weight: 300; font-size: 14px; color: white;">Admin</span></div>
        <nav>
            <a href="#" class="active">Overview</a>
            <a href="#users">Users</a>
            <a href="#reports">Reports</a>
        </nav>
        <div class="admin-profile">
            <span style="font-size: 14px;"><?= htmlspecialchars($admin['username']) ?></span>
            <img src="../uploads/<?= htmlspecialchars($admin['picture'] ?? 'default.png') ?>"
                style="width:35px; height:35px; border-radius:50%; vertical-align:middle;">
            <a href="../logout.php"
                style="color: var(--danger); text-decoration: none; font-size: 13px; margin-left: 10px;">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?= $total_users ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Posts</h3>
                <p><?= $total_posts ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Reports</h3>
                <p><?= $total_reports ?></p>
            </div>
        </div>

        <div class="section-header" id="users">
            <h2>User Management</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $users_sql = "SELECT * FROM users ORDER BY created_at DESC";
                $users_res = mysqli_query($conn, $users_sql);
                while ($u = mysqli_fetch_assoc($users_res)): ?>
                    <tr id="user-row-<?= $u['id'] ?>">
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <img src="../uploads/<?= $u['picture'] ?? 'default.png' ?>"
                                style="width: 30px; height: 30px; border-radius: 50%;">
                            <span><?= htmlspecialchars($u['username']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span
                                style="color: <?= $u['type'] == 'admin' ? 'var(--accent-blue)' : 'var(--text-gray)' ?>"><?= strtoupper($u['type']) ?></span>
                        </td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] != $admin_id): ?>
                                <button class="btn-edit" onclick='openEditModal(<?= json_encode($u) ?>)'>Edit</button>
                                <button class="btn-delete" onclick="deleteUser(<?= $u['id'] ?>)">Delete</button>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--text-gray);">Current Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="section-header" id="reports">
            <h2>Pending Reports</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Reported Post</th>
                    <th>Reported By</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $reports_sql = "SELECT r.*, u.username as reporter_name, p.caption 
                               FROM reports r 
                               JOIN users u ON r.user_id = u.id 
                               JOIN posts p ON r.post_id = p.id 
                               ORDER BY r.created_at DESC";
                $reports_res = mysqli_query($conn, $reports_sql);
                if (mysqli_num_rows($reports_res) > 0):
                    while ($r = mysqli_fetch_assoc($reports_res)): ?>
                        <tr id="report-row-<?= $r['id'] ?>">
                            <td><i
                                    style="color: var(--text-gray);">"<?= htmlspecialchars(substr($r['caption'] ?? '', 0, 30)) ?>..."</i>
                            </td>
                            <td><?= htmlspecialchars($r['reporter_name']) ?></td>
                            <td style="color: #ffcc00;"><?= htmlspecialchars($r['reason']) ?></td>
                            <td><?= date('M d, h:i A', strtotime($r['created_at'])) ?></td>
                            <td>
                                <button class="btn-delete" onclick="deletePost(<?= $r['post_id'] ?>, <?= $r['id'] ?>)">Remove
                                    Post</button>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color: var(--text-gray);">No pending reports.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal" id="editModal">
        <div class="modal-content">
            <h3 style="margin-top:0">Edit User</h3>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id">
                <label>Username</label><input type="text" id="edit_username" required>
                <label>Email</label><input type="email" id="edit_email" required>
                <label>Role</label>
                <select id="edit_type">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" onclick="closeModal()"
                        style="flex:1; background:transparent; color:white; border:1px solid var(--border-color); padding:10px; border-radius:8px; cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1; background:var(--accent-blue); color:black; border:none; padding:10px; border-radius:8px; cursor:pointer; font-weight:bold;">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editModal');

        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_type').value = user.type;
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }

        document.getElementById('editUserForm').onsubmit = function (e) {
            e.preventDefault();
            const userId = document.getElementById('edit_user_id').value;
            const username = document.getElementById('edit_username').value;
            const email = document.getElementById('edit_email').value;
            const type = document.getElementById('edit_type').value;

            fetch('admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=edit_user&user_id=${userId}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&type=${type}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { location.reload(); }
                    else { alert("Error: " + data.message); }
                });
        };

        function deleteUser(userId) {
            if (confirm("Delete this user permanently?")) {
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_user&user_id=${userId}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) { document.getElementById(`user-row-${userId}`).remove(); }
                    });
            }
        }

        function deletePost(postId, reportId) {
            if (confirm("Are you sure you want to delete this reported post?")) {
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_post&post_id=${postId}&report_id=${reportId}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(`report-row-${reportId}`).remove();
                            alert("Post removed successfully.");
                        }
                    });
            }
        }
    </script>
</body>

</html>