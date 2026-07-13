<?php
require "userFunc.php";

$cid = $_REQUEST['catid'];
//creating object of data class which is child of databse class
$object = new data();
$object->cid = $cid;
$val = $object->catfetch();

?>

<input type="hidden" name="id" value="<?php echo $val['id'] ?>">

<div>
    <label for="">Category Name : </label>
    <input type="text" name="cnm" value="<?php echo $val['cname'] ?>" required>
</div>
<button type="submit" name="update">Update</button>