<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

// Fetch all customers for multi-select
$customersList = $object->fetchCustomers();

// Handle Form Submit (Add)
if (isset($_POST['submit'])) {
    $code = strtoupper(trim($_POST['code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_cart_amount = floatval($_POST['min_cart_amount']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Process allowed users checkboxes
    $allowed_users = null;
    if (isset($_POST['allowed_users_checked']) && is_array($_POST['allowed_users_checked'])) {
        $allowed_users = implode(',', $_POST['allowed_users_checked']);
    }

    // Check unique code
    $chk = $object->conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = :code");
    $chk->execute([':code' => $code]);
    if ($chk->fetchColumn() > 0) {
        $_SESSION['msg'] = "Coupon code '{$code}' already exists!";
        $_SESSION['msg_type'] = "error";
    } else {
        $ok = $object->couponSubmit($code, $discount_type, $discount_value, $min_cart_amount, $expiry_date, $allowed_users, $active);
        if ($ok) {
            $_SESSION['msg'] = "Coupon '{$code}' created successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Failed to create coupon.";
            $_SESSION['msg_type'] = "error";
        }
    }
    header("Location: admincoupons.php");
    exit();
}

// Handle Form Update (Edit)
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $code = strtoupper(trim($_POST['code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_cart_amount = floatval($_POST['min_cart_amount']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Process allowed users checkboxes
    $allowed_users = null;
    if (isset($_POST['allowed_users_checked']) && is_array($_POST['allowed_users_checked'])) {
        $allowed_users = implode(',', $_POST['allowed_users_checked']);
    }

    // Check unique code (excluding current ID)
    $chk = $object->conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = :code AND id != :id");
    $chk->execute([':code' => $code, ':id' => $id]);
    if ($chk->fetchColumn() > 0) {
        $_SESSION['msg'] = "Coupon code '{$code}' already exists on another coupon!";
        $_SESSION['msg_type'] = "error";
    } else {
        $ok = $object->couponUpdate($id, $code, $discount_type, $discount_value, $min_cart_amount, $expiry_date, $allowed_users, $active);
        if ($ok) {
            $_SESSION['msg'] = "Coupon updated successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Failed to update coupon.";
            $_SESSION['msg_type'] = "error";
        }
    }
    header("Location: admincoupons.php");
    exit();
}

// Pagination logic
$limit = 10;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;
$totalRows = $object->countCoupons();
$totalPages = ceil($totalRows / $limit);

$coupons = $object->fetchCoupons($limit, $offset);

// Summarize stats for coupons
$totalCount = $totalRows;
$activeCount = (int)$object->conn->query("SELECT COUNT(*) FROM coupons WHERE active = 1 AND client_id = " . CLIENT_ID)->fetchColumn();
$expiredCount = (int)$object->conn->query("SELECT COUNT(*) FROM coupons WHERE expiry_date IS NOT NULL AND expiry_date < '" . date("Y-m-d") . "' AND client_id = " . CLIENT_ID)->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons Management - Admin</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">

    <script src="jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border);
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            height: 48px;
            width: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(22, 163, 74, 0.1);
            color: #16a34a;
        }
        .btn-primary {
            background: #16a34a;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            box-shadow: 0 4px 10px rgba(22, 163, 74, 0.15);
        }
        .user-select-list {
            border: 1px solid var(--border);
            border-radius: 8px;
            max-height: 150px;
            overflow-y: auto;
            padding: 8px 12px;
            background: var(--background);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .user-select-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-color);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 20px;
            background: #fee2e2;
            color: #991b1b;
        }
        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px;">Promo Coupons</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Manage your store's promotional coupon codes and user restrictions.</p>
                </div>
                <button type="button" onclick="openModalForAdd()" class="btn-primary">
                    <span class="material-icons" style="font-size: 18px;">add_circle</span> Add Coupon
                </button>
            </div>

            <!-- Stats Overview -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">local_offer</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Total Coupons</span>
                        <h3 style="margin: 4px 0 0 0; font-size: 20px; font-weight: 800;"><?= $totalCount ?> Coupons</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(22, 163, 74, 0.1); color: #16a34a;">
                        <span class="material-icons">task_alt</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Active Coupons</span>
                        <h3 style="margin: 4px 0 0 0; font-size: 20px; font-weight: 800;"><?= $activeCount ?> Active</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <span class="material-icons">event_busy</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Expired Coupons</span>
                        <h3 style="margin: 4px 0 0 0; font-size: 20px; font-weight: 800;"><?= $expiredCount ?> Expired</h3>
                    </div>
                </div>
            </div>

            <!-- Table Div -->
            <div class="table-div" style="width: 100%; overflow-x: auto; box-sizing: border-box; background: var(--bg-card, #fff); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Coupon Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Spend</th>
                            <th>Expiry Date</th>
                            <th>Allowed Users</th>
                            <th>Status</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($coupons)) { 
                            $cnt = $offset;
                            foreach ($coupons as $row) {
                                $cnt++;
                                $isExpired = $row['expiry_date'] !== null && $row['expiry_date'] < date('Y-m-d');
                                $statusLabel = $row['active'] == 1 && !$isExpired ? 'Active' : ($isExpired ? 'Expired' : 'Inactive');
                        ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td style="font-weight: bold; color: var(--text-color); font-size: 14px; letter-spacing: 0.5px;"><?= htmlspecialchars($row['code']) ?></td>
                                    <td style="text-transform: capitalize; font-weight: 500;"><?= htmlspecialchars($row['discount_type']) ?></td>
                                    <td style="font-weight: bold;"><?= $row['discount_type'] === 'percentage' ? $row['discount_value'].'%' : '₹'.$row['discount_value'] ?></td>
                                    <td>₹<?= htmlspecialchars($row['min_cart_amount']) ?></td>
                                    <td style="font-size: 12px; color: var(--text-muted);"><?= $row['expiry_date'] ? htmlspecialchars($row['expiry_date']) : 'Never Expires' ?></td>
                                    <td style="font-size: 12px; color: var(--text-muted);">
                                        <?php if (empty($row['allowed_users'])) { ?>
                                            All Registered Users
                                        <?php } else { 
                                            $userCount = count(explode(',', $row['allowed_users']));
                                            echo $userCount . ' selected user' . ($userCount > 1 ? 's' : '');
                                        } ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $statusLabel === 'Active' ? 'active' : '' ?>">
                                            <span class="material-icons" style="font-size: 12px;"><?= $statusLabel === 'Active' ? 'check_circle' : 'cancel' ?></span>
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" class="action-btn edit" onclick="couponedit(<?= $row['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">edit</span> Edit
                                            </button>
                                            <button type="button" class="action-btn delete" onclick="coupondelete(<?= $row['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">delete</span> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">No coupons found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1) { ?>
                <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px;">
                    <?php if ($page > 1) { ?>
                        <a href="?p=<?= $page - 1 ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-color); text-decoration: none; font-size: 13px; font-weight: bold;">Prev</a>
                    <?php } ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <a href="?p=<?= $i ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid <?= $i === $page ? '#16a34a' : 'var(--border)' ?>; background: <?= $i === $page ? '#16a34a' : 'var(--bg-card)' ?>; color: <?= $i === $page ? '#fff' : 'var(--text-color)' ?>; text-decoration: none; font-size: 13px; font-weight: bold;"><?= $i ?></a>
                    <?php } ?>
                    
                    <?php if ($page < $totalPages) { ?>
                        <a href="?p=<?= $page + 1 ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-color); text-decoration: none; font-size: 13px; font-weight: bold;">Next</a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Coupon Modal -->
    <div id="couponModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card, #fff); width: 100%; max-width: 480px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 1px solid var(--border); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0; font-size: 16px; font-weight: bold; color: var(--text-color);">Coupon Details</h3>
                <button type="button" onclick="closeModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div style="padding: 24px; max-height: calc(100vh - 120px); overflow-y: auto;">
                <form id="couponForm" action="" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Forms loaded by JS -->
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden helper containing list of customers -->
    <div id="customersCheckboxesHelper" style="display: none;">
        <?php foreach ($customersList as $cust) { ?>
            <label class="user-select-item">
                <input type="checkbox" name="allowed_users_checked[]" value="<?= $cust['id'] ?>">
                <span><?= htmlspecialchars($cust['username']) ?> (<?= htmlspecialchars($cust['email']) ?>)</span>
            </label>
        <?php } ?>
    </div>

    <script>
        function openModalForAdd() {
            $('#modalTitle').text('Create Promo Coupon');
            const userCheckboxes = $('#customersCheckboxesHelper').html();
            $('#couponForm').html(`
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Coupon Code</label>
                    <input type="text" name="code" placeholder="e.g. SAVE20" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%; font-weight: bold; text-transform: uppercase;">
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Discount Type</label>
                        <select name="discount_type" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                            <option value="percentage">Percentage (%)</option>
                            <option value="flat">Flat Amount (₹)</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Discount Value</label>
                        <input type="number" step="0.01" min="0" name="discount_value" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Min Spend Amount (₹)</label>
                        <input type="number" step="0.01" min="0" value="0.00" name="min_cart_amount" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Expiry Date</label>
                        <input type="date" name="expiry_date" style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Restrict Allowed User(s) <span style="font-size: 9px; font-weight: normal; text-transform: none;">(Leave unchecked for all users)</span></label>
                    <div class="user-select-list">
                        ${userCheckboxes}
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="active" id="active" checked style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="active" style="font-size: 13px; font-weight: 500; cursor: pointer; color: var(--text-color);">Activate coupon immediately</label>
                </div>

                <button type="submit" name="submit" class="btn-primary" style="margin-top: 12px; justify-content: center; width: 100%;">Create Coupon</button>
            `);
            $('#couponModal').css('display', 'flex');
        }

        function couponedit(id) {
            $('#modalTitle').text('Edit Coupon');
            $('#couponForm').load('admincouponEditDiv.php?id=' + id, function() {
                $('#couponModal').css('display', 'flex');
            });
        }

        function closeModal() {
            $('#couponModal').hide();
        }

        function coupondelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently remove this coupon and user discounts will no longer be available.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admincouponDelete.php?id=' + id;
                }
            });
        }

        // Show session flash alerts
        <?php if (isset($_SESSION['msg'])) { ?>
            Swal.fire({
                icon: '<?= $_SESSION['msg_type'] ?>',
                title: '<?= $_SESSION['msg'] ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
        <?php } ?>
    </script>
    <script src="admin.js"></script>
</body>

</html>
