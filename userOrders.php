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
    $query_str .= " AND id = :search_id";
    $params[':search_id'] = intval($search_id);
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
                            <input type="number" name="search_id" value="<?= htmlspecialchars($search_id) ?>" placeholder="e.g. 5"
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

            <!-- Orders Table List Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden">
                
                <?php if (count($orders) > 0) { ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/40 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-6 py-4">Order ID</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Payment Method</th>
                                    <th class="px-6 py-4">Amount</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                <?php foreach ($orders as $order) { ?>
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition">
                                        <!-- ID -->
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                            #<?= $order['id'] ?>
                                        </td>
                                        
                                        <!-- Date -->
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                            <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                                        </td>
                                        
                                        <!-- Payment Method -->
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full 
                                                <?= $order['payment_method'] === 'COD' 
                                                    ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' 
                                                    : 'bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400' ?>">
                                                <span class="material-icons text-[14px]">
                                                    <?= $order['payment_method'] === 'COD' ? 'payments' : 'account_balance_wallet' ?>
                                                </span>
                                                <?= htmlspecialchars($order['payment_method']) ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Total -->
                                        <td class="px-6 py-4 font-extrabold text-gray-900 dark:text-white">
                                            ₹<?= htmlspecialchars($order['total']) ?>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="px-6 py-4">
                                            <?php if ($order['status'] === 'Placed') { ?>
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600 dark:text-green-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    Placed / Paid
                                                </span>
                                            <?php } else { ?>
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-500 dark:text-amber-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Pending Payment
                                                </span>
                                            <?php } ?>
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td class="px-6 py-4 text-right flex items-center justify-end gap-2.5">
                                            <!-- View Details -->
                                            <button onclick="viewDetails(<?= $order['id'] ?>)"
                                                class="inline-flex items-center justify-center p-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition"
                                                title="View Details">
                                                <span class="material-icons text-[18px]">visibility</span>
                                            </button>
                                            
                                            <!-- Download Invoice -->
                                            <a href="downloadInvoice.php?order_id=<?= $order['id'] ?>"
                                                class="inline-flex items-center justify-center p-2 rounded-xl bg-green-50 hover:bg-green-100 dark:bg-green-950/20 dark:hover:bg-green-950/40 text-green-600 dark:text-green-400 transition"
                                                title="Download Invoice">
                                                <span class="material-icons text-[18px]">download_for_offline</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <!-- Empty State -->
                    <div class="text-center py-16 px-4">
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
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700 transform scale-95 transition-all duration-300">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-extrabold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                    <span class="material-icons text-green-600 dark:text-green-400">receipt_long</span>
                    Order Details
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
    </script>
    <script src="usernavbar.js"></script>
</body>

</html>
