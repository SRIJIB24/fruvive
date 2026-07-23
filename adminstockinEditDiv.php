<?php
require "userFunc.php";

$id = $_GET['id'];

$object = new data();
$object->sid = $id;
$row = $object->stockfetch();

$cats = $object->category();

/* Load products only of selected category */
$object->cid = $row['catid'];
$pros = $object->profetchcid();
?>

<input type="hidden" name="id" value="<?= $row['id'] ?>">

<div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- CATEGORY -->
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Category</label>
        <select name="catid" id="catid" onchange="proDiv()" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
            <option value="">--SELECT--</option>
            <?php foreach ($cats as $c) { ?>
                <option value="<?= $c['id'] ?>" <?= ($c['id'] == $row['catid']) ? 'selected' : '' ?>><?= $c['cname'] ?></option>
            <?php } ?>
        </select>
    </div>

    <!-- PRODUCT -->
    <div id="loadProDiv" style="display: flex; flex-direction: column; gap: 6px;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Product</label>
        <select name="proid" id="proid" onchange="proQuantDiv()" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
            <option value="">--SELECT--</option>
            <?php foreach ($pros as $p) { ?>
                <option value="<?= $p['id'] ?>" <?= ($p['id'] == $row['proid']) ? 'selected' : '' ?>><?= $p['pname'] ?></option>
            <?php } ?>
        </select>
    </div>

    <!-- PACK QUANTITY -->
    <div id="loadProQuantDiv" style="display: flex; flex-direction: column; gap: 6px;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Quantity Per Pack</label>
        <input type="text" name="packquant" value="<?= htmlspecialchars($row['pack_quant']) ?>" readonly style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--border); color: var(--text-color); width: 100%; box-sizing: border-box; cursor: not-allowed;">
    </div>

    <!-- DATE -->
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Date</label>
        <input type="date" name="date" value="<?= $row['date'] ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
    </div>

    <!-- PRICE PER PACK -->
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Price Per Pack</label>
        <input type="number" step="0.01" name="packprice" value="<?= $row['pack_price'] ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
    </div>

    <!-- TOTAL PACKS -->
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-muted);">Total Packs</label>
        <input type="number" name="totalquant" value="<?= $row['total_quant'] ?>" required style="padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--background); color: var(--text-color); width: 100%; box-sizing: border-box;">
    </div>

    <button name="update" style="background: #3b82f6; color: #fff; border: none; border-radius: 8px; padding: 10px; font-weight: bold; cursor: pointer; width: 100%;">Update Stock</button>
</div>
