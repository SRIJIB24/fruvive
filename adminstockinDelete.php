<?php
require "userFunc.php";

$id = $_GET['id'];

$object = new data();
$object->sdel = $id;
$object->stockDelete();

header("Location: adminstockin.php");
exit();
