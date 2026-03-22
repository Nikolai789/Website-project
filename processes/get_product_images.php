<?php
require_once __DIR__ . "/../configurations/config.php";

header('Content-Type: application/json; charset=utf-8');

$productId = $_GET['product_id'] ?? null;
$productIdInt = (int) $productId;

if ($productId === null || $productIdInt <= 0) {
    echo json_encode(['images' => []]);
    exit();
}

function blobToDataUri(?string $blob): ?string {
    if (empty($blob)) return null;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($blob);
    if (empty($mime)) {
        $mime = "image/jpeg";
    }

    return "data:" . $mime . ";base64," . base64_encode($blob);
}

$stmt = $conn->prepare("
    SELECT image_id, image
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, image_id ASC
");

if (!$stmt) {
    echo json_encode(['images' => []]);
    exit();
}

$stmt->bind_param("i", $productIdInt);
$stmt->execute();

$result = $stmt->get_result();
$images = [];

while ($row = $result->fetch_assoc()) {
    $images[] = [
        'image_id' => (int) $row['image_id'],
        'data_uri' => blobToDataUri($row['image'] ?? null),
    ];
}

$stmt->close();

echo json_encode(['images' => $images]);
exit();

?>

