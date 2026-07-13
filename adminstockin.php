<?php
require "userFunc.php";
//creating object of data class which is child of databse class
$object = new data();

//session check
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

    $object->stockIn();

    header("Location: adminstockin.php");
    exit();
}


$value2 = $object->stockAll();

if (isset($_POST['update'])) {
    $object->sid        = $_POST['id'];
    $object->catid      = $_POST['catid'];
    $object->proid      = $_POST['proid'];
    $object->date       = $_POST['date'];
    $object->packquant  = $_POST['packquant'];
    $object->packprice  = $_POST['packprice'];
    $object->totalquant = $_POST['totalquant'];

    $object->stockUpdate();

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
</head>


<body>
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="form-div">
                <form method="POST" id="editDiv">
                    <div>
                        <label>Category</label>
                        <select name="catid" id="catid" onchange="proDiv()" required>
                            <option value="">--SELECT--</option>
                            <?php foreach ($value1 as $val1) { ?>
                                <option value="<?= $val1['id'] ?>"><?= $val1['cname'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="loadProDiv">
                        <label>Product</label>
                        <select name="proid" required>
                            <option value="">--SELECT--</option>
                        </select>
                    </div>
                    <div id="loadProQuantDiv">
                        <label>Quantity Per Pack</label>
                        <input type="text" name="packquant" required>
                    </div>
                    <div>
                        <label>Date</label>
                        <input type="date" name="date" required>
                    </div>
                    <div>
                        <label>Price Per Pack</label>
                        <input type="number" step="0.01" name="packprice" required>
                    </div>
                    <div>
                        <label>Total Packs</label>
                        <input type="number" name="totalquant" required>
                    </div>
                    <button name="submit">Save Stock</button>
                </form>
            </div>

            <div class="table-div">
                <h1>Stock In Table</h1>

                <table>
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Date</th>
                            <th>Pack Qty</th>
                            <th>Pack Price</th>
                            <th>Total Packs</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $cnt = 0;
                        foreach ($value2 as $row) {
                            $cnt++;

                            $object->cid = $row['catid'];
                            $cat = $object->catfetch();

                            $object->pid = $row['proid'];
                            $pro = $object->profetch();
                        ?>
                            <tr>
                                <td><?= $cnt ?></td>
                                <td><?= $cat['cname'] ?></td>
                                <td><?= $pro['pname'] ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $row['pack_quant'] ?></td>
                                <td><?= $row['pack_price'] ?></td>
                                <td><?= $row['total_quant'] ?></td>
                                <td><?= $row['total_price'] ?></td>

                                <td>
                                    <button onclick="stockedit(<?= $row['id'] ?>)">Edit</button>
                                    <button onclick="stockdelete(<?= $row['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    </div>
    <script>
        function proDiv() {
            var val = document.getElementById('catid').value;
            $('#loadProDiv').load('admingetProDiv.php?cid=' + val).fadeIn('fast');
        }

        function proQuantDiv() {
            var val = document.getElementById('proid').value;
            $('#loadProQuantDiv').load('admingetProQuantDiv.php?id=' + val).fadeIn('fast');
        }

        function stockedit(id) {
            $('#editDiv').load('adminstockinEditDiv.php?id=' + id).fadeIn('fast');
        }

        function stockdelete(id) {
            window.location.href = 'adminstockinDelete.php?id=' + id;
        }

    </script>

    <script src="admin.js"></script>
</body>

</html>