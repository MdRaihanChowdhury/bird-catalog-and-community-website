<?php
// config.php
session_start();

// --- DB CONFIG ---
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'bcc';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// --- Helpers ---
function url($path = '') {
    // Adjust base if your project is in a subfolder
    $base = '/bcc/';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function redirect($path) {
    $base = '/bcc/'; // adjust if your project folder name is different
    header("Location: " . $base . ltrim($path, '/'));
    exit;
}


// Simple auth guard
function require_login() {
    if (empty($_SESSION['user_id'])) redirect('auth/login.php');
}
?>
