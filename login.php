<?php
require_once "signlogFunc.php";

if (isset($_POST['login'])) {
    $user = new users();
    $user->email = $_POST['email'];
    $user->pass = $_POST['pass'];
    $user->login();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Fruvive</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Material Icons & Google Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .quote-fade {
            animation: fadeInfinite 12s infinite;
        }
        @keyframes fadeInfinite {
            0%, 30% { opacity: 0; transform: translateY(10px); }
            5%, 25% { opacity: 1; transform: translateY(0); }
            35%, 65% { opacity: 0; transform: translateY(10px); }
            40%, 60% { opacity: 1; transform: translateY(0); }
            70%, 95% { opacity: 0; transform: translateY(10px); }
            75%, 90% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-stretch">

    <!-- Split Layout Wrapper -->
    <div class="w-full flex">
        
        <!-- Left Side: Brand & Thoughts (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-3/5 bg-gradient-to-tr from-green-800 via-emerald-800 to-teal-900 relative items-center justify-center p-12 overflow-hidden">
            
            <!-- Decorative circle glow -->
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-emerald-700/20 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-green-700/20 blur-3xl"></div>

            <div class="max-w-md w-full text-white space-y-8 z-10">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <img src="images/fruvive-logo.png" alt="Fruvive Logo" class="h-16 w-auto drop-shadow-md">
                    <span class="text-4xl font-extrabold tracking-wide drop-shadow-md">Fruvive</span>
                </div>

                <div class="space-y-4">
                    <h2 class="text-3xl font-bold leading-tight">Vibrant Health, Delivered Fresh.</h2>
                    <p class="text-green-100 text-sm leading-relaxed">
                        Fruvive brings handpicked premium fruits, seasonal organic harvests, and high-energy nuts straight to your household. Join us today and elevate your lifestyle.
                    </p>
                </div>

                <!-- Quotes Slideshow (Pure CSS Animation) -->
                <div class="relative h-24 pt-6 border-t border-white/20">
                    <!-- Quote 1 -->
                    <div class="absolute inset-0 quote-fade" style="animation-delay: 0s;">
                        <p class="italic text-base text-emerald-100">"Nature's candy: sweet, juicy, and packed with life-giving nutrients."</p>
                        <span class="block mt-2 text-xs font-semibold uppercase tracking-wider text-green-300">— Healthy Living Club</span>
                    </div>
                    <!-- Quote 2 -->
                    <div class="absolute inset-0 quote-fade" style="animation-delay: 4s; opacity: 0;">
                        <p class="italic text-base text-emerald-100">"Eat fresh, live vibrant. Your body is a temple, fuel it with Fruvive."</p>
                        <span class="block mt-2 text-xs font-semibold uppercase tracking-wider text-green-300">— Wellness Journal</span>
                    </div>
                    <!-- Quote 3 -->
                    <div class="absolute inset-0 quote-fade" style="animation-delay: 8s; opacity: 0;">
                        <p class="italic text-base text-emerald-100">"Handpicked organic goodness, delivered fresh from our fields to your doorstep."</p>
                        <span class="block mt-2 text-xs font-semibold uppercase tracking-wider text-green-300">— The Organic Farmer</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Form Card -->
        <div class="w-full lg:w-2/5 flex items-center justify-center p-8 bg-white">
            <div class="max-w-md w-full space-y-8">
                
                <!-- Welcome Title -->
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Sign In</h2>
                    <p class="mt-2 text-sm text-gray-500">Welcome back! Please enter your details.</p>
                </div>

                <form method="POST" class="mt-8 space-y-6">
                    
                    <!-- Email Input -->
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-semibold text-gray-700">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-icons text-lg">mail</span>
                            </span>
                            <input type="email" name="email" id="email" required placeholder="name@company.com"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50/50 focus:ring-2 focus:ring-green-500/20 focus:border-green-600 outline-none transition text-sm">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <label for="pass" class="text-sm font-semibold text-gray-700">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="material-icons text-lg">lock</span>
                            </span>
                            <input type="password" name="pass" id="pass" required placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl bg-gray-50/50 focus:ring-2 focus:ring-green-500/20 focus:border-green-600 outline-none transition text-sm">
                            <span id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 cursor-pointer hover:text-gray-600">
                                <span class="material-icons text-lg">visibility</span>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-xl transition shadow-md shadow-green-600/10 flex items-center justify-center gap-2">
                        <span class="material-icons text-lg">login</span>
                        Sign In
                    </button>

                </form>

                <!-- Actions -->
                <div class="text-center pt-2">
                    <p class="text-sm text-gray-500">
                        Don't have an account? 
                        <a href="signup.php" class="font-bold text-green-600 hover:underline">Create Account</a>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#pass');

        togglePassword.addEventListener('click', function (e) {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('.material-icons').textContent = type === 'password' ? 'visibility' : 'visibility_off';
        });
    </script>
</body>

</html>