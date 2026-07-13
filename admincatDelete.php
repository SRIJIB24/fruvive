<?php
require "userFunc.php";

$cid = $_REQUEST['catid'];

$object = new data();
$object->ciddel = $cid;
$object-> catDelete();
header("Location: admincategory.php");
exit();
?>