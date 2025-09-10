<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

// Fetch approved birds only
$result = $conn->query("
    SELECT id, user_id, name, species, description, image, status, created_at 
    FROM birds 
    WHERE status='approved'
    ORDER BY created_at DESC
");
?>

<h2>All Birds</h2>

<?php if (!empty($_SESSION['user_id'])): ?>
    <a href="create.php" class="btn">➕ Add Bird</a>
<?php else: ?>
    <p class="muted">Login to add a new bird.</p>
<?php endif; ?>

<div class="grid" style="margin-top:12px;">
<?php while ($row = $result->fetch_assoc()): ?>
  <div class="card" style="padding:12px;">
    <?php if (!empty($row['image'])): ?>
      <img src="<?php echo url('uploads/birds/' . $row['image']); ?>" 
           alt="<?php echo htmlspecialchars($row['name']); ?>" 
           style="width:100%; height:auto; border-radius:6px;">
    <?php endif; ?>
    
    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
    <p><em><?php echo htmlspecialchars($row['species']); ?></em></p>

    <p class="muted" style="font-size:12px;">Added on <?php echo $row['created_at']; ?></p>

    <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
      <div style="margin-top:8px;">
        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn">✏ Edit</a>
        <a href="delete.php?id=<?php echo $row['id']; ?>" 
           class="btn" 
           onclick="return confirm('Are you sure you want to delete this bird?')">🗑 Delete</a>
      </div>
    <?php endif; ?>
  </div>
<?php endwhile; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
