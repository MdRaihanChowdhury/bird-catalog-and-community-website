<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

// Validate ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "<p>Invalid bird.</p>";
    include __DIR__ . '/../partials/footer.php';
    exit;
}

// Fetch bird by ID
$stmt = $conn->prepare("SELECT name, species, description, image, created_at FROM birds WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$bird = $result->fetch_assoc();
$stmt->close();

if (!$bird): ?>
  <p>Bird not found.</p>
<?php else: ?>
  <h2><?php echo htmlspecialchars($bird['name']); ?></h2>
  <?php if (!empty($bird['image'])): ?>
    <img src="<?php echo url('uploads/birds/' . $bird['image']); ?>" 
         alt="<?php echo htmlspecialchars($bird['name']); ?>" 
         style="max-width:400px; border-radius:8px;">
  <?php endif; ?>

  <p><strong>Species:</strong> <?php echo htmlspecialchars($bird['species']); ?></p>
  <p><?php echo nl2br(htmlspecialchars($bird['description'])); ?></p>
  <p class="muted">Added on <?php echo $bird['created_at']; ?></p>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
