<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed Successfully - Fruvive</title>
    
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

        <!-- Success content wrapper -->
        <main class="flex-grow flex items-center justify-center px-4 py-16">
            <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-8 shadow-lg text-center space-y-6 transition">
                
                <!-- Success icon -->
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 shadow-sm">
                    <span class="material-icons text-5xl">check_circle</span>
                </div>

                <!-- Text -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold text-gray-950 dark:text-white">Order Placed Successfully!</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        Thank you for your order! Your organic fruits are being packed and will be delivered shortly.
                    </p>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700 pt-6 space-y-3">
                    <a href="index.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition shadow-md shadow-green-600/10">
                        Continue Shopping
                    </a>
                    
                    <a href="userProfile.php" class="block w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold py-3 rounded-xl transition">
                        View Account Profile
                    </a>
                </div>

            </div>
        </main>

        <?php include "userfooter.php" ?>
    </div>

    <!-- Scripts -->
    <script src="usernavbar.js"></script>
</body>

</html>
