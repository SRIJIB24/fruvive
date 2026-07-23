<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

header('Content-Type: application/json');

if (!$isLoggedIn) {
    echo json_encode(["status" => "error", "message" => "Please login to submit your review."]);
    exit;
}

$userid = $_SESSION['user_id'];
$pid = isset($_POST['pid']) ? (int)$_POST['pid'] : (isset($_POST['productid']) ? (int)$_POST['productid'] : 0);
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($pid <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(["status" => "error", "message" => "Invalid product rating parameters. Received PID: " . $pid . ", Rating: " . $rating]);
    exit;
}

// Add review to database
$res = $object->addProductReview($userid, $pid, $rating, $comment);

if ($res) {
    echo json_encode(["status" => "success", "message" => "Review submitted successfully! Thank you."]);
} else {
    echo json_encode(["status" => "error", "message" => "Could not submit your review. Please try again."]);
}
?>
