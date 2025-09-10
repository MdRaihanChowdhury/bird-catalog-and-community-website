<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

if (empty($_SESSION['user_id'])) redirect('../auth/login.php');
$error = '';

$id = $_GET['id'] ?? null;
if (!$id) redirect('index.php');

// Fetch current bird
$stmt = $conn->prepare("SELECT * FROM birds WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$bird = $result->fetch_assoc();
$stmt->close();

if (!$bird) redirect('index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    // Handle image upload
    $imageName = $bird['image']; // keep old if no new
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            $uploadDir = __DIR__ . '/../uploads/birds/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $error = 'Image upload failed.';
            }
        } else {
            $error = 'Only JPG, PNG, GIF allowed.';
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE birds SET name=?, species=?, description=?, image=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssssii", $name, $species, $desc, $imageName, $id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            redirect('index.php');
        } else {
            $error = 'Update failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php if ($error) echo '<div class="alert">'.htmlspecialchars($error).'</div>'; ?>

<div class="card" style="max-width:720px;margin:0 auto">
  <h2>Edit Bird</h2>
  <form method="post" enctype="multipart/form-data">
    <input name="name" value="<?php echo htmlspecialchars($bird['name']); ?>" required>
    <input name="species" value="<?php echo htmlspecialchars($bird['species']); ?>" required>
    <textarea name="description"><?php echo htmlspecialchars($bird['description']); ?></textarea>
    <?php if ($bird['image']): ?>
      <div>
        <img src="../uploads/birds/<?php echo htmlspecialchars($bird['image']); ?>" width="150">
      </div>
    <?php endif; ?>
    <label>Change Image</label>
    <input type="file" name="image">
    <button class="btn" type="submit">Update</button>
  </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
