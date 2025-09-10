<?php 
require_once __DIR__ . '/../config.php'; 
include __DIR__ . '/../partials/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare('SELECT id, password, username FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Login success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                redirect('index.php');
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter username and password.';
    }
}
?>

<?php if ($error): ?>
<div class="alert"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card" style="max-width:420px;margin:0 auto">
  <h2>Login</h2>
  <form method="post">
    <label>Username</label>
    <input type="text" name="username" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button class="btn" type="submit">Login</button>
  </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
