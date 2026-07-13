<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl === -1 ) {
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <!-- ✅ Correct Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
</head>

<body class="bg-gray-100">

<div class="min-h-screen flex flex-col">

    <?php include "usernavbar.php" ?>

    <!-- MAIN CONTENT -->
    <main class="flex-grow">

        <!-- Hero Section -->
        <section class="bg-green-600 text-white py-16">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Fresh Fruits, Delivered to Your Doorstep
                </h1>
                <p class="text-lg mb-6">
                    Handpicked, farm-fresh fruits packed with nutrition and delivered
                    straight from trusted farmers.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="#" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                        Shop Now
                    </a>
                    <a href="allfruits.php" class="border border-white px-6 py-3 rounded-lg hover:bg-white hover:text-green-600 transition">
                        Explore Fruits
                    </a>
                </div>
            </div>
        </section>

        <!-- Featured Fruits -->
        <section class="py-16">
            <div class="max-w-6xl mx-auto px-6">
                <h2 class="text-3xl font-bold text-center mb-12">
                    Our Popular Fruits
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">

                    <!-- Apple -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                        <img src="assets/image/product-image/1_3.jpg" alt="Fresh Apples" class="w-full h-48 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="text-xl font-semibold mb-2">Fresh Apples</h3>
                            <p class="text-gray-600 mb-2">Crisp, juicy and full of goodness</p>
                            <span class="block font-bold text-green-600 mb-3">₹180 / kg</span>
                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                    <!-- Banana -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                        <img src="assets/image/product-image/1_1.jpg" alt="Bananas" class="w-full h-48 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="text-xl font-semibold mb-2">Organic Bananas</h3>
                            <p class="text-gray-600 mb-2">Sweet & energy boosting</p>
                            <span class="block font-bold text-green-600 mb-3">₹60 / dozen</span>
                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                    <!-- Orange -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                        <img src="assets/image/product-image/1_29.jpg" alt="Oranges" class="w-full h-48 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="text-xl font-semibold mb-2">Juicy Oranges</h3>
                            <p class="text-gray-600 mb-2">Rich in Vitamin C</p>
                            <span class="block font-bold text-green-600 mb-3">₹120 / kg</span>
                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                    <!-- Mango -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                        <img src="assets/image/product-image/2_10.jpg" alt="Mangoes" class="w-full h-48 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="text-xl font-semibold mb-2">Seasonal Mangoes</h3>
                            <p class="text-gray-600 mb-2">Sweet, ripe & delicious</p>
                            <span class="block font-bold text-green-600 mb-3">₹250 / kg</span>
                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

</div>

<?php include "userfooter.php" ?>
<script src="usernavbar.js"></script>

</body>
</html>