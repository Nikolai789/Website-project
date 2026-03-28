<?php
require_once __DIR__ . "/../configurations/config.php";
require_once __DIR__ . "/../configurations/activity_logger.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../check-out/checkout.php");
    exit;
}

$user_id      = (int) $_SESSION['user_id'];
$cart_item_id = (int) ($_POST['cart_item_id'] ?? 0);

if ($cart_item_id <= 0) {
    $_SESSION['checkout_error'] = 'Invalid item.';
    header("Location: ../check-out/checkout.php");
    exit;
}

setActivityLogContext($conn, $user_id, 'removed_cart_item');

// Delete only if the item belongs to this user
$stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_item_id, $user_id);
$stmt->execute();
$stmt->close();

// If cart is now empty, send them back to the store
$stmt = $conn->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count === 0) {
    $_SESSION['cart_notice'] = 'Your cart is empty. Add some products first!';
    header("Location: ../index.php");
    exit;
}

$_SESSION['checkout_success'] = 'Item removed.';
header("Location: ../check-out/checkout.php");
exit;
