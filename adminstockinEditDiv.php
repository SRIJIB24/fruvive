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

<h3>Edit Stock Entry</h3>

<!-- CATEGORY -->
<div>
    <label>Category</label>
    <select name="catid" id="catid" onchange="proDiv()" required>
        <option value="">--SELECT--</option>
        <?php foreach ($cats as $c) { ?>
            <option value="<?= $c['id'] ?>"
                <?= ($c['id'] == $row['catid']) ? 'selected' : '' ?>>
                <?= $c['cname'] ?>
            </option>
        <?php } ?>
    </select>
</div>

<!-- PRODUCT -->
<div id="loadProDiv">
    <label>Product</label>
    <select name="proid" id="proid" onchange="proQuantDiv()" required>
        <option value="">--SELECT--</option>
        <?php foreach ($pros as $p) { ?>
            <option value="<?= $p['id'] ?>"
                <?= ($p['id'] == $row['proid']) ? 'selected' : '' ?>>
                <?= $p['pname'] ?>
            </option>
        <?php } ?>
    </select>
</div>

<!-- PACK QUANTITY -->
<div id="loadProQuantDiv">
    <label>Quantity Per Pack</label>
    <input type="text"
           name="packquant"
           value="<?= $row['pack_quant'] ?>"
           readonly>
</div>

<!-- DATE -->
<div>
    <label>Date</label>
    <input type="date"
           name="date"
           value="<?= $row['date'] ?>"
           required>
</div>

<!-- PRICE PER PACK -->
<div>
    <label>Price Per Pack</label>
    <input type="number"
           step="0.01"
           name="packprice"
           value="<?= $row['pack_price'] ?>"
           required>
</div>

<!-- TOTAL PACKS -->
<div>
    <label>Total Packs</label>
    <input type="number"
           name="totalquant"
           value="<?= $row['total_quant'] ?>"
           required>
</div>

<button name="update">Update Stock</button>
