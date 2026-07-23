<?php
require "userFunc.php";

$cid = $_REQUEST['catid'];
//creating object of data class which is child of databse class
$object = new data();
$object->cid = $cid;
$val = $object->catfetch();

?>

<input type="hidden" name="id" value="<?php echo $val['id'] ?>">

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category Name</label>
    <input type="text" name="cnm" value="<?php echo htmlspecialchars($val['cname']) ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
</div>

<div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Return Policy</label>
    <textarea name="return_policy" rows="3" style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box; resize: vertical;" placeholder="e.g. 7 days return policy"><?php echo htmlspecialchars($val['return_policy'] ?? '') ?></textarea>
</div>

<button type="submit" name="update" style="background: #3b82f6; color: #fff; border: none; border-radius: 8px; padding: 10px; font-weight: bold; cursor: pointer; margin-top: 12px;">Update</button>