<?php

require_once __DIR__ . "/../configurations/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';

    $stmt = $conn->prepare(
        "INSERT INTO products (name, description, price, stock, category)
         VALUES (?, ?, ?, ?, ?)"
    );

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
}

header("Location: ../admin.php");
exit();

?>
