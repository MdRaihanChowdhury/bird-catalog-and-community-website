<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../partials/header.php';

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) redirect('../auth/login.php');

// Fetch all birds of this user
$result = $conn->query("
    SELECT id, name, image, status, created_at
    FROM birds
    WHERE user_id = $user_id
    ORDER BY created_at DESC
");
?>

<h2>My Birds</h2>

<?php if ($result && $result->num_rows > 0): ?>
<div class="grid">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="tile">
        <a href="<?php echo url('birds/view.php?id=' . $row['id']); ?>">
            <?php if (!empty($row['image'])): ?>
                <img src="<?php echo url('uploads/birds/' . $row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
            <?php endif; ?>
            <div class="p">
                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                <small>
                    <?php 
                        echo $row['status'] ?? 'pending'; 
                        echo ' • Added: ' . $row['created_at'];
                    ?>
                </small>
            </div>
        </a>
        <div style="margin-top:6px;">
            <a href="<?php echo url('birds/edit.php?id=' . $row['id']); ?>" class="btn">✏ Edit</a>
            <a href="<?php echo url('birds/delete.php?id=' . $row['id']); ?>" 
               class="btn" style="background:#d9534f"
               onclick="return confirm('Are you sure you want to delete this bird?')">🗑 Delete</a>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php else: ?>
    <div class="alert">You have not added any birds yet.</div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
