<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$ok = $object->couponDelete($id);

if ($ok) {
    $_SESSION['msg'] = 'Coupon deleted successfully!';
    $_SESSION['msg_type'] = 'success';
} else {
    $_SESSION['msg'] = 'Failed to delete coupon.';
    $_SESSION['msg_type'] = 'error';
}

header("Location: admincoupons.php");
exit();
?>
