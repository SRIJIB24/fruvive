<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($page)
{
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>

<aside class="sidebar">
    <div class="logo">Admin Panel</div>

    <ul class="menu">

        <li class="<?= isActive('admin.php'); ?>">
            <a href="admin.php">
                <span class="material-icons">dashboard</span>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="<?= isActive('admincategory.php'); ?>">
            <a href="admincategory.php">
                <span class="material-icons">category</span>
                <span class="menu-text">Category</span>
            </a>
        </li>

        <li class="<?= isActive('adminproducts.php'); ?>">
            <a href="adminproducts.php">
                <span class="material-icons">inventory_2</span>
                <span class="menu-text">Products</span>
            </a>
        </li>

        <li class="<?= isActive('adminstockin.php'); ?>">
            <a href="adminstockin.php">
                <span class="material-icons">add_box</span>
                <span class="menu-text">Stock In</span>
            </a>
        </li>

        <li class="<?= isActive('adminstockout.php'); ?>">
            <a href="adminstockout.php">
                <span class="material-icons">indeterminate_check_box</span>
                <span class="menu-text">Stock Out</span>
            </a>
        </li>

        <li class="<?= isActive('adminReceiveorder.php'); ?>">
            <a href="adminReceiveorder.php">
                <span class="material-icons">receipt_long</span>
                <span class="menu-text">Orders</span>
            </a>
        </li>

        <li class="<?= isActive('adminAssignDelivery.php'); ?>">
            <a href="adminAssignDelivery.php">
                <span class="material-icons">local_shipping</span>
                <span class="menu-text">Assign Delivery</span>
            </a>
        </li>

        <li class="<?= isActive('adminusers.php'); ?>">
            <a href="adminusers.php">
                <span class="material-icons">people</span>
                <span class="menu-text">Users</span>
            </a>
        </li>

    </ul>
</aside>