<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

header("Content-Type: application/json");

$cart_id = (int)$_POST['cartid'];
$qty = (int)$_POST['qty'];

$stmt = $object->conn->prepare("SELECT productid FROM cart WHERE id = :id");
$stmt->execute([':id' => $cart_id]);
$pid = $stmt->fetchColumn();

if ($pid) {
    $products = $object->stockinfetchpid($pid);
    if ($products && $qty > $products['total_quant']) {
        echo json_encode(["status" => "error", "message" => "Only " . $products['total_quant'] . " items left in stock."]);
        exit;
    }
}

$sql = $object->updatecartQuant($cart_id,$qty);
echo json_encode([
    "status"=>"success",
]);