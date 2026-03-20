<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- these two tags are important for SEO to understand our website -->
    <meta name="description" content="we have a wide selection of gadget peripherals">
    <meta name="keywords" content= "mouse, keyboards, headphones"> 

    <link rel="stylesheet" href="css/style.css">
    
    <link rel="stylesheet" href="css/header.css">

    <link rel="stylesheet" href="css/main.css">

    <link rel="stylesheet" href="css/footer.css">

    <link rel="stylesheet" href="css/navigation.css">

    <link rel="stylesheet" href="css/navigation2.css">
    <title>Periph</title>

</head>
<body>
    <?php include "includes/nav.php" ?>
    <?php include "includes/second_nav.php" ?>
    <?php include "includes/header.php" ?>
    <?php include "configurations/config.php"; ?>

    <?php
        /**
         * Convert an image BLOB to a `data:` URI so it can be displayed in <img src="...">.
         * Note: your schema stores only the raw bytes (no mime type column), so we infer mime from the buffer.
         */
        function blobToDataUri(?string $blob): ?string {
            if (empty($blob)) return null;

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($blob);
            if (empty($mime)) {
                // Fallback; most product images are jpg/png anyway.
                $mime = "image/jpeg";
            }

            return "data:" . $mime . ";base64," . base64_encode($blob);
        }

        $category = $_GET['category'] ?? null;

    if ($category) {
        $stmt = $conn->prepare("
            SELECT p.*,
                (
                    SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.product_id
                    ORDER BY pi.is_primary DESC, pi.image_id ASC
                    LIMIT 1
                ) AS image_blob
            FROM products p
            WHERE p.category = ?
        ");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("
            SELECT p.*,
                (
                    SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.product_id
                    ORDER BY pi.is_primary DESC, pi.image_id ASC
                    LIMIT 1
                ) AS image_blob
            FROM products p
        ");
    }
    ?>

    <main>
        <div class="product-container">
            <div class="product-categories">
                <a href="index.php">All</a>
                <a href="index.php?category=Mouse">Mouse</a>
                <a href="index.php?category=Keyboard">Keyboard</a>
                <a href="index.php?category=Headphone">Headphone</a>
            </div>
            <hr>
            <div class="product-items">
                <?php while ($product = $result->fetch_assoc()): ?>

                    <div class="products">
                        <a href="product.php?id=<?= $product['product_id'] ?>">
                            <?php $imgSrc = blobToDataUri($product['image_blob'] ?? null); ?>
                            <?php if ($imgSrc): ?>
                                <img
                                    src="<?= $imgSrc ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="product-img"
                                >
                            <?php endif; ?>
                            <p class="name"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                            <p class="stocks">Stock: <span><?= $product['stock'] ?></span></p>
                        </a>
                    </div>

                <?php endwhile; ?>
            </div>

        </div>
    </main>
    
    <?php include "includes/footer.php" ?>

    <script src="javascript/script.js"></script>
</body>
</html>