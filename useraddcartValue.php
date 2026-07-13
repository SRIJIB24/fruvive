<?php
// require "config.php";
require "userFunc.php";
$object = new data();
$object->sessionCheck();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['pid'];
$quantity = 1;

$products = $object->stockinfetchpid($product_id);

if (!$products) {
    echo json_encode(["status" => "error"]);
    exit;
}

$product_name = $products['pname'];
$price = $products['pack_price'];

$check = $object->fetchCartpid($user_id, $product_id);

if ($check) {
    $newQty = $check['qty'] + 1;
    if ($newQty > $products['total_quant']) {
        echo json_encode(["status" => "error", "message" => "Only " . $products['total_quant'] . " items left in stock."]);
        exit;
    }

    $sql = $object->conn->prepare("UPDATE cart SET qty=:qty WHERE id=:id");
    $sql->execute([
        ':qty' => $newQty,
        ':id' => $check['id']
    ]);
} else {
    if (1 > $products['total_quant']) {
        echo json_encode(["status" => "error", "message" => "This item is currently out of stock."]);
        exit;
    }
    $object->insertCart($user_id, $product_id, $product_name, $price, $quantity);
}

echo json_encode([
    "status" => "success",
    "pname" => $product_name
]);
