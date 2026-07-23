<?php
require "userFunc.php";
$object = new data();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['userlevel']) && (int)$_SESSION['userlevel'] === -1;
$userid = ($isLoggedIn && !$isAdmin) ? $_SESSION['user_id'] : null;

header('Content-Type: application/json');
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : 'fetch';

if ($action === 'fetch') {
    $list = $object->fetchNotifications($userid);
    $unreadCount = $object->getUnreadNotificationsCount($userid);
    
    echo json_encode([
        "status" => "success",
        "notifications" => $list,
        "unread_count" => $unreadCount
    ]);
    exit;
}

if ($action === 'read') {
    $object->markNotificationsRead($userid);
    echo json_encode([
        "status" => "success",
        "message" => "Notifications marked as read."
    ]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid action requested."]);
?>
