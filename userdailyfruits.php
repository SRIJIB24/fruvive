<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$object->catid = '1';
$value = $object->fetchFruits($object->catid);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Fruits - Fruvive</title>
    
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
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen transition-colors duration-300">

    <div class="main flex flex-col min-h-screen">
        <?php include "usernavbar.php" ?>

        <!-- Hero Title -->
        <div class="text-center py-12 px-4 bg-gradient-to-b from-green-50/50 to-transparent dark:from-green-950/20">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight sm:text-5xl">Daily Fruits</h1>
            <p class="max-w-2xl mx-auto mt-3 text-base text-gray-500 dark:text-gray-400">Handpicked organic daily essential fruits for a healthy lifestyle.</p>
        </div>

        <!-- Catalog Grid -->
        <main class="flex-grow max-w-7xl mx-auto w-full px-4 md:px-8 pb-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                
                <?php foreach ($value as $val1) {
                    $proid = $val1['proid'];
                    $price = $val1['pack_price'];
                    $discountAmount = ($price * 11.25) / 100;
                    $oldPrice = round($price + $discountAmount);
                ?>
                    <!-- Product Card -->
                    <div class="group relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between">
                        
                        <div>
                            <!-- Discount Tag -->
                            <span class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm z-10">
                                10% OFF
                            </span>

                            <!-- Image Container -->
                            <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-900 p-4 aspect-square items-center">
                                <img src="<?php echo htmlspecialchars($val1['img_url'] ?: 'assets/image/product-image/default.png'); ?>" alt="Product" 
                                    class="h-36 object-contain transition-transform duration-300 group-hover:scale-110">
                            </div>

                            <!-- Product Info -->
                            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition">
                                <?php echo htmlspecialchars($val1['pname']) ?>
                            </h3>

                            <p class="text-xs font-semibold text-orange-500 mt-1 uppercase tracking-wider">
                                <?php echo htmlspecialchars($val1['pack_quant']) ?>
                            </p>
                            
                            <?php 
                            $stock = (int)$val1['total_quant'];
                            if ($stock > 0 && $stock <= 5) { ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-500 bg-red-50 dark:bg-red-950/20 px-2 py-0.5 rounded-full mt-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                    Only <?= $stock ?> left!
                                </span>
                            <?php } ?>
                        </div>

                        <!-- Price & CTA Button -->
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl font-extrabold text-green-600 dark:text-green-400">
                                    ₹<?php echo $price ?>
                                </span>
                                <span class="text-sm text-gray-400 line-through">
                                    ₹<?php echo $oldPrice ?>
                                </span>
                            </div>

                            <!-- Action button -->
                            <?php
                            $count = $object->isincart($proid, $userid);
                            if ($stock <= 0) {
                            ?>
                                <button class="mt-4 w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-semibold py-2.5 rounded-xl cursor-not-allowed" disabled>
                                    Out of Stock
                                </button>
                            <?php
                            } else if ($count == 0) {
                            ?>
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
    <script src="usernavbar.js"></script>
    <script src="userglobal.js"></script>
</body>

</html>