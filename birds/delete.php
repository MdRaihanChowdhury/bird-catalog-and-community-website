<?php
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['user_id'])) redirect('../auth/login.php');

$id = $_GET['id'] ?? null;
if (!$id) redirect('index.php');

// Delete only if user owns the bird
$stmt = $conn->prepare("DELETE FROM birds WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

redirect('index.php');
