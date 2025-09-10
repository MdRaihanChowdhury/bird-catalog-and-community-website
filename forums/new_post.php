<?php 
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

// Redirect to login if user is not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: ' . url('auth/login.php'));
    exit;
}

$board = (int)($_GET['board'] ?? $_GET['b'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $body    = trim($_POST['body'] ?? '');
    $bird_id = (int)($_POST['bird_id'] ?? 0);
    $uid     = $_SESSION['user_id'];
    $loc     = trim($_POST['location'] ?? '');
    $img     = trim($_POST['image_url'] ?? ''); // fallback if user pastes link

    // ✅ Handle image upload
    if (!empty($_FILES['image_file']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/birds/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp  = $_FILES['image_file']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['image_file']['name']);
        $target   = $uploadDir . $fileName;

        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['image_file']['type'], $allowed)) {
            if (move_uploaded_file($fileTmp, $target)) {
                $img = $fileName; // store filename in DB
            } else {
                $error = "❌ Failed to upload image.";
            }
        } else {
            $error = "⚠ Only JPG, PNG, GIF, WEBP allowed.";
        }
    }

    if (!$title || !$body) {
        $error = '⚠ Please fill all required fields.';
    } elseif ($board <= 0) {
        $error = '⚠ Invalid forum board.';
    } elseif (!$error) {
        $stmt = $conn->prepare("
            INSERT INTO posts (user_id, forum_id, title, content, location, image_url, bird_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            $error = '❌ Prepare failed: ' . htmlspecialchars($conn->error);
        } else {
            $stmt->bind_param('iissssi', $uid, $board, $title, $body, $loc, $img, $bird_id);

            if ($stmt->execute()) {
                header('Location: ' . url('forums/board.php?id=' . $board));
                exit;
            } else {
                $error = '❌ Failed to create post: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    }
}
?>

<?php if ($error): ?>
    <div class="alert"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card" style="max-width:900px;margin:0 auto">
    <h2>📝 New Post</h2>
    <form method="post" enctype="multipart/form-data">
        <input name="title" placeholder="Post title" required>
        <textarea name="body" placeholder="Write your post..." required></textarea>
        <input name="location" placeholder="Location (optional)">

        <!-- Either URL or file upload -->
        <input name="image_url" placeholder="Image URL (optional)">
        <label>Or upload image:</label>
        <input type="file" name="image_file" accept="image/*">

        <label class="muted">Attach bird (optional):</label>
        <select name="bird_id">
            <option value="0">-- none --</option>
            <?php 
            $birds = $conn->query("SELECT id, name FROM birds ORDER BY name ASC");
            if ($birds) {
                while ($bb = $birds->fetch_assoc()): ?>
                    <option value="<?php echo $bb['id']; ?>">
                        <?php echo htmlspecialchars($bb['name']); ?>
                    </option>
                <?php endwhile; 
            }
            ?>
        </select>

        <button class="btn" type="submit">Publish</button>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
