<?php
require_once __DIR__ . "/configurations/config.php";
require_once __DIR__ . "/configurations/activity_logger.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to your cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id    = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity'])   ? (int) $_POST['quantity']   : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

// Check stock
$stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Fetch existing cart item first
$stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

$current_cart_qty = $existing ? $existing['quantity'] : 0;

// Now check if adding more would exceed stock
if (($current_cart_qty + $quantity) > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock']);
    exit;
}

// Update or insert
if ($existing) {
    $new_qty = $existing['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    $stmt->bind_param("ii", $new_qty, $existing['cart_item_id']);
    $cartItemId = (int) $existing['cart_item_id'];
    $logAction = 'increased_cart_quantity';
} else {
    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    $cartItemId = 0;
    $logAction = 'added_to_cart';
}

$saved = $stmt->execute();
if (!$existing) {
    $cartItemId = (int) $stmt->insert_id;
}
$stmt->close();

if ($saved && $cartItemId > 0) {
    logActivity($conn, $user_id, $logAction, 'cart_items', $cartItemId);
}

echo json_encode(['success' => true, 'message' => 'Added to cart!']);
