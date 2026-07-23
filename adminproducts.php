<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();
if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

$value1 = $object->category();

// Search filters
$search_cat = isset($_GET['search_cat']) ? $_GET['search_cat'] : '';
$search_pname = isset($_GET['search_pname']) ? trim($_GET['search_pname']) : '';

// Build Query for count and select
$sqlCount = "SELECT COUNT(*) FROM products WHERE client_id = :client_id";
$sqlSelect = "SELECT id, cid, pname, quant, img_url FROM products WHERE client_id = :client_id";
$params = [':client_id' => CLIENT_ID];

if ($search_cat !== '') {
    $sqlCount .= " AND cid = :search_cat";
    $sqlSelect .= " AND cid = :search_cat";
    $params[':search_cat'] = $search_cat;
}
if ($search_pname !== '') {
    $sqlCount .= " AND pname LIKE :search_pname";
    $sqlSelect .= " AND pname LIKE :search_pname";
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

$sqlSelect .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmtSelect = $object->conn->prepare($sqlSelect);
foreach ($params as $k => $v) {
    $stmtSelect->bindValue($k, $v);
}
$stmtSelect->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtSelect->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtSelect->execute();
$value2 = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);


//form submit
if (isset($_POST['submit'])) {
    $object->catid = $_POST['cnm'];
    $object->pname = $_POST['pnm'];
    $object->pquant = $_POST['quant'];
    $pid = $object->proSubmit();

    // Image upload
    if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_img']['tmp_name'];
        $fileName = $_FILES['product_img']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $newFileName = $object->catid . "_" . $pid . "." . $fileExtension;
        $uploadFileDir = 'assets/image/product-image/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $object->updateProductImgUrl($pid, $dest_path);
        }
    } else {
        $object->updateProductImgUrl($pid, 'assets/image/product-image/default.png');
    }

    $_SESSION['msg'] = 'Product added successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: adminproducts.php");
    exit();
}

//form update
if (isset($_POST['update'])) {
    $object->proedit = $_POST['id'];
    $object->catid = $_POST['cnm'];
    $object->pname = $_POST['pnm'];
    $object->pquant = $_POST['quant'];
    $object->proUpdate();

    // Image upload in edit
    if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_img']['tmp_name'];
        $fileName = $_FILES['product_img']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $newFileName = $object->catid . "_" . $object->proedit . "." . $fileExtension;
        $uploadFileDir = 'assets/image/product-image/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $object->updateProductImgUrl($object->proedit, $dest_path);
        }
    }

    $_SESSION['msg'] = 'Product updated successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: adminproducts.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>


    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="stylesheet" href="admin.css">

    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">
    <link rel="stylesheet" href="adminproducts.css">

    <script src="jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body>
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px;">Store Products</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Manage your catalogs, pack details, and product thumbnails.</p>
                </div>
                <button type="button" onclick="openModalForAdd()" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.15);">
                    <span class="material-icons" style="font-size: 18px;">add_circle</span> Add Product
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
                        <a href="adminproducts.php" style="background: var(--border, #e5e7eb); color: var(--text-color); border: none; border-radius: 8px; padding: 10px 20px; font-weight: bold; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; height: 38px; text-decoration: none; box-sizing: border-box;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Modern Table -->
            <div class="table-div" style="width: 100%; overflow-x: auto; box-sizing: border-box; background: var(--bg-card, #fff); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Sl</th>
                            <th style="width: 80px;">Image</th>
                            <th>Products Name</th>
                            <th>Category Name</th>
                            <th>Quantity Per Pack</th>
                            <th style="width: 200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($value2)) {
                            $cnt = $offset;
                            foreach ($value2 as $val2) {
                                $cnt++;
                                $object->cid = $val2['cid'];
                                $val3 = $object->catfetch();
                            ?>
                                <tr>
                                    <td><?php echo $cnt ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($val2['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product Image" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                                    </td>
                                    <td style="font-weight: bold; color: var(--text-color);"><?php echo htmlspecialchars($val2['pname']) ?></td>
                                    <td><?php echo htmlspecialchars($val3 ? $val3['cname'] : 'Deleted Category') ?></td>
                                    <td style="font-weight: 500; color: #f97316;"><?php echo htmlspecialchars($val2['quant']) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" class="action-btn edit" onclick="proedit(<?php echo $val2['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">edit</span> Edit
                                            </button>
                                            <button type="button" class="action-btn delete" onclick="prodelete(<?php echo $val2['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">delete</span> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No products found.</td>
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
        </div> <!-- Close .content -->
    </div> <!-- Close .main -->

    <!-- Modern Entry Modal Overlay -->
    <div id="productModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card, #fff); width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 1px solid var(--border); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0; font-size: 16px; font-weight: bold; color: var(--text-color);">Product Details</h3>
                <button type="button" onclick="closeModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div style="padding: 24px;">
                <form action="" method="POST" id="editDiv" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Default content loaded for Add -->
                </form>
            </div>
        </div>
    </div>

    <!-- Dropdown Options Hidden Helper -->
    <div id="categoryOptionsHelper" style="display: none;">
        <?php foreach ($value1 as $val1) { ?>
            <option value="<?php echo $val1['id'] ?>"><?php echo htmlspecialchars($val1['cname']) ?></option>
        <?php } ?>
    </div>

    <script>
        function openModalForAdd() {
            $('#modalTitle').text('Add New Product');
            const catOpts = $('#categoryOptionsHelper').html();
            $('#editDiv').html(`
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category Name</label>
                    <select name="cnm" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                        <option value="">--SELECT--</option>
                        ${catOpts}
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Product Name</label>
                    <input type="text" name="pnm" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Quantity Per Pack</label>
                    <input type="text" name="quant" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Product Image</label>
                    <input type="file" name="product_img" accept="image/*" style="font-size: 12px;">
                </div>
                <button type="submit" name="submit" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px; font-weight: bold; cursor: pointer; margin-top: 12px; width: 100%;">Submit Entry</button>
            `);
            $('#productModal').css('display', 'flex');
        }

        function proedit(proid) {
            $('#modalTitle').text('Edit Product');
            $('#editDiv').load('adminproEditDiv.php?proid=' + proid, function() {
                $('#productModal').css('display', 'flex');
            });
        }

        function closeModal() {
            $('#productModal').hide();
        }

        function prodelete(proid) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'All stock entries for this product will also be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'adminproDelete.php?proid=' + proid;
                }
            });
        }

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