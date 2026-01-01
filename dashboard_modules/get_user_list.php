<?php
include('../config.php');
$type = $_GET['type'] ?? '';
$targetId = (int) ($_GET['user_id'] ?? 0);

if ($type == 'followers') {
    $sql = "SELECT u.id, u.username, u.picture FROM follows f 
            JOIN users u ON f.follower_id = u.id WHERE f.following_id = $targetId";
} else {
    $sql = "SELECT u.id, u.username, u.picture FROM follows f 
            JOIN users u ON f.following_id = u.id WHERE f.follower_id = $targetId";
}

$res = mysqli_query($conn, $sql);
?>

<style>
    .user-list-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    .user-list-item:last-child {
        border-bottom: none;
    }

    .user-list-item:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .user-list-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--accent-blue);
    }

    .user-list-link {
        text-decoration: none;
        color: var(--text-white);
        font-weight: 600;
        font-size: 14px;
        flex: 1;
    }

    .user-list-link:hover {
        color: var(--accent-blue);
    }
</style>

<?php
if ($res && mysqli_num_rows($res) > 0) {
    while ($u = mysqli_fetch_assoc($res)) {
        // Correctly handling the uploads path
        $pic = !empty($u['picture']) ? $u['picture'] : 'default.png';

        // Using htmlspecialchars for safety and linking to user_profile.php
        echo "<div class='user-list-item'>
                <img src='../uploads/" . htmlspecialchars($pic) . "' class='user-list-pic'>
                <a href='user_profile.php?user_id=" . (int) $u['id'] . "' class='user-list-link'>"
            . htmlspecialchars($u['username']) .
            "</a>
              </div>";
    }
} else {
    echo "<p style='color:var(--text-gray); text-align:center; margin-top: 20px; font-size: 14px;'>No users to show.</p>";
}
?>