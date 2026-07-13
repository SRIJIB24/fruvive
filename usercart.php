<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$cart = $object->fetchCart();
$address = $object->fetchSelectAddress($userid);
$allAddresses = $object->fetchAddress($userid);

// Handle select address change
if (isset($_POST['select_address'])) {
    $addrId = intval($_POST['address_id']);
    $object->updateSelectAddress($addrId, $userid);
    header("Location: usercart.php");
    exit();
}

// Coupon processing logic
$coupon_msg = '';
$coupon_status = ''; // 'success' or 'error'

if (isset($_POST['apply_coupon'])) {
    $code = trim($_POST['coupon']);
    if (empty($code)) {
        $coupon_msg = "Please enter a coupon code.";
        $coupon_status = "error";
    } else {
        $coupon = $object->verifyCoupon($code);
        if ($coupon) {
            $cartTotal = 0;
            foreach ($cart as $item) {
                $cartTotal += $item['price'] * $item['qty'];
            }
            if ($cartTotal >= $coupon['min_cart_amount']) {
                $_SESSION['applied_coupon'] = $coupon['code'];
                $_SESSION['coupon_type'] = $coupon['discount_type'];
                $_SESSION['coupon_value'] = $coupon['discount_value'];
                $coupon_msg = "Coupon '{$coupon['code']}' applied successfully!";
                $coupon_status = "success";
            } else {
                $coupon_msg = "Minimum cart amount of ₹" . $coupon['min_cart_amount'] . " required for this coupon.";
                $coupon_status = "error";
            }
        } else {
            $coupon_msg = "Invalid or expired coupon code.";
            $coupon_status = "error";
        }
    }
}

