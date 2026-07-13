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
        $stmt_items = $object->conn->prepare("SELECT oi.*, p.pname, p.quant, p.img_url FROM order_items oi JOIN products p ON oi.productid = p.id WHERE oi.orderid = :orderid");
        $stmt_items->execute([':orderid' => $orderid]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        $subtotal = 0;
        ?>
        <div class="space-y-4">
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-3 text-sm text-gray-500 dark:text-gray-400">
                <span>Ordered on: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                <span class="font-bold text-gray-900 dark:text-white">Order #<?= $order['id'] ?></span>
            </div>
            
            <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto pr-1">
                <?php foreach ($items as $item) { 
                    $sub = $item['price'] * $item['qty'];
                    $subtotal += $sub;
                    ?>
                    <div class="flex items-center gap-3 py-3">
                        <img src="<?= htmlspecialchars($item['img_url'] ?: 'assets/image/product-image/default.png') ?>" alt="Fruit" class="w-12 h-12 object-contain rounded-lg bg-gray-50 dark:bg-gray-900 p-1">
                        <div class="flex-grow min-w-0">
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white truncate"><?= htmlspecialchars($item['pname']) ?></h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Qty: <?= htmlspecialchars($item['qty']) ?> | Size: <?= htmlspecialchars($item['quant']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-extrabold text-sm text-gray-900 dark:text-white">₹<?= htmlspecialchars($sub) ?></p>
                            <p class="text-xs text-gray-400">₹<?= htmlspecialchars($item['price']) ?> / pack</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-2 text-sm">
                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900 dark:text-white">₹<?= htmlspecialchars($subtotal) ?></span>
                </div>
                <?php 
                $discount = $subtotal - $order['total'];
                if ($discount > 0) { 
                ?>
                    <div class="flex justify-between text-green-600 dark:text-green-400 font-semibold">
                        <span>Discount Applied</span>
                        <span>- ₹<?= htmlspecialchars($discount) ?></span>
                    </div>
                <?php } ?>
                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                    <span>Delivery</span>
                    <span class="text-green-600 dark:text-green-400 font-bold">FREE</span>
                </div>
                <div class="flex justify-between border-t border-gray-100 dark:border-gray-700 pt-2 text-base font-bold text-gray-900 dark:text-white">
                    <span>Total Amount</span>
                    <span class="text-green-600 dark:text-green-400">₹<?= htmlspecialchars($order['total']) ?></span>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
