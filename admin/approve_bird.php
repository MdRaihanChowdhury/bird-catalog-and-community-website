<?php
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['user_id']) || $_SESSION['role_id'] > 2) redirect('../index.php');

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id && in_array($action, ['approve','reject'])) {
    if ($action === 'approve') {
        $conn->query("UPDATE birds SET status='approved' WHERE id=$id");
    } else {
        $conn->query("DELETE FROM birds WHERE id=$id"); // reject = delete
    }
}

redirect('birds.php');
