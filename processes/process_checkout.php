<?php
require_once __DIR__ . "/../configurations/config.php";
require_once __DIR__ . "/../configurations/authentication.php";
session_start();

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../check-out/checkout.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch cart items with current price and stock
$stmt = $conn->prepare("
    SELECT ci.cart_item_id, ci.quantity, p.product_id, p.name, p.price, p.stock
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) {
    $_SESSION['cart_notice'] = 'Your cart is empty. Add some products first!';
    header("Location: ../index.php");
    exit;
}

// Validate stock for all items before touching the DB
foreach ($items as $item) {
    if ($item['quantity'] > $item['stock']) {
        $_SESSION['checkout_error'] = "'{$item['name']}' does not have enough stock.";
        header("Location: ../check-out/checkout.php");
        exit;
    }
}

$total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));

// Use a transaction so either everything saves or nothing does
$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, status)  
        VALUES (?, ?, 'pending')
    ");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();

    // Clear the cart
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['checkout_error'] = 'Something went wrong. Please try again.';
    header("Location: ../check-out/checkout.php");
    exit;
}

$_SESSION['order_success'] = 'Your order has been placed successfully!';
header("Location: ../profile.php");
exit;