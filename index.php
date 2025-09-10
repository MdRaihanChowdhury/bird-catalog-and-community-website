<?php
require_once __DIR__ . '/config.php';
include __DIR__ . '/partials/header.php';

$search = trim($_GET['search'] ?? '');

$user_id = $_SESSION['user_id'] ?? 0;
$role_id = 3; // default 'user'
if ($user_id) {
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    $stmt->close();
}
?>

<!-- ✅ Search Form (always visible) -->
<form method="get" style="max-width:600px;margin:20px auto;text-align:center;">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
           placeholder="Search birds by name..." 
           style="width:70%;padding:8px;border-radius:8px;border:1px solid #ccc;">
    <button type="submit" class="btn">Search</button>
</form>

<?php
// ------------------- Admin/Moderator Dashboard -------------------
if ($role_id == 1): 
    // Handle approve/reject actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($id && ($action === 'approve' || $action === 'reject')) {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $conn->prepare("UPDATE birds SET status=? WHERE id=?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    $pending = $conn->query("SELECT id, name, image, user_id, created_at 
                              FROM birds 
                              WHERE status='pending' 
                              ORDER BY created_at ASC");
    ?>

    <h2>Admin Dashboard - Pending Birds</h2>

    <?php if ($pending && $pending->num_rows > 0): ?>
    <table class="card">
    <thead>
    <tr><th>Image</th><th>Name</th><th>Owner</th><th>Submitted</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php while($row = $pending->fetch_assoc()): ?>
    <tr>
    <td>
        <?php if(!empty($row['image'])): ?>
            <img src="<?php echo url('uploads/birds/' . $row['image']); ?>" 
                 style="width:80px;height:50px;object-fit:cover">
        <?php endif; ?>
    </td>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td>
    <?php
    $stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
    $stmt->bind_param("i", $row['user_id']);
    $stmt->execute();
    $stmt->bind_result($owner);
    $stmt->fetch();
    $stmt->close();
    echo htmlspecialchars($owner);
    ?>
    </td>
    <td><?php echo $row['created_at']; ?></td>
    <td>
    <form method="post" style="display:inline-block">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <button type="submit" name="action" value="approve" class="btn">✅ Approve</button>
        <button type="submit" name="action" value="reject" class="btn" style="background:#d9534f">❌ Reject</button>
    </form>
    </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
    </table>
    <?php else: ?>
    <div class="alert">No pending birds.</div>
    <?php endif; ?>

<?php endif; // close admin dashboard ?>

<?php
// ------------------- Approved Birds Listing (visible for all roles) -------------------
if ($search) {
    $stmt = $conn->prepare("SELECT id, name, image, created_at 
                            FROM birds 
                            WHERE status='approved' 
                              AND name LIKE CONCAT('%', ?, '%') 
                            ORDER BY created_at DESC");
    $stmt->bind_param("s", $search);
} else {
    $stmt = $conn->prepare("SELECT id, name, image, created_at 
                            FROM birds 
                            WHERE status='approved' 
                            ORDER BY created_at DESC LIMIT 50");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h2><?php echo $search ? "Search Results" : "Recent Birds"; ?></h2>

<div class="grid">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="tile">
        <a href="<?php echo url('birds/view.php?id=' . $row['id']); ?>">
            <?php if(!empty($row['image'])): ?>
                <img src="<?php echo url('uploads/birds/' . $row['image']); ?>" 
                     alt="<?php echo htmlspecialchars($row['name']); ?>">
            <?php endif; ?>
            <div class="p">
                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
            </div>
        </a>
    </div>
<?php endwhile; ?>
</div>

<?php
$stmt->close();
include __DIR__ . '/partials/footer.php';
?>
