<?php
require "userFunc.php";

$cid = $_REQUEST['catid'];

$object = new data();
$object->ciddel = $cid;
$object->catDelete();
$_SESSION['msg'] = 'Category deleted successfully!';
$_SESSION['msg_type'] = 'success';
header("Location: admincategory.php");
exit();
?>