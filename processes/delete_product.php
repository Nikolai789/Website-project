<?php
require_once __DIR__ . '/../configurations/config.php';
require_once __DIR__ . '/../configurations/authentication.php';
require_once __DIR__ . '/../configurations/activity_logger.php';
require_once __DIR__ . '/../configurations/order_status.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($productId <= 0) {
    $_SESSION['admin_product_error'] = 'Invalid product selected.';
    header('Location: ../admin.php');
    exit;
}

// Keep delivered order history intact while still allowing catalog cleanup.
$stmt = $conn->prepare('
    SELECT o.status, COUNT(*) AS total
    FROM order_items oi
    INNER JOIN orders o ON o.order_id = oi.order_id
    WHERE oi.product_id = ?
    GROUP BY o.status
');
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();
$orderStatusCounts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$hasOrderHistory = !empty($orderStatusCounts);
$hasActiveOrderHistory = false;

foreach ($orderStatusCounts as $row) {
    $status = normalizeOrderStatus((string) ($row['status'] ?? ''));
    if (!in_array($status, completedOrderStatuses(), true)) {
        $hasActiveOrderHistory = true;
        break;
    }
}

if ($hasActiveOrderHistory) {
    $_SESSION['admin_product_error'] = 'Cannot remove this product because it is still part of an active order.';
    header('Location: ../admin.php');
    exit;
}

$conn->begin_transaction();

try {
    // Remove cart/image references first for schemas without ON DELETE CASCADE.
    $stmt = $conn->prepare('DELETE FROM cart_items WHERE product_id = ?');
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $stmt->close();

    if ($hasOrderHistory) {
        setCurrentActivityLogContext($conn, 'archived_product');

        $stmt = $conn->prepare('UPDATE products SET is_archived = 1, stock = 0 WHERE product_id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        if ($affectedRows < 1) {
            $conn->rollback();
            $_SESSION['admin_product_error'] = 'Product was not found or already removed.';
            header('Location: ../admin.php');
            exit;
        }

        $conn->commit();
        $_SESSION['admin_product_success'] = 'Product removed from the catalog and kept for delivered order history.';
    } else {
        $stmt = $conn->prepare('DELETE FROM product_images WHERE product_id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $stmt->close();

        setCurrentActivityLogContext($conn, 'deleted_product');

        $stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $deletedRows = $stmt->affected_rows;
        $stmt->close();

        if ($deletedRows < 1) {
            $conn->rollback();
            $_SESSION['admin_product_error'] = 'Product was not found or already removed.';
            header('Location: ../admin.php');
            exit;
        }

        $conn->commit();
        $_SESSION['admin_product_success'] = 'Product deleted successfully.';
    }
} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    $_SESSION['admin_product_error'] = 'Unable to delete product because related records still exist.';
}

header('Location: ../admin.php');
exit;
