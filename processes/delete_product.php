<?php
require_once __DIR__ . '/../configurations/config.php';
require_once __DIR__ . '/../configurations/authentication.php';
require_once __DIR__ . '/../configurations/activity_logger.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($productId <= 0) {
    header('Location: ../admin.php');
    exit;
}

$stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
$stmt->bind_param('i', $productId);
$deleted = $stmt->execute();
$affectedRows = $stmt->affected_rows;
$stmt->close();

if ($deleted && $affectedRows > 0) {
    logCurrentUserActivity($conn, 'deleted_product', 'products', $productId);
}

header('Location: ../admin.php');
exit;
