<?php
require "userFunc.php";

$proid = $_REQUEST['proid'];

$object = new data();
$object->prodel = $proid;
$object-> proDelete();
header("Location: adminproducts.php");
exit();
?>