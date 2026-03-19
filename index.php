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

    <main>
        <div class="product-container">
            <div class="product-categories">
                <a href="">Mouse</a>
                <a href="">Keyboard</a>
                <a href="">Headset</a>
            </div>
                <hr>
            <div class="product-items">
                <?php
                    $result = $conn->query("SELECT * FROM products");

                    while ($product = $result->fetch_assoc()): ?>

                    <div class="products">
                        <a href="product.php?id=<?= $product['product_id'] ?>">
                            <img src="Assets/product_images/<?= htmlspecialchars($product['image']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="product-img">
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