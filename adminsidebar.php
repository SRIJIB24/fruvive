<?php
if (!isset($object)) {
    require_once "userFunc.php";
    $object = new data();
}

$sidebarCurrentPage = basename($_SERVER['PHP_SELF']);
$sidebarCurrentSlug = isset($_GET['page']) ? trim($_GET['page']) : '';

if ($sidebarCurrentPage === 'admincompany.php' && $sidebarCurrentSlug === '') {
    $sidebarCurrentSlug = 'our-story';
}
if ($sidebarCurrentPage === 'adminpolicies.php' && $sidebarCurrentSlug === '') {
    $sidebarCurrentSlug = 'terms-service';
}

if (!function_exists('isActive')) {
    function isActive($page, $slug = '')
    {
        global $sidebarCurrentPage, $sidebarCurrentSlug;
        if ($slug !== '') {
            return ($sidebarCurrentPage === $page && $sidebarCurrentSlug === $slug) ? 'active' : '';
        }
        return ($sidebarCurrentPage === $page && $sidebarCurrentSlug === '') ? 'active' : ($sidebarCurrentPage === $page && $slug === '' ? 'active' : '');
    }
}

// Fetch dynamic pages from database
$sidebarCompanyPages = [];
$sidebarPolicyPages = [];
try {
    $companyMenuStmt = $object->conn->query("SELECT slug, title FROM pages_content WHERE section = 'company' AND client_id = 1 ORDER BY id ASC");
    $sidebarCompanyPages = $companyMenuStmt->fetchAll(PDO::FETCH_ASSOC);

    $policyMenuStmt = $object->conn->query("SELECT slug, title FROM pages_content WHERE section = 'policy' AND client_id = 1 ORDER BY id ASC");
    $sidebarPolicyPages = $policyMenuStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Graceful fallback
}

// Icon mapping helper
if (!function_exists('getPageIcon')) {
    function getPageIcon($slug, $default = 'article')
    {
        $icons = [
            'our-story' => 'history_edu',
            'why-fruvive' => 'help_outline',
            'news-blogs' => 'newspaper',
            'terms-service' => 'gavel',
            'privacy-shield' => 'security',
            'refund-policy' => 'currency_exchange',
            'shipping-guide' => 'local_shipping'
        ];
        return isset($icons[$slug]) ? $icons[$slug] : $default;
    }
}

if (!function_exists('isGroupActive')) {
    function isGroupActive($group)
    {
        global $sidebarCurrentPage;
        if ($group === 'policy') {
            return $sidebarCurrentPage === 'adminpolicies.php';
        }
        if ($group === 'company') {
            return $sidebarCurrentPage === 'admincompany.php';
        }
        return false;
    }
}
?>

<aside class="sidebar">
    <div class="logo">Admin Panel</div>

    <!-- Flat Navigation Menu (Scrollable) -->
    <div class="accordion-menu" style="overflow-y: auto; max-height: calc(100vh - 80px); padding-bottom: 20px;">
        
        <!-- Standard Flat Links -->
        <ul class="accordion-content" style="display: block; margin: 0; padding: 0; border: none; margin-left: 0;">
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
            <li class="<?= isActive('adminreturns.php'); ?>">
                <a href="adminreturns.php">
                    <span class="material-icons">assignment_return</span>
                    <span class="menu-text">Returns & Refunds</span>
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
            <li class="<?= isActive('admincoupons.php'); ?>">
                <a href="admincoupons.php">
                    <span class="material-icons">local_offer</span>
                    <span class="menu-text">Coupons</span>
                </a>
            </li>
        </ul>

        <!-- Accordion 1: Our Company -->
        <div class="accordion-group <?php echo isGroupActive('company') ? 'open' : ''; ?>" id="accordion-company" style="margin-top: 16px;">
            <button type="button" class="accordion-trigger" onclick="toggleAccordion('company')">
                <span class="material-icons trigger-icon">business</span>
                <span class="menu-text">Our Company</span>
                <span class="material-icons arrow-icon">expand_more</span>
            </button>
            <ul class="accordion-content">
                <li class="<?= ($sidebarCurrentPage === 'admincompany.php' && $sidebarCurrentSlug === 'our-story' && !isset($_GET['page'])) ? 'active' : ''; ?>">
                    <a href="admincompany.php">
                        <span class="material-icons">settings</span>
                        <span class="menu-text">Manage Company Info</span>
                    </a>
                </li>
                <!-- Dynamic Company Pages (Our Story, Why Fruvive?, News & Blogs, etc.) -->
                <?php foreach ($sidebarCompanyPages as $p) { ?>
                    <li class="<?= isActive('admincompany.php', $p['slug']); ?>">
                        <a href="admincompany.php?page=<?= urlencode($p['slug']) ?>">
                            <span class="material-icons"><?= getPageIcon($p['slug']) ?></span>
                            <span class="menu-text"><?= htmlspecialchars($p['title']) ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <!-- Accordion 2: Help & Policy -->
        <div class="accordion-group <?php echo isGroupActive('policy') ? 'open' : ''; ?>" id="accordion-policy">
            <button type="button" class="accordion-trigger" onclick="toggleAccordion('policy')">
                <span class="material-icons trigger-icon">gavel</span>
                <span class="menu-text">Help & Policy</span>
                <span class="material-icons arrow-icon">expand_more</span>
            </button>
            <ul class="accordion-content">
                <li class="<?= ($sidebarCurrentPage === 'adminpolicies.php' && $sidebarCurrentSlug === 'terms-service' && !isset($_GET['page'])) ? 'active' : ''; ?>">
                    <a href="adminpolicies.php">
                        <span class="material-icons">settings</span>
                        <span class="menu-text">Manage Policies</span>
                    </a>
                </li>
                <!-- Dynamic Policy Pages (Terms of Service, Privacy Shield, Refund Policy, Shipping Guide, etc.) -->
                <?php foreach ($sidebarPolicyPages as $p) { ?>
                    <li class="<?= isActive('adminpolicies.php', $p['slug']); ?>">
                        <a href="adminpolicies.php?page=<?= urlencode($p['slug']) ?>">
                            <span class="material-icons"><?= getPageIcon($p['slug'], 'policy') ?></span>
                            <span class="menu-text"><?= htmlspecialchars($p['title']) ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

    </div>
</aside>

<script>
    function toggleAccordion(groupId) {
        const el = document.getElementById('accordion-' + groupId);
        if (el) {
            el.classList.toggle('open');
        }
    }
</script>