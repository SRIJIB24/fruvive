<?php
require "userFunc.php";
$object = new data();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to submit a return request."]);
    exit;
}

$userid = $_SESSION['user_id'];

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid return data payload."]);
    exit;
}

$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
$product_id = (isset($data['product_id']) && $data['product_id'] !== null) ? intval($data['product_id']) : null;
$qty = isset($data['qty']) ? intval($data['qty']) : 1;
$reason_raw = isset($data['reason']) ? trim($data['reason']) : '';

// Validate reason
$reasons_map = [
    'product_missing' => 'Product Missing',
    'damaged_product' => 'Damaged Product',
    'unsatisfactory_quality' => 'Unsatisfactory Quality',
    'wrong_item' => 'Wrong Item Delivered',
    'other' => 'Other'
];
$reason = isset($reasons_map[$reason_raw]) ? $reasons_map[$reason_raw] : 'Other';

if ($order_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Order ID."]);
    exit;
}

// 1. Verify ownership of the order
$stmt = $object->conn->prepare("SELECT * FROM orders WHERE id = :id AND userid = :userid AND client_id = :client_id");
$stmt->execute([':id' => $order_id, ':userid' => $userid, ':client_id' => CLIENT_ID]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(["status" => "error", "message" => "Order not found or access denied."]);
    exit;
}

// 2. Verify status is not already cancelled
if ($order['status'] === 'Cancelled') {
    echo json_encode(["status" => "error", "message" => "Cannot return a cancelled order."]);
    exit;
}

// 3. Verify the 8-hour window
$order_time = strtotime($order['created_at']);
$time_diff = time() - $order_time;
if ($time_diff > 8 * 3600) { // 8 hours in seconds
    echo json_encode(["status" => "error", "message" => "Return requests are only allowed within 8 hours of order placement."]);
    exit;
}

// 4. Verify product belongs to this order (if product_id is specified)
if ($product_id !== null) {
    $item_stmt = $object->conn->prepare("SELECT * FROM order_items WHERE orderid = :orderid AND productid = :productid AND client_id = :client_id");
    $item_stmt->execute([':orderid' => $order_id, ':productid' => $product_id, ':client_id' => CLIENT_ID]);
    $item = $item_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        echo json_encode(["status" => "error", "message" => "The specified product is not part of this order."]);
        exit;
    }
    if ($qty < 1 || $qty > $item['qty']) {
        echo json_encode(["status" => "error", "message" => "Invalid quantity specified for return."]);
        exit;
    }
}

// 5. Check if a return request for this order/item is already logged
if ($product_id === null) {
    $check_stmt = $object->conn->prepare("SELECT COUNT(*) FROM order_returns WHERE order_id = :order_id AND product_id IS NULL AND client_id = :client_id");
    $check_stmt->execute([':order_id' => $order_id, ':client_id' => CLIENT_ID]);
} else {
    $check_stmt = $object->conn->prepare("SELECT COUNT(*) FROM order_returns WHERE order_id = :order_id AND product_id = :product_id AND client_id = :client_id");
    $check_stmt->execute([':order_id' => $order_id, ':product_id' => $product_id, ':client_id' => CLIENT_ID]);
}
$existing_requests = $check_stmt->fetchColumn();
if ($existing_requests > 0) {
    echo json_encode(["status" => "error", "message" => "A return request has already been submitted for this item/order."]);
    exit;
}

// 6. Insert return request into order_returns table
$insert_stmt = $object->conn->prepare("INSERT INTO order_returns (order_id, product_id, qty, reason, status, client_id) VALUES (:order_id, :product_id, :qty, :reason, 'Pending', :client_id)");
$res = $insert_stmt->execute([
    ':order_id' => $order_id,
    ':product_id' => $product_id,
    ':qty' => $qty,
    ':reason' => $reason,
    ':client_id' => CLIENT_ID
]);

if ($res) {
    $longOrderId = $order['order_id'] ?: '#' . $order_id;
    
    // Add notifications
    if ($product_id === null) {
        $object->addNotification($userid, "Return Requested", "Your return request for entire order {$longOrderId} is submitted.", "return_requested");
        $object->addNotification(null, "New Order Return", "Customer has requested return for entire order {$longOrderId}.", "return_created");
    } else {
        // Fetch product name
        $pro_stmt = $object->conn->prepare("SELECT pname FROM products WHERE id = :pid");
        $pro_stmt->execute([':pid' => $product_id]);
        $pname = $pro_stmt->fetchColumn() ?: "Product";
        $object->addNotification($userid, "Return Requested", "Your return request for '{$pname}' (Order {$longOrderId}) is submitted.", "return_requested");
        $object->addNotification(null, "New Product Return", "Customer has requested return for '{$pname}' (Order {$longOrderId}).", "return_created");
    }
    
    echo json_encode(["status" => "success", "message" => "Your return request has been submitted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to log the return request."]);
}
exit;