// Recalculate discount
$coupon_discount = 0;
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $object->verifyCoupon($_SESSION['applied_coupon']);
    if ($coupon) {
        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += $item['price'] * $item['qty'];
        }
        if ($cartTotal >= $coupon['min_cart_amount']) {
            if ($coupon['discount_type'] === 'percentage') {
                $coupon_discount = round(($cartTotal * $coupon['discount_value']) / 100);
            } else {
                $coupon_discount = min($coupon['discount_value'], $cartTotal);
            }
            $_SESSION['discount_amount'] = $coupon_discount;
        } else {
            unset($_SESSION['applied_coupon']);
            unset($_SESSION['coupon_type']);
            unset($_SESSION['coupon_value']);
            unset($_SESSION['discount_amount']);
        }
    } else {
        unset($_SESSION['applied_coupon']);
        unset($_SESSION['coupon_type']);
        unset($_SESSION['coupon_value']);
        unset($_SESSION['discount_amount']);
    }
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}
$final_total = max(0, $total - $coupon_discount);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Fruvive</title>
    
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
    <script src="jquery-1.9.1.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen transition-colors duration-300">

    <div class="main flex flex-col min-h-screen">
        <?php include "usernavbar.php" ?>

        <!-- Cart View Container -->
        <main class="flex-grow max-w-6xl mx-auto w-full px-4 py-8 md:py-12">
            
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-icons text-green-600">shopping_cart</span>
                    Shopping Cart
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review your fresh produce items before checkout.</p>
            </div>

            <!-- Shipping Address Summary -->
            <?php if ($address) { ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition">
                    <div class="flex items-start gap-3">
                        <span class="material-icons text-green-600 mt-0.5">location_on</span>
                        <div class="space-y-1">
                            <p class="text-sm text-gray-900 dark:text-white font-semibold">
                                Deliver to: <span class="text-green-600 font-bold"><?php echo htmlspecialchars($address['name']); ?></span>
                                <span class="ml-2 text-[10px] uppercase font-bold tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">
                                    <?php echo htmlspecialchars($address['type']); ?>
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-2xl">
                                <?php echo htmlspecialchars($address['address']); ?>, <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> - <?php echo htmlspecialchars($address['pincode']); ?>
                            </p>
                        </div>
                    </div>
                    <button onclick="openOrderAddressPopup()" class="w-full sm:w-auto border border-green-600 text-green-600 dark:border-green-400 dark:text-green-400 px-5 py-2 text-xs font-bold rounded-xl hover:bg-green-50 dark:hover:bg-green-950/20 transition">
                        Change Address
                    </button>
                </div>
            <?php } else { ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition">
                    <div>
                        <p class="text-sm text-red-600 dark:text-red-400 font-bold flex items-center gap-1.5">
                            <span class="material-icons text-base">warning</span>
                            No Delivery Address Selected
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Please add a shipping address to enable order placements.</p>
                    </div>
                    <button onclick="window.location.href='userAddAddress.php?from=cart'" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 text-xs font-bold rounded-xl transition shadow-md shadow-green-600/10">
                        Add Address
                    </button>
                </div>
            <?php } ?>

            <!-- Cart Layout Grid -->
            <?php if (empty($cart)) { ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-16 text-center shadow-sm">
                    <span class="material-icons text-6xl text-gray-300 dark:text-gray-600">shopping_cart</span>
                    <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 mt-4">Your Cart is Empty</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Add fresh organic fruits or gift baskets to get started.</p>
                    <a href="index.php" class="mt-6 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-md shadow-green-600/10">
                        Continue Shopping
                    </a>
                </div>
            <?php } else { ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left: Cart Items List -->
                    <div class="lg:col-span-2 space-y-4">
                        <?php foreach ($cart as $item) { ?>
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm flex gap-6 hover:shadow-md transition">
                                <!-- Image -->
                                <div class="w-24 h-24 flex-shrink-0 bg-gray-50 dark:bg-gray-900 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 flex items-center justify-center">
                                    <img src="<?php echo htmlspecialchars($item['img_url'] ?: 'assets/image/product-image/default.png'); ?>" class="w-20 h-20 object-contain">
                                </div>

                                <!-- Details -->
                                <div class="flex-grow flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start gap-4">
                                            <h3 class="text-base font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($item['pname']) ?></h3>
                                            
                                            <!-- Delete Button -->
                                            <form method="POST" action="usercartDel.php">
                                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="text-gray-400 hover:text-red-500 transition">
                                                    <span class="material-icons text-xl">delete_outline</span>
                                                </button>
                                            </form>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">Price: ₹<?= htmlspecialchars($item['price']) ?></p>
                                    </div>

                                    <!-- Quantity selector -->
                                    <div class="flex justify-between items-center mt-4">
                                        <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-gray-50 dark:bg-gray-900">
                                            <button onclick="qtyDec(<?= $item['id'] ?>, <?= $item['qty'] ?>)" class="px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 transition font-bold text-gray-500 hover:text-gray-700 dark:hover:text-white">-</button>
                                            <span class="px-4 text-sm font-semibold text-gray-800 dark:text-white"><?= $item['qty'] ?></span>
                                            <button onclick="qtyInc(<?= $item['id'] ?>, <?= $item['qty'] ?>)" class="px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 transition font-bold text-gray-500 hover:text-gray-700 dark:hover:text-white">+</button>
                                        </div>

                                        <span class="text-base font-bold text-green-600 dark:text-green-400">
                                            ₹<?= $item['price'] * $item['qty'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Right: Summary & Checkout -->
                    <div class="lg:col-span-1 lg:sticky lg:top-[80px] lg:self-start">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3">Price Details</h3>
                            
                            <!-- Coupon Form -->
                            <form method="POST" class="space-y-3">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Promo Coupon</label>
                                <div class="flex gap-2">
                                    <input type="text" name="coupon" placeholder="Enter Coupon Code" value="<?php echo htmlspecialchars($_SESSION['applied_coupon'] ?? ''); ?>"
                                        class="flex-grow border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition dark:text-white">
                                    <button type="submit" name="apply_coupon" class="bg-green-600 hover:bg-green-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition shadow-md shadow-green-600/10">
                                        Apply
                                    </button>
                                </div>
                            </form>

                            <!-- Calculation rows -->
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Items Total (MRP)</span>
                                    <span class="font-semibold">₹<?= $total ?></span>
                                </div>
                                
                                <?php if ($coupon_discount > 0) { ?>
                                    <div class="flex justify-between text-green-600 dark:text-green-400">
                                        <span>Coupon Discount (<?= htmlspecialchars($_SESSION['applied_coupon']) ?>)</span>
                                        <span>- ₹<?= $coupon_discount ?></span>
                                    </div>
                                <?php } ?>

                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Delivery Charges</span>
                                    <span class="text-green-600 dark:text-green-400 font-bold">FREE</span>
                                </div>

                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                                    <span>Total Amount</span>
                                    <span>₹<?= $final_total ?></span>
                                </div>
                            </div>

                            <!-- Place Order Action -->
                            <button onclick="handlePlaceOrder()" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition shadow-md shadow-orange-500/10 flex items-center justify-center gap-2">
                                <span class="material-icons">payment</span>
                                Place Order
                            </button>
                        </div>
                    </div>

                </div>
            <?php } ?>

        </main>

        <!-- Order Address Panel Popup -->
        <div id="addressPopup" class="fixed top-0 right-0 w-full h-full bg-black/40 hidden z-50 transition duration-300">
            <div id="addressPanel" class="absolute right-0 top-0 w-full max-w-[420px] h-full bg-white dark:bg-gray-800 shadow-2xl translate-x-full transition duration-300 overflow-y-auto flex flex-col justify-between">
                
                <div>
                    <!-- Panel Header -->
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="material-icons text-green-600">location_on</span>
                            Select Address
                        </h2>
                        <button onclick="closeOrderAddressPopup()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                            <span class="material-icons">close</span>
                        </button>
                    </div>

                    <!-- Panel Address List -->
                    <div class="p-5 space-y-4">
                        <?php if (!empty($allAddresses)) { ?>
                            <?php foreach ($allAddresses as $addr) { ?>
                                <form method="POST">
                                    <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                    <button type="submit" name="select_address" class="w-full text-left p-4 rounded-xl border transition-all duration-200 <?php echo ($address && $address['id'] == $addr['id']) ? 'border-green-600 bg-green-50/30 dark:bg-green-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'; ?>">
                                        <div class="flex justify-between items-start">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($addr['name']); ?></span>
                                            <span class="text-[9px] uppercase font-bold tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">
                                                <?php echo htmlspecialchars($addr['type']); ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                                            <?php echo htmlspecialchars($addr['address']); ?>, <?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?> - <?php echo htmlspecialchars($addr['pincode']); ?>
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 font-medium">Phone: <?php echo htmlspecialchars($addr['phone']); ?></p>
                                    </button>
                                </form>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="text-center py-12">
                                <span class="material-icons text-4xl text-gray-300 dark:text-gray-600">location_off</span>
                                <p class="text-sm text-gray-500 mt-2">No saved addresses found.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Add address bottom action -->
                <div class="p-5 border-t border-gray-100 dark:border-gray-700">
                    <button onclick="window.location.href='userAddAddress.php?from=cart'" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                        <span class="material-icons text-base">add</span>
                        Add New Address
                    </button>
                </div>

            </div>
        </div>

        <?php include "userfooter.php" ?>
    </div>

    <!-- SweetAlert Outcomes -->
    <?php if (!empty($coupon_msg)) { ?>
        <script>
            Swal.fire({
                icon: '<?php echo $coupon_status; ?>',
                title: 'Coupon Applied',
                text: '<?php echo $coupon_msg; ?>',
                confirmButtonColor: '#16a34a'
            });
        </script>
    <?php } ?>

    <!-- JS Actions -->
    <script>
        function openOrderAddressPopup() {
            const popup = document.getElementById("addressPopup");
            const panel = document.getElementById("addressPanel");
            popup.classList.remove("hidden");
            setTimeout(() => {
                panel.classList.remove("translate-x-full");
            }, 50);
        }

        function closeOrderAddressPopup() {
            const popup = document.getElementById("addressPopup");
            const panel = document.getElementById("addressPanel");
            panel.classList.add("translate-x-full");
            setTimeout(() => {
                popup.classList.add("hidden");
            }, 300);
        }

        // Place order validation
        function handlePlaceOrder() {
            <?php if (!$address) { ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'No Address Selected',
                    text: 'Please select or add a delivery address to place your order.',
                    confirmButtonColor: '#ea580c'
                });
            <?php } else { ?>
                window.location.href = 'userPayment.php';
            <?php } ?>
        }

        // Qty Increment AJAX trigger
        function qtyInc(cartId, currentQty) {
            if (currentQty >= 5) {
                Swal.fire({
                    icon: 'info',
                    title: 'Limit Reached',
                    text: 'You can add up to 5 items of a single product to your cart.',
                    confirmButtonColor: '#16a34a'
                });
                return;
            }
            updateQty(cartId, currentQty + 1);
        }

        // Qty Decrement AJAX trigger
        function qtyDec(cartId, currentQty) {
            if (currentQty <= 1) {
                return;
            }
            updateQty(cartId, currentQty - 1);
        }

        function updateQty(cartId, newQty) {
            $.ajax({
                url: 'usercartUpdate.php',
                type: 'POST',
                data: { id: cartId, qty: newQty },
                success: function(response) {
                    if (response && response.status === "error") {
                        Swal.fire({
                            icon: 'error',
                            title: 'Insufficient Stock',
                            text: response.message,
                            confirmButtonColor: '#ea580c'
                        });
                    } else {
                        window.location.reload();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating quantity.',
                        confirmButtonColor: '#16a34a'
                    });
                }
            });
        }
    </script>

    <script src="usernavbar.js"></script>
</body>

</html>