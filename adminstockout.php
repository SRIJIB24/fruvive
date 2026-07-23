<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

// Handle Manual Stock Out Logging
if (isset($_POST['log_stockout'])) {
    $proid = intval($_POST['proid']);
    $qty = intval($_POST['qty']);
    $reason = trim($_POST['reason']);
    
    if ($proid <= 0 || $qty <= 0 || empty($reason)) {
        $_SESSION['msg'] = "Please select a valid product, enter a positive quantity, and specify a reason.";
        $_SESSION['msg_type'] = "error";
    } else {
        // Fetch unit pack price and stock
        $stmt_check = $object->conn->prepare("SELECT s.total_quant, s.pack_price FROM stock_in s WHERE s.proid = :pid AND s.client_id = :client_id");
        $stmt_check->execute([':pid' => $proid, ':client_id' => CLIENT_ID]);
        $prodStock = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$prodStock) {
            $_SESSION['msg'] = "Selected product does not have active stock records.";
            $_SESSION['msg_type'] = "error";
        } else if ($prodStock['total_quant'] < $qty) {
            $_SESSION['msg'] = "Insufficient stock! Current stock: " . $prodStock['total_quant'];
            $_SESSION['msg_type'] = "error";
        } else {
            // Deduct from stock_in
            $up = $object->conn->prepare("UPDATE stock_in SET total_quant = total_quant - :reduce WHERE proid = :pid AND client_id = :client_id");
            $up->execute([':reduce' => $qty, ':pid' => $proid, ':client_id' => CLIENT_ID]);

            // Add record to stock_out ledger
            $object->addStockOut($proid, $qty, $reason, $prodStock['pack_price']);
            
            $_SESSION['msg'] = "Logged " . $qty . " items as '" . htmlspecialchars($reason) . "' successfully!";
            $_SESSION['msg_type'] = "success";
        }
    }
    header("Location: adminstockout.php");
    exit();
}

// Fetch filters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$reasonFilter = isset($_GET['reason']) ? trim($_GET['reason']) : '';
$search_cat = isset($_GET['search_cat']) ? $_GET['search_cat'] : '';
$search_pname = isset($_GET['search_pname']) ? trim($_GET['search_pname']) : '';

// Build Query
$sqlCount = "
    SELECT COUNT(*) 
    FROM stock_out so
    JOIN products p ON so.proid = p.id
    LEFT JOIN stock_in si ON p.id = si.proid
    LEFT JOIN category c ON si.catid = c.id
    WHERE so.client_id = :client_id 
      AND DATE(so.created_at) BETWEEN :start_date AND :end_date
";
$sqlSelect = "
    SELECT so.*, p.pname, p.img_url, p.quant as pack_size, c.cname 
    FROM stock_out so
    JOIN products p ON so.proid = p.id
    LEFT JOIN stock_in si ON p.id = si.proid
    LEFT JOIN category c ON si.catid = c.id
    WHERE so.client_id = :client_id 
      AND DATE(so.created_at) BETWEEN :start_date AND :end_date
";
$params = [
    ':client_id' => CLIENT_ID,
    ':start_date' => $startDate,
    ':end_date' => $endDate
];

if ($reasonFilter !== '') {
    $sqlCount .= " AND so.reason LIKE :reason";
    $sqlSelect .= " AND so.reason LIKE :reason";
    $params[':reason'] = '%' . $reasonFilter . '%';
}
if ($search_cat !== '') {
    $sqlCount .= " AND si.catid = :search_cat";
    $sqlSelect .= " AND si.catid = :search_cat";
    $params[':search_cat'] = $search_cat;
}
if ($search_pname !== '') {
    $sqlCount .= " AND p.pname LIKE :search_pname";
    $sqlSelect .= " AND p.pname LIKE :search_pname";
    $params[':search_pname'] = '%' . $search_pname . '%';
}

$stmtCount = $object->conn->prepare($sqlCount);
$stmtCount->execute($params);
$totalRows = (int)$stmtCount->fetchColumn();

// Pagination
$limit = 10;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalRows / $limit);

$sqlSelect .= " ORDER BY so.id DESC LIMIT :limit OFFSET :offset";
$stmtSelect = $object->conn->prepare($sqlSelect);
foreach ($params as $k => $v) {
    $stmtSelect->bindValue($k, $v);
}
$stmtSelect->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtSelect->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtSelect->execute();
$reportData = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

// Summarize stats for all filtered items (not just current page)
$sqlStats = "
    SELECT so.qty, so.sale_price, so.reason
    FROM stock_out so
    JOIN products p ON so.proid = p.id
    LEFT JOIN stock_in si ON p.id = si.proid
    WHERE so.client_id = :client_id 
      AND DATE(so.created_at) BETWEEN :start_date AND :end_date
