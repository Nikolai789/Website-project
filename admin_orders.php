<?php
require_once __DIR__ . "/configurations/config.php";
require_once __DIR__ . "/configurations/authentication.php";
requireAdmin();

$allowedStatuses = ['pending', 'paid', 'shipped', 'delivered'];
$statusFilter = $_GET['status'] ?? '';
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$customerSearch = trim($_GET['q'] ?? '');
if ($customerSearch !== '') {
    $customerSearch = mb_substr($customerSearch, 0, 100);
}

$flashSuccess = $_SESSION['admin_order_success'] ?? '';
$flashError = $_SESSION['admin_order_error'] ?? '';
unset($_SESSION['admin_order_success'], $_SESSION['admin_order_error']);

$query = "
    SELECT
        o.order_id,
        o.user_id,
        o.total_amount,
        o.status,
        o.created_at,
        u.username,
        u.email,
        u.address
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
";

$conditions = [];
$params = [];
$types = '';

if ($statusFilter !== '') {
    $conditions[] = "o.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($customerSearch !== '') {
    $conditions[] = "(u.username LIKE ? OR u.email LIKE ?)";
    $searchTerm = '%' . $customerSearch . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY o.created_at DESC';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$orderItems = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'order_id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));

    $stmt = $conn->prepare("
        SELECT
            oi.order_id,
            p.name,
            oi.quantity,
            oi.unit_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id IN ($placeholders)
        ORDER BY oi.item_id ASC
    ");
    $stmt->bind_param($types, ...$orderIds);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($items as $item) {
        $orderItems[$item['order_id']][] = $item;
    }
}

function formatStatusLabel(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

function statusClassName(string $status): string
{
    return preg_replace('/[^a-z0-9_-]/i', '-', strtolower($status)) ?: 'unknown';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <header class="admin-header">
            <div class="admin-title">Admin Panel</div>

            <nav class="admin-nav">
                <a href="admin.php" class="tab">Products</a>
                <a href="admin_orders.php" class="tab active">Orders</a>
                <a href="admin_logs.php" class="tab">Logs</a>
            </nav>

            <a href="logout.php" class="admin-logout">logout</a>
        </header>

        <main class="admin-main">
            <?php if ($flashSuccess !== ''): ?>
                <p class="admin-flash admin-flash-success"><?= htmlspecialchars($flashSuccess) ?></p>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <p class="admin-flash admin-flash-error"><?= htmlspecialchars($flashError) ?></p>
            <?php endif; ?>

            <section class="toolbar orders-toolbar">
                <div>
                    <h1 class="section-title">Customer Orders</h1>
                    <p class="section-subtitle">Review each order, confirm the buyer details, and update its order status.</p>
                </div>
            </section>

            <section class="filters">
                <div class="filters-left">
                    <span class="filters-label">filter</span>
                    <form method="get" class="filters-form orders-filter-form" id="orders-filter-form">
                        <select name="status" class="filter-select" onchange="this.form.submit()">
                            <option value="">All statuses</option>
                            <?php foreach ($allowedStatuses as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(formatStatusLabel($status)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="filters-right">
                    <input
                        type="text"
                        form="orders-filter-form"
                        name="q"
                        class="search-input"
                        placeholder="search customer"
                        value="<?= htmlspecialchars($customerSearch) ?>"
                        autocomplete="off"
                    >
                    <button type="submit" form="orders-filter-form" class="secondary-btn">Apply</button>
                </div>
            </section>

            <?php if (empty($orders)): ?>
                <section class="empty-state">
                    <h2>No orders found</h2>
                    <p>There are no customer orders matching the current filters.</p>
                </section>
            <?php else: ?>
                <section class="orders-grid">
                    <?php foreach ($orders as $order): ?>
                        <?php $statusClass = statusClassName((string) $order['status']); ?>
                        <article class="order-card">
                            <div class="order-card-header">
                                <div>
                                    <div class="order-kicker">Order #<?= (int) $order['order_id'] ?></div>
                                    <h2 class="order-customer-name"><?= htmlspecialchars($order['username']) ?></h2>
                                    <p class="order-meta-line">
                                        <?= htmlspecialchars($order['email']) ?>
                                    </p>
                                </div>

                                <div class="order-header-right">
                                    <span class="order-status-badge status-<?= htmlspecialchars($statusClass) ?>">
                                        <?= htmlspecialchars(formatStatusLabel((string) $order['status'])) ?>
                                    </span>
                                    <span class="order-total">PHP <?= number_format((float) $order['total_amount'], 2) ?></span>
                                </div>
                            </div>

                            <div class="order-info-grid">
                                <div class="order-info-box">
                                    <span class="order-info-label">Customer ID</span>
                                    <strong><?= (int) $order['user_id'] ?></strong>
                                </div>
                                <div class="order-info-box">
                                    <span class="order-info-label">Placed On</span>
                                    <strong><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></strong>
                                </div>
                                <div class="order-info-box order-info-box-wide">
                                    <span class="order-info-label">Shipping Address</span>
                                    <strong><?= htmlspecialchars($order['address'] ?: 'No address provided') ?></strong>
                                </div>
                            </div>

                            <div class="order-items-block">
                                <div class="order-items-title">Items Ordered</div>
                                <table class="products-table orders-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems[$order['order_id']] ?? [] as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['name']) ?></td>
                                                <td><?= (int) $item['quantity'] ?></td>
                                                <td>PHP <?= number_format((float) $item['unit_price'], 2) ?></td>
                                                <td>PHP <?= number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <form class="order-status-form" action="processes/update_order_status.php" method="post">
                                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">

                                <label class="order-status-label" for="status-<?= (int) $order['order_id'] ?>">Update Status</label>
                                <div class="order-status-actions">
                                    <select
                                        name="status"
                                        id="status-<?= (int) $order['order_id'] ?>"
                                        class="filter-select order-status-select"
                                    >
                                        <?php foreach ($allowedStatuses as $status): ?>
                                            <option value="<?= htmlspecialchars($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(formatStatusLabel($status)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" class="primary-btn">Save Status</button>
                                </div>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
