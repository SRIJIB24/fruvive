<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

$userid = $isLoggedIn ? $_SESSION['user_id'] : null;

$wishlist = [];
if ($isLoggedIn) {
    $wishlist = $object->fetchWishlist($userid);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Fruvive</title>
    
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

        <!-- Main Content -->
        <main class="flex-grow max-w-7xl mx-auto w-full px-6 py-12">
            
            <!-- Page Header -->
            <div class="mb-10 flex items-center gap-3">
                <div class="bg-red-100 dark:bg-red-900/30 p-2.5 rounded-2xl">
                    <span class="material-icons text-red-500 text-2xl">favorite</span>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">My Wishlist</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Your personal curated list of organic fresh products.</p>
                </div>
            </div>

            <!-- Empty Wishlist Layout -->
            <div id="emptyWishlistView" class="<?php echo ($isLoggedIn && empty($wishlist)) ? '' : 'hidden'; ?> bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-16 text-center shadow-sm max-w-2xl mx-auto">
                <span class="material-icons text-6xl text-red-300 dark:text-red-900/40">favorite_border</span>
                <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 mt-4">Your Wishlist is Empty</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Add items to your wishlist to keep track of your favorite organic products.</p>
                <a href="index.php" class="mt-6 inline-block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-xl transition shadow-md shadow-green-600/10 no-underline text-sm">
                    Discover Products
                </a>
            </div>

            <!-- Wishlist Products Grid -->
            <div id="wishlistGrid" class="<?php echo ($isLoggedIn && !empty($wishlist)) ? '' : 'hidden'; ?> grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
                <?php if ($isLoggedIn) { ?>
                    <?php foreach ($wishlist as $val1) {
                        $proid = $val1['proid'];
                        $price = $val1['pack_price'];
                        $stock = (int)$val1['total_quant'];
                        $discountAmount = ($price * 11.25) / 100;
                        $oldPrice = round($price + $discountAmount);
                        $isWishlisted = true; // since it is inside database wishlist
                    ?>
                        <!-- Product Card -->
                        <div class="group relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between" id="productCard-<?php echo $proid; ?>">
                            <div>
                                <!-- Wishlist heart trigger -->
                                <button class="wishlist-btn absolute top-4 right-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm p-2 rounded-full shadow-sm hover:scale-110 active:scale-95 transition z-10"
                                    data-pid="<?php echo $proid; ?>" data-wishlisted="1">
                                    <span class="material-icons text-lg text-red-500">favorite</span>
                                </button>

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
                                $count = $object->isincart($proid, $userid);
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
                <?php } ?>
            </div>

        </main>

        <!-- Popups & Footer -->
        <div id="cartPopup" class="fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-[150%] bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-5 w-80 border border-gray-200 dark:border-gray-700 transition-all duration-500 ease-in-out z-50">
            <div class="flex items-start gap-4">
                <div class="bg-green-100 dark:bg-green-950 p-2.5 rounded-full">
                    <span class="material-icons text-green-600 dark:text-green-400 text-2xl">check_circle</span>
                </div>
                <div class="flex-grow">
                    <h4 class="font-bold text-gray-900 dark:text-white text-base">Added to Cart</h4>
                    <p id="popupProduct" class="text-gray-500 dark:text-gray-400 text-xs mt-0.5"></p>
                    <div class="mt-4 flex gap-2.5 text-xs font-semibold">
                        <a href="usercart.php" class="flex-grow text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition">
                            View Cart
                        </a>
                        <button onclick="hidePopup()" class="flex-grow border border-gray-200 dark:border-gray-700 hover:bg-gray-50 py-2 rounded-lg transition text-gray-700 dark:text-gray-300">
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
    
    <script>
        // Guest Wishlist Rendering Logic
        const isUserLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        if (!isUserLoggedIn) {
            renderGuestWishlist();
        }

        function renderGuestWishlist() {
            const grid = document.getElementById("wishlistGrid");
            const emptyView = document.getElementById("emptyWishlistView");
            
            const wishlistIds = JSON.parse(localStorage.getItem('fruvive_wishlist')) || [];
            
            if (wishlistIds.length === 0) {
                grid.classList.add("hidden");
                emptyView.classList.remove("hidden");
                return;
            }

            // Fetch wishlist items from catalog endpoint
            fetch("getWishlistProducts.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ ids: wishlistIds })
            })
            .then(res => res.json())
            .then(products => {
                if (products.length === 0) {
                    grid.classList.add("hidden");
                    emptyView.classList.remove("hidden");
                    return;
                }

                grid.classList.remove("hidden");
                emptyView.classList.add("hidden");

                let html = "";
                products.forEach(p => {
                    const price = parseFloat(p.pack_price);
                    const stock = parseInt(p.total_quant);
                    const oldPrice = Math.round(price + (price * 11.25) / 100);

                    // Check if item is in guest cart
                    const localCart = JSON.parse(localStorage.getItem('fruvive_cart')) || [];
                    const isInCart = localCart.some(item => item.pid == p.proid);

                    let actionBtn = "";
                    if (stock <= 0) {
                        actionBtn = `<button class="mt-4 w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-semibold py-2.5 rounded-xl cursor-not-allowed" disabled>Out of Stock</button>`;
                    } else if (!isInCart) {
                        actionBtn = `<button class="add-cart-btn mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-green-600/10 active:scale-98" data-pid="${p.proid}">Add to Cart</button>`;
                    } else {
                        actionBtn = `<button class="go-cart-btn mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-emerald-600/10 active:scale-98" data-pid="${p.proid}">Go to Cart</button>`;
                    }

                    html += `
                        <div class="group relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm hover:shadow-xl transition duration-300 flex flex-col justify-between" id="productCard-${p.proid}">
                            <div>
                                <button class="wishlist-btn absolute top-4 right-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm p-2 rounded-full shadow-sm hover:scale-110 active:scale-95 transition z-10"
                                    data-pid="${p.proid}" data-wishlisted="1">
                                    <span class="material-icons text-lg text-red-500">favorite</span>
                                </button>

                                <div class="flex justify-center overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-900 p-4 aspect-square items-center cursor-pointer quick-view-trigger" data-pid="${p.proid}">
                                    <img src="${p.img_url || 'assets/image/product-image/default.png'}" alt="Product" 
                                        class="h-36 object-contain transition-transform duration-300 group-hover:scale-110">
                                </div>

                                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition cursor-pointer quick-view-trigger" data-pid="${p.proid}">
                                    ${p.pname}
                                </h3>

                                <p class="text-xs font-semibold text-orange-500 mt-1 uppercase tracking-wider">
                                    ${p.pack_quant}
                                </p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-extrabold text-green-600 dark:text-green-400">₹${price}</span>
                                    <span class="text-sm text-gray-400 line-through">₹${oldPrice}</span>
                                </div>
                                ${actionBtn}
                            </div>
                        </div>
                    `;
                });

                grid.innerHTML = html;

                // Re-bind listeners to guest cards
                document.querySelectorAll(".wishlist-btn").forEach((button) => {
                    button.addEventListener("click", function (e) {
                        e.stopPropagation();
                        const pid = this.getAttribute("data-pid");
                        // For wishlist page, remove card from view instantly on toggle
                        toggleProductWishlist(pid, this);
                        const card = document.getElementById("productCard-" + pid);
                        if (card) card.remove();
                        
                        // Check if wishlist becomes empty
                        const updated = JSON.parse(localStorage.getItem('fruvive_wishlist')) || [];
                        if (updated.length === 0) {
                            grid.classList.add("hidden");
                            emptyView.classList.remove("hidden");
                        }
                    });
                });

                document.querySelectorAll(".add-cart-btn").forEach((button) => {
                    button.addEventListener("click", function (e) {
                        e.stopPropagation();
                        const pid = this.getAttribute("data-pid");
                        const card = this.closest(".group");
                        const img = card ? card.querySelector("img").getAttribute("src") : "";
                        handleAddToCart(pid, img);
                    });
                });

                document.querySelectorAll(".go-cart-btn").forEach((button) => {
                    button.addEventListener("click", function (e) {
                        e.stopPropagation();
                        window.location.href = "usercart.php";
                    });
                });

                document.querySelectorAll(".quick-view-trigger").forEach((element) => {
                    element.addEventListener("click", function (e) {
                        e.stopPropagation();
                        const pid = this.getAttribute("data-pid");
                        const card = this.closest(".group");
                        if (!card) return;

                        const pname = card.querySelector("h3").innerText.trim();
                        const img = card.querySelector("img").getAttribute("src");
                        const quant = card.querySelector("p").innerText.trim();
                        const priceText = card.querySelector(".text-green-600").innerText.replace("₹", "").trim();
                        const oldPriceText = card.querySelector(".line-through") ? card.querySelector(".line-through").innerText : "";

                        openQuickView(pid, pname, img, quant, priceText, oldPriceText);
                    });
                });
            });
        }

        // Handle DB wishlist page remove event updates
        if (isUserLoggedIn) {
            document.querySelectorAll(".wishlist-btn").forEach((button) => {
                button.addEventListener("click", function (e) {
                    e.stopPropagation();
                    const pid = this.getAttribute("data-pid");
                    toggleProductWishlist(pid, this);
                    const card = document.getElementById("productCard-" + pid);
                    if (card) card.remove();

                    // Check if DB list is empty, reload page to show empty view
                    setTimeout(() => {
                        const remaining = document.querySelectorAll("#wishlistGrid > div");
                        if (remaining.length === 0) {
                            document.getElementById("wishlistGrid").classList.add("hidden");
                            document.getElementById("emptyWishlistView").classList.remove("hidden");
                        }
                    }, 500);
                });
            });
        }
    </script>
</body>

</html>