";
if ($reasonFilter !== '') {
    $sqlStats .= " AND so.reason LIKE :reason";
}
if ($search_cat !== '') {
    $sqlStats .= " AND si.catid = :search_cat";
}
if ($search_pname !== '') {
    $sqlStats .= " AND p.pname LIKE :search_pname";
}
$stmtStats = $object->conn->prepare($sqlStats);
$stmtStats->execute($params);
$allFilteredRows = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

$totalItems = 0;
$totalValue = 0;
$saleCount = 0;
$adjustCount = 0;
foreach ($allFilteredRows as $row) {
    $totalItems += $row['qty'];
    $totalValue += $row['qty'] * $row['sale_price'];
    if (strpos($row['reason'], 'Sale') !== false) {
        $saleCount += $row['qty'];
    } else {
        $adjustCount += $row['qty'];
    }
}

// Fetch categories list
$value1 = $object->category();

// Fetch products list for manual logging
$prostmt = $object->conn->prepare("SELECT p.id, p.pname, si.total_quant FROM products p JOIN stock_in si ON p.id = si.proid WHERE p.client_id = :client_id ORDER BY p.pname ASC");
$prostmt->execute([':client_id' => CLIENT_ID]);
$productsList = $prostmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out Ledger - Admin</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">
    <link rel="stylesheet" href="adminstockin.css"> 

    <script src="jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .product-thumbnail {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border);
        }
        
        .reason-badge {
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
        .reason-badge.sale {
            background: #dcfce7;
            color: #166534;
        }
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
        .filter-bar {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border);
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }
        .filter-row {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
        }
        .filter-row label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--text-muted);
        }
        .filter-row input, .filter-row select {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 13px;
            background: var(--background);
            color: var(--text-color);
            outline: none;
            width: 100%;
            box-sizing: border-box;
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
        .btn-secondary {
            background: var(--border, #e5e7eb);
            color: var(--text-color);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            text-decoration: none;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="light">
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px;">Stock Out Ledger & Report</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Track inventory sales, loss adjustments, and log wastage.</p>
                </div>
                <button type="button" onclick="openModalForLoss()" style="background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);">
                    <span class="material-icons" style="font-size: 18px;">remove_circle_outline</span> Log Stock Loss
                </button>
            </div>

            <!-- Stats Overview -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <span class="material-icons">inventory</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Total Stock Removed</span>
                        <h3 style="margin: 4px 0 0 0; font-size: 20px; font-weight: 800;"><?= number_format($totalItems) ?> Items</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(249, 115, 22, 0.1); color: #f97316;">
                        <span class="material-icons">payments</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Total Value Removed</span>
                        <h3 style="margin: 4px 0 0 0; font-size: 20px; font-weight: 800;">₹<?= number_format($totalValue, 2) ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <span class="material-icons">shopping_bag</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Sales vs Shrinkage</span>
                        <h3 style="margin: 4px 0 0 0; font-size: 14px; font-weight: bold; color: var(--text-color);">
                            Sales: <?= $saleCount ?> | Loss: <?= $adjustCount ?>
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="filter-bar">
                <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 16px; width: 100%; align-items: flex-end; margin: 0;">
                    <div class="filter-row" style="flex: 1; min-width: 130px;">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div class="filter-row" style="flex: 1; min-width: 130px;">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <div class="filter-row" style="flex: 1; min-width: 150px;">
                        <label>Category</label>
                        <select name="search_cat">
                            <option value="">All Categories</option>
                            <?php foreach ($value1 as $cat) { ?>
                                <option value="<?= $cat['id'] ?>" <?= $search_cat == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['cname']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-row" style="flex: 2; min-width: 180px;">
                        <label>Search Product</label>
                        <input type="text" name="search_pname" value="<?= htmlspecialchars($search_pname) ?>" placeholder="Search product name...">
                    </div>
                    <div class="filter-row" style="flex: 1; min-width: 140px;">
                        <label>Reason Filter</label>
                        <select name="reason">
                            <option value="">All Reasons</option>
                            <option value="Sale" <?php echo $reasonFilter === 'Sale' ? 'selected' : ''; ?>>Sales only</option>
                            <option value="Wastage" <?php echo $reasonFilter === 'Wastage' ? 'selected' : ''; ?>>Wastage only</option>
                            <option value="Damaged" <?php echo $reasonFilter === 'Damaged' ? 'selected' : ''; ?>>Damaged only</option>
                            <option value="Expired" <?php echo $reasonFilter === 'Expired' ? 'selected' : ''; ?>>Expired only</option>
                            <option value="Stock Audit Correction" <?php echo $reasonFilter === 'Stock Audit Correction' ? 'selected' : ''; ?>>Audit Corrections only</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn-primary">
                            <span class="material-icons" style="font-size: 16px;">filter_alt</span> Filter
                        </button>
                        <a href="adminstockout.php" class="btn-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Ledger Table -->
            <div class="table-div" style="width: 100%; overflow-x: auto; box-sizing: border-box; background: var(--bg-card, #fff); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category Name</th>
                            <th>Pack Size</th>
                            <th>Quantity Removed</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Reason / Transaction</th>
                            <th>Date Logged</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($reportData)) { 
                            $cnt = $offset;
                            foreach ($reportData as $row) {
                                $cnt++;
                                $isSale = strpos($row['reason'], 'Sale') !== false;
                                $rowTotal = $row['qty'] * $row['sale_price'];
                        ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($row['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product" class="product-thumbnail">
                                    </td>
                                    <td style="font-weight: bold; color: var(--text-color);"><?= htmlspecialchars($row['pname']) ?></td>
                                    <td><?= htmlspecialchars($row['cname'] ?: 'Uncategorized') ?></td>
                                    <td><?= htmlspecialchars($row['pack_size']) ?></td>
                                    <td style="font-weight: bold; color: #f97316;"><?= htmlspecialchars($row['qty']) ?></td>
                                    <td>₹<?= htmlspecialchars($row['sale_price']) ?></td>
                                    <td style="font-weight: bold; color: #16a34a;">₹<?= number_format($rowTotal, 2) ?></td>
                                    <td>
                                        <span class="reason-badge <?= $isSale ? 'sale' : '' ?>">
                                            <span class="material-icons" style="font-size: 12px;"><?= $isSale ? 'shopping_cart' : 'warning' ?></span>
                                            <?= htmlspecialchars($row['reason']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($row['created_at']) ?></td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 24px;">No stock out records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1) { 
                    $searchParams = '&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate);
                    if ($reasonFilter !== '') {
                        $searchParams .= '&reason=' . urlencode($reasonFilter);
                    }
                    if ($search_cat !== '') {
                        $searchParams .= '&search_cat=' . urlencode($search_cat);
                    }
                    if ($search_pname !== '') {
                        $searchParams .= '&search_pname=' . urlencode($search_pname);
                    }
                ?>
                <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px;">
                    <?php if ($page > 1) { ?>
                        <a href="?p=<?= $page - 1 ?><?= $searchParams ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-color); text-decoration: none; font-size: 13px; font-weight: bold;">Prev</a>
                    <?php } ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <a href="?p=<?= $i ?><?= $searchParams ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid <?= $i === $page ? '#16a34a' : 'var(--border)' ?>; background: <?= $i === $page ? '#16a34a' : 'var(--bg-card)' ?>; color: <?= $i === $page ? '#fff' : 'var(--text-color)' ?>; text-decoration: none; font-size: 13px; font-weight: bold;"><?= $i ?></a>
                    <?php } ?>
                    
                    <?php if ($page < $totalPages) { ?>
                        <a href="?p=<?= $page + 1 ?><?= $searchParams ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-color); text-decoration: none; font-size: 13px; font-weight: bold;">Next</a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Manual Log Loss Modal Overlay -->
    <div id="lossModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card, #fff); width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 1px solid var(--border); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: var(--text-color);">Log Stock Loss / Wastage</h3>
                <button type="button" onclick="closeModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div style="padding: 24px;">
                <form id="lossForm" action="" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Select Product</label>
                        <select name="proid" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                            <option value="">--SELECT PRODUCT--</option>
                            <?php foreach ($productsList as $p) { ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['pname']) ?> (Stock: <?= $p['total_quant'] ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Quantity to Remove</label>
                        <input type="number" name="qty" min="1" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Reason / Category of Loss</label>
                        <select name="reason" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                            <option value="Wastage">Wastage / Spoiled</option>
                            <option value="Damaged">Damaged in Transit</option>
                            <option value="Expired">Expired Stock</option>
                            <option value="Stock Audit Correction">Stock Audit Correction</option>
                        </select>
                    </div>
                    <button type="submit" name="log_stockout" class="btn-primary" style="margin-top: 12px; justify-content: center; width: 100%;">
                        <span class="material-icons" style="font-size: 16px;">remove_circle_outline</span> Log Inventory Reduction
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModalForLoss() {
            $('#lossModal').css('display', 'flex');
        }

        function closeModal() {
            $('#lossModal').hide();
        }

        $('#lossForm').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Confirm Stock Reduction?',
                text: 'This action will permanently deduct stock from warehouse records.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reduce stock!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

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
