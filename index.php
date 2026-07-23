<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

$userid = $isLoggedIn ? $_SESSION['user_id'] : null;

// Fetch recommendation lists
$personalTop = [];
if ($isLoggedIn) {
    $personalTop = $object->fetchPersonalTopProducts($userid);
}
$globalTop = $object->fetchGlobalTopProducts();
$allProducts = $object->fetchAllProducts();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organic Fresh Store - Fruvive</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    
    <!-- Material Icons & Google Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen transition-colors duration-300">

    <div class="min-h-screen flex flex-col justify-between">
        <?php include "usernavbar.php" ?>

        <!-- Hero Section -->
        <section class="relative bg-gradient-to-r from-green-600 to-emerald-700 dark:from-green-800 dark:to-emerald-900 text-white py-20 px-6 shadow-md overflow-hidden">
            <!-- Background overlays -->
            <div class="absolute inset-0 bg-cover bg-center opacity-10 mix-blend-overlay" style="background-image: url('images/hero-fruits.jpg');"></div>
            
            <div class="max-w-6xl mx-auto text-center relative z-10 space-y-6">
                <span class="bg-white/20 dark:bg-black/20 text-white font-bold text-xs px-3.5 py-1.5 rounded-full uppercase tracking-wider">
                    🌱 100% Organic & Farm Fresh
                </span>
                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight max-w-4xl mx-auto">
                    Fresh Organic Produce Delivered to Your Home
                </h1>
                <p class="text-lg md:text-xl text-green-50 max-w-2xl mx-auto font-medium">
                    Handpicked local harvest packed with natural nutrients and delivered straight from eco-friendly farms.
                </p>
                <div class="pt-4 flex justify-center gap-4 text-sm font-bold">
                    <a href="#exploreProducts" class="bg-white text-green-700 hover:bg-gray-100 px-7 py-3.5 rounded-xl transition shadow-lg shadow-black/10 no-underline">
                        Shop Catalog
                    </a>
                    <a href="userseasonalfruits.php" class="border border-white/50 text-white hover:bg-white/10 px-7 py-3.5 rounded-xl transition no-underline">
                        Seasonal Offers
                    </a>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <main class="flex-grow">
            
            <!-- Categories Circles Section (Flipkart / Blinkit Style) -->
            <section class="py-10 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-800/50 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-6 md:px-8">
                    
                    <!-- Circular Categories Slider Container -->
                    <div class="flex gap-6 md:gap-10 overflow-x-auto pb-4 justify-start md:justify-center scrollbar-hide scroll-smooth snap-x snap-mandatory">
                        <!-- Daily Fruits -->
                        <a href="userdailyfruits.php" class="flex flex-col items-center gap-2.5 min-w-[90px] no-underline group snap-start">
                            <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 group-hover:border-green-600 dark:group-hover:border-green-400 group-hover:shadow-md group-hover:scale-105 transition-all duration-300 bg-gray-50 dark:bg-gray-900 p-2 flex items-center justify-center">
                                <img src="assets/image/product-image/1_1.jpg" onerror="this.src='assets/image/product-image/default.png'" alt="Daily Fruits" class="w-full h-full object-contain">
                            </div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors text-center whitespace-nowrap">Daily Fruits</span>
                        </a>
                        
                        <!-- Seasonal Fruits -->
                        <a href="userseasonalfruits.php" class="flex flex-col items-center gap-2.5 min-w-[90px] no-underline group snap-start">
                            <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 group-hover:border-green-600 dark:group-hover:border-green-400 group-hover:shadow-md group-hover:scale-105 transition-all duration-300 bg-gray-50 dark:bg-gray-900 p-2 flex items-center justify-center">
                                <img src="assets/image/product-image/2_10.jpg" onerror="this.src='assets/image/product-image/default.png'" alt="Seasonal Fruits" class="w-full h-full object-contain">
                            </div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors text-center whitespace-nowrap">Seasonal Fruits</span>
                        </a>
                        
                        <!-- Dry Fruits -->
                        <a href="userdryfruits.php" class="flex flex-col items-center gap-2.5 min-w-[90px] no-underline group snap-start">
                            <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 group-hover:border-green-600 dark:group-hover:border-green-400 group-hover:shadow-md group-hover:scale-105 transition-all duration-300 bg-gray-50 dark:bg-gray-900 p-2 flex items-center justify-center">
                                <img src="assets/image/product-image/3_30.jpg" onerror="this.src='assets/image/product-image/default.png'" alt="Dry Fruits" class="w-full h-full object-contain">
                            </div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors text-center whitespace-nowrap">Dry Fruits</span>
                        </a>

                        <!-- Gift Baskets -->
                        <a href="usergiftbasket.php" class="flex flex-col items-center gap-2.5 min-w-[90px] no-underline group snap-start">
                            <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 group-hover:border-green-600 dark:group-hover:border-green-400 group-hover:shadow-md group-hover:scale-105 transition-all duration-300 bg-gray-50 dark:bg-gray-900 p-2 flex items-center justify-center">
                                <img src="assets/image/product-image/5_48.jpg" onerror="this.src='assets/image/product-image/default.png'" alt="Gift Baskets" class="w-full h-full object-contain">
                            </div>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors text-center whitespace-nowrap">Gift Baskets</span>
                        </a>
                        
                    </div>
                </div>
            </section>
            
            <!-- 1. Personalized Recommendations ("Most Liked by You") -->
            <?php if (!empty($personalTop)) { ?>
                <section class="py-16 bg-green-50/20 dark:bg-green-950/10 border-b border-gray-100 dark:border-gray-800/50">
                    <div class="max-w-7xl mx-auto px-6 md:px-8">
                        <div class="mb-10 flex items-center gap-2.5">
                            <div class="bg-red-100 dark:bg-red-900/30 p-2.5 rounded-2xl">
                                <span class="material-icons text-red-500 text-2xl">favorite</span>
                            </div>
                            <div>
                                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Most Liked by You</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Top picks based on your previous order frequency.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
                            <?php foreach ($personalTop as $val1) {
                                $proid = $val1['proid'];
                                $price = $val1['pack_price'];
                                $stock = (int)$val1['total_quant'];
                                $discountAmount = ($price * 11.25) / 100;
                                $oldPrice = round($price + $discountAmount);
                            ?>
                                <!-- Product Card -->
                                <div class="group relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between">
                                    <div>
                                        <!-- Wishlist heart button -->
                                        <?php 
                                        $isWishlisted = false;
                                        if ($isLoggedIn) {
                                            $isWishlisted = $object->isInWishlist($userid, $proid);
                                        }
                                        ?>
                                        <button class="wishlist-btn absolute top-4 right-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm p-2 rounded-full shadow-sm hover:scale-110 active:scale-95 transition z-10"
                                            data-pid="<?php echo $proid; ?>" data-wishlisted="<?php echo $isWishlisted ? '1' : '0'; ?>">
                                            <span class="material-icons text-lg <?php echo $isWishlisted ? 'text-red-500' : 'text-gray-400'; ?>">
                                                <?php echo $isWishlisted ? 'favorite' : 'favorite_border'; ?>
                                            </span>
                                        </button>

                                        <!-- Favorite tag indicator -->
                                        <span class="absolute top-4 left-4 bg-red-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider z-10 flex items-center gap-1 shadow-sm">
                                            <span class="material-icons text-[10px]">favorite</span> Liked
                                        </span>

                                        <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-900 p-4 aspect-square items-center cursor-pointer quick-view-trigger" data-pid="<?php echo $proid; ?>">
                                            <img src="<?php echo htmlspecialchars($val1['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product" 
                                                class="h-36 object-contain transition-transform duration-300 group-hover:scale-110">
                                        </div>

                                        <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition cursor-pointer quick-view-trigger" data-pid="<?php echo $proid; ?>">
                                            <?php echo htmlspecialchars($val1['pname']) ?>
                                        </h3>

                                        <p class="text-xs font-semibold text-orange-500 mt-1 uppercase tracking-wider">
                                            <?php echo htmlspecialchars($val1['pack_quant']) ?>
                                        </p>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-extrabold text-green-600 dark:text-green-400">₹<?php echo $price ?></span>
                                            <span class="text-sm text-gray-400 line-through">₹<?php echo $oldPrice ?></span>
                                        </div>

                                        <!-- Actions -->
                                        <?php
                                        $count = 0;
                                        if ($userid) {
                                            $count = $object->isincart($proid, $userid);
                                        }
                                        if ($stock <= 0) {
                                        ?>
                                            <button class="mt-4 w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-semibold py-2.5 rounded-xl cursor-not-allowed" disabled>
                                                Out of Stock
                                            </button>
                                        <?php } else if ($count == 0) { ?>
                                            <button class="add-cart-btn mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-green-600/10 active:scale-98" data-pid="<?php echo $proid; ?>">
                                                Add to Cart
                                            </button>
                                        <?php } else { ?>
                                            <button class="go-cart-btn mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-emerald-600/10 active:scale-98" data-pid="<?php echo $proid; ?>">
                                                Go to Cart
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </section>
            <?php } ?>

            <!-- 2. Global Popular Products -->
            <?php if (!empty($globalTop)) { ?>
                <section class="py-16 border-b border-gray-100 dark:border-gray-800/50">
                    <div class="max-w-7xl mx-auto px-6 md:px-8">
                        <div class="mb-10 flex items-center gap-2.5">
                            <div class="bg-orange-100 dark:bg-orange-900/30 p-2.5 rounded-2xl">
                                <span class="material-icons text-orange-500 text-2xl">trending_up</span>
                            </div>
                            <div>
                                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Most Popular Items</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Top-selling items ordered by all organic lovers.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
                            <?php foreach ($globalTop as $val1) {
                                $proid = $val1['proid'];
                                $price = $val1['pack_price'];
                                $stock = (int)$val1['total_quant'];
                                $discountAmount = ($price * 11.25) / 100;
                                $oldPrice = round($price + $discountAmount);
                            ?>
                                <!-- Product Card -->
                                <div class="group relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between">
                                    <div>
                                        <!-- Wishlist heart button -->
                                        <?php 
                                        $isWishlisted = false;
                                        if ($isLoggedIn) {
                                            $isWishlisted = $object->isInWishlist($userid, $proid);
                                        }
                                        ?>
                                        <button class="wishlist-btn absolute top-4 right-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm p-2 rounded-full shadow-sm hover:scale-110 active:scale-95 transition z-10"
                                            data-pid="<?php echo $proid; ?>" data-wishlisted="<?php echo $isWishlisted ? '1' : '0'; ?>">
                                            <span class="material-icons text-lg <?php echo $isWishlisted ? 'text-red-500' : 'text-gray-400'; ?>">
                                                <?php echo $isWishlisted ? 'favorite' : 'favorite_border'; ?>
                                            </span>
                                        </button>

                                        <span class="absolute top-4 left-4 bg-orange-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider z-10 flex items-center gap-1 shadow-sm">
                                            🔥 Popular
                                        </span>

                                        <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-900 p-4 aspect-square items-center cursor-pointer quick-view-trigger" data-pid="<?php echo $proid; ?>">
                                            <img src="<?php echo htmlspecialchars($val1['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product" 
                                                class="h-36 object-contain transition-transform duration-300 group-hover:scale-110">
                                        </div>

                                        <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition cursor-pointer quick-view-trigger" data-pid="<?php echo $proid; ?>">
                                            <?php echo htmlspecialchars($val1['pname']) ?>
                                        </h3>

                                        <p class="text-xs font-semibold text-orange-500 mt-1 uppercase tracking-wider">
                                            <?php echo htmlspecialchars($val1['pack_quant']) ?>
                                        </p>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-extrabold text-green-600 dark:text-green-400">₹<?php echo $price ?></span>
                                            <span class="text-sm text-gray-400 line-through">₹<?php echo $oldPrice ?></span>
                                        </div>

                                        <!-- Actions -->
                                        <?php
                                        $count = 0;
                                        if ($userid) {
                                            $count = $object->isincart($proid, $userid);
                                        }
                                        if ($stock <= 0) {
                                        ?>
                                            <button class="mt-4 w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-semibold py-2.5 rounded-xl cursor-not-allowed" disabled>
                                                Out of Stock
                                            </button>
                                        <?php } else if ($count == 0) { ?>
                                            <button class="add-cart-btn mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-green-600/10 active:scale-98" data-pid="<?php echo $proid; ?>">
                                                Add to Cart
                                            </button>
                                        <?php } else { ?>
                                            <button class="go-cart-btn mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-emerald-600/10 active:scale-98" data-pid="<?php echo $proid; ?>">
                                                Go to Cart
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </section>
            <?php } ?>

            <!-- 3. All Products Grid -->
            <section id="exploreProducts" class="py-16">
                <div class="max-w-7xl mx-auto px-6 md:px-8">
                    <div class="mb-10 flex items-center gap-2.5">
                        <div class="bg-green-100 dark:bg-green-900/30 p-2.5 rounded-2xl">
                            <span class="material-icons text-green-600 text-2xl">storefront</span>
                        </div>
                        <div>
                            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Explore All Products</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Explore our entire catalogue of fresh agricultural items.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
                        <?php foreach ($allProducts as $val1) {
                            $proid = $val1['proid'];
                            $price = $val1['pack_price'];
                            $stock = (int)$val1['total_quant'];
                            $discountAmount = ($price * 11.25) / 100;
                            $oldPrice = round($price + $discountAmount);
                        ?>
                            <!-- Product Card -->
                            <div class="group relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between">
                                <div>
                                    <!-- Wishlist heart button -->
                                    <?php 
                                    $isWishlisted = false;
                                    if ($isLoggedIn) {
                                        $isWishlisted = $object->isInWishlist($userid, $proid);
                                    }
                                    ?>
                                    <button class="wishlist-btn absolute top-4 right-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm p-2 rounded-full shadow-sm hover:scale-110 active:scale-95 transition z-10"
                                        data-pid="<?php echo $proid; ?>" data-wishlisted="<?php echo $isWishlisted ? '1' : '0'; ?>">
                                        <span class="material-icons text-lg <?php echo $isWishlisted ? 'text-red-500' : 'text-gray-400'; ?>">
                                            <?php echo $isWishlisted ? 'favorite' : 'favorite_border'; ?>
                                        </span>
                                    </button>

                                    <span class="absolute top-4 left-4 bg-green-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider z-10 flex items-center gap-1 shadow-sm">
                                        Organic
                                    </span>

                                    <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-900 p-4 aspect-square items-center cursor-pointer quick-view-trigger" data-pid="<?php echo $proid; ?>">
                                        <img src="<?php echo htmlspecialchars($val1['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product" 
                                            class="h-36 object-contain transition-transform duration-300 group-hover:scale-110">
                                    </div>

                                    <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition cursor-pointer quick-view-trigger" data-pid="<?php echo $proid; ?>">
                                        <?php echo htmlspecialchars($val1['pname']) ?>
                                    </h3>

                                    <p class="text-xs font-semibold text-orange-500 mt-1 uppercase tracking-wider">
                                        <?php echo htmlspecialchars($val1['pack_quant']) ?>
                                    </p>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl font-extrabold text-green-600 dark:text-green-400">₹<?php echo $price ?></span>
                                        <span class="text-sm text-gray-400 line-through">₹<?php echo $oldPrice ?></span>
                                    </div>

                                    <!-- Actions -->
                                    <?php
                                    $count = 0;
                                    if ($userid) {
                                        $count = $object->isincart($proid, $userid);
                                    }
                                    if ($stock <= 0) {
                                    ?>
                                        <button class="mt-4 w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-semibold py-2.5 rounded-xl cursor-not-allowed" disabled>
                                            Out of Stock
                                        </button>
                                    <?php } else if ($count == 0) { ?>
                                        <button class="add-cart-btn mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-green-600/10 active:scale-98" data-pid="<?php echo $proid; ?>">
                                            Add to Cart
                                        </button>
                                    <?php } else { ?>
                                        <button class="go-cart-btn mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-emerald-600/10 active:scale-98" data-pid="<?php echo $proid; ?>">
                                            Go to Cart
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>

        </main>

        <!-- Popups & Footer -->
        <div id="cartPopup" class="fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-[150%] bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-5 w-80 border border-gray-200 dark:border-gray-700 transition-all duration-500 ease-in-out z-50">
            <div class="flex items-start gap-4">
                <div class="bg-green-100 dark:bg-green-900/30 p-2.5 rounded-full">
                    <span class="material-icons text-green-600 dark:text-green-400 text-2xl">check_circle</span>
                </div>
                <div class="flex-grow">
                    <h4 class="font-bold text-gray-900 dark:text-white text-base">Added to Cart</h4>
                    <p id="popupProduct" class="text-gray-500 dark:text-gray-400 text-xs mt-0.5"></p>
                    <div class="mt-4 flex gap-2.5 text-xs font-semibold">
                        <a href="usercart.php" class="flex-grow text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition">
                            View Cart
                        </a>
                        <button onclick="hidePopup()" class="flex-grow border border-gray-200 dark:border-gray-700 dark:hover:bg-gray-700 hover:bg-gray-50 py-2 rounded-lg transition text-gray-700 dark:text-gray-300">
                            Continue
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php include "userfooter.php" ?>
    </div>

    <!-- Scripts -->
    <script src="usernavbar.js?v=1.3"></script>
    <script src="userglobal.js?v=1.3"></script>
</body>

</html>