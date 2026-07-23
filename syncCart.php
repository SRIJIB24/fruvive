<?php
require "userFunc.php";
$object = new data();

// Ensure session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not authenticated."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || !isset($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Invalid cart data payload."]);
    exit;
}

$cartItems = $data['cart'];
$syncCount = 0;

foreach ($cartItems as $item) {
    $pid = (int)$item['pid'];
    $qty = (int)$item['qty'];

    if ($pid <= 0 || $qty <= 0) continue;

    $product = $object->stockinfetchpid($pid);
    if (!$product) continue;

    $product_name = $product['pname'];
    $price = $product['pack_price'];
    $maxStock = $product['total_quant'];

    $existing = $object->fetchCartpid($user_id, $pid);
    if ($existing) {
        // Merge quantities, capping at 5 and available stock
        $newQty = min(5, $existing['qty'] + $qty);
        $newQty = min($newQty, $maxStock);

        $sql = $object->conn->prepare("UPDATE cart SET qty = :qty WHERE id = :id");
        $sql->execute([
            ':qty' => $newQty,
            ':id' => $existing['id']
        ]);
    } else {
        // Insert new entry
        $finalQty = min(5, $qty);
        $finalQty = min($finalQty, $maxStock);

        if ($finalQty > 0) {
            $object->insertCart($user_id, $pid, $product_name, $price, $finalQty);
        }
    }
    $syncCount++;
}

echo json_encode([
    "status" => "success",
    "message" => "Successfully synced $syncCount items from local storage."
]);
?>
