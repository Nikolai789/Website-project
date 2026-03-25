<?php

require_once __DIR__ . "/../configurations/config.php";
require_once __DIR__ . "/../configurations/authentication.php";
require_once __DIR__ . "/../configurations/activity_logger.php";
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';

    // Validate image sizes before doing anything
    $maxImageSize = 2 * 1024 * 1024; // 2MB per image
    if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
        foreach ($_FILES['images']['size'] as $size) {
            if ($size > $maxImageSize) {
                header('Location: ../admin.php?img_error=1');
                exit;
            }
        }
    }
    
    $stmt = $conn->prepare('INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)');

    $stmt->bind_param(
        "ssdis",
        $name,
        $description,
        $price,
        $stock,
        $category
    );

    $stmt->execute();
    $productId = $stmt->insert_id;
    $stmt->close();

    if ($productId && !empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
        $imagesTmp = $_FILES['images']['tmp_name'];
        $total = count($imagesTmp);

        $imgStmt = $conn->prepare(
            "INSERT INTO product_images (product_id, image) VALUES (?, ?)"
        );

        for ($i = 0; $i < $total; $i++) {
            if (empty($imagesTmp[$i])) {
                continue;
            }

            $imageData = file_get_contents($imagesTmp[$i]);
            $imgStmt->bind_param("is", $productId, $imageData);
            $imgStmt->execute();
        }

        $imgStmt->close();
    }

    if ($productId) {
        logCurrentUserActivity($conn, 'created_product', 'products', (int) $productId);
    }
}

header("Location: ../admin.php");
exit();

?>
