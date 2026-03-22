<?php
require_once __DIR__ . "/../configurations/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin.php");
    exit();
}

$productId = $_POST['product_id'] ?? null;
$name = trim($_POST['name'] ?? '');
$category = $_POST['category'] ?? '';
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$description = trim($_POST['description'] ?? '');

$allowedCategories = ['Keyboard', 'Mouse', 'Headphone'];
if (!in_array($category, $allowedCategories, true)) {
    // basic guard; if invalid, send back to admin
    header("Location: ../admin.php");
    exit();
}

if ($productId === null || $name === '' || $description === '') {
    header("Location: ../admin.php");
    exit();
}

$productIdInt = (int) $productId;
$stockInt = (int) $stock;
$priceFloat = (float) $price;

if ($productIdInt <= 0) {
    header("Location: ../admin.php");
    exit();
}

/**
 * Compress uploaded image bytes to JPEG to reduce DB packet size.
 * Returns compressed binary string or null if compression fails.
 */
function compressToJpegFromBytes(?string $bytes, int $quality): ?string {
    if (empty($bytes)) return null;
    if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
        return null; // GD not available
    }

    $img = @imagecreatefromstring($bytes);
    if (!$img) return null;

    ob_start();
    $ok = @imagejpeg($img, null, $quality);
    $data = ob_get_clean();
    imagedestroy($img);

    if (!$ok || empty($data)) return null;
    return $data;
}

$stmt = $conn->prepare(
    "UPDATE products
     SET name = ?, category = ?, stock = ?, price = ?, description = ?
     WHERE product_id = ?"
);

if (!$stmt) {
    header("Location: ../admin.php");
    exit();
}

$stmt->bind_param(
    "ssidsi",
    $name,
    $category,
    $stockInt,
    $priceFloat,
    $description,
    $productIdInt
);

$conn->begin_transaction();

$imagesLoaded = (($_POST['images_loaded'] ?? '0') === '1');
$keptImageIdsRaw = $_POST['kept_image_ids'] ?? '';
$keptImageIds = [];
if (is_string($keptImageIdsRaw) && trim($keptImageIdsRaw) !== '') {
    foreach (explode(',', $keptImageIdsRaw) as $idPart) {
        $idPart = trim($idPart);
        if ($idPart === '') continue;
        $idInt = (int) $idPart;
        if ($idInt > 0) $keptImageIds[] = $idInt;
    }
}
$keptImageIds = array_values(array_unique($keptImageIds));

$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    $imagesInsertOk = true;
    $maxStoredBytes = 3 * 1024 * 1024; // 3MB per image (keeps under common max_allowed_packet defaults)
    $jpegQuality = 72;

    // Update images:
    // - If we successfully loaded current images in the UI (`images_loaded=1`), delete images not kept.
    // - If not loaded, don't delete existing images (avoid accidental removal).
    if ($imagesLoaded) {
        if (count($keptImageIds) === 0) {
            $delStmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $productIdInt);
                $delStmt->execute();
                $delStmt->close();
            }
        } else {
            $placeholders = implode(',', array_fill(0, count($keptImageIds), '?'));
            $sql = "DELETE FROM product_images WHERE product_id = ? AND image_id NOT IN ($placeholders)";
            $delStmt = $conn->prepare($sql);
            if ($delStmt) {
                $types = 'i' . str_repeat('i', count($keptImageIds));
                $params = array_merge([$productIdInt], $keptImageIds);
                $delStmt->bind_param($types, ...$params);
                $delStmt->execute();
                $delStmt->close();
            }
        }
    }

    // Insert newly uploaded images (if any).
    if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
        $imagesTmp = $_FILES['images']['tmp_name'];
        $imagesSize = $_FILES['images']['size'] ?? [];
        $total = count($imagesTmp);

        $imgStmt = $conn->prepare(
            "INSERT INTO product_images (product_id, image) VALUES (?, ?)"
        );

        if ($imgStmt) {
            for ($i = 0; $i < $total; $i++) {
                if (empty($imagesTmp[$i]) || !is_uploaded_file($imagesTmp[$i])) {
                    continue;
                }

                $imageBytes = file_get_contents($imagesTmp[$i]);
                if ($imageBytes === false) continue;

                // Compress to reduce DB packet size.
                $compressed = compressToJpegFromBytes($imageBytes, $jpegQuality);
                $imageData = $compressed !== null ? $compressed : $imageBytes;

                // Guard: if still too large, stop and rollback.
                if (strlen($imageData) > $maxStoredBytes) {
                    $imagesInsertOk = false;
                    break;
                }

                $imgStmt->bind_param("is", $productIdInt, $imageData);
                $imgStmt->execute();
            }
            $imgStmt->close();
        }
    }

    if (!$imagesInsertOk) {
        $conn->rollback();
        header("Location: ../admin.php?img_error=too_large");
        exit();
    }

    // Recompute primary image only when UI had the existing images loaded (so deletions may have happened).
    if ($imagesLoaded) {
        $resetStmt = $conn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        if ($resetStmt) {
            $resetStmt->bind_param("i", $productIdInt);
            $resetStmt->execute();
            $resetStmt->close();
        }

        $firstStmt = $conn->prepare(
            "SELECT image_id
             FROM product_images
             WHERE product_id = ?
             ORDER BY image_id ASC
             LIMIT 1"
        );
        if ($firstStmt) {
            $firstStmt->bind_param("i", $productIdInt);
            $firstStmt->execute();
            $firstRes = $firstStmt->get_result();
            $firstRow = $firstRes ? $firstRes->fetch_assoc() : null;
            $firstId = $firstRow ? (int) $firstRow['image_id'] : 0;
            $firstStmt->close();

            if ($firstId > 0) {
                $primaryStmt = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE product_id = ? AND image_id = ?");
                if ($primaryStmt) {
                    $primaryStmt->bind_param("ii", $productIdInt, $firstId);
                    $primaryStmt->execute();
                    $primaryStmt->close();
                }
            }
        }
    }

    $conn->commit();
} else {
    $conn->rollback();
}

// Try to keep the user on the same page state (filters) when possible.
$redirect = $_SERVER['HTTP_REFERER'] ?? '../admin.php';

header("Location: {$redirect}");
exit();

?>

