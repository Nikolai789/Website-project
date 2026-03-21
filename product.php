<?php
require_once __DIR__ . "/configurations/config.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) { header("Location: index.php"); exit; }

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header("Location: index.php"); exit; }

// Fetch all images for this product
$imgStmt = $conn->prepare("
    SELECT image FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, image_id ASC
");
$imgStmt->bind_param("i", $id);
$imgStmt->execute();
$imagesResult = $imgStmt->get_result();
$images = $imagesResult->fetch_all(MYSQLI_ASSOC);
$imgStmt->close();

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

// Pre-convert all image blobs to data URIs
$imgSrcs = array_filter(array_map(
    fn($row) => blobToDataUri($row['image'] ?? null),
    $images
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Periph</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/navigation2.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/product.css">
</head>
<body>
    <?php include "includes/nav.php" ?>
    <?php include "includes/second_nav.php" ?>

    <main>
        <div class="container">

            <a href="index.php" class="back-link">← Back to products</a>

            <div class="product-detail">

                <!-- Image gallery -->
                <div class="product-gallery">
                    <?php if (!empty($imgSrcs)): ?>
                        <?php $srcList = array_values($imgSrcs); ?>

                        <?php if (count($srcList) > 1): ?>
                            <button class="gallery-arrow left" onclick="changeImage(-1)">&#8249;</button>
                        <?php endif; ?>

                        <img
                            src="<?= $srcList[0] ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="main-image"
                            id="main-image"
                        >

                        <?php if (count($srcList) > 1): ?>
                            <button class="gallery-arrow right" onclick="changeImage(1)">&#8250;</button>
                        <?php endif; ?>

                        <?php if (count($srcList) > 1): ?>
                            <p class="image-counter">
                                <span id="current-index">1</span> / <?= count($srcList) ?>
                            </p>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="main-image no-image"></div>
                    <?php endif; ?>
                </div>

                <!-- Product info -->
                <div class="product-info">
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                    <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    <p class="stocks">Stock: <span><?= (int) $product['stock'] ?></span></p>
                    <p class="category">Category: <?= htmlspecialchars($product['category']) ?></p>
                    <button>Add to Cart</button>
                </div>

            </div>
        </div>
    </main>

    <?php include "includes/footer.php" ?>

    <script>
        const images = <?= json_encode(array_values($imgSrcs)) ?>;
        let current = 0;

        function changeImage(direction) {
            current = (current + direction + images.length) % images.length;
            document.getElementById('main-image').src = images[current];
            document.getElementById('current-index').textContent = current + 1;
        }
    </script>
</body>
</html>