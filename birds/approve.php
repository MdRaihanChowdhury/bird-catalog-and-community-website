<?php
require_once __DIR__ . '/../config.php';

// Check if user is admin or moderator
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','moderator'])) {
    redirect('../index.php');
}

$bird_id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($bird_id && $action === 'approve') {
    $stmt = $conn->prepare("UPDATE birds SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $bird_id);
    $stmt->execute();
    $stmt->close();
    redirect('index.php');
}

// Fetch all pending birds
$birds = $conn->query("SELECT * FROM birds WHERE status='pending'");
?>
<div class="card">
<h2>Pending Birds</h2>
<?php while($b = $birds->fetch_assoc()): ?>
  <div class="tile">
    <h3><?php echo htmlspecialchars($b['name']); ?></h3>
    <a href="?id=<?php echo $b['id']; ?>&action=approve" class="btn">Approve</a>
  </div>
<?php endwhile; ?>

