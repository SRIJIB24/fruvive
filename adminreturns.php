<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

$msg = '';
$msg_type = 'success';

// Handle Approve / Reject Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $ret_id = intval($_GET['id']);
    $action = trim($_GET['action']);
    
    // Fetch return request details
    $ret_stmt = $object->conn->prepare("
        SELECT r.*, o.userid, o.order_id as long_order_id 
        FROM order_returns r
        JOIN orders o ON r.order_id = o.id
        WHERE r.id = :id AND r.client_id = :client_id
    ");
    $ret_stmt->execute([':id' => $ret_id, ':client_id' => CLIENT_ID]);
    $ret = $ret_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ret && $ret['status'] === 'Pending') {
        $order_id = $ret['order_id'];
        $product_id = $ret['product_id'];
        $qty = $ret['qty'];
        $customer_id = $ret['userid'];
        $long_order_id = $ret['long_order_id'] ?: '#' . $order_id;
        
        if ($action === 'approve') {
            // Start transaction
            $object->conn->beginTransaction();
            try {
                // Update return request status
                $up_stmt = $object->conn->prepare("UPDATE order_returns SET status = 'Approved' WHERE id = :id AND client_id = :client_id");
                $up_stmt->execute([':id' => $ret_id, ':client_id' => CLIENT_ID]);
                
                // Restock logic
                if ($product_id === null) {
                    // Whole order return: Restock all items
                    $items_stmt = $object->conn->prepare("SELECT productid, qty FROM order_items WHERE orderid = :orderid AND client_id = :client_id");
                    $items_stmt->execute([':orderid' => $order_id, ':client_id' => CLIENT_ID]);
                    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($items as $item) {
                        $up_stock = $object->conn->prepare("UPDATE stock_in SET total_quant = total_quant + :qty WHERE proid = :pid AND client_id = :client_id");
                        $up_stock->execute([':qty' => $item['qty'], ':pid' => $item['productid'], ':client_id' => CLIENT_ID]);
                    }
                    
                    // Mark order status as Returned
                    $order_up = $object->conn->prepare("UPDATE orders SET status = 'Returned' WHERE id = :id AND client_id = :client_id");
                    $order_up->execute([':id' => $order_id, ':client_id' => CLIENT_ID]);
                    
                    $object->addNotification($customer_id, "Return Approved", "Your return request for entire order {$long_order_id} has been approved. Refund initiated.", "return_approved");
                } else {
                    // Particular product return: Restock this item only
                    $up_stock = $object->conn->prepare("UPDATE stock_in SET total_quant = total_quant + :qty WHERE proid = :pid AND client_id = :client_id");
                    $up_stock->execute([':qty' => $qty, ':pid' => $product_id, ':client_id' => CLIENT_ID]);
                    
                    // Fetch product name
                    $pro_stmt = $object->conn->prepare("SELECT pname FROM products WHERE id = :pid");
                    $pro_stmt->execute([':pid' => $product_id]);
                    $pname = $pro_stmt->fetchColumn() ?: "item";
                    
                    $object->addNotification($customer_id, "Return Approved", "Your return request for '{$pname}' (Order {$long_order_id}) has been approved.", "return_approved");
                }
                
                $object->conn->commit();
                $msg = "Return request approved successfully and items restocked.";
                $msg_type = "success";
            } catch (Exception $e) {
                $object->conn->rollBack();
                $msg = "Transaction failed: " . $e->getMessage();
                $msg_type = "error";
            }
        } else if ($action === 'reject') {
            // Reject request
            $up_stmt = $object->conn->prepare("UPDATE order_returns SET status = 'Rejected' WHERE id = :id AND client_id = :client_id");
            $res = $up_stmt->execute([':id' => $ret_id, ':client_id' => CLIENT_ID]);
            
            if ($res) {
                if ($product_id === null) {
                    $object->addNotification($customer_id, "Return Rejected", "Your return request for order {$long_order_id} has been rejected.", "return_rejected");
                } else {
                    $pro_stmt = $object->conn->prepare("SELECT pname FROM products WHERE id = :pid");
                    $pro_stmt->execute([':pid' => $product_id]);
                    $pname = $pro_stmt->fetchColumn() ?: "item";
                    $object->addNotification($customer_id, "Return Rejected", "Your return request for '{$pname}' (Order {$long_order_id}) has been rejected.", "return_rejected");
                }
                $msg = "Return request rejected.";
                $msg_type = "success";
            } else {
                $msg = "Failed to reject return request.";
                $msg_type = "error";
            }
        }
    } else {
        $msg = "Invalid return request or already processed.";
        $msg_type = "error";
    }
}

