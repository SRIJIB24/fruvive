<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

header('Content-Type: application/json');

if (!$isLoggedIn) {
    echo json_encode(["status" => "error", "message" => "Not logged in."]);
    exit;
}

$userid = $_SESSION['user_id'];

// Get raw POST payload
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data['wishlist']) || !is_array($data['wishlist'])) {
    echo json_encode(["status" => "error", "message" => "No wishlist items payload."]);
    exit;
}

$synced = 0;
foreach ($data['wishlist'] as $pid) {
    $pid = (int)$pid;
    if ($pid > 0) {
        $object->addToWishlist($userid, $pid);
        $synced++;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Wishlist synced successfully.",
    "synced_count" => $synced,
    "total_count" => $object->wishlistCount($userid)
]);
?>
