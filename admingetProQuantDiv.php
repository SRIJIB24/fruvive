<?php
require "userFunc.php";

$id = $_REQUEST['id'];
//creating object of data class which is child of databse class
$object = new data();
$object->pid = $id;
$val = $object->profetch();

?>
<label>Quantity Per Pack</label>
<input type="text" name="packquant" value="<?php echo $val['quant'] ?>" readonly>