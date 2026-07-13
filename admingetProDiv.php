<?php
require "userFunc.php";

$cid = $_REQUEST['cid'];
//creating object of data class which is child of databse class
$object = new data();
$object->cid = $cid;
$val = $object->profetchcid();

?>
<label>Product</label>
<select name="proid" id="proid" onchange="proQuantDiv()" required>
    <option value="">---SELECT---</option>
    <?php foreach ($val as $p) { ?>
        <option value="<?= $p['id'] ?>"><?= $p['pname'] ?></option>
    <?php } ?>
</select>