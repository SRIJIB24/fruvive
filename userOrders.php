<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$userid = $_SESSION['user_id'];

// Get filter inputs
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';
$filter_method = isset($_GET['payment_method']) ? trim($_GET['payment_method']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$query_str = "SELECT * FROM orders WHERE userid = :userid AND client_id = :client_id";
$params = [':userid' => $userid, ':client_id' => CLIENT_ID];

if ($search_id !== '') {
    $query_str .= " AND (order_id LIKE :search_id OR id = :search_id_num)";
    $params[':search_id'] = '%' . $search_id . '%';
    $params[':search_id_num'] = intval($search_id);
}

if ($filter_method !== '') {
    $query_str .= " AND payment_method = :payment_method";
    $params[':payment_method'] = $filter_method;
}

if ($filter_status !== '') {
    $query_str .= " AND status = :status";
    $params[':status'] = $filter_status;
}

$query_str .= " ORDER BY id DESC";

$stmt = $object->conn->prepare($query_str);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count cart items for navbar
$count = $object->cartCount($userid);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Fruvive</title>
    
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen transition-colors duration-300">

    <div class="main flex flex-col min-h-screen">
        <?php include "usernavbar.php" ?>

        <!-- Hero section -->
        <div class="text-center py-10 px-4 bg-gradient-to-b from-green-50/50 to-transparent dark:from-green-950/20">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight sm:text-4xl">My Order History</h1>
            <p class="max-w-2xl mx-auto mt-2 text-sm text-gray-500 dark:text-gray-400">Track, manage, and download invoices for all your past purchases.</p>
        </div>

        <!-- Main container -->
        <main class="flex-grow max-w-7xl mx-auto w-full px-4 md:px-8 pb-16">

            <!-- Filter Panel Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-5 shadow-sm mb-8">
                <form method="GET" action="userOrders.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    
                    <!-- Search by Order ID -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Search Order ID</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-icons text-[18px]">search</span>
                            </span>
                            <input type="text" name="search_id" value="<?= htmlspecialchars($search_id) ?>" placeholder="e.g. FRV-20260720-7"
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm focus:border-green-600 focus:ring-1 focus:ring-green-600 focus:outline-none dark:text-white transition">
                        </div>
                    </div>

                    <!-- Filter by Payment Method -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Payment Method</label>
                        <select name="payment_method" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm focus:border-green-600 focus:ring-1 focus:ring-green-600 focus:outline-none dark:text-white transition">
                            <option value="">All Payment Types</option>
                            <option value="COD" <?= $filter_method === 'COD' ? 'selected' : '' ?>>Cash on Delivery (COD)</option>
                            <option value="UPI" <?= $filter_method === 'UPI' ? 'selected' : '' ?>>UPI Payment</option>
                            <option value="CARD" <?= $filter_method === 'CARD' ? 'selected' : '' ?>>Credit/Debit Card</option>
                        </select>
                    </div>

                    <!-- Filter by Order Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Order Status</label>
                        <select name="status" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm focus:border-green-600 focus:ring-1 focus:ring-green-600 focus:outline-none dark:text-white transition">
                            <option value="">All Statuses</option>
                            <option value="Placed" <?= $filter_status === 'Placed' ? 'selected' : '' ?>>Placed / Paid</option>
                            <option value="Pending Payment" <?= $filter_status === 'Pending Payment' ? 'selected' : '' ?>>Pending Payment</option>
                        </select>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex gap-2">
                        <button type="submit" 
                            class="flex-grow bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md shadow-green-600/10 active:scale-98 transition text-sm flex items-center justify-center gap-1">
                            <span class="material-icons text-[18px]">filter_alt</span>
                            Apply Filters
                        </button>
                        
                        <?php if ($search_id !== '' || $filter_method !== '' || $filter_status !== '') { ?>
                            <a href="userOrders.php" 
                                class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold py-2.5 px-3 rounded-xl transition text-sm flex items-center justify-center">
                                <span class="material-icons text-[18px]">clear</span>
                            </a>
                        <?php } ?>
                    </div>

                </form>
            </div>

            <!-- Orders Cards List -->
            <div class="space-y-6">
                <?php if (count($orders) > 0) { ?>
                    <?php foreach ($orders as $order) { 
                        // Fetch items for this order
                        $stmt_items = $object->conn->prepare("
                            SELECT oi.*, p.pname, p.quant, p.img_url 
                            FROM order_items oi 
                            JOIN products p ON oi.productid = p.id 
                            WHERE oi.orderid = :orderid AND oi.client_id = :client_id
                        ");
                        $stmt_items->execute([':orderid' => $order['id'], ':client_id' => CLIENT_ID]);
                        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                        
                        $status = $order['status'];
                        $statusColor = 'text-amber-500 bg-amber-50 dark:bg-amber-950/20';
                        $statusIcon = 'pending';
                        if ($status === 'Placed') {
                            $statusColor = 'text-blue-650 bg-blue-50 dark:bg-blue-950/20';
                            $statusIcon = 'check_circle';
                        } elseif ($status === 'Dispatched') {
                            $statusColor = 'text-orange-500 bg-orange-50 dark:bg-orange-950/20';
                            $statusIcon = 'local_shipping';
                        } elseif ($status === 'Delivered') {
                            $statusColor = 'text-green-650 bg-green-50 dark:bg-green-950/20';
                            $statusIcon = 'task_alt';
                        } elseif ($status === 'Cancelled') {
                            $statusColor = 'text-red-650 bg-red-50 dark:bg-red-950/20';
                            $statusIcon = 'cancel';
                        } elseif ($status === 'Returned') {
                            $statusColor = 'text-purple-650 bg-purple-50 dark:bg-purple-950/20';
                            $statusIcon = 'assignment_return';
                        }
                    ?>
                        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm hover:shadow-lg transition duration-300 overflow-hidden">
                            <!-- Card Header -->
                            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/10 border-b border-gray-100 dark:border-gray-700 flex flex-wrap justify-between items-center gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-green-50 dark:bg-green-950/30 rounded-xl text-green-600 dark:text-green-400">
                                        <span class="material-icons text-[20px]">shopping_bag</span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Order ID</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($order['order_id'] ?: '#' . $order['id']) ?></p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-6">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Date Placed</p>
                                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-300"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Payment</p>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            <?= htmlspecialchars($order['payment_method']) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Total Amount</p>
                                        <p class="text-sm font-extrabold text-green-600 dark:text-green-450">₹<?= htmlspecialchars($order['total']) ?></p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-full <?= $statusColor ?>">
                                        <span class="material-icons text-[14px]"><?= $statusIcon ?></span>
                                        <?= htmlspecialchars($status === 'Placed' ? 'Placed / Paid' : $status) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Card Body (Items list) -->
                            <div class="p-6 divide-y divide-gray-150/60 dark:divide-gray-700">
                                <?php foreach ($items as $item) { ?>
                                    <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                        <div class="flex items-center gap-4 min-w-0">
                                            <img src="<?= htmlspecialchars($item['img_url'] ?: 'assets/image/product-image/default.png') ?>" alt="Product" class="w-16 h-16 object-contain rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-1">
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-sm text-gray-900 dark:text-white truncate"><?= htmlspecialchars($item['pname']) ?></h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pack size: <?= htmlspecialchars($item['quant']) ?></p>
                                                <p class="text-xs text-gray-400 mt-0.5">Qty: <?= htmlspecialchars($item['qty']) ?> &times; ₹<?= htmlspecialchars($item['price']) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-extrabold text-sm text-gray-900 dark:text-white">₹<?= htmlspecialchars($item['price'] * $item['qty']) ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="px-6 py-4 bg-gray-50/30 dark:bg-gray-900/5 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php if (!empty($order['delivery_boy'])) { ?>
                                        <span class="flex items-center gap-1.5">
                                            <span class="material-icons text-green-600 dark:text-green-400 text-sm">local_shipping</span>
                                            Delivery Agent: <strong class="text-gray-750 dark:text-white"><?= htmlspecialchars($order['delivery_boy']) ?></strong> (<?= htmlspecialchars($order['delivery_status'] ?: 'Pending') ?>)
                                        </span>
                                    <?php } else { ?>
                                        <span class="flex items-center gap-1.5 text-gray-400">
                                            <span class="material-icons text-sm">hourglass_empty</span>
                                            Awaiting delivery assignment.
                                        </span>
                                    <?php } ?>
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <!-- View Details Button -->
                                    <button onclick="viewDetails(<?= $order['id'] ?>)" class="bg-green-600 hover:bg-green-700 text-white font-bold text-xs py-2.5 px-5 rounded-xl shadow-md shadow-green-600/10 active:scale-98 transition flex items-center gap-1.5">
                                        <span class="material-icons text-base">route</span>
                                        Track & Manage Details
                                    </button>
                                    
                                    <!-- Download Invoice -->
                                    <a href="downloadInvoice.php?order_id=<?= $order['id'] ?>" class="border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300 font-bold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-1">
                                        <span class="material-icons text-base">download_for_offline</span>
                                        Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <!-- Empty State -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 text-center py-16 px-4 shadow-sm">
                        <span class="material-icons text-gray-300 dark:text-gray-600 text-6xl">receipt_long</span>
                        <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">No Orders Found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">We couldn't find any order history matching your current filter selection.</p>
                        <a href="index.php" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-xl transition shadow-md shadow-green-600/10 mt-6 text-sm">
                            <span class="material-icons text-[18px]">shopping_basket</span>
                            Start Shopping
                        </a>
                    </div>
                <?php } ?>
            </div>

        </main>

        <?php include "userfooter.php" ?>
    </div>

    <!-- Order Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 transform scale-95 transition-all duration-300">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-extrabold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                    <span class="material-icons text-green-600 dark:text-green-400">receipt_long</span>
                    Order Details & Tracking
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <!-- Modal Body (Loaded via AJAX) -->
            <div id="modalBody" class="p-6">
                <!-- Loader -->
                <div class="flex flex-col items-center justify-center py-10 space-y-3">
                    <div class="w-8 h-8 border-2 border-green-600 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Loading details...</span>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
                    Close Window
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const modal = document.getElementById("detailsModal");
        const modalContent = modal.querySelector(".transform");
        const modalBody = document.getElementById("modalBody");

        function viewDetails(orderId) {
            // Open modal container
            modal.classList.remove("opacity-0", "pointer-events-none");
            modalContent.classList.remove("scale-95");
            modalContent.classList.add("scale-100");

            // Display loading spinner
            modalBody.innerHTML = `
                <div class="flex flex-col items-center justify-center py-10 space-y-3">
                    <div class="w-8 h-8 border-2 border-green-600 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Loading details...</span>
                </div>`;

            // Call AJAX
            fetch(`userOrderDetails.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(err => {
                    modalBody.innerHTML = `
                        <div class="text-center text-red-500 py-6">
                            <span class="material-icons text-4xl">error_outline</span>
                            <p class="text-sm mt-2 font-semibold">Failed to load order details. Please try again.</p>
                        </div>`;
                });
        }

        function closeModal() {
            modal.classList.add("opacity-0", "pointer-events-none");
            modalContent.classList.remove("scale-100");
            modalContent.classList.add("scale-95");
        }

        // Close on background click
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        function openOrderReview(pid, pname) {
            closeModal(); // close the order details modal
            
            Swal.fire({
                title: 'Rate & Review',
                html: `
                    <div class="text-left space-y-4">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">How would you rate <strong class="text-gray-850 dark:text-white">${pname}</strong>?</p>
                        
                        <!-- Stars Selector -->
                        <div class="flex items-center gap-1.5 my-3" id="swalStars">
                            <span class="material-icons text-gray-300 cursor-pointer text-3xl hover:scale-110 active:scale-95 transition" data-val="1">star_border</span>
                            <span class="material-icons text-gray-300 cursor-pointer text-3xl hover:scale-110 active:scale-95 transition" data-val="2">star_border</span>
                            <span class="material-icons text-gray-300 cursor-pointer text-3xl hover:scale-110 active:scale-95 transition" data-val="3">star_border</span>
                            <span class="material-icons text-gray-300 cursor-pointer text-3xl hover:scale-110 active:scale-95 transition" data-val="4">star_border</span>
                            <span class="material-icons text-gray-300 cursor-pointer text-3xl hover:scale-110 active:scale-95 transition" data-val="5">star_border</span>
                        </div>
                        
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Your Feedback</label>
                        <textarea id="swalComment" rows="3" placeholder="Share your experience with this fresh fruit..." class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm focus:border-green-600 focus:outline-none transition dark:text-white"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit Review',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#d33',
                didOpen: () => {
                    let selectedRating = 0;
                    const stars = document.querySelectorAll('#swalStars .material-icons');
                    stars.forEach((star) => {
                        star.addEventListener('click', function() {
                            selectedRating = parseInt(this.getAttribute('data-val'));
                            stars.forEach((s, idx) => {
                                if (idx < selectedRating) {
                                    s.innerText = 'star';
                                    s.classList.remove('text-gray-300');
                                    s.classList.add('text-amber-400');
                                } else {
                                    s.innerText = 'star_border';
                                    s.classList.remove('text-amber-400');
                                    s.classList.add('text-gray-300');
                                }
                            });
                        });
                    });
                    window.swalSelectedRating = () => selectedRating;
                },
                preConfirm: () => {
                    const rating = window.swalSelectedRating();
                    const comment = document.getElementById('swalComment').value.trim();
                    if (rating === 0) {
                        Swal.showValidationMessage('Please select a star rating first.');
                        return false;
                    }
                    return { rating: rating, comment: comment };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    // Submit via Fetch API
                    fetch('submitReview.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `productid=${pid}&rating=${data.rating}&comment=${encodeURIComponent(data.comment)}`
                    })
                    .then(res => res.json())
                    .then(resData => {
                        if (resData.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thank You!',
                                text: 'Your review has been submitted successfully.',
                                confirmButtonColor: '#16a34a'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Submission Failed',
                                text: resData.message || 'Unable to save review.',
                                confirmButtonColor: '#ea580c'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred during submission.',
                            confirmButtonColor: '#ea580c'
                        });
                    });
                }
            });
        }

        function initiateWholeOrderReturn(orderId) {
            closeModal();
            Swal.fire({
                title: 'Return Entire Order',
                text: 'Please select a reason for returning all items:',
                input: 'select',
                inputOptions: {
                    'product_missing': 'Product Missing',
                    'damaged_product': 'Damaged Product',
                    'unsatisfactory_quality': 'Unsatisfactory Quality',
                    'wrong_item': 'Wrong Item Delivered',
                    'other': 'Other'
                },
                inputPlaceholder: '-- Select Reason --',
                showCancelButton: true,
                confirmButtonText: 'Submit Return Request',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You must select a reason!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitReturnRequest(orderId, null, 1, result.value);
                }
            });
        }

        function initiateProductReturn(orderId, productId, productName, maxQty) {
            closeModal();
            Swal.fire({
                title: `Return Item: ${productName}`,
                text: 'Please select a reason for returning this item:',
                input: 'select',
                inputOptions: {
                    'product_missing': 'Product Missing',
                    'damaged_product': 'Damaged Product',
                    'unsatisfactory_quality': 'Unsatisfactory Quality',
                    'wrong_item': 'Wrong Item Delivered',
                    'other': 'Other'
                },
                inputPlaceholder: '-- Select Reason --',
                showCancelButton: true,
                confirmButtonText: 'Submit Return',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You must select a reason!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = result.value;
                    if (maxQty > 1) {
                        Swal.fire({
                            title: 'Quantity to Return',
                            text: `Enter quantity (1 to ${maxQty}):`,
                            input: 'number',
                            inputValue: maxQty,
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            confirmButtonColor: '#ef4444',
                            inputValidator: (val) => {
                                const q = parseInt(val);
                                if (isNaN(q) || q < 1 || q > maxQty) {
                                    return `Please enter a valid quantity between 1 and ${maxQty}!`;
                                }
                            }
                        }).then((qtyResult) => {
                            if (qtyResult.isConfirmed) {
                                submitReturnRequest(orderId, productId, parseInt(qtyResult.value), reason);
                            }
                        });
                    } else {
                        submitReturnRequest(orderId, productId, 1, reason);
                    }
                }
            });
        }

        function submitReturnRequest(orderId, productId, qty, reason) {
            fetch('submitReturn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, product_id: productId, qty: qty, reason: reason })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#16a34a'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: data.message,
                        confirmButtonColor: '#ea580c'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to communicate with the server.',
                    confirmButtonColor: '#ea580c'
                });
            });
        }
    </script>
    <script src="usernavbar.js?v=1.3"></script>
</body>

</html>
