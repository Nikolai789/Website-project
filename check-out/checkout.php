<?php
require_once __DIR__ . "/../configurations/config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT username, email, address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch cart items
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

// Empty cart — redirect to index with a message
if (empty($items)) {
    $_SESSION['cart_notice'] = 'Your cart is empty. Add some products first!';
    header("Location: ../index.php");
    exit;
}

$total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
$hasAddress = !empty(trim((string) ($user['address'] ?? '')));

// Read and clear session messages
$error   = $_SESSION['checkout_error']   ?? '';
$success = $_SESSION['checkout_success'] ?? '';
unset($_SESSION['checkout_error'], $_SESSION['checkout_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Periph</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/checkout.css">
</head>
<body>
    <?php include "../includes/nav.php" ?>

    <main>
        <div class="container">
            <div class="box">
                <h1>Checkout</h1>
            </div>

            <?php if (!empty($error)): ?>
                <p class="msg msg-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="msg msg-success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <div class="checkout-wrapper">
                <div class="box">
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>

                                    <!-- Quantity update form -->
                                    <td>
                                        <form action="../processes/update_cart_item.php" method="POST" class="inline-form">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                            <input
                                                type="number"
                                                name="quantity"
                                                value="<?= $item['quantity'] ?>"
                                                min="1"
                                                max="<?= $item['stock'] ?>"
                                                class="qty-input"
                                            >
                                            <button type="submit" class="btn-update">Update</button>
                                        </form>
                                    </td>

                                    <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>

                                    <!-- Remove form -->
                                    <td>
                                        <form action="../processes/remove_cart_item.php" method="POST" class="inline-form">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                            <button type="submit" class="btn-remove">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>Total</strong></td>
                                    <td><strong>₱<?= number_format($total, 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="checkout-form">
                    <div class="box">
                        <h2>Delivery Details</h2>
                        <form action="../processes/process_checkout.php" method="POST">
                            <label>Name</label>
                            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>

                            <label>Email</label>
                            <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled>

                            <label>Shipping Address</label>
                            <?php if ($hasAddress): ?>
                                <input type="text" value="<?= htmlspecialchars($user['address']) ?>" disabled>
                            <?php else: ?>
                                <input type="text" value="No address on file" disabled style="color: red;">
                                <small>Please <a href="../edit_profile.php">update your profile</a> to add a shipping address before placing an order.</small>
                            <?php endif; ?>

                            <label>Payment Method</label>
                            <input type="text" value="Cash on Delivery" disabled>

                            <button type="submit" <?= $hasAddress ? '' : 'disabled title="Add your shipping address first"' ?>>
                                <?= $hasAddress ? 'Place Order' : 'Add Address to Continue' ?>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include "../includes/footer.php" ?>
</body>
</html>
