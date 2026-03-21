<?php
require_once __DIR__ . "/configurations/config.php";

// Whitelist category to prevent arbitrary values hitting the DB
$allowedCategories = ['Mouse', 'Keyboard', 'Headphone'];
$category = $_GET['category'] ?? null;
if ($category !== null && !in_array($category, $allowedCategories, true)) {
    $category = null;
}

/**
 * Convert an image BLOB to a data: URI for use in <img src="...">.
 * Infers mime type from the raw buffer since no mime column exists.
 */
function blobToDataUri(?string $blob): ?string {
    if (empty($blob)) return null;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->buffer($blob);
    if (empty($mime)) {
        $mime = 'image/jpeg';
    }

    return 'data:' . $mime . ';base64,' . base64_encode($blob);
}

$sql = "
    SELECT p.*,
        (
            SELECT pi.image FROM product_images pi
            WHERE pi.product_id = p.product_id
            ORDER BY pi.is_primary DESC, pi.image_id ASC
            LIMIT 1
        ) AS image_blob
    FROM products p
";

if ($category) {
    $stmt = $conn->prepare($sql . " WHERE p.category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="We have a wide selection of gadget peripherals">
    <meta name="keywords" content="mouse, keyboards, headphones">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/navigation2.css">

    <title>GearHub</title>
</head>
<body>
    <?php include "includes/nav.php" ?>
    <?php include "includes/second_nav.php" ?>
    <?php include "includes/header.php" ?>

    <main>
        <div class="product-container">

            <div class="product-categories">
                <a href="index.php"          <?= !$category ? 'class="active"' : '' ?>>All</a>
                <a href="index.php?category=Mouse"     <?= $category === 'Mouse'     ? 'class="active"' : '' ?>>Mouse</a>
                <a href="index.php?category=Keyboard"  <?= $category === 'Keyboard'  ? 'class="active"' : '' ?>>Keyboard</a>
                <a href="index.php?category=Headphone" <?= $category === 'Headphone' ? 'class="active"' : '' ?>>Headphone</a>
            </div>

            <hr>

            <div class="product-items">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="products">
                            <a href="product.php?id=<?= (int) $product['product_id'] ?>">
                                <?php $imgSrc = blobToDataUri($product['image_blob'] ?? null); ?>

                                <?php if ($imgSrc): ?>
                                    <img
                                        src="<?= $imgSrc ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="product-img"
                                    >
                                <?php else: ?>
                                    <div class="product-img product-img-placeholder"></div>
                                <?php endif; ?>

                                <div class="product-info">
                                    <p class="name"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                                    <p class="stocks">Stock: <span><?= (int) $product['stock'] ?></span></p>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-products">No products found.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <?php include "includes/footer.php" ?>

    <script src="javascript/script.js"></script>
</body>
</html>