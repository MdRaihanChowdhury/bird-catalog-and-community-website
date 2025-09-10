<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

// check admin/moderator
if (empty($_SESSION['user_id']) || $_SESSION['role_id'] > 2) redirect('../index.php');

$res = $conn->query("SELECT * FROM birds WHERE status='pending' ORDER BY created_at DESC");
?>

<h2>Pending Bird Approvals</h2>

<?php while($b = $res->fetch_assoc()): ?>
<div class="card">
  <h3><?php echo htmlspecialchars($b['name']); ?></h3>
  <?php if($b['image']): ?>
    <img src="<?php echo url('uploads/birds/' . $b['image']); ?>" style="max-width:200px;">
  <?php endif; ?>
  <p><?php echo htmlspecialchars($b['species']); ?></p>
  <p><?php echo nl2br(htmlspecialchars($b['description'])); ?></p>
  <a href="approve_bird.php?id=<?php echo $b['id']; ?>&action=approve" class="btn">✅ Approve</a>
  <a href="approve_bird.php?id=<?php echo $b['id']; ?>&action=reject" class="btn">❌ Reject</a>
</div>
<?php endwhile; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
