<?php require_once __DIR__ . '/../config.php'; include __DIR__ . '/../partials/header.php'; ?>
<div class="card"><h2>Forum Boards</h2><p class="muted">Choose a board</p></div>
<?php
$res = $conn->query('SELECT id, forum_name, description FROM forum_boards ORDER BY id');
if ($res && $res->num_rows): ?>
  <table class="card"><thead><tr><th>Board</th><th>Description</th><th>Action</th></tr></thead><tbody>
  <?php while($r=$res->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['forum_name']); ?></td>
      <td class="muted"><?php echo htmlspecialchars($r['description']); ?></td>
      <td><a class="btn" href="<?php echo url('forums/board.php?id='.$r['id']); ?>">Open</a></td>
    </tr>
  <?php endwhile; ?></tbody></table>
<?php else: ?><div class="alert">No boards yet.</div><?php endif; ?>
<?php include __DIR__ . '/../partials/footer.php'; ?>
