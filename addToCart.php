<?php
require "userFunc.php";

$object = new data();
$object->sessionCheck();

if ($object->userlvl !== 10) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userid = $_SESSION['userid']; // adjust if different
    $pid = $_POST['pid'];
    $pname = $_POST['pname'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];

    $getData = $object->fetchCartpid();

    if ($row = $sql->fetch()) 
    {
        $newQty = $row['qty'] + $qty;
        $object->updateCart();
    } else {
        $object->insertCart();
    }

    echo "success";
}
