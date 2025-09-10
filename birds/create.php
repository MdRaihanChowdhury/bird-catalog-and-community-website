<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

if (empty($_SESSION['user_id'])) redirect('../auth/login.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif'];
        $uploadDir = __DIR__ . '/../uploads/birds/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $imageName;

        if (!in_array($_FILES['image']['type'], $allowed)) {
            $error = 'Only JPG, PNG, GIF allowed.';
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $error = 'Image upload failed.';
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO birds (user_id, name, species, description, image, status) VALUES (?,?,?,?,?,?)");
        $status = 'pending';
        $stmt->bind_param("isssss", $user_id, $name, $species, $desc, $imageName, $status);

        if ($stmt->execute()) {
            $stmt->close();
            echo '<div class="alert">Bird submitted! Awaiting approval.</div>';
        } else {
            $error = 'Insert failed: ' . $stmt->error;
        }
    }
}
?>

<?php if ($error) echo '<div class="alert">'.htmlspecialchars($error).'</div>'; ?>

<div class="card" style="max-width:720px;margin:0 auto">
  <h2>Add Bird</h2>
  <form method="post" enctype="multipart/form-data">
    <input name="name" placeholder="Common Name" required>
    <input name="species" placeholder="Species" required>
    <textarea name="description" placeholder="Description"></textarea>
    <label>Upload Image</label>
    <input type="file" name="image">
    <button class="btn" type="submit">Save</button>
  </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
