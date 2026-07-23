<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();
if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

//fetch category names
$value1 = $object->category();

//form submit
if (isset($_POST['submit'])) {
    $object->catid      = $_POST['catid'];
    $object->proid      = $_POST['proid'];
    $object->date       = $_POST['date'];
    $object->packquant  = $_POST['packquant'];
    $object->packprice  = $_POST['packprice'];
    $object->totalquant = $_POST['totalquant'];

    if (floatval($object->packprice) < 0 || intval($object->totalquant) < 0) {
        $_SESSION['msg'] = 'Price per pack and Total packs cannot be negative!';
        $_SESSION['msg_type'] = 'error';
        header("Location: adminstockin.php");
        exit();
    }

    $object->stockIn();

    $_SESSION['msg'] = 'Stock intake recorded successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: adminstockin.php");
    exit();
}

// Search filters
$search_cat = isset($_GET['search_cat']) ? $_GET['search_cat'] : '';
$search_pname = isset($_GET['search_pname']) ? trim($_GET['search_pname']) : '';

// Build Query for count and select
$sqlCount = "SELECT COUNT(*) FROM stock_in s JOIN products p ON s.proid = p.id WHERE s.client_id = :client_id";
$sqlSelect = "SELECT s.* FROM stock_in s JOIN products p ON s.proid = p.id WHERE s.client_id = :client_id";
$params = [':client_id' => CLIENT_ID];

