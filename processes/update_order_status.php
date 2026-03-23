<?php
require_once __DIR__ . "/../configurations/config.php";
require_once __DIR__ . "/../configurations/authentication.php";
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin_orders.php");
    exit;
}

$orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
$status = strtolower(trim($_POST['status'] ?? ''));
$allowedStatuses = ['pending', 'paid', 'shipped', 'delivered'];

if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['admin_order_error'] = 'Invalid order update request.';
    header("Location: ../admin_orders.php");
    exit;
}

$stmt = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderExists = (bool) $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$orderExists) {
    $_SESSION['admin_order_error'] = 'Order not found.';
    header("Location: ../admin_orders.php");
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("si", $status, $orderId);
$stmt->execute();
$stmt->close();

$_SESSION['admin_order_success'] = "Order #{$orderId} status updated to " . ucwords(str_replace('_', ' ', $status)) . '.';
header("Location: ../admin_orders.php");
exit;
