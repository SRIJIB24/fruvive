<?php
if (file_exists(__DIR__ . "/credentials.php")) {
    require_once __DIR__ . "/credentials.php";
} else {
    require_once __DIR__ . "/credentials.template.php";
}
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$userid = $_SESSION['user_id'];

// Handle payment return redirection verification
if (isset($_GET['cf_order_id'])) {
    $orderid = intval($_GET['cf_order_id']);
    // Fetch local order to verify ownership
    $stmt = $object->conn->prepare("SELECT * FROM orders WHERE id = :id AND userid = :userid AND client_id = :client_id");
    $stmt->execute([':id' => $orderid, ':userid' => $userid, ':client_id' => CLIENT_ID]);
    $local_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($local_order && $local_order['status'] === 'Pending Payment') {
        // Query Cashfree for order status
        $url = "https://sandbox.cashfree.com/pg/orders/" . $orderid;
        $headers = [
            "x-client-id: " . CASHFREE_APP_ID,
            "x-client-secret: " . CASHFREE_SECRET_KEY,
            "x-api-version: 2023-08-01"
        ];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $cf_response = curl_exec($curl);
        curl_close($curl);

        if ($cf_response) {
            $cf_order = json_decode($cf_response, true);
            if (isset($cf_order['order_status']) && $cf_order['order_status'] === 'PAID') {
                // Success: Update status to Placed
                $upd = $object->conn->prepare("UPDATE orders SET status = 'Placed' WHERE id = :id AND client_id = :client_id");
                $upd->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);

                // Reduce stock
                $cart = $object->fetchCart();
                foreach ($cart as $item) {
                    $pid = $item['productid'];
                    $qty = $item['qty'];
                    $stmt_pack = $object->conn->prepare("SELECT quant FROM products WHERE id = :id AND client_id = :client_id");
                    $stmt_pack->execute([':id' => $pid, ':client_id' => CLIENT_ID]);
                    $packStr = $stmt_pack->fetchColumn();
                    $packVal = intval($packStr) ?: 1;
                    $reduce = $packVal * $qty;

                    $stmt_stock = $object->conn->prepare("UPDATE stock_in SET total_quant = total_quant - :reduce WHERE proid = :pid AND client_id = :client_id");
                    $stmt_stock->execute([':reduce' => $reduce, ':pid' => $pid, ':client_id' => CLIENT_ID]);
                }

                $object->clearCart($userid);
                unset($_SESSION['applied_coupon']);
                unset($_SESSION['coupon_type']);
                unset($_SESSION['coupon_value']);
                unset($_SESSION['discount_amount']);

                // Generate Invoice and Send Email
                try {
                    require_once "invoiceGenerator.php";
                    require_once "emailSender.php";
                    $generator = new invoiceGenerator();
                    $pdf_content = $generator->generate($orderid);
                    if ($pdf_content) {
                        $profile = $object->getprofile($userid);
                        $to_email = !empty($profile['email']) ? $profile['email'] : "email@example.com";
                        $to_name = !empty($profile['username']) ? $profile['username'] : "Customer";
                        emailSender::sendInvoice($to_email, $to_name, $orderid, $pdf_content);
                    }
                } catch (Exception $e) {
                    error_log("Invoice/Email dispatch failed: " . $e->getMessage());
                }

                header("Location: orderSuccess.php");
                exit();
            } else {
                // Fail: Clean up pending order items and order
                $del_items = $object->conn->prepare("DELETE FROM order_items WHERE orderid = :orderid AND client_id = :client_id");
                $del_items->execute([':orderid' => $orderid, ':client_id' => CLIENT_ID]);
                $del_order = $object->conn->prepare("DELETE FROM orders WHERE id = :id AND client_id = :client_id");
                $del_order->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);

                $_SESSION['payment_error'] = "Payment was not successful. Status: " . ($cf_order['order_status'] ?? 'CANCELLED') . ". Please try again.";
                header("Location: userPayment.php");
                exit();
            }
        }
    }
}

