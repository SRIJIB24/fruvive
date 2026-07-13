<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $object->deltcartproduct($id);
}
header("Location: usercart.php");
exit();
