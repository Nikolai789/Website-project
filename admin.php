<?php
require_once __DIR__ . "/configurations/config.php";

$allowedCategories = ['Keyboard', 'Mouse', 'Headphone'];
$selectedCategory = $_GET['category'] ?? '';
if (!in_array($selectedCategory, $allowedCategories, true)) {
    $selectedCategory = '';
}

$allowedStockSort = ['asc', 'desc'];
$stockSort = $_GET['stock_sort'] ?? '';
if (!in_array($stockSort, $allowedStockSort, true)) {
    $stockSort = '';
}

$allowedPriceSort = ['asc', 'desc'];
$priceSort = $_GET['price_sort'] ?? '';
if (!in_array($priceSort, $allowedPriceSort, true)) {
    $priceSort = '';
}

$orderBy = '';
if ($priceSort !== '') {
    $orderBy = $priceSort === 'asc' ? ' ORDER BY price ASC' : ' ORDER BY price DESC';
} elseif ($stockSort !== '') {
    $orderBy = $stockSort === 'asc' ? ' ORDER BY stock ASC' : ' ORDER BY stock DESC';
}

$searchQuery = trim($_GET['q'] ?? '');
if ($searchQuery !== '') {
    $searchQuery = mb_substr($searchQuery, 0, 100);
}

$conditions = [];
if ($selectedCategory !== '') {
    $escapedCategory = $conn->real_escape_string($selectedCategory);
    $conditions[] = "category = '$escapedCategory'";
}

if ($searchQuery !== '') {
    $escapedSearch = $conn->real_escape_string($searchQuery);
    $conditions[] = "name LIKE '%$escapedSearch%'";
}

$whereSql = count($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';
$productsResult = $conn->query("SELECT name, category, stock, price, description FROM products{$whereSql}{$orderBy}");
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
                <button class="primary-btn" id="open-add-product">Add new product</button>
            </section>

            <section class="filters">
                <div class="filters-left">
                    <span class="filters-label">sort</span>
                    <form method="get" class="filters-form" id="filters-form">
                        <select name="category" class="filter-select" onchange="this.form.submit()">
                            <option value="">All categories</option>
                            <option value="Mouse" <?= $selectedCategory === 'Mouse' ? 'selected' : '' ?>>Mouse</option>
                            <option value="Keyboard" <?= $selectedCategory === 'Keyboard' ? 'selected' : '' ?>>Keyboard</option>
                            <option value="Headphone" <?= $selectedCategory === 'Headphone' ? 'selected' : '' ?>>Headphone</option>
                        </select>
                        <select name="stock_sort" class="filter-select" onchange="this.form.submit()">
                            <option value="" <?= $stockSort === '' ? 'selected' : '' ?>>Stock: default</option>
                            <option value="desc" <?= $stockSort === 'desc' ? 'selected' : '' ?>>Most to least</option>
                            <option value="asc" <?= $stockSort === 'asc' ? 'selected' : '' ?>>Least to most</option>
                        </select>
                        <select name="price_sort" class="filter-select" onchange="this.form.submit()">
                            <option value="" <?= $priceSort === '' ? 'selected' : '' ?>>Price: default</option>
                            <option value="desc" <?= $priceSort === 'desc' ? 'selected' : '' ?>>Highest to lowest</option>
                            <option value="asc" <?= $priceSort === 'asc' ? 'selected' : '' ?>>Lowest to highest</option>
                        </select>
                    </form>
                </div>
                <div class="filters-right">
                    <input
                        type="text"
                        form="filters-form"
                        name="q"
                        id="search-product"
                        class="search-input"
                        placeholder="search product"
                        value="<?= htmlspecialchars($searchQuery) ?>"
                        autocomplete="off"
                    >
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

    <div class="modal-backdrop" id="add-product-backdrop">
        <div class="modal-panel">
            <h2 class="modal-title">add product</h2>
            <form class="add-product-form" action="processes/add_product.php" method="post" enctype="multipart/form-data">
                <input
                    type="text"
                    name="name"
                    class="input-block"
                    placeholder="product name"
                    required
                >

                <select
                    name="category"
                    class="input-block"
                    required
                >
                    <option value="" disabled selected>product category (keyboard, mouse, headphone)</option>
                    <option value="Keyboard">Keyboard</option>
                    <option value="Mouse">Mouse</option>
                    <option value="Headphone">Headphone</option>
                </select>

                <div class="row-2col">
                    <input
                        type="number"
                        name="price"
                        class="input-block"
                        placeholder="price"
                        min="0"
                        step="0.01"
                        required
                    >
                    <input
                        type="number"
                        name="stock"
                        class="input-block"
                        placeholder="stock"
                        min="0"
                        step="1"
                        required
                    >
                </div>

                <textarea
                    name="description"
                    class="input-block textarea"
                    placeholder="description"
                    rows="4"
                    required
                ></textarea>

                <label class="input-block file-label">
                    <span>add product images</span>
                    <input type="file" id="image-input" name="images[]" accept="image/*" multiple>
                </label>

                <div class="image-preview-multiple" id="image-preview" style="display: none;"></div>

                <div class="modal-actions">
                    <button type="button" class="secondary-btn" id="close-add-product">Cancel</button>
                    <button type="submit" class="primary-btn">Save product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterProductsTable(query) {
            const q = (query || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.products-table tbody tr');

            rows.forEach((row) => {
                const nameCell = row.querySelector('td');
                const nameText = (nameCell ? nameCell.textContent : '').toLowerCase();
                row.style.display = q === '' || nameText.includes(q) ? '' : 'none';
            });
        }

        const searchInput = document.getElementById('search-product');
        if (searchInput) {
            filterProductsTable(searchInput.value);
            searchInput.addEventListener('input', (e) => {
                filterProductsTable(e.target.value);
            });
        }

        const openBtn = document.getElementById('open-add-product');
        const closeBtn = document.getElementById('close-add-product');
        const backdrop = document.getElementById('add-product-backdrop');
        const imageInput = document.getElementById('image-input');
        const imagePreview = document.getElementById('image-preview');
        const imagesDataTransfer = new DataTransfer();

        function openModal() {
            backdrop.classList.add('visible');
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            backdrop.classList.remove('visible');
            document.body.classList.remove('modal-open');
        }

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) {
                closeModal();
            }
        });

        function renderImagePreview() {
            const allFiles = Array.from(imagesDataTransfer.files);
            imagePreview.innerHTML = '';

            if (!allFiles.length) {
                imagePreview.style.display = 'none';
                return;
            }

            allFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'image-thumb';
                    wrapper.dataset.index = String(index);

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'image-thumb-remove';
                    removeBtn.textContent = '×';
                    removeBtn.addEventListener('click', () => {
                        const dt = new DataTransfer();
                        Array.from(imagesDataTransfer.files).forEach((f, i) => {
                            if (i !== index) {
                                dt.items.add(f);
                            }
                        });
                        imagesDataTransfer.items.clear();
                        Array.from(dt.files).forEach((f) => imagesDataTransfer.items.add(f));
                        imageInput.files = imagesDataTransfer.files;
                        renderImagePreview();
                    });

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    imagePreview.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });

            imagePreview.style.display = 'flex';
        }

        imageInput.addEventListener('change', (event) => {
            const newFiles = Array.from(event.target.files || []);

            newFiles.forEach((file) => {
                imagesDataTransfer.items.add(file);
            });

            imageInput.files = imagesDataTransfer.files;
            renderImagePreview();
        });
    </script>
</body>
</html>