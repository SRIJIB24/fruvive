<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];
$isAdmin = ($object->userlvl === -1);

if (isset($_GET['order_id'])) {
    $orderid = intval($_GET['order_id']);
    
    if ($isAdmin) {
        // Admins can see any order details
        $stmt = $object->conn->prepare("SELECT * FROM orders WHERE id = :id AND client_id = :client_id");
        $stmt->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);
    } else {
        // Customers can only see their own order details
        $stmt = $object->conn->prepare("SELECT * FROM orders WHERE id = :id AND userid = :userid AND client_id = :client_id");
        $stmt->execute([':id' => $orderid, ':userid' => $userid, ':client_id' => CLIENT_ID]);
    }
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        $stmt_items = $object->conn->prepare("SELECT oi.*, p.pname, p.quant, p.img_url FROM order_items oi JOIN products p ON oi.productid = p.id WHERE oi.orderid = :orderid");
        $stmt_items->execute([':orderid' => $orderid]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        $order_time = strtotime($order['created_at']);
        $time_diff = time() - $order_time;
        $can_return = (!$isAdmin && $time_diff <= 8 * 3600 && $order['status'] !== 'Cancelled' && $order['status'] !== 'Returned');

        // Fetch user address
        $address = $object->fetchSelectAddress($order['userid']);

        // Fetch returns data
        $stmt_returns = $object->conn->prepare("SELECT * FROM order_returns WHERE order_id = :order_id AND client_id = :client_id");
        $stmt_returns->execute([':order_id' => $orderid, ':client_id' => CLIENT_ID]);
        $returns_list = $stmt_returns->fetchAll(PDO::FETCH_ASSOC);
        
        $returns_map = [];
        $has_item_returns = false;
        foreach ($returns_list as $r) {
            $returns_map[$r['product_id']] = $r;
            if ($r['product_id'] !== null) {
                $has_item_returns = true;
            }
        }

        $can_return_whole_order = ($can_return && !$has_item_returns && !isset($returns_map[null]));

        // Stepper Status Definition
        $status = $order['status'];
        $delivery_status = isset($order['delivery_status']) ? $order['delivery_status'] : '';
        $delivery_boy = isset($order['delivery_boy']) ? $order['delivery_boy'] : '';

        $steps = [];
        if ($status === 'Cancelled') {
            $steps = [
                [
                    'label' => 'Order Placed',
                    'sublabel' => date('d M, Y', $order_time),
                    'done' => true,
                    'active' => false
                ],
                [
                    'label' => 'Cancelled',
                    'sublabel' => 'Cancelled',
                    'done' => true,
                    'active' => true,
                    'is_cancel' => true
                ]
            ];
        } elseif ($status === 'Returned') {
            $steps = [
                [
                    'label' => 'Order Placed',
                    'sublabel' => date('d M, Y', $order_time),
                    'done' => true,
                    'active' => false
                ],
                [
                    'label' => 'Delivered',
                    'sublabel' => 'Delivered',
                    'done' => true,
                    'active' => false
                ],
                [
                    'label' => 'Returned',
                    'sublabel' => 'Return Approved',
                    'done' => true,
                    'active' => true,
                    'is_return' => true
                ]
            ];
        } else {
            $steps = [
                [
                    'label' => 'Ordered',
                    'sublabel' => date('d M, Y', $order_time),
                    'done' => ($status !== 'Pending Payment'),
                    'active' => ($status === 'Placed')
                ],
                [
                    'label' => 'Packed & Shipped',
                    'sublabel' => ($status === 'Dispatched' || $status === 'Delivered') ? 'Dispatched' : 'Pending',
                    'done' => ($status === 'Dispatched' || $status === 'Delivered'),
                    'active' => ($status === 'Dispatched')
                ],
                [
                    'label' => 'Out for Delivery',
                    'sublabel' => ($delivery_status === 'Out for Delivery' || $status === 'Delivered') ? 'Out for delivery' : 'Pending',
                    'done' => ($delivery_status === 'Out for Delivery' || $status === 'Delivered'),
                    'active' => ($delivery_status === 'Out for Delivery')
                ],
                [
                    'label' => 'Delivered',
                    'sublabel' => ($status === 'Delivered') ? 'Delivered' : 'Pending',
                    'done' => ($status === 'Delivered'),
                    'active' => ($status === 'Delivered')
                ]
            ];
        }

        $subtotal = 0;
        ?>
        <div class="space-y-6">
            <!-- 2-Column Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Shipping details -->
                <div class="bg-gray-50/50 dark:bg-gray-900/20 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 p-4 space-y-2">
                    <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-1">
                        <span class="material-icons text-sm">local_shipping</span> Delivery Address
                    </h4>
                    <?php if ($address) { ?>
                        <p class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-1.5">
                            <?= htmlspecialchars($address['name']) ?>
                            <span class="text-[9px] uppercase font-bold tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-500 px-2 py-0.5 rounded">
                                <?= htmlspecialchars($address['type']) ?>
                            </span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            <?= htmlspecialchars($address['address']) ?>, <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['pincode']) ?>
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">Phone: <?= htmlspecialchars($address['phone']) ?></p>
                    <?php } else { ?>
                        <p class="text-xs text-gray-400 italic">No delivery address selected for this order.</p>
                    <?php } ?>
                </div>

                <!-- Price breakdown -->
                <div class="bg-gray-50/50 dark:bg-gray-900/20 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 p-4 space-y-2 text-xs">
                    <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-1 mb-1">
                        <span class="material-icons text-sm">payment</span> Payment Details
                    </h4>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Payment Mode:</span>
                        <span class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($order['payment_method']) ?></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 dark:border-gray-700/40 pt-1.5 mt-1.5">
                        <span class="text-gray-500">Order Value:</span>
                        <span class="font-bold text-gray-800 dark:text-white">₹<?= htmlspecialchars($order['total']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Stepper Progress Tracker -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 p-5">
                <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-1">
                    <span class="material-icons text-sm">timeline</span> Order Shipment Progress
                </h4>
                
                <div class="relative flex justify-between items-center w-full px-2">
                    <!-- Tracker Bar Background -->
                    <div class="absolute left-0 right-0 top-4 h-0.5 bg-gray-100 dark:bg-gray-700 -z-10 mx-6"></div>
                    <!-- Tracker Bar Active -->
                    <?php 
                    $doneCount = 0;
                    foreach ($steps as $s) { if ($s['done']) $doneCount++; }
                    $percent = ($doneCount > 1) ? (($doneCount - 1) / (count($steps) - 1)) * 100 : 0;
                    $lineColor = ($status === 'Cancelled') ? 'bg-red-500' : (($status === 'Returned') ? 'bg-purple-500' : 'bg-green-500');
                    ?>
                    <div class="absolute left-0 top-4 h-0.5 <?= $lineColor ?> -z-10 transition-all duration-500 mx-6" style="width: calc(<?= $percent ?>% - 12px)"></div>
                    
                    <?php foreach ($steps as $idx => $s) { 
                        $circleColor = 'bg-gray-100 text-gray-450 dark:bg-gray-700 dark:text-gray-500';
                        $textColor = 'text-gray-500 dark:text-gray-400';
                        if ($s['done']) {
                            if (isset($s['is_cancel'])) {
                                $circleColor = 'bg-red-500 text-white';
                                $textColor = 'text-red-650 dark:text-red-400 font-bold';
                            } elseif (isset($s['is_return'])) {
                                $circleColor = 'bg-purple-500 text-white';
                                $textColor = 'text-purple-650 dark:text-purple-400 font-bold';
                            } else {
                                $circleColor = 'bg-green-500 text-white';
                                $textColor = 'text-green-650 dark:text-green-405 font-bold';
                            }
                        }
                        if ($s['active']) {
                            $circleColor .= ' ring-4 ring-offset-2 ring-offset-white dark:ring-offset-gray-800 ' . (isset($s['is_cancel']) ? 'ring-red-500/30' : (isset($s['is_return']) ? 'ring-purple-500/30' : 'ring-green-500/30'));
                        }
                    ?>
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 <?= $circleColor ?>">
                                <?php if (isset($s['is_cancel'])) { ?>
                                    <span class="material-icons text-sm">close</span>
                                <?php } elseif (isset($s['is_return'])) { ?>
                                    <span class="material-icons text-sm">assignment_return</span>
                                <?php } else { ?>
                                    <?php if ($s['done']) { ?>
                                        <span class="material-icons text-sm">check</span>
                                    <?php } else { ?>
                                        <?= $idx + 1 ?>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="text-center mt-2 px-1">
                                <p class="text-[10px] font-extrabold tracking-tight <?= $textColor ?>"><?= htmlspecialchars($s['label']) ?></p>
                                <p class="text-[9px] text-gray-400 mt-0.5 leading-none"><?= htmlspecialchars($s['sublabel']) ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Ordered Items Details List -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-icons text-sm">view_list</span> Items Ordered
                </h4>
                
                <div class="bg-white dark:bg-gray-800 border border-gray-250/70 dark:border-gray-700/80 rounded-2xl divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto px-4 shadow-sm">
                    <?php foreach ($items as $item) { 
                        $sub = $item['price'] * $item['qty'];
                        $subtotal += $sub;
                        
                        // Check if this item is in the returns process
                        $item_returned = isset($returns_map[$item['productid']]);
                        $item_ret_status = $item_returned ? $returns_map[$item['productid']]['status'] : '';
                        
                        $is_order_returned = isset($returns_map[null]);
                        $order_ret_status = $is_order_returned ? $returns_map[null]['status'] : '';
                        ?>
                        <div class="flex items-center gap-4 py-4">
                            <img src="<?= htmlspecialchars($item['img_url'] ?: 'assets/image/product-image/default.png') ?>" alt="Fruit" class="w-16 h-16 object-contain rounded-xl bg-gray-50 dark:bg-gray-900 p-1 border border-gray-100 dark:border-gray-800">
                            <div class="flex-grow min-w-0">
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white truncate"><?= htmlspecialchars($item['pname']) ?></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Qty: <?= htmlspecialchars($item['qty']) ?> | Size: <?= htmlspecialchars($item['quant']) ?></p>
                                
                                <!-- Return Status Badges -->
                                <?php if ($is_order_returned) { ?>
                                    <span class="inline-flex items-center gap-0.5 text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400 mt-1">
                                        <span class="material-icons text-[12px]">keyboard_return</span>
                                        Order Return: <?= htmlspecialchars($order_ret_status) ?>
                                    </span>
                                <?php } elseif ($item_returned) { ?>
                                    <span class="inline-flex items-center gap-0.5 text-[10px] font-bold px-2 py-0.5 rounded 
                                        <?= $item_ret_status === 'Approved' ? 'bg-green-50 text-green-600 dark:bg-green-950/20 dark:text-green-400' 
                                            : ($item_ret_status === 'Rejected' ? 'bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400' 
                                                : 'bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400') ?> mt-1">
                                        <span class="material-icons text-[12px]">keyboard_return</span>
                                        Item Return: <?= htmlspecialchars($item_ret_status) ?>
                                    </span>
                                <?php } ?>

                                <!-- Actions -->
                                <div class="flex gap-2 mt-2 items-center">
                                    <?php if (!$isAdmin) { ?>
                                        <button onclick="openOrderReview(<?= $item['productid'] ?>, '<?= htmlspecialchars($item['pname'], ENT_QUOTES) ?>')" class="text-[10px] font-bold text-green-600 hover:text-green-700 bg-green-50 dark:bg-green-950/20 hover:bg-green-150 px-2 py-0.5 rounded transition">
                                            Rate & Review
                                        </button>
                                    <?php } ?>
                                    <?php if ($can_return && !$item_returned && !$is_order_returned) { ?>
                                        <button onclick="initiateProductReturn(<?= $order['id'] ?>, <?= $item['productid'] ?>, '<?= htmlspecialchars($item['pname'], ENT_QUOTES) ?>', <?= $item['qty'] ?>)" class="text-[10px] font-bold text-red-650 hover:text-red-750 bg-red-50 dark:bg-red-950/20 hover:bg-red-150 px-2 py-0.5 rounded transition">
                                            Return Item
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-extrabold text-sm text-gray-900 dark:text-white">₹<?= htmlspecialchars($sub) ?></p>
                                <p class="text-[10px] text-gray-400">₹<?= htmlspecialchars($item['price']) ?> / pack</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Footer Return Entire Order / Status Notice -->
            <?php if ($can_return_whole_order) { ?>
                <div class="flex justify-center pt-2">
                    <button onclick="initiateWholeOrderReturn(<?= $order['id'] ?>)" class="w-full bg-red-50 hover:bg-red-100 dark:bg-red-950/20 text-red-600 dark:text-red-400 font-bold py-2.5 rounded-xl transition text-xs flex items-center justify-center gap-1 shadow-sm border border-red-200/50 dark:border-red-900/30">
                        <span class="material-icons text-base">assignment_return</span>
                        Return Entire Order
                    </button>
                </div>
            <?php } elseif (isset($returns_map[null])) { ?>
                <div class="p-3 bg-purple-50 dark:bg-purple-950/20 border border-purple-200/40 rounded-xl text-center">
                    <p class="text-xs font-bold text-purple-700 dark:text-purple-400 flex items-center justify-center gap-1">
                        <span class="material-icons text-sm">info</span>
                        Return request for the entire order is currently: <span class="underline uppercase"><?= htmlspecialchars($returns_map[null]['status']) ?></span>
                    </p>
                </div>
            <?php } ?>
        </div>
        <?php
    }
}
?>
