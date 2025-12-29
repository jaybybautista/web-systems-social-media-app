<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

$current_user = $_SESSION['id'] ?? null;
$post_id = (int) ($_GET['post_id'] ?? 0);

/* PAGINATION (FIXED) */
$limit = (int) ($_GET['limit'] ?? 5);   // <- FIXED invisible character
$offset = (int) ($_GET['offset'] ?? 0);

if (!$post_id) {
    echo json_encode([
        'success' => false,
        'comments' => [],
        'current_user' => $current_user
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH ALL COMMENTS (PARENTS + REPLIES)
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT
        c.id,
        c.post_id,
        c.parent_id,
        c.content,
        c.created_at,
        c.user_id,
        u.username,
        u.picture,

        /* ownership */
        IF(c.user_id = ?, 1, 0) AS is_owner,

        /* like count */
        (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) AS like_count,

        /* liked by current user */
        EXISTS(
            SELECT 1 FROM comment_likes
            WHERE comment_id = c.id AND user_id = ?
        ) AS liked_by_me

    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");

$stmt->bind_param("iii", $current_user, $current_user, $post_id);
$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| BUILD COMMENT TREE
|--------------------------------------------------------------------------
*/
$map = [];
$tree = [];

while ($row = $result->fetch_assoc()) {
    $row['is_owner'] = (bool) $row['is_owner'];
    $row['liked_by_me'] = (bool) $row['liked_by_me'];
    $row['like_count'] = (int) $row['like_count'];
    $row['replies'] = [];

    $map[$row['id']] = $row;
}

foreach ($map as $id => &$comment) {
    if ($comment['parent_id']) {
        if (isset($map[$comment['parent_id']])) {
            $map[$comment['parent_id']]['replies'][] = &$comment;
        }
    } else {
        $tree[] = &$comment;
    }
}
unset($comment);

/*
|--------------------------------------------------------------------------
| PAGINATION (ROOT COMMENTS ONLY)
|--------------------------------------------------------------------------
*/
$total_comments = count($tree);
$tree = array_slice($tree, $offset, $limit);

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/
echo json_encode([
    'success' => true,
    'comments' => $tree,
    'total_comments' => $total_comments,
    'current_user' => $current_user
]);
