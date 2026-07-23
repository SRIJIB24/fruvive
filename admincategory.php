<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();
if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

//form submit
if (isset($_POST['submit'])) {
    $object->cname = $_POST['cnm'];
    $object->return_policy = $_POST['return_policy'];
    $object->catSubmit();
    $_SESSION['msg'] = 'Category added successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: admincategory.php");
    exit();
}

if (isset($_POST['update'])) {
    $object->cidedit = $_POST['id'];
    $object->cname = $_POST['cnm'];
    $object->return_policy = $_POST['return_policy'];
    $object->catUpdate();
    $_SESSION['msg'] = 'Category updated successfully!';
    $_SESSION['msg_type'] = 'success';
    header("Location: admincategory.php");
    exit();
}

// Pagination
$limit = 10;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

// Fetch count
$stmtCount = $object->conn->query("SELECT COUNT(*) FROM category WHERE client_id = " . CLIENT_ID);
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch limited data
$stmt = $object->conn->prepare("SELECT id, cname, return_policy FROM category WHERE client_id = :client_id ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':client_id', CLIENT_ID, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$value = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <link rel="stylesheet" href="admincategory.css">

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
                    <h1 style="margin: 0; font-size: 24px;">Product Categories</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Manage your store's menu sections and categorization rules.</p>
                </div>
                <button type="button" onclick="openModalForAdd()" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.15);">
                    <span class="material-icons" style="font-size: 18px;">add_circle</span> Add Category
                </button>
            </div>

            <!-- Modern Table -->
            <div class="table-div" style="width: 100%; overflow-x: auto; box-sizing: border-box; background: var(--bg-card, #fff); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Sl</th>
                            <th>Category</th>
                            <th>Return Policy</th>
                            <th style="width: 200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($value)) {
                            $cnt = $offset;
                            foreach ($value as $val) {
                                $cnt++
                            ?>
                                <tr>
                                    <td><?php echo $cnt ?></td>
                                    <td style="font-weight: bold; color: var(--text-color);"><?php echo htmlspecialchars($val['cname']) ?></td>
                                    <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($val['return_policy'] ?: 'No policy specified') ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" class="action-btn edit" onclick="catedit(<?php echo $val['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">edit</span> Edit
                                            </button>
                                            <button type="button" class="action-btn delete" onclick="catdelete(<?php echo $val['id'] ?>)" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: opacity 0.2s;">
                                                <span class="material-icons" style="font-size: 14px;">delete</span> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else { ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">No categories found.</td>
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

    <!-- Modern Entry Modal Overlay -->
    <div id="categoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card, #fff); width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 1px solid var(--border); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0; font-size: 16px; font-weight: bold; color: var(--text-color);">Category Details</h3>
                <button type="button" onclick="closeModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div style="padding: 24px;">
                <form action="" method="POST" id="editDiv" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Default content loaded for Add -->
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModalForAdd() {
            $('#modalTitle').text('Add New Category');
            $('#editDiv').html(`
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category Name</label>
                    <input type="text" name="cnm" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
                    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Return Policy</label>
                    <textarea name="return_policy" rows="3" style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%; resize: vertical;" placeholder="e.g. 7 days return policy"></textarea>
                </div>
                <button type="submit" name="submit" style="background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px; font-weight: bold; cursor: pointer; margin-top: 12px; width: 100%;">Submit Entry</button>
            `);
            $('#categoryModal').css('display', 'flex');
        }

        function catedit(catid) {
            $('#modalTitle').text('Edit Category');
            $('#editDiv').load('admincatEditDiv.php?catid=' + catid, function() {
                $('#categoryModal').css('display', 'flex');
            });
        }

        function closeModal() {
            $('#categoryModal').hide();
        }

        function catdelete(catid) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'All assigned products will lose this category reference.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admincatDelete.php?catid=' + catid;
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