<?php
    $count = $object->cartCount($_SESSION['user_id']);
    $userProfile = $object->getprofile($_SESSION['user_id']);
?>
<nav class="w-full h-[60px] bg-white dark:bg-gray-800 flex items-center px-6 shadow-md dark:shadow-black/40 sticky top-0 z-50">

    <!-- LEFT NAV -->
    <div class="w-[11.5%] flex items-center">
        <a href="index.php" class="flex items-center gap-1 no-underline">
            <img src="images/fruvive-logo.png" alt="Fruvive Logo" class="h-[42px] w-auto">
            <span class="text-[22px] font-bold text-gray-800 dark:text-gray-100 tracking-wide">
                Fruvive
            </span>
        </a>
    </div>

    <!-- MIDDLE NAV -->
    <div class="w-[54.5%] flex justify-center gap-[35px]">

        <a href="userdailyfruits.php" class="nav-link text-gray-600 dark:text-gray-300 font-medium pb-1 no-underline transition-all duration-300 hover:text-green-500">
            Daily Fruits
        </a>

        <a href="userseasonalfruits.php" class="nav-link text-gray-600 dark:text-gray-300 font-medium pb-1 no-underline transition-all duration-300 hover:text-green-500">
            Seasonal Fruits
        </a>

        <a href="userdryfruits.php" class="nav-link text-gray-600 dark:text-gray-300 font-medium pb-1 no-underline transition-all duration-300 hover:text-green-500">
            Dry Fruits
        </a>

        <a href="usercutfruits.php" class="nav-link text-gray-600 dark:text-gray-300 font-medium pb-1 no-underline transition-all duration-300 hover:text-green-500">
            Cut Fruit Cups
        </a>

        <a href="usergiftbasket.php" class="nav-link text-gray-600 dark:text-gray-300 font-medium pb-1 no-underline transition-all duration-300 hover:text-green-500">
            Gift Basket
        </a>
    </div>

    <!-- RIGHT NAV -->
    <div class="w-[34%] flex items-center justify-end gap-5">

        <!-- 🔍 SEARCH -->
        <div class="w-[60%] flex items-center 
            bg-gray-100 dark:bg-gray-700
            px-[10px] py-[6px] 
            border border-gray-200 dark:border-gray-600 
            rounded-lg transition-all duration-300
            focus-within:bg-green-50 dark:focus-within:bg-green-900/40
            focus-within:ring-2 focus-within:ring-green-600/20">

            <span class="material-icons text-gray-500 dark:text-gray-300 text-[22px]">
                search
            </span>

            <input type="text" placeholder="Search Items..." class="bg-transparent outline-none pl-2 w-full text-[16px] text-gray-800 dark:text-gray-100">
        </div>

        <!-- PROFILE -->
        <div class="relative group">
            <!-- trigger -->
            <div id="profileTrigger" class="flex items-center gap-2 cursor-pointer h-[43px] px-2 border border-gray-300 dark:border-gray-600
                rounded-lg bg-white dark:bg-gray-800 hover:border-green-600 hover:shadow-md transition-all duration-200">

                <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold text-sm shadow-md">
                    <?php if (!empty($userProfile['user_img'])) { ?>
                        <img src="<?php echo htmlspecialchars($userProfile['user_img']); ?>" class="w-full h-full object-cover">
                    <?php } else { ?>
                        <?php echo strtoupper($_SESSION['username'][0] ?? 'U') ?>
                    <?php } ?>
                </div>

                <span id="arrowIcon" class="material-icons text-gray-600 dark:text-gray-300 transition-transform duration-300">
                    expand_more
                </span>
            </div>

            <!-- dropdown -->
            <div id="profileDropdown" class="absolute right-0 top-[120%] w-[210px]
                bg-white dark:bg-gray-800
                rounded-xl shadow-lg dark:shadow-black/50
                overflow-hidden hidden ">
                <!-- profile -->
                <a href="userProfile.php" class="w-full px-4 py-3 flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="material-icons text-[18px]">person</span>
                    Profile
                </a>
                <!-- address -->
                <a href="userAddress.php" class="w-full px-4 py-3 flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="material-icons text-[18px]">location_on</span>
                    My Address
                </a>
                <!-- orders -->
                <a href="userOrders.php" class="w-full px-4 py-3 flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="material-icons text-[18px]">receipt_long</span>
                    My Orders
                </a>
                <!-- dark mode button -->
                <button id="darkToggle" class="w-full px-4 py-3 flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="material-icons text-[18px]">dark_mode</span>
                    Dark Mode
                </button>

                <a href="#" class="w-full px-4 py-3 flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="material-icons text-[18px]">settings</span>
                    Settings
                </a>

                <hr class="border-gray-200 dark:border-gray-700">

                <a href="logout.php" class="w-full px-4 py-3 flex items-center gap-2 text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span class="material-icons text-[18px]">logout</span>
                    Logout
                </a>
            </div>
        </div>

        <!-- cart -->
        <div class="relative">

            <button onclick="window.location.href='usercart.php'"
                class="cart-btn flex items-center justify-center px-3 py-2 border border-gray-200 dark:border-gray-600
                rounded-lg bg-white dark:bg-gray-800 hover:border-green-600 transition">

                <span class="material-icons text-gray-700 dark:text-gray-200 text-[24px]">
                    shopping_cart
                </span>
            </button>

            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[11px] font-semibold px-1.5 rounded-full min-w-[18px] text-center">
                <span id="cartCount"><?php echo $count ?></span>
            </span>

        </div>

    </div>
</nav>