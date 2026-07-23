<?php
    $count = 0;
    $userProfile = null;
    if (isset($_SESSION['user_id'])) {
        $count = $object->cartCount($_SESSION['user_id']);
        $userProfile = $object->getprofile($_SESSION['user_id']);
    }
?>
<style>
@media (max-width: 640px) {
    /* Tighten padding inside product cards on mobile */
    .group.relative {
        padding: 0.875rem !important; /* tight p-3.5 padding */
        border-radius: 1.25rem !important; /* rounded-2xl */
    }
    
    /* Make product image container slightly smaller */
    .group .aspect-square {
        padding: 0.5rem !important;
    }
    
    .group img {
        height: 5.5rem !important; /* neat h-22 scale */
    }
    
    /* Scale down product card headings and spacing */
    .group h3 {
        font-size: 0.875rem !important; /* text-sm font size */
        margin-top: 0.5rem !important;
        line-height: 1.25 !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .group p {
        font-size: 0.75rem !important; /* text-xs quantity */
        margin-top: 0.25rem !important;
    }
    
    /* Adjust wishlist toggle button positioning and size */
    .wishlist-btn {
        top: 0.5rem !important;
        right: 0.5rem !important;
        padding: 0.375rem !important; /* smaller p-1.5 */
    }
    
    .wishlist-btn .material-icons {
        font-size: 1rem !important; /* text-base */
    }
    
    /* Adjust indicators */
    .absolute.top-4.left-4 {
        top: 0.5rem !important;
        left: 0.5rem !important;
        font-size: 8px !important;
        padding: 0.125rem 0.375rem !important;
    }

    /* Small font styling for prices and buttons */
    .group .flex.items-baseline {
        gap: 0.25rem !important;
        margin-top: 0.5rem !important;
    }
    
    .group .text-green-600 {
        font-size: 0.95rem !important; /* smaller price */
    }
    
    .group .line-through {
        font-size: 0.75rem !important; /* smaller old price */
    }
    
    /* Make Add to Cart buttons highly compact */
    .group button.flex.items-center,
    .group a.flex.items-center {
        padding: 0.5rem 0.75rem !important; /* tight padding */
        font-size: 0.75rem !important; /* text-xs buttons */
        border-radius: 0.75rem !important; /* rounded-xl */
        margin-top: 0.5rem !important;
    }
    
    .group button.flex.items-center .material-icons,
    .group a.flex.items-center .material-icons {
        font-size: 0.875rem !important; /* text-sm */
    }
    
    /* Add extra padding at the bottom of the page to avoid sticky bar overlay */
    body {
        padding-bottom: 64px !important;
    }
}
</style>
<nav class="w-full h-[65px] bg-white dark:bg-gray-800 flex items-center justify-between px-4 md:px-8 shadow-md dark:shadow-black/40 sticky top-0 z-50 transition-colors duration-300">

    <!-- Left Brand & Mobile Burger -->
    <div class="flex items-center gap-3 md:w-[150px] lg:w-[200px] shrink-0">
        <!-- Burger Button (Mobile) -->
        <button id="burgerBtn" class="flex md:hidden text-gray-600 dark:text-gray-200 focus:outline-none p-1.5 rounded-xl border border-gray-100 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-900 transition">
            <span class="material-icons text-[24px]">menu</span>
        </button>

        <a href="index.php" class="flex items-center gap-1.5 no-underline">
            <img src="images/fruvive-logo.png" alt="Fruvive Logo" class="h-[38px] w-auto">
            <span class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight hidden sm:inline-block">
                Fruvive
            </span>
        </a>
    </div>

    <!-- Middle category links (Desktop) -->
    <div class="hidden md:flex flex-grow items-center justify-center gap-4 lg:gap-6 xl:gap-8">
        <a href="userdailyfruits.php" class="nav-link text-gray-500 dark:text-gray-300 font-bold text-sm tracking-wide pb-1 no-underline transition-all duration-200 hover:text-green-600 dark:hover:text-green-400">
            Daily Fruits
        </a>
        <a href="userseasonalfruits.php" class="nav-link text-gray-500 dark:text-gray-300 font-bold text-sm tracking-wide pb-1 no-underline transition-all duration-200 hover:text-green-600 dark:hover:text-green-400">
            Seasonal Fruits
        </a>
        <a href="userdryfruits.php" class="nav-link text-gray-500 dark:text-gray-300 font-bold text-sm tracking-wide pb-1 no-underline transition-all duration-200 hover:text-green-600 dark:hover:text-green-400">
            Dry Fruits
        </a>
        <a href="usergiftbasket.php" class="nav-link text-gray-500 dark:text-gray-300 font-bold text-sm tracking-wide pb-1 no-underline transition-all duration-200 hover:text-green-600 dark:hover:text-green-400">
            Gift Baskets
        </a>
    </div>

    <!-- Right Search, Actions, Profile -->
    <div class="flex items-center gap-2.5 md:gap-3.5 justify-end md:w-[320px] lg:w-[380px] shrink-0">
        
        <!-- 🔍 SEARCH BAR (Responsive width) -->
        <div class="flex items-center bg-gray-100/80 dark:bg-gray-700 px-2.5 py-1.5 border border-gray-255/30 dark:border-gray-600 rounded-xl transition-all duration-200 focus-within:bg-white dark:focus-within:bg-gray-900/60 focus-within:ring-2 focus-within:ring-green-500/20 w-28 sm:w-36 md:w-40 lg:w-52 xl:w-60">
            <span class="material-icons text-gray-400 dark:text-gray-400 text-lg">
                search
            </span>
            <input type="text" id="navbarSearch" placeholder="Search Items..." class="bg-transparent border-none outline-none pl-2 w-full text-xs text-gray-800 dark:text-gray-100 font-medium">
        </div>

        <!-- Wishlist -->
        <div class="relative">
            <button onclick="window.location.href='userwishlist.php'"
                class="flex items-center justify-center p-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 hover:border-green-600 dark:hover:border-green-500 hover:shadow-sm transition active:scale-95 duration-200">
                <span class="material-icons text-gray-600 dark:text-gray-300 text-[21px]">
                    favorite_border
                </span>
            </button>
            <?php
            $wishCount = 0;
            if (isset($_SESSION['user_id'])) {
                $wishCount = $object->wishlistCount($_SESSION['user_id']);
            }
            ?>
            <span id="wishlistBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center border border-white <?php echo $wishCount > 0 ? '' : 'hidden'; ?>">
                <?php echo $wishCount; ?>
            </span>
        </div>

        <!-- Cart -->
        <div class="relative">
            <button onclick="window.location.href='usercart.php'"
                class="cart-btn flex items-center justify-center p-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 hover:border-green-600 dark:hover:border-green-500 hover:shadow-sm transition active:scale-95 duration-200">
                <span class="material-icons text-gray-600 dark:text-gray-300 text-[21px]">
                    shopping_cart
                </span>
            </button>
            <span id="cartCount" class="absolute -top-1 -right-1 bg-green-600 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center border border-white <?php echo $count > 0 ? '' : 'hidden'; ?>">
                <?php echo $count; ?>
            </span>
        </div>

        <!-- Notifications Bell (User Panel) -->
        <?php if (isset($_SESSION['user_id'])) { ?>
            <div class="relative" id="notifContainer">
                <button id="notifBellBtn" class="flex items-center justify-center p-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 hover:border-green-600 dark:hover:border-green-500 hover:shadow-sm transition active:scale-95 duration-200 relative cursor-pointer">
                    <span class="material-icons text-gray-600 dark:text-gray-300 text-[21px]">notifications</span>
                    <span id="notifBadge" class="absolute -top-1 -right-1 bg-orange-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center border border-white hidden">0</span>
                </button>
                <div id="notifDropdown" class="absolute right-0 top-[125%] w-[290px] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200/80 dark:border-gray-750 py-2 hidden z-50 transition duration-200">
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center select-none">
                        <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Alerts Log</span>
                        <button id="markReadBtn" class="text-[10px] text-green-600 dark:text-green-400 font-bold border-none bg-transparent hover:underline cursor-pointer">Mark all as read</button>
                    </div>
                    <div id="notifList" class="max-h-[250px] overflow-y-auto divide-y divide-gray-50 dark:divide-gray-750">
                        <p class="text-center py-6 text-xs text-gray-400 italic">No alerts logged yet.</p>
                    </div>
                </div>
            </div>
        <?php } ?>

        <!-- PROFILE / LOGIN -->
        <?php if (isset($_SESSION['user_id'])) { ?>
            <div class="relative">
                <!-- trigger (down arrow removed) -->
                <div id="profileTrigger" class="flex items-center gap-2 cursor-pointer p-1 border border-gray-200 dark:border-gray-700 rounded-full hover:border-green-600 dark:hover:border-green-500 hover:shadow-md transition duration-200">
                    <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-sm shadow-inner select-none">
                        <?php if (!empty($userProfile['user_img'])) { ?>
                            <img src="<?php echo htmlspecialchars($userProfile['user_img']); ?>" class="w-full h-full object-cover">
                        <?php } else { ?>
                            <?php echo strtoupper($_SESSION['username'][0] ?? 'U') ?>
                        <?php } ?>
                    </div>
                </div>

                <!-- dropdown -->
                <div id="profileDropdown" class="absolute right-0 top-[125%] w-[210px] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200/80 dark:border-gray-750 overflow-hidden hidden z-50">
                    <a href="userProfile.php" class="w-full px-4 py-3 flex items-center gap-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 no-underline transition">
                        <span class="material-icons text-gray-400 text-base">person</span>
                        Profile Details
                    </a>
                    <a href="userAddress.php" class="w-full px-4 py-3 flex items-center gap-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 no-underline transition">
                        <span class="material-icons text-gray-400 text-base">location_on</span>
                        My Address
                    </a>
                    <a href="userOrders.php" class="w-full px-4 py-3 flex items-center gap-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 no-underline transition">
                        <span class="material-icons text-gray-400 text-base">receipt_long</span>
                        My Orders
                    </a>
                    
                    <!-- dark mode toggler button -->
                    <button id="darkToggle" class="w-full px-4 py-3 flex items-center gap-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left border-none bg-transparent cursor-pointer transition">
                        <span class="material-icons text-gray-400 text-base">dark_mode</span>
                        Dark Mode
                    </button>

                    <a href="#" class="w-full px-4 py-3 flex items-center gap-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 no-underline transition">
                        <span class="material-icons text-gray-400 text-base">settings</span>
                        Account Settings
                    </a>

                    <hr class="border-gray-100 dark:border-gray-700 my-0">

                    <a href="logout.php" class="w-full px-4 py-3 flex items-center gap-2.5 text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 no-underline transition">
                        <span class="material-icons text-red-400 text-base">logout</span>
                        Sign Out
                    </a>
                </div>
            </div>
        <?php } else { ?>
            <a href="login.php" class="flex items-center gap-1 h-[34px] px-3.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold text-xs shadow-sm transition duration-200 no-underline whitespace-nowrap">
                <span class="material-icons text-sm">login</span>
                Sign In
            </a>
            <!-- Keep a hidden darkToggle for page JS compatibility if script references it -->
            <button id="darkToggle" class="hidden"></button>
        <?php } ?>

    </div>
</nav>

<!-- Mobile Burger Slide Drawer -->
<div id="mobileDrawer" class="fixed inset-0 z-50 bg-black/40 hidden transition-opacity duration-350 ease-out">
    <div id="mobileDrawerPanel" class="absolute left-0 top-0 h-full w-[280px] bg-white dark:bg-gray-800 shadow-2xl p-6 transform -translate-x-full transition-transform duration-300 ease-out flex flex-col justify-between">
        
        <div>
            <!-- Header -->
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                <span class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <img src="images/fruvive-logo.png" alt="Fruvive Logo" class="h-8 w-auto">
                    Fruvive
                </span>
                <button id="closeBurgerBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white focus:outline-none">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <!-- Category links -->
            <div class="mt-6 flex flex-col gap-1 pr-1">
                <span class="text-[9px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider px-3 mb-1">Categories</span>
                <a href="userdailyfruits.php" class="nav-link-mobile px-3 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 no-underline hover:bg-green-50 dark:hover:bg-green-950/20 hover:text-green-600 dark:hover:text-green-400 transition flex items-center gap-2">
                    <span class="material-icons text-lg">today</span> Daily Fruits
                </a>
                <a href="userseasonalfruits.php" class="nav-link-mobile px-3 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 no-underline hover:bg-green-50 dark:hover:bg-green-950/20 hover:text-green-600 dark:hover:text-green-400 transition flex items-center gap-2">
                    <span class="material-icons text-lg">eco</span> Seasonal Fruits
                </a>
                <a href="userdryfruits.php" class="nav-link-mobile px-3 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 no-underline hover:bg-green-50 dark:hover:bg-green-950/20 hover:text-green-600 dark:hover:text-green-400 transition flex items-center gap-2">
                    <span class="material-icons text-lg">spa</span> Dry Fruits
                </a>
                <a href="usergiftbasket.php" class="nav-link-mobile px-3 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 no-underline hover:bg-green-50 dark:hover:bg-green-950/20 hover:text-green-600 dark:hover:text-green-400 transition flex items-center gap-2">
                    <span class="material-icons text-lg">card_giftcard</span> Gift Baskets
                </a>
            </div>
        </div>
        
        <!-- Mobile Footer info inside drawer -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider text-center">
            🌱 Farm Fresh Produce Store
        </div>
    </div>
</div>

<!-- Sticky Bottom Navigation Bar (Mobile only) like Flipkart/Blinkit -->
<div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 h-[56px] flex md:hidden items-center justify-around z-50 shadow-[0_-3px_10px_rgba(0,0,0,0.06)] transition-colors duration-300">
    <!-- Home -->
    <a href="index.php" class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 no-underline hover:text-green-600 dark:hover:text-green-400 gap-0.5">
        <span class="material-icons text-[20px]">home</span>
        <span class="text-[9px] font-bold uppercase tracking-wider scale-90">Home</span>
    </a>
    
    <!-- Categories Toggle -->
    <button id="mobileDrawerBtn" class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 border-none bg-transparent hover:text-green-600 dark:hover:text-green-400 gap-0.5 cursor-pointer">
        <span class="material-icons text-[20px]">grid_view</span>
        <span class="text-[9px] font-bold uppercase tracking-wider scale-90">Categories</span>
    </button>
    
    <!-- Wishlist -->
    <a href="userwishlist.php" class="relative flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 no-underline hover:text-green-600 dark:hover:text-green-400 gap-0.5">
        <span class="material-icons text-[20px]">favorite_border</span>
        <span class="text-[9px] font-bold uppercase tracking-wider scale-90">Wishlist</span>
        <span id="mobileWishlistBadge" class="absolute -top-1 right-2 bg-red-500 text-white text-[8px] font-bold h-3.5 w-3.5 rounded-full flex items-center justify-center border border-white <?php echo $wishCount > 0 ? '' : 'hidden'; ?>">
            <?php echo $wishCount; ?>
        </span>
    </a>
    
    <!-- Cart -->
    <a href="usercart.php" class="relative flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 no-underline hover:text-green-600 dark:hover:text-green-400 gap-0.5">
        <span class="material-icons text-[20px]">shopping_cart</span>
        <span class="text-[9px] font-bold uppercase tracking-wider scale-90">Cart</span>
        <span id="mobileCartCount" class="absolute -top-1 right-1.5 bg-green-600 text-white text-[8px] font-bold h-3.5 w-3.5 rounded-full flex items-center justify-center border border-white <?php echo $count > 0 ? '' : 'hidden'; ?>">
            <?php echo $count; ?>
        </span>
    </a>

    <!-- Account -->
    <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="userProfile.php" class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 no-underline hover:text-green-600 dark:hover:text-green-400 gap-0.5">
            <span class="material-icons text-[20px]">person</span>
            <span class="text-[9px] font-bold uppercase tracking-wider scale-90">Account</span>
        </a>
    <?php } else { ?>
        <a href="login.php" class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 no-underline hover:text-green-600 dark:hover:text-green-400 gap-0.5">
            <span class="material-icons text-[20px]">login</span>
            <span class="text-[9px] font-bold uppercase tracking-wider scale-90">Sign In</span>
        </a>
    <?php } ?>
</div>