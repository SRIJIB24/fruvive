<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

header('Content-Type: application/json');

$pid = isset($_POST['pid']) ? (int)$_POST['pid'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($pid <= 0 || !in_array($action, ['add', 'remove'])) {
    echo json_encode(["status" => "error", "message" => "Invalid parameters."]);
    exit;
}

if (!$isLoggedIn) {
    echo json_encode(["status" => "guest_success", "action" => $action, "pid" => $pid]);
    exit;
}

$userid = $_SESSION['user_id'];

if ($action === 'add') {
    $res = $object->addToWishlist($userid, $pid);
    if ($res) {
        echo json_encode(["status" => "success", "action" => "add", "count" => $object->wishlistCount($userid)]);
    } else {
        echo json_encode(["status" => "error", "message" => "Could not add to wishlist."]);
    }
} else {
    $res = $object->removeFromWishlist($userid, $pid);
    if ($res) {
        echo json_encode(["status" => "success", "action" => "remove", "count" => $object->wishlistCount($userid)]);
    } else {
        echo json_encode(["status" => "error", "message" => "Could not remove from wishlist."]);
    }
}
?>
