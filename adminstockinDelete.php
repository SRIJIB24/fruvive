<?php
require "userFunc.php";

$id = $_GET['id'];

$object = new data();
$object->sdel = $id;
$object->stockDelete();

$_SESSION['msg'] = 'Stock intake record deleted successfully!';
$_SESSION['msg_type'] = 'success';
header("Location: adminstockin.php");
exit();
