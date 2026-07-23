<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

header('Content-Type: application/json');

$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
if ($pid <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid product ID."]);
    exit;
}

$product = $object->stockinfetchpid($pid);
if (!$product) {
    echo json_encode(["status" => "error", "message" => "Product not found."]);
    exit;
}

// Fetch details from products table as well to get image URL & quantity weight details
$sql = $object->conn->prepare("SELECT img_url, quant FROM products WHERE id = :id AND client_id = :client_id");
$sql->execute([':id' => $pid, ':client_id' => CLIENT_ID]);
$pDetails = $sql->fetch(PDO::FETCH_ASSOC);

$img = $pDetails ? $pDetails['img_url'] : 'assets/image/product-image/default.png';
$quant = $pDetails ? $pDetails['quant'] : '';

$summary = $object->getProductRatingSummary($pid);
$reviews = $object->fetchProductReviews($pid);

echo json_encode([
    "status" => "success",
    "isLoggedIn" => $isLoggedIn,
    "pid" => $pid,
    "pname" => $product['pname'],
    "price" => $product['pack_price'],
    "img" => $img,
    "quant" => $quant,
    "max_stock" => $product['total_quant'],
    "avg_rating" => $summary['avg_rating'],
    "review_count" => $summary['review_count'],
    "reviews" => $reviews
]);
?>
