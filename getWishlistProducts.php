<?php
require "userFunc.php";
$object = new data();

header('Content-Type: application/json');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data['ids']) || !is_array($data['ids'])) {
    echo json_encode([]);
    exit;
}

$ids = array_map('intval', $data['ids']);
if (empty($ids)) {
    echo json_encode([]);
    exit;
}

// Prepare in-clause statement safely
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT s.*, p.pname, p.img_url 
          FROM stock_in s
          JOIN products p ON s.proid = p.id
          WHERE s.proid IN ($placeholders) AND s.client_id = ? AND p.client_id = ?";

$stmt = $object->conn->prepare($query);

// Execute query with parameters
$params = array_merge($ids, [CLIENT_ID, CLIENT_ID]);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
?>
