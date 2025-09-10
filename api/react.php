<?php
require_once __DIR__ . '/../config.php';

// Make sure user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);
$action  = $_POST['action'] ?? '';

if (!$post_id || !in_array($action, ['like', 'unlike'])) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// Check if the like already exists
$stmt = $conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$stmt->store_result();
$exists = $stmt->num_rows > 0;
$stmt->close();

if ($action === 'like' && !$exists) {
    $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $status = 'liked';
} elseif ($action === 'unlike' && $exists) {
    $stmt = $conn->prepare("DELETE FROM likes WHERE post_id=? AND user_id=?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $status = 'unliked';
} else {
    $status = 'no_change';
}

// Return updated like count
$result = $conn->query("SELECT COUNT(*) as count FROM likes WHERE post_id=$post_id");
$count = $result->fetch_assoc()['count'];

echo json_encode(['status' => $status, 'likes' => (int)$count]);
