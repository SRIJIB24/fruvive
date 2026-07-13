<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$userid = $_SESSION['user_id'];

if (isset($_GET['order_id'])) {
    $orderid = intval($_GET['order_id']);
    
    // Verify ownership
    $stmt = $object->conn->prepare("SELECT * FROM orders WHERE id = :id AND userid = :userid AND client_id = :client_id");
    $stmt->execute([':id' => $orderid, ':userid' => $userid, ':client_id' => CLIENT_ID]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        require_once "invoiceGenerator.php";
        $generator = new invoiceGenerator();
        $pdf_content = $generator->generate($orderid);
        
        if ($pdf_content) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="invoice_' . $orderid . '.pdf"');
            echo $pdf_content;
            exit();
        }
    }
}

header("Location: userOrders.php");
exit();
