<?php
include "configurations/config.php";

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $conn->prepare("
    SELECT
        p.*,
        (
            SELECT pi.image
            FROM product_images pi
            WHERE pi.product_id = p.product_id
            ORDER BY pi.is_primary DESC, pi.image_id ASC
            LIMIT 1
        ) AS image_blob
    FROM products p
    WHERE p.product_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { echo "Product not found."; exit; }

/**
 * Convert an image BLOB to a `data:` URI so it can be displayed in <img src="...">.
 * Note: your schema stores only the raw bytes (no mime type column), so we infer mime from the buffer.
 */
function blobToDataUri(?string $blob): ?string {
    if (empty($blob)) return null;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($blob);
    if (empty($mime)) {
        $mime = "image/jpeg";
    }

    return "data:" . $mime . ";base64," . base64_encode($blob);
}

$imgSrc = blobToDataUri($product['image_blob'] ?? null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            <div class="product-detail">
                <?php if ($imgSrc): ?>
                    <img
                        src="<?= $imgSrc ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                    >
                <?php endif; ?>
                <div class="product-info">
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                    <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    <p class="stocks">Stock: <span><?= $product['stock'] ?></span></p>
                    <p class="category">Category: <?= $product['category'] ?></p>
                    <button>Add to Cart</button>
                </div>
            </div>
        </div>
    </main>

    <?php include "includes/footer.php" ?>
</body>
</html>