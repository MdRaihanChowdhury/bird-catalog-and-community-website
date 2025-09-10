<?php require_once __DIR__ . '/../config.php'; include __DIR__ . '/../partials/header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT p.*, u.username, f.forum_name FROM posts p LEFT JOIN users u ON u.id=p.user_id LEFT JOIN forum_boards f ON f.id=p.forum_id WHERE p.id=?');
$stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result(); $post = $res->fetch_assoc();
if (!$post) { echo '<div class="alert">Post not found.</div>'; include __DIR__ . '/../partials/footer.php'; exit; }
?>
<div class="card"><h2><?php echo htmlspecialchars($post['title']); ?></h2>
  <p class="muted">by <?php echo htmlspecialchars($post['username'] ?? 'Anon'); ?> in <?php echo htmlspecialchars($post['forum_name']); ?> — <?php echo htmlspecialchars($post['created_at']); ?></p>
  <?php if (!empty($post['image_url'])): ?><img src="<?php echo htmlspecialchars($post['image_url']); ?>" style="max-width:100%;border-radius:8px;margin:10px 0"><?php endif; ?>
  <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
  <div style="margin-top:12px">
    <form method="post" action="<?php echo url('api/react.php'); ?>" style="display:inline-block">
      <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
      <input type="hidden" name="type" value="like">
      <button class="btn" type="submit">👍 Like</button>
    </form>
    <a class="btn" href="<?php echo url('forums/board.php?id='.$post['forum_id']); ?>">Back</a>
  </div>
</div>
<!-- Comments -->
<div class="card" style="margin-top:12px"><h3>Comments</h3>
  <?php
  $cs = $conn->prepare('SELECT c.*, u.username FROM comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.post_id=? ORDER BY c.id ASC');
  $cs->bind_param('i',$id); $cs->execute(); $cres = $cs->get_result();
  if ($cres && $cres->num_rows): while($c=$cres->fetch_assoc()): ?>
    <div style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.06)"><strong><?php echo htmlspecialchars($c['username']?:'Anon'); ?></strong> <span class="muted" style="font-size:12px"><?php echo htmlspecialchars($c['created_at']); ?></span>
      <p><?php echo nl2br(htmlspecialchars($c['content'])); ?></p></div>
  <?php endwhile; else: ?><div class="muted">No comments yet.</div><?php endif; ?>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <form method="post" action="<?php echo url('api/comment.php'); ?>">
      <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
      <textarea name="content" placeholder="Write a comment..." required></textarea>
      <button class="btn" type="submit">Comment</button>
    </form>
  <?php else: ?><p class="muted">Login to comment.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