// Fetch all return requests
$stmt = $object->conn->prepare("
    SELECT r.*, o.order_id as long_order_id, u.username, u.email, p.pname, p.quant 
    FROM order_returns r
    JOIN orders o ON r.order_id = o.id
    JOIN users u ON o.userid = u.id
    LEFT JOIN products p ON r.product_id = p.id
    WHERE r.client_id = :client_id
    ORDER BY r.id DESC
");
$stmt->execute([':client_id' => CLIENT_ID]);
$returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Returns - Admin</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">

    <script src="jquery-1.9.1.min.js"></script>
    
    <style>
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .badge-pending {
            background: #fef9c3;
            color: #ca8a04;
        }
        .badge-approved {
            background: #dcfce7;
            color: #15803d;
        }
        .badge-rejected {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 550;
            font-size: 14px;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body class="light">
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="table-div" style="width: 100%; max-width: 100%; box-sizing: border-box;">
                <div style="margin-bottom: 24px;">
                    <h1 style="margin: 0; font-size: 24px;">Customer Returns & Refunds</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Review, approve, or reject return logs and manage automatic inventory restocking.</p>
                </div>

                <?php if ($msg !== '') { ?>
                    <div class="alert alert-<?= $msg_type ?>">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php } ?>

                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Sl</th>
                            <th>Order ID</th>
                            <th>Target Item</th>
                            <th>Customer</th>
                            <th style="width: 80px;">Qty</th>
                            <th>Reason</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th style="text-align: right; width: 180px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($returns)) { 
                            $cnt = 0;
                            foreach ($returns as $row) {
                                $cnt++;
                                $status = $row['status'];
                                $badge_class = 'badge-pending';
                                if ($status === 'Approved') $badge_class = 'badge-approved';
                                if ($status === 'Rejected') $badge_class = 'badge-rejected';
                        ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($row['long_order_id']) ?></td>
                                    <td>
                                        <?php if ($row['product_id'] === null) { ?>
                                            <span style="color: #6b7280; font-style: italic; font-weight: bold;">Entire Order</span>
                                        <?php } else { ?>
                                            <span style="font-weight: bold; color: var(--text-color);"><?= htmlspecialchars($row['pname']) ?></span>
                                            <span style="font-size: 11px; color: var(--text-muted); block;">(<?= htmlspecialchars($row['quant']) ?>)</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: bold;"><?= htmlspecialchars($row['username']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></div>
                                    </td>
                                    <td><?= $row['qty'] ?></td>
                                    <td>
                                        <span style="font-weight: 550; color: #b91c1c; font-size: 13px;"><?= htmlspecialchars($row['reason']) ?></span>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-muted);">
                                        <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                            <?php if ($status === 'Pending') { ?>
                                                <a href="adminreturns.php?action=approve&id=<?= $row['id'] ?>" class="action-btn edit" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; text-decoration: none; cursor: pointer;">
                                                    <span class="material-icons" style="font-size: 14px;">check</span> Approve
                                                </a>
                                                <a href="adminreturns.php?action=reject&id=<?= $row['id'] ?>" class="action-btn delete" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; text-decoration: none; cursor: pointer;">
                                                    <span class="material-icons" style="font-size: 14px;">close</span> Reject
                                                </a>
                                            <?php } else { ?>
                                                <span style="color: var(--text-muted); font-size: 12px; font-style: italic;">Processed</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                    <span class="material-icons" style="font-size: 48px; display: block; margin-bottom: 10px;">assignment_late</span>
                                    No customer return requests found.
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
