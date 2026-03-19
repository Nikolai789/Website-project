<?php
include "configurations/config.php";

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { echo "Product not found."; exit; }
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
                <img src="Assets/product_images/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>">
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