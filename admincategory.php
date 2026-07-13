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

//form submit
if (isset($_POST['submit'])) {
    $object->cname = $_POST['cnm'];
    $object->catSubmit();
    header("Location: admincategory.php");
    exit();
}



if (isset($_POST['update'])) {

    $object->cidedit = $_POST['id'];
    $object->cname = $_POST['cnm'];
    $object->catUpdate();
    header("Location: admincategory.php");
    exit();
}

$value = $object->category();

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
</head>


<body>
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="form-div">
                <form action="" method="POST" id="editDiv">
                    <h3>Category Entry</h3>
                    <div>
                        <label for="">Category Name</label>
                        <input type="text" name="cnm" required>
                    </div>
                    <button type="submit" name="submit">submit</button>
                </form>
            </div>

            <div class="table-div">
                <h1>Category Table</h1>
                <table>
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 0;
                        foreach ($value as $val) {
                            $cnt++
                        ?>
                            <tr>
                                <td><?php echo $cnt ?></td>
                                <td><?php echo $val['cname'] ?></td>
                                <td>
                                    <button type="button" onclick="catedit(<?php echo $val['id'] ?>)">edit</button>
                                    <button type="button" onclick="catdelete(<?php echo $val['id'] ?>)">delete</button>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function catedit(catid) {
            $('#editDiv').load('admincatEditDiv.php?catid=' + catid).fadeIn('fast');
        }

        function catdelete(catid) {
            window.location.href = ('admincatDelete.php?catid=' + catid);
        }
    </script>
    <script src="admin.js"></script>
</body>

</html>