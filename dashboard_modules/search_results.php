<?php
session_start();
if (!isset($_SESSION['id'])) {
  header("Location: ../login_modules/login.php");
  exit();
}
$id = $_SESSION['id'];
include('../config.php');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = [];

if (!empty($query)) {
  $searchQuery = '%' . mysqli_real_escape_string($conn, $query) . '%';
  $sql = "SELECT id, username, picture 
          FROM users 
          WHERE username LIKE '$searchQuery' 
          AND id != $id 
          ORDER BY username ASC";

  $result = mysqli_query($conn, $sql);
  if ($result && mysqli_num_rows($result) > 0) {
    while ($user = mysqli_fetch_assoc($result)) {
      $users[] = $user;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results | MySocial</title>
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

    .logo {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 50px;
      padding-left: 15px;
    }

    .sidebar-left a,
    .sidebar-btn {
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
    .sidebar-btn:hover {
      background: rgba(77, 182, 255, 0.1);
      border-color: var(--accent-blue);
    }

    .content {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }

    .search-header {
      max-width: 700px;
      margin: 0 auto 40px auto;
    }

    .search-header h2 {
      font-size: 28px;
      margin: 0 0 10px 0;
    }

    .search-info {
      color: var(--text-gray);
      font-size: 14px;
      margin-bottom: 20px;
    }

    .back-btn {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-white);
      padding: 8px 20px;
      border-radius: 50px;
      cursor: pointer;
      font-size: 14px;
      transition: 0.3s;
      text-decoration: none;
      display: inline-block;
    }

    .back-btn:hover {
      background: rgba(77, 182, 255, 0.1);
      border-color: var(--accent-blue);
    }

    .users-container {
      max-width: 700px;
      margin: 0 auto;
    }

    .user-result {
      background: var(--card-bg);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border: 2px solid var(--border-color);
      transition: 0.3s;
    }

    .user-result:hover {
      border-color: var(--accent-blue);
      background: rgba(77, 182, 255, 0.05);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
      flex: 1;
    }

    .user-pic {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      object-fit: cover;
    }

    .user-name {
      font-weight: 600;
      font-size: 16px;
    }

    .view-profile-btn {
      background: rgba(77, 182, 255, 0.1);
      border: 1px solid var(--accent-blue);
      color: var(--accent-blue);
      padding: 8px 20px;
      border-radius: 20px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
      transition: 0.3s;
    }

    .view-profile-btn:hover {
      background: var(--accent-blue);
      color: black;
    }

    .no-results {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-gray);
    }

    .no-results-icon {
      font-size: 48px;
      margin-bottom: 20px;
      opacity: 0.5;
    }
  </style>
</head>

<body>

  <div class="sidebar-left">
    <div class="logo">MySocial</div>
    <a href="../user_modules/profile.php">Profile</a>
    <a href="user_dashboard.php">Newsfeed</a>
    <button class="sidebar-btn" onclick="document.querySelector('.upload-form').style.display='flex'">Create Post</button>
    <a href="../logout.php" style="margin-top: 50px;">Logout</a>
  </div>

  <div class="content">
    <div class="search-header">
      <h2>Search Results</h2>
      <?php if (!empty($query)): ?>
        <div class="search-info">
          Showing results for "<strong><?= htmlspecialchars($query) ?></strong>"
          <?php if (!empty($users)): ?>
            (<?= count($users) ?> <?= count($users) === 1 ? 'user' : 'users' ?> found)
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <a href="user_dashboard.php" class="back-btn">← Back to Newsfeed</a>
    </div>

    <div class="users-container">
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
          <div class="user-result">
            <div class="user-info">
              <img src="../uploads/<?= htmlspecialchars($user['picture'] ?? 'default.png') ?>" alt="<?= htmlspecialchars($user['username']) ?>" class="user-pic">
              <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <a href="view_profile.php?user_id=<?= $user['id'] ?>" class="view-profile-btn">View Profile</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-results">
          <div class="no-results-icon"><i class="fas fa-search"></i></div>
          <?php if (!empty($query)): ?>
            <p>No users found matching "<strong><?= htmlspecialchars($query) ?></strong>"</p>
          <?php else: ?>
            <p>Enter a search query to find users</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>