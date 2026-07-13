<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

header("Content-Type: application/json");

if (isset($_POST['id']) && isset($_POST['qty'])) {
    $id = (int)$_POST['id'];
    $qty = (int)$_POST['qty'];
    
    $stmt = $object->conn->prepare("SELECT productid FROM cart WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $pid = $stmt->fetchColumn();
    
    if ($pid) {
        $products = $object->stockinfetchpid($pid);
        if ($products && $qty > $products['total_quant']) {
            echo json_encode(["status" => "error", "message" => "Only " . $products['total_quant'] . " items left in stock."]);
            exit;
        }
    }
    
    $object->updatecartQuant($id, $qty);
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
}
