<?php
require "userFunc.php";

$id = intval($_GET['id']);
$object = new data();
$coupon = $object->couponFetchById($id);
$customersList = $object->fetchCustomers();

if (!$coupon) {
    echo "<p style='color: red;'>Coupon not found.</p>";
    exit();
}

$allowedArr = !empty($coupon['allowed_users']) ? array_map('trim', explode(',', $coupon['allowed_users'])) : [];

?>
<input type="hidden" name="id" value="<?= $coupon['id'] ?>">

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Coupon Code</label>
    <input type="text" name="code" value="<?= htmlspecialchars($coupon['code']) ?>" placeholder="e.g. SAVE20" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%; font-weight: bold; text-transform: uppercase;">
</div>

<div style="display: flex; gap: 12px;">
    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Discount Type</label>
        <select name="discount_type" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
            <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
            <option value="flat" <?= $coupon['discount_type'] === 'flat' ? 'selected' : '' ?>>Flat Amount (₹)</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Discount Value</label>
        <input type="number" step="0.01" min="0" name="discount_value" value="<?= htmlspecialchars($coupon['discount_value']) ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
    </div>
</div>

<div style="display: flex; gap: 12px;">
    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Min Spend Amount (₹)</label>
        <input type="number" step="0.01" min="0" name="min_cart_amount" value="<?= htmlspecialchars($coupon['min_cart_amount']) ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
    </div>
    <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Expiry Date</label>
        <input type="date" name="expiry_date" value="<?= $coupon['expiry_date'] ? htmlspecialchars($coupon['expiry_date']) : '' ?>" style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); box-sizing: border-box; width: 100%;">
    </div>
</div>

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Restrict Allowed User(s) <span style="font-size: 9px; font-weight: normal; text-transform: none;">(Leave unchecked for all users)</span></label>
    <div class="user-select-list" style="border: 1px solid var(--border); border-radius: 8px; max-height: 150px; overflow-y: auto; padding: 8px 12px; background: var(--background); display: flex; flex-direction: column; gap: 8px;">
        <?php foreach ($customersList as $cust) { 
            $checked = in_array((string)$cust['id'], $allowedArr) ? 'checked' : '';
        ?>
            <label class="user-select-item" style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-color);">
                <input type="checkbox" name="allowed_users_checked[]" value="<?= $cust['id'] ?>" <?= $checked ?>>
                <span><?= htmlspecialchars($cust['username']) ?> (<?= htmlspecialchars($cust['email']) ?>)</span>
            </label>
        <?php } ?>
    </div>
</div>

<div style="display: flex; align-items: center; gap: 8px;">
    <input type="checkbox" name="active" id="activeEdit" <?= $coupon['active'] == 1 ? 'checked' : '' ?> style="width: 16px; height: 16px; cursor: pointer;">
    <label for="activeEdit" style="font-size: 13px; font-weight: 500; cursor: pointer; color: var(--text-color);">Coupon is active</label>
</div>

<button type="submit" name="update" class="btn-primary" style="margin-top: 12px; justify-content: center; width: 100%;">Update Coupon</button>
