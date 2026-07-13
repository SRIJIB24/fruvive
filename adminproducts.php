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

$value1 = $object->category();
$value2 = $object->products();

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
</head>


<body>
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="form-div">
                <form action="" method="POST" id="editDiv" enctype="multipart/form-data">
                    <h3>Products Entry</h3>
                    <div>
                        <label for="">Category Name</label>
                        <select name="cnm" id="">
                            <option value="">--SELECT--</option>
                            <?php
                            foreach ($value1 as $val1) {
                            ?>
                                <option value="<?php echo $val1['id'] ?>"><?php echo $val1['cname'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="">Product Name</label>
                        <input type="text" name="pnm" required>
                    </div>
                    <div>
                        <label for="">Quantity Per Pack</label>
                        <input type="text" name="quant" required>
                    </div>
                    <div>
                        <label for="">Product Image</label>
                        <input type="file" name="product_img" accept="image/*">
                    </div>
                    <button type="submit" name="submit">submit</button>
                </form>
            </div>

            <div class="table-div">
                <h1>Products Table</h1>
                <table>
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Image</th>
                            <th>Category Name</th>
                            <th>Products Name</th>
                            <th>Quantity Per Pack</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 0;
                        foreach ($value2 as $val2) {
                            $cnt++;
                            $object->cid = $val2['cid'];
                            $val3 = $object->catfetch();

                        ?>
                            <tr>
                                <td><?php echo $cnt ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($val2['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                                </td>
                                <td><?php echo $val3['cname'] ?></td>
                                <td><?php echo $val2['pname'] ?></td>
                                <td><?php echo $val2['quant'] ?></td>
                                <td>
                                    <button type="button" onclick="proedit(<?php echo $val2['id'] ?>)">edit</button>
                                    <button type="button" onclick="prodelete(<?php echo $val2['id'] ?>)">delete</button>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
        <script>
            function proedit(proid) {
                $('#editDiv').load('adminproEditDiv.php?proid=' + proid).fadeIn('fast');
            }

            function prodelete(proid) {
                window.location.href = ('adminproDelete.php?proid=' + proid);
            }
        </script>
        <script src="admin.js"></script>
</body>

</html>