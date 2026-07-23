<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$from = $_GET['from'] ?? '';
$address = $object->fetchAddress($userid);

if (isset($_POST['submit-btn'])) {

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $pincode = $_POST['pincode'];
    $locality = $_POST['locality'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $landmark = $_POST['landmark'] ?? '';
    $altphone = $_POST['altphone'] ?? '';
    $type = $_POST['type'] ?? 'home';
    $default = isset($_POST['default']) ? 1 : 0;

    $object->insertAddress($userid, $name, $phone, $address, $city, $state, $pincode, $locality, $landmark, $altphone, $type, $default);

    if ($from == 'cart') {
        header("Location: usercart.php");
    } else {
        header("Location: userAddress.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Address</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>

<body class="bg-gray-100">

    <div class="main">

        <?php include "usernavbar.php" ?>

        <div class="max-w-3xl mx-auto p-6">

            <!-- Page Title -->
            <div class="flex items-center gap-2 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Manage Addresses</h2>
            </div>

            <!-- Form -->
            <div class="bg-white p-6 rounded-lg shadow-md max-w-3xl mx-auto">

                <h2 class="text-lg font-semibold mb-5 text-gray-700">
                    ADD A NEW ADDRESS
                </h2>

                <form method="POST" class="space-y-5">

                    <input type="hidden" name="userid" value="<?php echo $userid ?>">

                    <!-- Name + Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="relative">
                            <input type="text" name="name" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">
                                Name
                            </label>
                        </div>

                        <div class="relative">
                            <input type="text" name="phone" maxlength="10" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">
                                10-digit mobile number
                            </label>
                        </div>

                    </div>

                    <!-- Pincode + Locality -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="relative">
                            <input type="text" name="pincode" maxlength="6" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">
                                Pincode
                            </label>
                        </div>

                        <div class="relative">
                            <input type="text" name="locality" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">
                                Locality
                            </label>
                        </div>

                    </div>

                    <!-- Address -->
                    <div class="relative">

                        <textarea name="address" rows="3" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>

                        <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">
                            Address (Area and Street)
                        </label>

                    </div>

                    <!-- City + State -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="relative">
                            <input type="text" name="city" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">
                                City / District / Town
                            </label>
                        </div>

                        <div>
                            <select name="state" required class="w-full border rounded-md px-3 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

                                <option value="">--Select State--</option>
                                <option>Andaman and Nicobar Islands</option>
                                <option>Andhra Pradesh</option>
                                <option>Arunachal Pradesh</option>
                                <option>Assam</option>
                                <option>Bihar</option>
                                <option>Chandigarh</option>
                                <option>Chhattisgarh</option>
                                <option>Dadra and Nagar Haveli and Daman and Diu</option>
                                <option>Delhi</option>
                                <option>Goa</option>
                                <option>Gujarat</option>
                                <option>Haryana</option>
                                <option>Himachal Pradesh</option>
                                <option>Jammu and Kashmir</option>
                                <option>Jharkhand</option>
                                <option>Karnataka</option>
                                <option>Kerala</option>
                                <option>Ladakh</option>
                                <option>Lakshadweep</option>
                                <option>Madhya Pradesh</option>
                                <option>Maharashtra</option>
                                <option>Manipur</option>
                                <option>Meghalaya</option>
                                <option>Mizoram</option>
                                <option>Nagaland</option>
                                <option>Odisha</option>
                                <option>Puducherry</option>
                                <option>Punjab</option>
                                <option>Rajasthan</option>
                                <option>Sikkim</option>
                                <option>Tamil Nadu</option>
                                <option>Telangana</option>
                                <option>Tripura</option>
                                <option>Uttar Pradesh</option>
                                <option>Uttarakhand</option>
                                <option>West Bengal</option>

                            </select>
                        </div>

                    </div>

                    <!-- Landmark + Alternate Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="relative">
                            <input type="text" name="landmark" class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500">
                                Landmark (Optional)
                            </label>
                        </div>

                        <div class="relative">
                            <input type="text" name="altphone" maxlength="10" class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500">
                                Alternate Phone (Optional)
                            </label>
                        </div>

                    </div>

                    <!-- Address Type -->
                    <div>

                        <p class="text-sm font-semibold text-gray-700 mb-2">
                            Address Type
                        </p>

                        <div class="flex gap-6">

                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="home" checked>
                                <span>Home</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="work">
                                <span>Work</span>
                            </label>

                        </div>

                    </div>

                    <!-- Default Address -->
                    <?php if($address) { ?>
                    <div class="flex items-center gap-2">

                        <input type="checkbox" name="default" value="1">

                        <label class="text-sm text-gray-600">
                            Set as default address
                        </label>

                    </div>
                    <?php } ?>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">

                        <button type="submit" name="submit-btn" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-md font-medium">

                            SAVE

                        </button>

                        <a href="userAddress.php" class="bg-gray-200 px-6 py-2 rounded-md">

                            CANCEL

                        </a>

                    </div>

                </form>
            </div>

        </div>

        <?php include "userfooter.php" ?>

    </div>

    <script src="usernavbar.js?v=1.3"></script>

</body>

</html>