if (isset($_POST['paynow'])) {
    $payment = $_POST['payment_method'];

    $cart = $object->fetchCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }

    $discount = isset($_SESSION['discount_amount']) ? $_SESSION['discount_amount'] : 0;
    $final_total = max(0, $total - $discount);

    $initial_status = ($payment === 'COD') ? 'Placed' : 'Pending Payment';
    
    // Insert local order
    $insert = $object->conn->prepare("INSERT INTO orders(userid, total, payment_method, status, client_id)
    VALUES(:userid, :total, :payment, :status, :client_id)");
    $insert->execute([':userid' => $userid, ':total' => $final_total, ':payment' => $payment, ':status' => $initial_status, ':client_id' => CLIENT_ID]);
    $orderid = $object->conn->lastInsertId();

    // Insert local items
    foreach ($cart as $item) {
        $pid = $item['productid'];
        $qty = $item['qty'];
        $price = $item['price'];
        $object->insertOrderItem($orderid, $pid, $qty, $price);
    }

    if ($payment === 'COD') {
        // Cash on delivery completes immediately
        foreach ($cart as $item) {
            $pid = $item['productid'];
            $qty = $item['qty'];
            $stmt_pack = $object->conn->prepare("SELECT quant FROM products WHERE id = :id AND client_id = :client_id");
            $stmt_pack->execute([':id' => $pid, ':client_id' => CLIENT_ID]);
            $packStr = $stmt_pack->fetchColumn();
            $packVal = intval($packStr) ?: 1;
            $reduce = $packVal * $qty;

            $stmt_stock = $object->conn->prepare("UPDATE stock_in SET total_quant = total_quant - :reduce WHERE proid = :pid AND client_id = :client_id");
            $stmt_stock->execute([':reduce' => $reduce, ':pid' => $pid, ':client_id' => CLIENT_ID]);
        }
        $object->clearCart($userid);
        unset($_SESSION['applied_coupon']);
        unset($_SESSION['coupon_type']);
        unset($_SESSION['coupon_value']);
        unset($_SESSION['discount_amount']);

        // Generate Invoice and Send Email
        try {
            require_once "invoiceGenerator.php";
            require_once "emailSender.php";
            $generator = new invoiceGenerator();
            $pdf_content = $generator->generate($orderid);
            if ($pdf_content) {
                $profile = $object->getprofile($userid);
                $to_email = !empty($profile['email']) ? $profile['email'] : "email@example.com";
                $to_name = !empty($profile['username']) ? $profile['username'] : "Customer";
                emailSender::sendInvoice($to_email, $to_name, $orderid, $pdf_content);
            }
        } catch (Exception $e) {
            error_log("Invoice/Email dispatch failed: " . $e->getMessage());
        }

        header("Location: orderSuccess.php");
        exit();
    } else {
        // Online Payment (UPI or CARD) via Cashfree
        $cf_url = "https://sandbox.cashfree.com/pg/orders";
        $cf_headers = [
            "Content-Type: application/json",
            "x-client-id: " . CASHFREE_APP_ID,
            "x-client-secret: " . CASHFREE_SECRET_KEY,
            "x-api-version: 2023-08-01"
        ];
        
        $profile = $object->getprofile($userid);
        $customer_phone = !empty($profile['phone']) ? $profile['phone'] : "9999999999";
        $customer_name = !empty($profile['username']) ? $profile['username'] : "Customer";
        $customer_email = !empty($profile['email']) ? $profile['email'] : "email@example.com";

        $cf_payload = [
            "order_id" => (string)$orderid,
            "order_amount" => (float)$final_total,
            "order_currency" => "INR",
            "customer_details" => [
                "customer_id" => "cust_" . $userid,
                "customer_name" => $customer_name,
                "customer_email" => $customer_email,
                "customer_phone" => $customer_phone
            ],
            "order_meta" => [
                "return_url" => "http://localhost/fruvive/userPayment.php?cf_order_id=" . $orderid
            ]
        ];

        $cf_curl = curl_init($cf_url);
        curl_setopt($cf_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cf_curl, CURLOPT_POST, true);
        curl_setopt($cf_curl, CURLOPT_POSTFIELDS, json_encode($cf_payload));
        curl_setopt($cf_curl, CURLOPT_HTTPHEADER, $cf_headers);

        $cf_response = curl_exec($cf_curl);
        $cf_err = curl_error($cf_curl);
        curl_close($cf_curl);

        if ($cf_err) {
            $_SESSION['payment_error'] = "Unable to connect to Cashfree payment gateway. Please try again.";
            // clean up pending order
            $del_items = $object->conn->prepare("DELETE FROM order_items WHERE orderid = :orderid AND client_id = :client_id");
            $del_items->execute([':orderid' => $orderid, ':client_id' => CLIENT_ID]);
            $del_order = $object->conn->prepare("DELETE FROM orders WHERE id = :id AND client_id = :client_id");
            $del_order->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);

            header("Location: userPayment.php");
            exit();
        } else {
            $cf_data = json_decode($cf_response, true);
            if (isset($cf_data['payment_session_id'])) {
                $_SESSION['cf_payment_session_id'] = $cf_data['payment_session_id'];
                header("Location: userPayment.php");
                exit();
            } else {
                $_SESSION['payment_error'] = "Cashfree PG Error: " . ($cf_data['message'] ?? 'Unable to initialize transaction.');
                // clean up pending order
                $del_items = $object->conn->prepare("DELETE FROM order_items WHERE orderid = :orderid AND client_id = :client_id");
                $del_items->execute([':orderid' => $orderid, ':client_id' => CLIENT_ID]);
                $del_order = $object->conn->prepare("DELETE FROM orders WHERE id = :id AND client_id = :client_id");
                $del_order->execute([':id' => $orderid, ':client_id' => CLIENT_ID]);

                header("Location: userPayment.php");
                exit();
            }
        }
    }
}

