<?php require_once __DIR__ . '/../config.php';
require_login();
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if ($post_id && $content) {
  $ins = $conn->prepare('INSERT INTO comments (post_id,user_id,content) VALUES (?,?,?)');
  $ins->bind_param('iis', $post_id, $_SESSION['user_id'], $content);
  $ins->execute();
}
redirect('forums/show.php?id='.$post_id);
