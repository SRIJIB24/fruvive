<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();
$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$address = $object->fetchAddress($userid);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Addresses - Fruvive</title>
    
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

        <!-- Main Content -->
        <main class="flex-grow max-w-4xl mx-auto w-full px-4 py-8 md:py-12">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-icons text-green-600">location_on</span>
                        My Addresses
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your shipping and billing addresses.</p>
                </div>

                <button onclick="window.location.href='userAddAddress.php'" 
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl transition flex items-center gap-2 shadow-md shadow-green-600/10 justify-center">
                    <span class="material-icons text-[20px]">add</span>
                    Add New Address
                </button>
            </div>

            <!-- Address List -->
            <div class="space-y-4">

                <?php if (!empty($address)) : ?>
                    <?php foreach ($address as $row) : ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-5 md:p-6 shadow-sm hover:shadow-md transition duration-300">

                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">

                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <!-- Name -->
                                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </h3>
                                        
                                        <!-- Type Tag (Home / Work) -->
                                        <span class="text-[10px] uppercase font-bold tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">
                                            <?php echo htmlspecialchars($row['type'] ?? 'home'); ?>
                                        </span>

                                        <!-- Default Badge -->
                                        <?php if (isset($row['is_default']) && $row['is_default']) { ?>
                                            <span class="text-[10px] font-bold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded border border-green-200/50 dark:border-green-800/30">
                                                Default
                                            </span>
                                        <?php } ?>
                                    </div>

                                    <!-- Address body -->
                                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                        <?php if (!empty($row['locality'])) { ?>
                                            <p class="font-medium"><?php echo htmlspecialchars($row['locality']); ?></p>
                                        <?php } ?>
                                        
                                        <p class="leading-relaxed"><?php echo htmlspecialchars($row['address']); ?></p>
                                        
                                        <p><?php echo htmlspecialchars($row['city'] . ', ' . $row['state'] . ' - ' . $row['pincode']); ?></p>
                                        
                                        <?php if (!empty($row['landmark'])) { ?>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                <span class="font-semibold">Landmark:</span> <?php echo htmlspecialchars($row['landmark']); ?>
                                            </p>
                                        <?php } ?>
                                    </div>

                                    <!-- Contact details -->
                                    <div class="pt-2 flex flex-col gap-1 text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-icons text-gray-400 text-[18px]">phone</span>
                                            <span><?php echo htmlspecialchars($row['phone']); ?></span>
                                        </div>
                                        <?php if (!empty($row['alt_phone'])) { ?>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="material-icons text-gray-400 text-[16px]">call</span>
                                                <span>Alt: <?php echo htmlspecialchars($row['alt_phone']); ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2 self-stretch sm:self-auto justify-end border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100 dark:border-gray-700">
                                    
                                    <!-- Edit Button -->
                                    <a href="editAddress.php?id=<?php echo urlencode($row['id']); ?>" 
                                        class="flex items-center gap-1 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-3 py-1.5 rounded-xl transition font-medium">
                                        <span class="material-icons text-[18px]">edit</span>
                                        Edit
                                    </a>

                                    <!-- Delete Button -->
                                    <form method="POST" action="deleteAddress.php" onsubmit="return confirm('Are you sure you want to delete this address?');" class="inline">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <button type="submit" 
                                            class="flex items-center gap-1 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1.5 rounded-xl transition font-medium">
                                            <span class="material-icons text-[18px]">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                    
                                </div>

                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center shadow-sm">
                        <span class="material-icons text-5xl text-gray-300 dark:text-gray-600">location_off</span>
                        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mt-4">No Saved Addresses</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">You haven't saved any addresses yet. Add one to speed up checkout.</p>
                        <button onclick="window.location.href='userAddAddress.php'" 
                            class="mt-6 bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                            Add First Address
                        </button>
                    </div>
                <?php endif; ?>

            </div>

        </main>

        <?php include "userfooter.php" ?>
    </div>

    <script src="usernavbar.js"></script>
</body>

</html>