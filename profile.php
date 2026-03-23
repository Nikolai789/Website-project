<?php
session_start();
require_once __DIR__ . "/configurations/config.php";
require_once __DIR__ . "/configurations/authentication.php";

requireLogin();

$user_id = (int) $_SESSION['user_id'];

// Read and clear order success message
$orderSuccess = $_SESSION['order_success'] ?? '';
unset($_SESSION['order_success']);

// Fetch all orders for this user
$stmt = $conn->prepare("
    SELECT order_id, total_amount, status, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch order items for each order
$orderItems = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'order_id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));

    $stmt = $conn->prepare("
        SELECT oi.order_id, p.name, oi.quantity, oi.unit_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id IN ($placeholders)
        ORDER BY oi.item_id ASC
    ");
    $stmt->bind_param($types, ...$orderIds);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as $row) {
        $orderItems[$row['order_id']][] = $row;
    }
}

function formatOrderStatus(string $status): string
{
    return ucfirst($status);
}

function orderStatusClass(string $status): string
{
    return preg_replace('/[^a-z0-9_-]/i', '-', strtolower($status)) ?: 'unknown';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
    <?php include "includes/nav.php"; ?>

    <main class="profile-wrapper">
        <div class="profile-header">
            <h2><span class="user_text">User</span> <span class="profile_text">Profile</span></h2>
            <div class="header-line"></div>
        </div>

        <?php if (!empty($orderSuccess)): ?>
            <p class="msg msg-success"><?= htmlspecialchars($orderSuccess) ?></p>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-banner">
                <div class="banner-grid"></div>
            </div>

            <div class="profile-identity">
                <div class="avatar-wrap">
                    <div class="avatar">
                        <?= strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 2)) ?>
                    </div>
                    <div class="avatar-ring"></div>
                </div>
                <div class="profile-name-block">
                    <div class="profile-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
                    <div class="profile-email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
                </div>
            </div>

            <div class="card-divider"></div>

            <div class="profile-info">
                <div class="info-cell">
                    <div class="info-label">Username</div>
                    <div class="info-value accent"><?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($_SESSION['email'] ?? '-') ?></div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge">
                            <span class="status-dot"></span> Active
                        </span>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                <a href="logout.php" class="btn btn-ghost">Logout</a>
            </div>
        </div>

        <div class="orders-section">
            <div class="profile-header">
                <h2><span class="user_text">Order</span> <span class="profile_text">History</span></h2>
                <div class="header-line"></div>
            </div>

            <?php if (empty($orders)): ?>
                <p class="no-orders">You have no orders yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php
                        $items = $orderItems[$order['order_id']] ?? [];
                        $itemCount = array_sum(array_map(static fn($item) => (int) $item['quantity'], $items));
                        $statusClass = orderStatusClass((string) $order['status']);
                    ?>
                    <article class="receipt-card">
                        <button
                            type="button"
                            class="receipt-header"
                            onclick="toggleReceipt(<?= (int) $order['order_id'] ?>, this)"
                            aria-expanded="false"
                            aria-controls="receipt-<?= (int) $order['order_id'] ?>"
                        >
                            <div class="receipt-meta">
                                <span class="receipt-id">Order</span>
                                <span class="receipt-date">Placed on <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="receipt-summary">
                                <span class="receipt-count"><?= $itemCount ?> item<?= $itemCount === 1 ? '' : 's' ?></span>
                                <span class="receipt-status status-<?= htmlspecialchars($statusClass) ?>">
                                    <?= htmlspecialchars(formatOrderStatus((string) $order['status'])) ?>
                                </span>
                                <span class="receipt-total">PHP <?= number_format((float) $order['total_amount'], 2) ?></span>
                                <span class="receipt-toggle" aria-hidden="true">▼</span>
                            </div>
                        </button>

                        <div class="receipt-body" id="receipt-<?= (int) $order['order_id'] ?>" hidden>
                            <div class="receipt-body-head">
                                <div>
                                    <span class="receipt-body-label">Items</span>
                                    <strong><?= $itemCount ?> total item<?= $itemCount === 1 ? '' : 's' ?></strong>
                                </div>
                                <div>
                                    <span class="receipt-body-label">Order Total</span>
                                    <strong>PHP <?= number_format((float) $order['total_amount'], 2) ?></strong>
                                </div>
                            </div>

                            <table class="receipt-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td><?= (int) $item['quantity'] ?></td>
                                            <td>PHP <?= number_format((float) $item['unit_price'], 2) ?></td>
                                            <td>PHP <?= number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>Total</strong></td>
                                        <td><strong>PHP <?= number_format((float) $order['total_amount'], 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleReceipt(orderId, trigger) {
            const body = document.getElementById('receipt-' + orderId);
            const isOpen = !body.hasAttribute('hidden');

            if (isOpen) {
                body.setAttribute('hidden', '');
                trigger?.setAttribute('aria-expanded', 'false');
                trigger?.classList.remove('is-open');
                return;
            }

            body.removeAttribute('hidden');
            trigger?.setAttribute('aria-expanded', 'true');
            trigger?.classList.add('is-open');
        }
    </script>
</body>
</html>
