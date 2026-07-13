<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $object->deleteAddress($id, $userid);
    }
}

header("Location: userAddress.php");
exit();
?>