$cart = $object->fetchCart();
$address = $object->fetchSelectAddress($userid);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Fruvive</title>

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

        <!-- Checkout Main Container -->
        <main class="flex-grow max-w-6xl mx-auto w-full px-4 py-8 md:py-12">

            <!-- Progress Stepper -->
            <div class="mb-8 flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs sm:text-sm font-semibold select-none">
                <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                    <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-xs">1</span>
                    <span>Cart</span>
                </div>
                <span class="text-gray-300 dark:text-gray-700">/</span>
                <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                    <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-xs">2</span>
                    <span>Address</span>
                </div>
                <span class="text-gray-300 dark:text-gray-700">/</span>
                <div class="flex items-center gap-2 text-orange-500">
                    <span class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-950/30 flex items-center justify-center text-xs">3</span>
                    <span>Payment</span>
                </div>
            </div>

            <!-- Page Title -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-icons text-green-600 dark:text-green-400">security</span>
                    Secure Checkout
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Complete your purchase safely using one of our trusted payment options.</p>
            </div>

            <!-- Delivery Address Summary -->
            <?php if ($address) { ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition">
                    <div class="flex items-start gap-3">
                        <span class="material-icons text-green-600 dark:text-green-400 mt-0.5">location_on</span>
                        <div class="space-y-1">
                            <p class="text-sm text-gray-900 dark:text-white font-semibold">
                                Deliver to: <span class="text-green-600 dark:text-green-400 font-bold"><?php echo htmlspecialchars($address['name']); ?></span>
                                <span class="ml-2 text-[10px] uppercase font-bold tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">
                                    <?php echo htmlspecialchars($address['type']); ?>
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-2xl">
                                <?php echo htmlspecialchars($address['address']); ?>, <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> - <?php echo htmlspecialchars($address['pincode']); ?>
                            </p>
                        </div>
                    </div>
                    <button onclick="window.location.href='usercart.php'" class="w-full sm:w-auto border border-green-600 text-green-600 dark:border-green-400 dark:text-green-400 px-5 py-2 text-xs font-bold rounded-xl hover:bg-green-50 dark:hover:bg-green-950/20 transition">
                        Change Address
                    </button>
                </div>
            <?php } ?>

            <?php if (empty($cart)) { ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-16 text-center shadow-sm">
                    <span class="material-icons text-6xl text-gray-300 dark:text-gray-600">shopping_cart</span>
                    <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 mt-4">Your Cart is Empty</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Add fresh organic products before heading to payment.</p>
                    <a href="index.php" class="mt-6 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-md shadow-green-600/10">
                        Browse Products
                    </a>
                </div>
            <?php } else { ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left: Payment Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <form method="POST">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                    <span class="material-icons text-green-600 dark:text-green-400">payment</span>
                                    Select Payment Method
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Cash on Delivery -->
                                    <label class="payment-method-card relative flex flex-col justify-between p-5 border border-gray-200 dark:border-gray-700 rounded-2xl cursor-pointer hover:shadow-md transition-all duration-200 bg-gray-50/50 dark:bg-gray-900/20 select-none">
                                        <input type="radio" name="payment_method" value="COD" checked class="hidden">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="material-icons text-gray-500 dark:text-gray-400 text-3xl">local_shipping</span>
                                            <span class="checkmark-icon material-icons text-green-600 dark:text-green-400 hidden text-xl">check_circle</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Cash on Delivery</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pay with cash when order arrives.</p>
                                        </div>
                                    </label>

                                    <!-- UPI Payments -->
                                    <label class="payment-method-card relative flex flex-col justify-between p-5 border border-gray-200 dark:border-gray-700 rounded-2xl cursor-pointer hover:shadow-md transition-all duration-200 bg-gray-50/50 dark:bg-gray-900/20 select-none">
                                        <input type="radio" name="payment_method" value="UPI" class="hidden">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="material-icons text-gray-500 dark:text-gray-400 text-3xl">qr_code_scanner</span>
                                            <span class="checkmark-icon material-icons text-green-600 dark:text-green-400 hidden text-xl">check_circle</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">UPI</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Instant payment via PhonePe, GPay, Paytm.</p>
                                        </div>
                                    </label>

                                    <!-- CARD -->
                                    <label class="payment-method-card relative flex flex-col justify-between p-5 border border-gray-200 dark:border-gray-700 rounded-2xl cursor-pointer hover:shadow-md transition-all duration-200 bg-gray-50/50 dark:bg-gray-900/20 select-none">
                                        <input type="radio" name="payment_method" value="CARD" class="hidden">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="material-icons text-gray-500 dark:text-gray-400 text-3xl">credit_card</span>
                                            <span class="checkmark-icon material-icons text-green-600 dark:text-green-400 hidden text-xl">check_circle</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Credit/Debit Card</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Visa, Mastercard, RuPay cards supported.</p>
                                        </div>
                                    </label>
                                </div>

                                <button type="submit" name="paynow" class="mt-8 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl transition shadow-md shadow-green-600/10 flex items-center justify-center gap-2">
                                    <span class="material-icons">security</span>
                                    COMPLETE ORDER & PAY
                                </button>
                            </div>
                        </form>

                        <!-- Safe Checkout Banner -->
                        <div class="bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-200/50 dark:border-gray-800 p-5 flex flex-wrap justify-around items-center gap-4">
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <span class="material-icons text-green-600 dark:text-green-400 text-lg">lock</span>
                                SSL Encrypted Connection
                            </div>
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <span class="material-icons text-green-600 dark:text-green-400 text-lg">verified_user</span>
                                100% Safe Payments
                            </div>
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <span class="material-icons text-green-600 dark:text-green-400 text-lg">local_shipping</span>
                                Fresh Quality Guaranteed
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sticky Order Summary -->
                    <div class="lg:col-span-1 lg:sticky lg:top-[80px] lg:self-start space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                                <span class="material-icons text-green-600 dark:text-green-400">receipt_long</span>
                                Order Summary
                            </h3>

                            <!-- Items List -->
                            <div class="max-h-48 overflow-y-auto space-y-3 pr-1">
                                <?php 
                                $total = 0;
                                foreach ($cart as $item) { 
                                    $sub = $item['price'] * $item['qty'];
                                    $total += $sub;
                                ?>
                                    <div class="flex justify-between items-center text-sm gap-2">
                                        <div class="min-w-0 flex-grow">
                                            <p class="font-semibold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($item['pname']) ?></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Qty: <?= htmlspecialchars($item['qty']) ?></p>
                                        </div>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">₹<?= htmlspecialchars($sub) ?></span>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- Calculations -->
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-3 text-sm">
                                <?php 
                                $discount = isset($_SESSION['discount_amount']) ? $_SESSION['discount_amount'] : 0;
                                $final_total = max(0, $total - $discount);
                                ?>
                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                    <span>Subtotal (MRP)</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">₹<?= htmlspecialchars($total) ?></span>
                                </div>
                                
                                <?php if ($discount > 0) { ?>
                                    <div class="flex justify-between text-green-600 dark:text-green-400 font-semibold">
                                        <span>Coupon Discount</span>
                                        <span>- ₹<?= htmlspecialchars($discount) ?></span>
                                    </div>
                                <?php } ?>

                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                    <span>Delivery Fee</span>
                                    <span class="text-green-600 dark:text-green-400 font-bold">FREE</span>
                                </div>

                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                                    <span>Total Amount</span>
                                    <span class="text-green-600 dark:text-green-400">₹<?= htmlspecialchars($final_total) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            <?php } ?>

        </main>

        <?php include "userfooter.php" ?>
    </div>

    <!-- Payment Cards Selection Animation Script & Cashfree SDK Integration -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const cards = document.querySelectorAll(".payment-method-card");
            
            function updatePaymentCards() {
                cards.forEach(card => {
                    const radio = card.querySelector("input[type='radio']");
                    const checkmark = card.querySelector(".checkmark-icon");
                    if (radio.checked) {
                        card.classList.add("border-green-600", "bg-green-50/30", "dark:bg-green-950/20", "dark:border-green-400");
                        card.classList.remove("border-gray-200", "dark:border-gray-700", "bg-gray-50/50", "dark:bg-gray-900/20");
                        if (checkmark) checkmark.classList.remove("hidden");
                    } else {
                        card.classList.remove("border-green-600", "bg-green-50/30", "dark:bg-green-950/20", "dark:border-green-400");
                        card.classList.add("border-gray-200", "dark:border-gray-700", "bg-gray-50/50", "dark:bg-gray-900/20");
                        if (checkmark) checkmark.classList.add("hidden");
                    }
                });
            }

            cards.forEach(card => {
                card.addEventListener("click", () => {
                    const radio = card.querySelector("input[type='radio']");
                    radio.checked = true;
                    updatePaymentCards();
                });
            });

            // Initial styling call
            updatePaymentCards();
        });
    </script>

    <!-- Cashfree PG Initialization -->
    <?php if (isset($_SESSION['cf_payment_session_id'])) { 
        $session_id = $_SESSION['cf_payment_session_id'];
        unset($_SESSION['cf_payment_session_id']);
    ?>
        <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const cashfree = Cashfree({ mode: "sandbox" });
                cashfree.checkout({
                    paymentSessionId: "<?php echo htmlspecialchars($session_id); ?>",
                    redirectTarget: "_self"
                });
            });
        </script>
    <?php } ?>

    <!-- Payment Failure Alert Notification -->
    <?php if (isset($_SESSION['payment_error'])) { 
        $err = $_SESSION['payment_error'];
        unset($_SESSION['payment_error']);
    ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Unsuccessful',
                    text: '<?php echo htmlspecialchars($err); ?>',
                    confirmButtonColor: '#ea580c'
                });
            });
        </script>
    <?php } ?>

    <script src="usernavbar.js"></script>
</body>

</html>