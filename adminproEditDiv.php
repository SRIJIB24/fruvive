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
<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category Name</label>
    <select name="cnm" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
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

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Product Name</label>
    <input type="text" name="pnm" value="<?php echo htmlspecialchars($val1['pname']) ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
</div>

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Quantity Per Pack</label>
    <input type="text" name="quant" value="<?php echo htmlspecialchars($val1['quant']) ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
</div>

<?php if (!empty($val1['img_url'])) { ?>
<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Current Image</label>
    <img src="<?php echo htmlspecialchars($val1['img_url']); ?>" alt="Current Image" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
</div>
<?php } ?>

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Change Product Image</label>
    <input type="file" name="product_img" accept="image/*" style="font-size: 12px;">
</div>

<button type="submit" name="update" style="background: #3b82f6; color: #fff; border: none; border-radius: 8px; padding: 10px; font-weight: bold; cursor: pointer; margin-top: 12px; width: 100%;">Update Product</button>