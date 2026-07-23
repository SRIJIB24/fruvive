<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

// Handle Delivery Assignment Submit
if (isset($_POST['assign_delivery'])) {
    $orderid = intval($_POST['order_id']);
    $delivery_boy = trim($_POST['delivery_boy']);
    $delivery_status = $_POST['delivery_status'];
    
    $stmt = $object->conn->prepare("UPDATE orders SET delivery_boy = :boy, delivery_status = :status WHERE id = :id AND client_id = :client_id");
    $stmt->execute([':boy' => $delivery_boy, ':status' => $delivery_status, ':id' => $orderid, ':client_id' => CLIENT_ID]);
    
    // Fetch order owner and order_id
    $ownerStmt = $object->conn->prepare("SELECT userid, order_id FROM orders WHERE id = :id AND client_id = :client_id");
    $ownerStmt->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);
    $ord = $ownerStmt->fetch(PDO::FETCH_ASSOC);

    if ($ord) {
        $customer_id = $ord['userid'];
        $longOrderId = $ord['order_id'] ?: $orderid;
        if ($delivery_status === 'Out for Delivery') {
            $object->addNotification($customer_id, "Order Out for Delivery", "Your order #{$longOrderId} is out for delivery with {$delivery_boy}.", "outfor_delivery");
        } else if ($delivery_status === 'Delivered') {
            $object->addNotification($customer_id, "Order Delivered", "Your order #{$longOrderId} has been delivered successfully. Enjoy your fresh fruits!", "order_delivered");
            $object->addNotification(null, "Order #{$longOrderId} Delivered", "Order #{$longOrderId} has been successfully delivered by {$delivery_boy}.", "delivered");
        } else {
            $object->addNotification($customer_id, "Order Status Update", "Your order #{$longOrderId} status has been updated to: {$delivery_status}.", "order_status_update");
        }
    }

    // Auto-update main order status to 'Delivered' if delivery is complete
    if ($delivery_status === 'Delivered') {
        $stmt_status = $object->conn->prepare("UPDATE orders SET status = 'Delivered' WHERE id = :id AND client_id = :client_id");
        $stmt_status->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);
    }
    
    header("Location: adminAssignDelivery.php");
    exit();
}

// Fetch all orders
$stmt = $object->conn->query("
    SELECT o.*, u.username 
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
    <title>Assign Delivery - Admin</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">
    
    <script src="jquery-1.9.1.min.js"></script>

    <style>
        .delivery-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .delivery-table th, .delivery-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .delivery-table th {
            background: var(--border);
            font-weight: bold;
        }
        
        .boy-input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-color);
            outline: none;
            font-size: 13px;
            width: 180px;
        }
        
        .status-select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-color);
            outline: none;
            font-size: 13px;
        }
        
        .btn-save {
            background: #16a34a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            transition: background 0.2s;
        }
        
        .btn-save:hover {
            background: #15803d;
        }
        
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 12px;
        }
        
        .badge-unassigned {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-assigned {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .badge-shipping {
            background: #fef08a;
            color: #a16207;
        }
        
        .badge-delivered {
            background: #dcfce7;
            color: #15803d;
        }
    </style>
</head>

<body class="light">
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="table-div" style="width: 100%; max-width: 100%; box-sizing: border-box;">
                <h1>Assign & Track Delivery Boy Logistics</h1>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                    Allocate delivery personnel to pending orders and monitor shipping stages.
                </p>

                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order Status</th>
                            <th>Delivery Assignment Status</th>
                            <th>Delivery Boy Name</th>
                            <th>Delivery Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)) { 
                            foreach ($orders as $row) {
                                $del_status = $row['delivery_status'] ?: 'Pending';
                                
                                // Determine assignment badge
                                if (empty($row['delivery_boy'])) {
                                    $badge_class = "badge-unassigned";
                                    $badge_text = "Unassigned";
                                } else {
                                    if ($del_status === 'Delivered') {
                                        $badge_class = "badge-delivered";
                                        $badge_text = "Completed";
                                    } else if ($del_status === 'Out for Delivery') {
                                        $badge_class = "badge-shipping";
                                        $badge_text = "Out for Delivery";
                                    } else {
                                        $badge_class = "badge-assigned";
                                        $badge_text = "Assigned";
                                    }
                                }
                        ?>
                                <tr>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($row['order_id'] ?: '#' . $row['id']) ?></td>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($row['username']) ?></td>
                                    <td>
                                        <span style="font-weight: bold; font-size: 13px; color: <?= $row['status'] === 'Delivered' ? '#16a34a' : ($row['status'] === 'Placed' ? '#2563eb' : '#eab308') ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
                                    </td>
                                    
                                    <!-- Delivery Boy Assignment Form -->
                                    <form method="POST" action="">
                                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                        
                                        <td>
                                            <input type="text" name="delivery_boy" value="<?= htmlspecialchars($row['delivery_boy'] ?? '') ?>" placeholder="Enter Driver Name" class="boy-input" required>
                                        </td>
                                        <td>
                                            <select name="delivery_status" class="status-select">
                                                <option value="Pending" <?= $del_status === 'Pending' ? 'selected' : '' ?>>Pending / Assigned</option>
                                                <option value="Out for Delivery" <?= $del_status === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                                <option value="Delivered" <?= $del_status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                            </select>
                                        </td>
                                        <td style="text-align: right;">
                                            <button type="submit" name="assign_delivery" class="btn-save">
                                                <span class="material-icons" style="font-size: 16px;">save</span>
                                                Save
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                    <span class="material-icons" style="font-size: 48px; display: block; margin-bottom: 10px;">local_shipping</span>
                                    No customer orders found to assign.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>

</html>
