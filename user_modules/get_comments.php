<?php
session_start();
include('../config.php');

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set header to HTML so the browser/JS renders it correctly as elements
header('Content-Type: text/html; charset=UTF-8');

$current_user = $_SESSION['id'] ?? null;
$post_id = (int) ($_GET['post_id'] ?? 0);

if (!$post_id) {
    echo '<div style="color:red; padding:10px;">Error: No post specified.</div>';
    exit;
}

try {
    /* FETCH ALL COMMENTS */
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
            IF(c.user_id = ?, 1, 0) AS is_owner
        FROM comments c
        JOIN users u ON u.id = c.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");

    $stmt->bind_param("ii", $current_user, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $all_comments = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['parent_id'] = ($row['parent_id'] === null || $row['parent_id'] == 0) ? 0 : (int) $row['parent_id'];
        $row['user_id'] = (int) $row['user_id'];
        $row['is_owner'] = (int) $row['is_owner'];
        $all_comments[] = $row;
    }

    /**
     * RECURSIVE FUNCTION TO RENDER COMMENTS & REPLIES
     */
    function renderComments($comments, $parentId = 0)
    {
        $html = '';
        foreach ($comments as $c) {
            if ($c['parent_id'] == $parentId) {
                $isOwner = $c['is_owner'];
                $profilePic = (!empty($c['picture'])) ? $c['picture'] : 'default.png';
                $isReply = ($parentId > 0);

                // Escape content for safe JS passing to keep Edit/Delete working
                $escapedContent = addslashes($c['content']);
                $jsContent = str_replace(["\r", "\n"], ' ', $escapedContent);

                $html .= '<div class="comment-block" style="margin-bottom: 12px; position: relative; width: 100%;">';

                // Container for the comment itself
                $html .= '<div class="comment-row comment-item" style="display: flex; gap: 10px; padding: 5px 15px 5px ' . ($isReply ? '55px' : '15px') . '; position: relative; align-items: flex-start;">';

                // Vertical line for replies
                if ($isReply) {
                    $html .= '<div class="thread-line" style="position: absolute; left: 30px; top: -12px; bottom: 20px; width: 2px; background: rgba(255,255,255,0.1); border-radius: 1px;"></div>';
                }

                // IMAGE PATH: Relative to the module location
                $html .= '
                        <img src="../uploads/' . htmlspecialchars($profilePic) . '" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.1); z-index: 2;">
                        <div style="display: flex; flex-direction: column; flex: 1; min-width: 0;">
                            <div style="background: rgba(255, 255, 255, 0.05); padding: 8px 14px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); align-self: flex-start; max-width: 90%;">
                                <div style="color: #ffffff; font-weight: 700; font-size: 13px; margin-bottom: 2px; display: block;">' . htmlspecialchars($c['username']) . '</div>
                                <div id="comment-text-' . $c['id'] . '" class="comment-content-text" style="color: #efefef; font-size: 13.5px; line-height: 1.4; word-break: break-word; display: block;">' . nl2br(htmlspecialchars($c['content'])) . '</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px; margin-top: 4px; margin-left: 10px; font-size: 11px; color: #a8a8a8;">
                                <span>' . date('M d', strtotime($c['created_at'])) . '</span>
                                <span style="cursor: pointer; font-weight: 600;" onclick="setReply(' . $c['id'] . ', \'' . addslashes($c['username']) . '\')">Reply</span>';

                if ($isOwner) {
                    $html .= '
                                <span style="cursor: pointer; font-weight: 600; color: #4db6ff;" onclick="editComment(' . $c['id'] . ', \'' . $jsContent . '\')">Edit</span>
                                <span style="cursor: pointer; font-weight: 600; color: #ff5e5e;" onclick="deleteComment(' . $c['id'] . ')">Delete</span>';
                }

                $html .= '
                            </div>
                        </div>
                    </div>';

                // RECURSION CALL for nested replies
                $html .= renderComments($comments, $c['id']);

                $html .= '</div>';
            }
        }
        return $html;
    }

    // Generate the HTML content
    $rendered_html = renderComments($all_comments);

    if (empty($rendered_html)) {
        echo '<div style="color:#71767b; padding:40px 20px; text-align:center; font-size: 14px;">No comments yet.</div>';
    } else {
        // Output the HTML directly so JavaScript innerHTML works
        echo $rendered_html;
    }

} catch (Exception $e) {
    echo '<div style="color:red; padding:10px;">System Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
exit;