<?php
require "userFunc.php";

$proid = $_REQUEST['proid'];

$object = new data();
$object->prodel = $proid;
$object->proDelete();
$_SESSION['msg'] = 'Product deleted successfully!';
$_SESSION['msg_type'] = 'success';
header("Location: adminproducts.php");
exit();
?>