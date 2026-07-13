<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$profile  = $object->getprofile($userid);

if (isset($_POST['update-profile'])) {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    
    $user_img = $profile['user_img'] ?? null;
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = "user_" . $userid . "." . $fileExtension;
            $uploadFileDir = 'assets/image/profile-img/';
            
            // Recreate dir if missing
            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            // Delete old file if different extension
            if ($user_img && file_exists($user_img) && $user_img !== $dest_path) {
                @unlink($user_img);
            }
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $user_img = $dest_path;
            }
        }
    }
    
    $object->updateProfile($userid, $username, $phone, $user_img);
    
    $_SESSION['username'] = $username;
    
    header("Location: userProfile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Fruvive</title>
    
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
        <main class="flex-grow max-w-6xl mx-auto w-full px-4 py-8 md:py-12">
            
            <!-- Welcome Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Account Settings</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your profile details and preferences.</p>
            </div>

            <!-- Profile Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Profile Card -->
                <div class="lg:col-span-1 lg:sticky lg:top-[80px] lg:self-start">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-6 shadow-sm flex flex-col items-center">
                        
                        <!-- Avatar Wrapper -->
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white dark:border-gray-700 shadow-md bg-gradient-to-tr from-green-500 to-emerald-600 flex items-center justify-center text-white text-4xl font-bold">
                                <?php if (!empty($profile['user_img']) && file_exists($profile['user_img'])) { ?>
                                    <img src="<?php echo htmlspecialchars($profile['user_img']); ?>" class="w-full h-full object-cover">
                                <?php } else { ?>
                                    <?php echo strtoupper($profile['username'][0] ?? 'U') ?>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- User Info -->
                        <h2 class="mt-4 text-xl font-bold text-gray-900 dark:text-white text-center">
                            <?php echo htmlspecialchars($profile['username']) ?>
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 text-center">
                            <?php echo htmlspecialchars($profile['email']) ?>
                        </p>

                        <!-- Level Badge -->
                        <span class="mt-3 px-3 py-1 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-semibold rounded-full border border-green-200/50 dark:border-green-800/30">
                            Customer
                        </span>

                        <div class="w-full border-t border-gray-100 dark:border-gray-700 my-6"></div>

                        <!-- Info details -->
                        <div class="w-full space-y-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Account status</span>
                                <span class="font-medium text-green-600">Active</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Last login</span>
                                <span class="font-medium text-gray-900 dark:text-white text-right">
                                    <?php echo date("d M Y, h:i A", strtotime($profile['lastlogin'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-6 md:p-8 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Profile Details</h3>

                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            
                            <!-- Username Input -->
                            <div class="space-y-2">
                                <label for="username" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Username</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <span class="material-icons text-lg">person</span>
                                    </span>
                                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($profile['username']) ?>" required
                                        class="w-full pl-10 pr-4 py-2.5 border rounded-xl bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-600 outline-none transition dark:text-white">
                                </div>
                            </div>

                            <!-- Email Input (Disabled/Read-only) -->
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <span class="material-icons text-lg">mail</span>
                                    </span>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($profile['email']) ?>" readonly
                                        class="w-full pl-10 pr-4 py-2.5 border rounded-xl bg-gray-100 dark:bg-gray-900/30 border-gray-200 dark:border-gray-700 text-gray-500 cursor-not-allowed outline-none">
                                </div>
                                <p class="text-xs text-gray-400">Email address cannot be changed.</p>
                            </div>

                            <!-- Phone Input -->
                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Phone Number</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <span class="material-icons text-lg">phone</span>
                                    </span>
                                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? '') ?>" placeholder="Enter phone number" maxlength="15"
                                        class="w-full pl-10 pr-4 py-2.5 border rounded-xl bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-green-500/20 focus:border-green-600 outline-none transition dark:text-white">
                                </div>
                            </div>

                            <!-- File Upload Input -->
                            <div class="space-y-2">
                                <label for="profile_pic" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Profile Picture</label>
                                <div class="flex items-center gap-4">
                                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*"
                                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-xl file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-green-50 file:text-green-700
                                        dark:file:bg-green-900/30 dark:file:text-green-300
                                        file:cursor-pointer hover:file:opacity-90">
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 mt-8">
                                <button type="submit" name="update-profile"
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-md shadow-green-600/10">
                                    Save Changes
                                </button>
                                <a href="index.php"
                                    class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium px-6 py-2.5 rounded-xl transition">
                                    Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>

        </main>

        <?php include "userfooter.php" ?>

    </div>

    <script src="usernavbar.js"></script>

</body>

</html>