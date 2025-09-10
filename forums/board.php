<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

$user_id = $_SESSION['user_id'] ?? 0;
$role_id = 3; // default normal user

if ($user_id) {
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    $stmt->close();
}

// Get board ID
$board_id = (int)($_GET['id'] ?? $_GET['board'] ?? 0);
if (!$board_id) {
    echo '<div class="alert">Invalid board.</div>';
    include __DIR__ . '/../partials/footer.php';
    exit;
}

// ------------------- Handle New Comment -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_post'])) {
    $post_id = (int)($_POST['post_id'] ?? 0);
    $content = trim($_POST['comment_content'] ?? '');
    if ($post_id && $user_id && $content !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $content);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ------------------- Handle Like/Unlike -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post'])) {
    $post_id = (int)($_POST['post_id'] ?? 0);
    if ($post_id && $user_id) {
        $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // unlike
            $stmt2 = $conn->prepare("DELETE FROM likes WHERE post_id=? AND user_id=?");
            $stmt2->bind_param("ii", $post_id, $user_id);
            $stmt2->execute();
            $stmt2->close();
        } else {
            // like
            $stmt2 = $conn->prepare("INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
            $stmt2->bind_param("ii", $post_id, $user_id);
            $stmt2->execute();
            $stmt2->close();
        }
        $stmt->close();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ------------------- Handle Delete Post (Admin/Moderator) -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post']) && ($role_id == 1 || $role_id == 2)) {
    $delete_id = (int)($_POST['post_id'] ?? 0);
    if ($delete_id) {
        $conn->query("DELETE FROM comments WHERE post_id=$delete_id");
        $conn->query("DELETE FROM likes WHERE post_id=$delete_id");
        $conn->query("DELETE FROM posts WHERE id=$delete_id");
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ------------------- Fetch Board Info -------------------
$stmt = $conn->prepare("SELECT forum_name, description FROM forum_boards WHERE id=?");
$stmt->bind_param("i", $board_id);
$stmt->execute();
$stmt->bind_result($board_name, $board_desc);
$stmt->fetch();
$stmt->close();

echo "<h2>" . htmlspecialchars($board_name) . "</h2>";
if ($board_desc) echo "<p>" . htmlspecialchars($board_desc) . "</p>";

// 🔹 Show "Create Post" button only if logged in
if ($user_id) {
    echo '<div style="margin:10px 0;">
        <a href="' . url("forums/new_post.php?board=$board_id") . '" class="btn">➕ Create New Post</a>
    </div>';
}

// ------------------- Fetch Posts -------------------
$stmt = $conn->prepare("
    SELECT posts.id, posts.title, posts.content, posts.image_url, posts.user_id, posts.created_at, users.username
    FROM posts 
    JOIN users ON posts.user_id = users.id
    WHERE posts.forum_id=? 
    ORDER BY posts.created_at DESC
");
$stmt->bind_param("i", $board_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
?>
<div class="card">
    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
    <p>By <strong><?php echo htmlspecialchars($row['username']); ?></strong> on <?php echo $row['created_at']; ?></p>
    <?php if (!empty($row['image_url'])): ?>
        <img src="<?php echo url('uploads/birds/' . $row['image_url']); ?>" style="max-width:200px;height:auto;margin:6px 0;">
    <?php endif; ?>
    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

    <!-- Like button -->
    <form method="post" style="display:inline-block;">
        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
        <button type="submit" name="like_post" class="btn">
            <?php
            $stmt2 = $conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
            $stmt2->bind_param("ii", $row['id'], $user_id);
            $stmt2->execute();
            $stmt2->store_result();
            echo $stmt2->num_rows > 0 ? "💖 Unlike" : "🤍 Like";
            $stmt2->close();
            ?>
        </button>
        <?php
        $stmt3 = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id=?");
        $stmt3->bind_param("i", $row['id']);
        $stmt3->execute();
        $stmt3->bind_result($like_count);
        $stmt3->fetch();
        $stmt3->close();
        echo " ($like_count)";
        ?>
    </form>

    <!-- 🔹 Delete Post (Admin/Moderator only) -->
    <?php if ($role_id == 1 || $role_id == 2): ?>
        <form method="post" style="display:inline-block;margin-left:10px;" onsubmit="return confirm('Delete this post permanently?');">
            <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
            <button type="submit" name="delete_post" class="btn" style="background:#d9534f;">❌ Delete</button>
        </form>
    <?php endif; ?>

    <!-- Comments -->
    <div style="margin-top:10px;">
        <strong>Comments:</strong>
        <?php
        $stmt4 = $conn->prepare("
            SELECT comments.id, comments.content, comments.created_at, users.username
            FROM comments
            JOIN users ON comments.user_id=users.id
            WHERE post_id=? ORDER BY created_at ASC
        ");
        $stmt4->bind_param("i", $row['id']);
        $stmt4->execute();
        $res_comments = $stmt4->get_result();
        while ($c = $res_comments->fetch_assoc()):
        ?>
        <div style="border-top:1px solid #ccc; padding:4px 0;">
            <strong><?php echo htmlspecialchars($c['username']); ?></strong>:
            <?php echo nl2br(htmlspecialchars($c['content'])); ?>
            <span style="font-size:12px;color:#666;"> (<?php echo $c['created_at']; ?>)</span>
        </div>
        <?php endwhile; $stmt4->close(); ?>
    </div>

    <!-- Add comment form -->
    <?php if ($user_id): ?>
    <form method="post" style="margin-top:6px;">
        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
        <textarea name="comment_content" placeholder="Add a comment..." required style="width:100%;padding:6px;"></textarea>
        <button type="submit" name="comment_post" class="btn" style="margin-top:4px;">Comment</button>
    </form>
    <?php endif; ?>
</div>
<?php
endwhile;
$stmt->close();

include __DIR__ . '/../partials/footer.php';
?>