if ($search_cat !== '') {
    $sqlCount .= " AND s.catid = :search_cat";
    $sqlSelect .= " AND s.catid = :search_cat";
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

$sqlSelect .= " ORDER BY s.id DESC LIMIT :limit OFFSET :offset";
$stmtSelect = $object->conn->prepare($sqlSelect);
foreach ($params as $k => $v) {
    $stmtSelect->bindValue($k, $v);
}
$stmtSelect->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtSelect->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtSelect->execute();
$value2 = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['update'])) {
    $object->sid        = $_POST['id'];
    $object->catid      = $_POST['catid'];
    $object->proid      = $_POST['proid'];
    $object->date       = $_POST['date'];
    $object->packquant  = $_POST['packquant'];
    $object->packprice  = $_POST['packprice'];
    $object->totalquant = $_POST['totalquant'];

    if (floatval($object->packprice) < 0 || intval($object->totalquant) < 0) {
        $_SESSION['msg'] = 'Price per pack and Total packs cannot be negative!';
        $_SESSION['msg_type'] = 'error';
        header("Location: adminstockin.php");
        exit();
    }

    $object->stockUpdate();

    $_SESSION['msg'] = 'Stock intake record updated successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: adminstockin.php");
    exit();
}



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category</title>


    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="stylesheet" href="admin.css">

    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">

    <link rel="stylesheet" href="adminstockin.css">

    <script src="jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body>
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px;">Inventory Stock Intake</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Manage your store's warehouse products purchase log and pack prices.</p>
                </div>
                <button type="button" onclick="openModalForAdd()" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.15);">
                    <span class="material-icons" style="font-size: 18px;">add_circle</span> Add Stock Intake
                </button>
            </div>

            <!-- Modern Search Filter Div -->
            <div style="background: var(--bg-card, #fff); padding: 16px 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
                <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 16px; width: 100%; align-items: flex-end; margin: 0;">
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 200px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category</label>
                        <select name="search_cat" style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); font-size: 13px; outline: none; width: 100%; box-sizing: border-box;">
                            <option value="">All Categories</option>
                            <?php foreach ($value1 as $cat) { ?>
                                <option value="<?= $cat['id'] ?>" <?= $search_cat == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['cname']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; flex: 2; min-width: 250px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Search Product</label>
                        <input type="text" name="search_pname" value="<?= htmlspecialchars($search_pname) ?>" placeholder="Search by product name..." style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); font-size: 13px; outline: none; width: 100%; box-sizing: border-box;">
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-weight: bold; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; height: 38px;">
                            <span class="material-icons" style="font-size: 18px;">search</span> Search
                        </button>
                        <a href="adminstockin.php" style="background: var(--border, #e5e7eb); color: var(--text-color); border: none; border-radius: 8px; padding: 10px 20px; font-weight: bold; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; height: 38px; text-decoration: none; box-sizing: border-box;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Modern Table -->
            <div class="table-div" style="width: 100%; box-sizing: border-box; background: var(--bg-card, #fff); padding: 24px; border-radius: 12px; border: 1px solid var(--border); overflow-x: auto;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Intake Date</th>
                            <th>Pack Qty</th>
                            <th>Pack Price</th>
                            <th>Total Packs</th>
                            <th>Total Price</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (!empty($value2)) {
                            $cnt = $offset;
                            foreach ($value2 as $row) {
                                $cnt++;

                                $object->cid = $row['catid'];
                                $cat = $object->catfetch();

                                $object->pid = $row['proid'];
                                $pro = $object->profetch();
                            ?>
                                <tr>
                                    <td><?= $cnt ?></td>
                                    <td style="font-weight: bold; color: var(--text-color);"><?= htmlspecialchars($pro ? $pro['pname'] : 'Deleted Product') ?></td>
                                    <td><?= htmlspecialchars($cat ? $cat['cname'] : 'Deleted Category') ?></td>
                                    <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($row['date']) ?></td>
                                    <td><?= htmlspecialchars($row['pack_quant']) ?></td>
                                    <td>₹<?= htmlspecialchars($row['pack_price']) ?></td>
                                    <td style="font-weight: bold; color: #f97316;"><?= htmlspecialchars($row['total_quant']) ?></td>
                                    <td style="font-weight: bold; color: #16a34a;">₹<?= htmlspecialchars($row['total_price']) ?></td>

                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" class="action-btn edit" onclick="stockedit(<?= $row['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">edit</span> Edit
                                            </button>
                                            <button type="button" class="action-btn delete" onclick="stockdelete(<?= $row['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">delete</span> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">No stock intake records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1) { 
                    $searchParams = '';
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

    <!-- Modern Entry Modal Overlay -->
    <div id="stockModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card, #fff); width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 1px solid var(--border); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0; font-size: 16px; font-weight: bold; color: var(--text-color);">Stock Intake Details</h3>
                <button type="button" onclick="closeModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div style="padding: 24px;">
                <form method="POST" id="editDiv" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Default content loaded for Add -->
                </form>
            </div>
        </div>
    </div>

    <!-- Dropdown Options Hidden Helper -->
    <div id="catIntakeOptionsHelper" style="display: none;">
        <?php foreach ($value1 as $val1) { ?>
            <option value="<?= $val1['id'] ?>"><?= htmlspecialchars($val1['cname']) ?></option>
        <?php } ?>
    </div>

    <script>
        function openModalForAdd() {
            $('#modalTitle').text('Add New Stock Intake');
            const catOpts = $('#catIntakeOptionsHelper').html();
            $('#editDiv').html(`
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category</label>
                        <select name="catid" id="catid" onchange="proDiv()" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                            <option value="">--SELECT--</option>
                            ${catOpts}
                        </select>
                    </div>
                    <div id="loadProDiv" style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Product</label>
                        <select name="proid" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                            <option value="">--SELECT--</option>
                        </select>
                    </div>
                    <div id="loadProQuantDiv" style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Quantity Per Pack</label>
                        <input type="text" name="packquant" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Date</label>
                        <input type="date" name="date" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Price Per Pack</label>
                        <input type="number" step="0.01" name="packprice" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Total Packs</label>
                        <input type="number" name="totalquant" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                    </div>
                    <button name="submit" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px; font-weight: bold; cursor: pointer; margin-top: 12px; width: 100%;">Save Stock</button>
                </div>
            `);
            $('#stockModal').css('display', 'flex');
        }

        function proDiv() {
            var val = document.getElementById('catid').value;
            // Style loaded product select input
            $('#loadProDiv').load('admingetProDiv.php?cid=' + val, function() {
                $('#loadProDiv select').attr('style', 'padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;');
                $('#loadProDiv label').attr('style', 'font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);');
            });
        }

        function proQuantDiv() {
            var val = document.getElementById('proid').value;
            // Style loaded product quantity input
            $('#loadProQuantDiv').load('admingetProQuantDiv.php?id=' + val, function() {
                $('#loadProQuantDiv input').attr('style', 'padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;');
                $('#loadProQuantDiv label').attr('style', 'font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);');
            });
        }

        function stockedit(id) {
            $('#modalTitle').text('Edit Stock Intake');
            $('#editDiv').load('adminstockinEditDiv.php?id=' + id, function() {
                $('#stockModal').css('display', 'flex');
            });
        }

        function closeModal() {
            $('#stockModal').hide();
        }

        function stockdelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently modify current product quantities.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'adminstockinDelete.php?id=' + id;
                }
            });
        }

        // JS negative values validation on form submit
        $(document).on('submit', '#editDiv', function(e) {
            const packprice = parseFloat($(this).find('input[name="packprice"]').val());
            const totalquant = parseFloat($(this).find('input[name="totalquant"]').val());
            
            if (isNaN(packprice) || packprice < 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Price per pack cannot be negative!',
                    confirmButtonColor: '#16a34a'
                });
                return false;
            }
            
            if (isNaN(totalquant) || totalquant < 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Total packs/quantity cannot be negative!',
                    confirmButtonColor: '#16a34a'
                });
                return false;
            }
        });

        // Show session flash alerts
        <?php if (isset($_SESSION['msg'])) { ?>
            Swal.fire({
                icon: '<?= $_SESSION['msg_type'] ?>',
                title: '<?= $_SESSION['msg'] ?>',
                showConfirmButton: false,
                timer: 1500
            });
            <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
        <?php } ?>
    </script>

    <script src="admin.js"></script>
</body>

</html>