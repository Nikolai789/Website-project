<?php
require_once __DIR__ . "/configurations/config.php";
require_once __DIR__ . "/configurations/authentication.php";
requireAdmin();

$flashSuccess = $_SESSION['admin_product_success'] ?? '';
$flashError = $_SESSION['admin_product_error'] ?? '';
unset($_SESSION['admin_product_success'], $_SESSION['admin_product_error']);

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

$allowedDateSort = ['asc', 'desc'];
$dateSort = $_GET['date_sort'] ?? '';
if (!in_array($dateSort, $allowedDateSort, true)) {
    $dateSort = '';
}

$orderBy = '';
if ($priceSort !== '') {
    $orderBy = $priceSort === 'asc' ? ' ORDER BY price ASC' : ' ORDER BY price DESC';
} elseif ($stockSort !== '') {
    $orderBy = $stockSort === 'asc' ? ' ORDER BY stock ASC' : ' ORDER BY stock DESC';
} elseif ($dateSort !== '') {
    $orderBy = $dateSort === 'asc' ? ' ORDER BY date_added ASC' : ' ORDER BY date_added DESC';
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
$productsResult = $conn->query("
    SELECT product_id, name, category, stock, stock_status, price, date_added, description
    FROM vw_catalog_products{$whereSql}{$orderBy}
");
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
                <a href="admin.php" class="tab active">Products</a>
                <a href="admin_orders.php" class="tab">Orders</a>
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

            <?php if (!empty($_GET['img_error'])): ?>
                <p class="admin-inline-error">
                    Image upload failed: one or more images were too large. Please use smaller images.
                </p>
            <?php endif; ?>
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
                        <select name="date_sort" class="filter-select" onchange="this.form.submit()">
                            <option value="" <?= $dateSort === '' ? 'selected' : '' ?>>Date: default</option>
                            <option value="desc" <?= $dateSort === 'desc' ? 'selected' : '' ?>>Newest first</option>
                            <option value="asc" <?= $dateSort === 'asc' ? 'selected' : '' ?>>Oldest first</option>
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
                            <th>stock status</th>
                            <th>price</th>
                            <th>date added</th>
                            <th>product description</th>
                            <th class="actions-header">actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($productsResult && $productsResult->num_rows > 0): ?>
                            <?php while ($row = $productsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= (int) $row['stock'] ?></td>
                                    <td><?= htmlspecialchars($row['stock_status'] ?? '') ?></td>
                                    <td>₱<?= number_format($row['price'], 2) ?></td>
                                    <td><?= date('M j, Y', strtotime($row['date_added'])) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td class="table-actions-cell">
                                        <button
                                            type="button"
                                            class="secondary-btn edit-product-btn"
                                            data-product-id="<?= (int) $row['product_id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES) ?>"
                                            data-category="<?= htmlspecialchars($row['category'] ?? '', ENT_QUOTES) ?>"
                                            data-stock="<?= (int) ($row['stock'] ?? 0) ?>"
                                            data-price="<?= htmlspecialchars((string) ($row['price'] ?? 0), ENT_QUOTES) ?>"
                                            data-description="<?= htmlspecialchars($row['description'] ?? '', ENT_QUOTES) ?>"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="danger-btn delete-product-btn"
                                            data-product-id="<?= (int) $row['product_id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES) ?>"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-table-cell">No products found.</td>
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

    <div class="modal-backdrop" id="edit-product-backdrop">
        <div class="modal-panel">
            <h2 class="modal-title">edit product</h2>
            <form
                id="edit-product-form"
                class="add-product-form"
                action="processes/update_product.php"
                method="post"
                enctype="multipart/form-data"
            >
                <input type="hidden" name="product_id" id="edit-product-id">

                <input
                    type="text"
                    name="name"
                    id="edit-product-name"
                    class="input-block"
                    placeholder="product name"
                    required
                >

                <select
                    name="category"
                    id="edit-product-category"
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
                        id="edit-product-price"
                        class="input-block"
                        placeholder="price"
                        min="0"
                        step="0.01"
                        required
                    >
                    <input
                        type="number"
                        name="stock"
                        id="edit-product-stock"
                        class="input-block"
                        placeholder="stock"
                        min="0"
                        step="1"
                        required
                    >
                </div>

                <textarea
                    name="description"
                    id="edit-product-description"
                    class="input-block textarea"
                    placeholder="description"
                    rows="4"
                    required
                ></textarea>

                <label class="input-block file-label">
                    <span>add images (optional)</span>
                    <input type="file" id="edit-image-input" name="images[]" accept="image/*" multiple>
                </label>

                <input type="hidden" name="kept_image_ids" id="edit-kept-image-ids" value="">
                <input type="hidden" name="images_loaded" id="edit-images-loaded" value="0">
                <div class="image-preview-multiple" id="edit-image-preview" style="display: none;"></div>

                <div class="modal-actions">
                    <button type="button" class="secondary-btn" id="close-edit-product">Cancel</button>
                    <button type="submit" class="primary-btn">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="delete-product-backdrop">
        <div class="modal-panel modal-panel-compact">
            <h2 class="modal-title">delete product</h2>
            <p id="delete-confirm-text" class="delete-confirm-text">
                Are you sure you want to delete this product?
            </p>
            <form action="processes/delete_product.php" method="post">
                <input type="hidden" name="product_id" id="delete-product-id">
                <div class="modal-actions modal-actions-center">
                    <button type="button" class="secondary-btn" id="close-delete-product">Cancel</button>
                    <button type="submit" class="danger-btn">Delete</button>
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
        const editBackdrop = document.getElementById('edit-product-backdrop');
        const imageInput = document.getElementById('image-input');
        const imagePreview = document.getElementById('image-preview');
        const imagesDataTransfer = new DataTransfer();

        function openModal(targetBackdrop) {
            targetBackdrop.classList.add('visible');
            document.body.classList.add('modal-open');
        }

        function closeModal(targetBackdrop) {
            targetBackdrop.classList.remove('visible');
            document.body.classList.remove('modal-open');
        }

        openBtn.addEventListener('click', () => openModal(backdrop));
        closeBtn.addEventListener('click', () => closeModal(backdrop));
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) {
                closeModal(backdrop);
            }
        });

        const closeEditBtn = document.getElementById('close-edit-product');
        closeEditBtn.addEventListener('click', () => closeModal(editBackdrop));
        editBackdrop.addEventListener('click', (event) => {
            if (event.target === editBackdrop) {
                closeModal(editBackdrop);
            }
        });

        const editProductIdInput = document.getElementById('edit-product-id');
        const editProductNameInput = document.getElementById('edit-product-name');
        const editProductCategoryInput = document.getElementById('edit-product-category');
        const editProductStockInput = document.getElementById('edit-product-stock');
        const editProductPriceInput = document.getElementById('edit-product-price');
        const editProductDescriptionInput = document.getElementById('edit-product-description');

        const editImageInput = document.getElementById('edit-image-input');
        const editImagePreview = document.getElementById('edit-image-preview');
        const editKeptImageIdsInput = document.getElementById('edit-kept-image-ids');
        const editImagesLoadedInput = document.getElementById('edit-images-loaded');

        let editExistingImageIds = [];
        let editImagesDataTransfer = new DataTransfer();

        function setEditKeptImageIds(ids) {
            editKeptImageIdsInput.value = (ids || []).join(',');
        }

        function updateEditPreviewVisibility() {
            const hasExisting = editImagePreview.querySelectorAll('.edit-existing-thumb').length > 0;
            const hasNew = editImagePreview.querySelectorAll('.edit-new-thumb').length > 0;
            editImagePreview.style.display = hasExisting || hasNew ? 'flex' : 'none';
        }

        function resetEditImagesUI() {
            editExistingImageIds = [];
            setEditKeptImageIds([]);
            editImagesLoadedInput.value = '0';

            editImagesDataTransfer = new DataTransfer();
            editImageInput.value = '';

            editImagePreview.innerHTML = '';
            editImagePreview.style.display = 'none';
        }

        function renderEditNewImages() {
            // Remove old new-image thumbs and re-render from current DataTransfer state.
            editImagePreview.querySelectorAll('.edit-new-thumb').forEach((n) => n.remove());

            const newFiles = Array.from(editImagesDataTransfer.files);
            newFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'image-thumb edit-new-thumb';
                    wrapper.dataset.index = String(index);

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'image-thumb-remove';
                    removeBtn.textContent = '×';
                    removeBtn.addEventListener('click', () => {
                        const currentFiles = Array.from(editImagesDataTransfer.files);
                        const dt = new DataTransfer();
                        currentFiles.forEach((f, i) => {
                            if (i !== index) dt.items.add(f);
                        });
                        editImagesDataTransfer = dt;
                        editImageInput.files = editImagesDataTransfer.files;
                        renderEditNewImages();
                        updateEditPreviewVisibility();
                    });

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    editImagePreview.appendChild(wrapper);
                    updateEditPreviewVisibility();
                };
                reader.readAsDataURL(file);
            });
        }

        editImageInput.addEventListener('change', (event) => {
            const newFiles = Array.from(event.target.files || []);
            newFiles.forEach((file) => editImagesDataTransfer.items.add(file));

            editImageInput.files = editImagesDataTransfer.files;
            renderEditNewImages();
            updateEditPreviewVisibility();
        });

        function openEditModalFromButton(button) {
            editProductIdInput.value = button.dataset.productId || '';
            editProductNameInput.value = button.dataset.name || '';
            editProductCategoryInput.value = button.dataset.category || '';
            editProductStockInput.value = button.dataset.stock || 0;
            editProductPriceInput.value = button.dataset.price || 0;
            editProductDescriptionInput.value = button.dataset.description || '';

            resetEditImagesUI();
            openModal(editBackdrop);

            const productId = button.dataset.productId;
            if (!productId) return;

            fetch(`processes/get_product_images.php?product_id=${encodeURIComponent(productId)}`)
                .then((r) => r.json())
                .then((data) => {
                    const images = Array.isArray(data.images) ? data.images : [];
                    editExistingImageIds = images.map((img) => (img.image_id ? String(img.image_id) : '')).filter(Boolean);
                    setEditKeptImageIds(editExistingImageIds);
                    editImagesLoadedInput.value = '1';

                    images.forEach((img) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'image-thumb edit-existing-thumb';
                        wrapper.dataset.imageId = String(img.image_id || '');

                        const imgEl = document.createElement('img');
                        imgEl.src = img.data_uri || '';
                        imgEl.alt = 'product image';

                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'image-thumb-remove';
                        removeBtn.textContent = '×';
                        removeBtn.addEventListener('click', () => {
                            const idToRemove = String(wrapper.dataset.imageId || '');
                            wrapper.remove();
                            editExistingImageIds = editExistingImageIds.filter((id) => id !== idToRemove);
                            setEditKeptImageIds(editExistingImageIds);
                            updateEditPreviewVisibility();
                        });

                        wrapper.appendChild(imgEl);
                        wrapper.appendChild(removeBtn);
                        editImagePreview.appendChild(wrapper);
                    });

                    updateEditPreviewVisibility();
                })
                .catch(() => {
                    // If fetching fails, keep the edit modal usable for text fields.
                });
        }

        document.querySelectorAll('.edit-product-btn').forEach((btn) => {
            btn.addEventListener('click', () => openEditModalFromButton(btn));
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

        // script behaviour of delete button
        const deleteBackdrop = document.getElementById('delete-product-backdrop');
        const closeDeleteBtn = document.getElementById('close-delete-product');
        const deleteProductIdInput = document.getElementById('delete-product-id');
        const deleteConfirmText = document.getElementById('delete-confirm-text');

        closeDeleteBtn.addEventListener('click', () => closeModal(deleteBackdrop));
        deleteBackdrop.addEventListener('click', (event) => {
            if (event.target === deleteBackdrop) closeModal(deleteBackdrop);
        });

        document.querySelectorAll('.delete-product-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                deleteProductIdInput.value = btn.dataset.productId;
                deleteConfirmText.textContent =
                    `Are you sure you want to delete "${btn.dataset.name}"? This cannot be undone.`;
                openModal(deleteBackdrop);
            });
        });
    </script>
</body>
</html>
