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
$quantity     = (int) ($_POST['quantity']     ?? 0);

if ($cart_item_id <= 0 || $quantity <= 0) {
    $_SESSION['checkout_error'] = 'Invalid quantity.';
    header("Location: ../check-out/checkout.php");
    exit;
}

// Verify the cart item belongs to this user and get current stock
$stmt = $conn->prepare("
    SELECT ci.cart_item_id, p.stock
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_item_id = ? AND ci.user_id = ?
");
$stmt->bind_param("ii", $cart_item_id, $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['checkout_error'] = 'Cart item not found.';
    header("Location: ../check-out/checkout.php");
    exit;
}

if ($quantity > $row['stock']) {
    $_SESSION['checkout_error'] = 'Quantity exceeds available stock.';
    header("Location: ../check-out/checkout.php");
    exit;
}

$stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND user_id = ?");
$stmt->bind_param("iii", $quantity, $cart_item_id, $user_id);
$updated = $stmt->execute();
$stmt->close();

if ($updated) {
    logActivity($conn, $user_id, 'updated_cart_quantity', 'cart_items', $cart_item_id);
}

$_SESSION['checkout_success'] = 'Quantity updated.';
header("Location: ../check-out/checkout.php");
exit;
