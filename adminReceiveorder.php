<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

// Handle Order Status Update
if (isset($_POST['update_status'])) {
    $orderid = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $stmt = $object->conn->prepare("UPDATE orders SET status = :status WHERE id = :id AND client_id = :client_id");
    $stmt->execute([':status' => $status, ':id' => $orderid, ':client_id' => CLIENT_ID]);

    // Fetch order owner details
    $ownerStmt = $object->conn->prepare("SELECT userid, order_id FROM orders WHERE id = :id AND client_id = :client_id");
    $ownerStmt->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);
    $ord = $ownerStmt->fetch(PDO::FETCH_ASSOC);

    if ($ord) {
        $customer_id = $ord['userid'];
        $longOrderId = $ord['order_id'] ?: $orderid;
        if ($status === 'Delivered') {
            $object->addNotification($customer_id, "Order Delivered", "Your order #{$longOrderId} has been delivered successfully. Enjoy your fresh fruits!", "order_delivered");
            $object->addNotification(null, "Order #{$longOrderId} Delivered", "Order #{$longOrderId} has been marked as delivered.", "delivered");
        } else if ($status === 'Out for Delivery' || $status === 'Shipped') {
            $object->addNotification($customer_id, "Order Out for Delivery", "Your order #{$longOrderId} is out for delivery with our executive.", "outfor_delivery");
        } else if ($status === 'Cancelled') {
            $object->addNotification($customer_id, "Order Cancelled", "Your order #{$longOrderId} has been cancelled.", "order_cancelled");
            $object->addNotification(null, "Order #{$longOrderId} Cancelled", "Order #{$longOrderId} has been cancelled.", "cancelled");
        } else {
            $object->addNotification($customer_id, "Order Status Update", "Your order #{$longOrderId} status has been updated to: {$status}.", "order_status_update");
        }
    }
    
    header("Location: adminReceiveorder.php");
    exit();
}

// Fetch all orders (joined with customer details)
$stmt = $object->conn->query("
    SELECT o.*, u.username, u.email 
    FROM orders o
    JOIN users u ON o.userid = u.id
    ORDER BY o.id DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders - Admin</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">
    
    <script src="jquery-1.9.1.min.js"></script>

    <style>
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .orders-table th, .orders-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .orders-table th {
            background: var(--border);
            font-weight: bold;
        }
        
        .status-select {
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-color);
            font-size: 13px;
            outline: none;
        }
        
        .btn-view {
            background: #16a34a;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
        }
        
        .btn-view:hover {
            background: #15803d;
        }

        /* Modal styling */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        
        .modal-content {
            background: var(--bg-card);
            color: var(--text-color);
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            border: 1px solid var(--border);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 24px;
        }
        
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        
        .btn-close {
            background: var(--border);
            color: var(--text-color);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body class="light">
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="table-div" style="width: 100%; max-width: 100%; box-sizing: border-box;">
                <h1>Customer Received Orders</h1>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                    Monitor checkout transactions and update dispatch stages.
                </p>

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Order Date</th>
                            <th>Order Status</th>
                            <th>Delivery Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)) { 
                            foreach ($orders as $row) {
                        ?>
                                <tr>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($row['order_id'] ?: '#' . $row['id']) ?></td>
                                    <td>
                                        <div style="font-weight: bold;"><?= htmlspecialchars($row['username']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></div>
                                    </td>
                                    <td style="font-weight: bold; color: #16a34a;">₹<?= htmlspecialchars($row['total']) ?></td>
                                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                    <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" action="" style="margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                            <select name="status" class="status-select" onchange="this.form.submit()">
                                                <option value="Pending Payment" <?= $row['status'] === 'Pending Payment' ? 'selected' : '' ?>>Pending Payment</option>
                                                <option value="Placed" <?= $row['status'] === 'Placed' ? 'selected' : '' ?>>Placed / Paid</option>
                                                <option value="Dispatched" <?= $row['status'] === 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                                <option value="Delivered" <?= $row['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <span style="font-weight: bold; font-size: 13px; color: <?= $row['delivery_status'] === 'Delivered' ? '#16a34a' : ($row['delivery_status'] === 'Out for Delivery' ? '#eab308' : '#64748b') ?>">
                                            <?= htmlspecialchars($row['delivery_status'] ?: 'Pending') ?>
                                        </span>
                                        <?php if (!empty($row['delivery_boy'])) { ?>
                                            <div style="font-size: 11px; color: var(--text-muted);">By: <?= htmlspecialchars($row['delivery_boy']) ?></div>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button onclick="viewDetails(<?= $row['id'] ?>)" class="btn-view">
                                            <span class="material-icons" style="font-size: 16px;">visibility</span>
                                            Details
                                        </button>
                                    </td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                    <span class="material-icons" style="font-size: 48px; display: block; margin-bottom: 10px;">receipt</span>
                                    No customer orders placed yet.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Items Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin: 0; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: #16a34a;">shopping_basket</span>
                    Ordered Items List
                </h3>
                <button onclick="closeModal()" class="modal-close">&times;</button>
            </div>
            <div id="modalBody" style="padding-top: 10px;">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button onclick="closeModal()" class="btn-close">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(orderId) {
            $('#detailsModal').css('display', 'flex');
            $('#modalBody').html('<div style="text-align: center; padding: 20px;">Loading order items...</div>');
            $('#modalBody').load('userOrderDetails.php?order_id=' + orderId);
        }
        
        function closeModal() {
            $('#detailsModal').hide();
        }
        
        $(window).click(function(e) {
            if ($(e.target).hasClass('modal')) {
                closeModal();
            }
        });
    </script>

    <script src="admin.js"></script>
</body>

</html>