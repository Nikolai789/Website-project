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

setCurrentActivityLogContext($conn, 'deleted_product');

$stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
$stmt->bind_param('i', $productId);
$stmt->execute();
$stmt->close();

header('Location: ../admin.php');
exit;
