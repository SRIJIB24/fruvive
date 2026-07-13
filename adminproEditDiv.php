<?php
require "userFunc.php";

$proid = $_REQUEST['proid'];
//creating object of data class which is child of databse class
$object = new data();
$object->pid = $proid;
$val1 = $object->profetch();

$catid = $val1['cid'];

$value = $object->category();

?>
<input type="hidden" name="id" value="<?php echo $val1['id'] ?>">
<div>
    <select name="cnm" id="">
        <option value="">---SELECT---</option>
        <?php
        foreach ($value as $val2) {
        ?>
            <option value="<?php echo $val2['id'] ?>" <?php echo ($val2['id'] == $catid) ? 'Selected' : ''; ?>><?php echo $val2['cname'] ?></option>
        <?php
        }
        ?>
    </select>
</div>

<div>
    <label for="">Product Name</label>
    <input type="text" name="pnm" value="<?php echo $val1['pname'] ?>" required>
</div>
<div>
    <label for="">Quantity Per Pack</label>
    <input type="text" name="quant" value="<?php echo $val1['quant'] ?>" required>
</div>
<?php if (!empty($val1['img_url'])) { ?>
<div>
    <label>Current Image</label><br>
    <img src="<?php echo htmlspecialchars($val1['img_url']); ?>" alt="Current Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; border: 1px solid #ddd;">
</div>
<?php } ?>
<div>
    <label for="">Change Product Image</label>
    <input type="file" name="product_img" accept="image/*">
</div>
<button type="submit" name="update">update</button>