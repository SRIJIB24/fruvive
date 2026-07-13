<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();
if ($object->userlvl !== -1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_POST['userid']) && isset($_POST['status'])) {
    $userid = intval($_POST['userid']);
    $status = intval($_POST['status']);
    
    $stmt = $object->conn->prepare("UPDATE users SET active = :status WHERE id = :id");
    $result = $stmt->execute([':status' => $status, ':id' => $userid]);
    
    echo json_encode(['success' => $result]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid Request']);
?>
