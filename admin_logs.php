<?php
require_once __DIR__ . "/configurations/config.php";
require_once __DIR__ . "/configurations/authentication.php";
require_once __DIR__ . "/configurations/activity_logger.php";
requireAdmin();

$availableTables = ['products', 'orders', 'users', 'cart_items'];
$tableFilter = $_GET['table'] ?? '';
if (!in_array($tableFilter, $availableTables, true)) {
    $tableFilter = '';
}

$userSearch = trim($_GET['user'] ?? '');
if ($userSearch !== '') {
    $userSearch = function_exists('mb_substr') ? mb_substr($userSearch, 0, 100) : substr($userSearch, 0, 100);
}

$actionSearch = trim($_GET['action'] ?? '');
if ($actionSearch !== '') {
    $actionSearch = function_exists('mb_substr') ? mb_substr($actionSearch, 0, 100) : substr($actionSearch, 0, 100);
}

$logsTableExists = activityLogsTableExists($conn);

$logs = [];

if ($logsTableExists) {
    $query = "
        SELECT
            l.log_id,
            l.user_id,
            l.action,
            l.table_name,
            l.record_id,
            l.logged_at,
            u.username,
            u.email
        FROM activity_logs l
        LEFT JOIN users u ON l.user_id = u.user_id
    ";

    $conditions = [];
    $params = [];
    $types = '';

    if ($tableFilter !== '') {
        $conditions[] = "l.table_name = ?";
        $params[] = $tableFilter;
        $types .= 's';
    }

    if ($actionSearch !== '') {
        $conditions[] = "l.action LIKE ?";
        $params[] = '%' . $actionSearch . '%';
        $types .= 's';
    }

    if ($userSearch !== '') {
        $conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR CAST(l.user_id AS CHAR) LIKE ?)";
        $searchTerm = '%' . $userSearch . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    if (!empty($conditions)) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $query .= ' ORDER BY l.logged_at DESC, l.log_id DESC LIMIT 250';

    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

function formatLogActionLabel(string $action): string
{
    return ucwords(str_replace('_', ' ', $action));
}

function formatLogTableLabel(string $tableName): string
{
    return ucwords(str_replace('_', ' ', $tableName));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logs</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <header class="admin-header">
            <div class="admin-title">Admin Panel</div>

            <nav class="admin-nav">
                <a href="admin.php" class="tab">Products</a>
                <a href="admin_orders.php" class="tab">Orders</a>
                <a href="admin_logs.php" class="tab active">Logs</a>
            </nav>

            <a href="logout.php" class="admin-logout">logout</a>
        </header>

        <main class="admin-main">
            <section class="toolbar orders-toolbar">
                <div>
                    <h1 class="section-title">Activity Logs</h1>
                    <p class="section-subtitle">Track product updates, order changes, authentication events, cart actions, and profile activity.</p>
                </div>
            </section>

            <?php if (!$logsTableExists): ?>
                <section class="empty-state">
                    <h2>Logs table not found</h2>
                    <p>Create the <code>activity_logs</code> table in your database to start recording activity.</p>
                </section>
            <?php else: ?>
                <section class="filters">
                    <div class="filters-left">
                        <span class="filters-label">filter</span>
                        <form method="get" class="filters-form logs-filter-form" id="logs-filter-form">
                            <select name="table" class="filter-select">
                                <option value="">All tables</option>
                                <?php foreach ($availableTables as $tableName): ?>
                                    <option value="<?= htmlspecialchars($tableName) ?>" <?= $tableFilter === $tableName ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(formatLogTableLabel($tableName)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>

                    <div class="filters-right logs-filters-right">
                        <input
                            type="text"
                            form="logs-filter-form"
                            name="action"
                            class="search-input"
                            placeholder="search action"
                            value="<?= htmlspecialchars($actionSearch) ?>"
                            autocomplete="off"
                        >
                        <input
                            type="text"
                            form="logs-filter-form"
                            name="user"
                            class="search-input"
                            placeholder="search user or id"
                            value="<?= htmlspecialchars($userSearch) ?>"
                            autocomplete="off"
                        >
                        <button type="submit" form="logs-filter-form" class="secondary-btn">Apply</button>
                    </div>
                </section>

                <?php if (empty($logs)): ?>
                    <section class="empty-state">
                        <h2>No logs found</h2>
                        <p>No activity entries match the current filters yet.</p>
                    </section>
                <?php else: ?>
                    <section class="table-wrapper">
                        <table class="products-table logs-table">
                            <thead>
                                <tr>
                                    <th>log id</th>
                                    <th>user</th>
                                    <th>user id</th>
                                    <th>action</th>
                                    <th>table</th>
                                    <th>record id</th>
                                    <th>logged at</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>#<?= (int) $log['log_id'] ?></td>
                                        <td>
                                            <div class="log-user">
                                                <strong class="log-user-name">
                                                    <?php if (!empty($log['username'])): ?>
                                                        <?= htmlspecialchars($log['username']) ?>
                                                    <?php elseif (!empty($log['user_id'])): ?>
                                                        User #<?= (int) $log['user_id'] ?>
                                                    <?php else: ?>
                                                        Guest/System
                                                    <?php endif; ?>
                                                </strong>
                                                <span class="log-user-meta">
                                                    <?php if (!empty($log['email'])): ?>
                                                        <?= htmlspecialchars($log['email']) ?>
                                                    <?php elseif (!empty($log['user_id'])): ?>
                                                        User ID <?= (int) $log['user_id'] ?>
                                                    <?php else: ?>
                                                        No linked account
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td><?= $log['user_id'] !== null ? (int) $log['user_id'] : 'N/A' ?></td>
                                        <td><span class="log-pill"><?= htmlspecialchars(formatLogActionLabel((string) $log['action'])) ?></span></td>
                                        <td><span class="log-pill log-pill-table"><?= htmlspecialchars(formatLogTableLabel((string) $log['table_name'])) ?></span></td>
                                        <td><?= $log['record_id'] !== null ? (int) $log['record_id'] : 'N/A' ?></td>
                                        <td><?= date('F j, Y g:i A', strtotime($log['logged_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
