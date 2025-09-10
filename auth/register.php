<?php require_once __DIR__ . '/../config.php'; include __DIR__ . '/../partials/header.php';
$error = ''; $ok='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (!$username || !$email || !$password) $error = 'All fields required.';
    else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO users (username,email,password,role_id) VALUES (?,?,?,3)');
        $stmt->bind_param('sss', $username, $email, $hash);
        if ($stmt->execute()) {
            $ok = 'Account created. You can log in now.';
        } else {
            $error = 'Could not create account. Username or email may be taken.';
        }
    }
}
?>
<?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($ok): ?><div class="card"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>
<div class="card" style="max-width:480px;margin:0 auto">
  <h2>Register</h2>
  <form method="post">
    <label>Username</label>
    <input name="username" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button class="btn" type="submit">Register</button>
  </form>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
