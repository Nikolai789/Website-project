<?php
require_once __DIR__ . "/configurations/config.php";

$productsResult = $conn->query("SELECT name, category, stock, price, description FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <header class="admin-header">
            <div class="admin-title">Admin Panel</div>

            <nav class="admin-nav">
                <a href="#" class="tab active">Products</a>
                <a href="#" class="tab">Dashboard</a>
            </nav>

            <a href="logout.php" class="admin-logout">logout</a>
        </header>

        <main class="admin-main">
            <section class="toolbar">
                <button class="primary-btn">Add new product</button>
            </section>

            <section class="filters">
                <div class="filters-left">
                    <span class="filters-label">sort</span>
                    <button class="filter-chip">category (dropdown)</button>
                    <button class="filter-chip">stock (sorts by stock)</button>
                </div>
                <div class="filters-right">
                    <button class="filter-chip wide">search product</button>
                </div>
            </section>

            <section class="table-wrapper">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>product name</th>
                            <th>category</th>
                            <th>stock</th>
                            <th>price</th>
                            <th>product description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($productsResult && $productsResult->num_rows > 0): ?>
                            <?php while ($row = $productsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= (int) $row['stock'] ?></td>
                                    <td>₱<?= number_format($row['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #555;">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>