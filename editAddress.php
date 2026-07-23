<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

$userid = $_SESSION['user_id'];

if ($object->userlvl === -1) {
    header("Location: admin.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: userAddress.php");
    exit();
}

$addr = $object->fetchAddressById($id, $userid);
if (!$addr) {
    header("Location: userAddress.php");
    exit();
}

$from = $_GET['from'] ?? '';

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
    
    // If the address was already default, we keep it default. Otherwise, check if user checked the box.
    $default = (isset($_POST['default']) || $addr['is_default'] == 1) ? 1 : 0;

    $object->updateAddress($id, $userid, $name, $phone, $address, $city, $state, $pincode, $locality, $landmark, $altphone, $type, $default);

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
    <title>Edit Address</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="main">
        <?php include "usernavbar.php" ?>

        <div class="max-w-3xl mx-auto p-6">
            <!-- Page Title -->
            <div class="flex items-center gap-2 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Edit Address</h2>
            </div>

            <!-- Form -->
            <div class="bg-white p-6 rounded-lg shadow-md max-w-3xl mx-auto">
                <h2 class="text-lg font-semibold mb-5 text-gray-700">EDIT YOUR ADDRESS</h2>

                <form method="POST" class="space-y-5">
                    <!-- Name + Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($addr['name']); ?>" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">Name</label>
                        </div>

                        <div class="relative">
                            <input type="text" name="phone" maxlength="10" value="<?php echo htmlspecialchars($addr['phone']); ?>" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">10-digit mobile number</label>
                        </div>
                    </div>

                    <!-- Pincode + Locality -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="text" name="pincode" maxlength="6" value="<?php echo htmlspecialchars($addr['pincode']); ?>" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">Pincode</label>
                        </div>

                        <div class="relative">
                            <input type="text" name="locality" value="<?php echo htmlspecialchars($addr['locality'] ?? ''); ?>" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">Locality</label>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="relative">
                        <textarea name="address" rows="3" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none"><?php echo htmlspecialchars($addr['address']); ?></textarea>
                        <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">Address (Area and Street)</label>
                    </div>

                    <!-- City + State -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="text" name="city" value="<?php echo htmlspecialchars($addr['city']); ?>" required class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500 peer-focus:text-blue-600">City / District / Town</label>
                        </div>

                        <div>
                            <select name="state" required class="w-full border rounded-md px-3 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">--Select State--</option>
                                <?php
                                $states = [
                                    "Andaman and Nicobar Islands", "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar",
                                    "Chandigarh", "Chhattisgarh", "Dadra and Nagar Haveli and Daman and Diu", "Delhi", "Goa",
                                    "Gujarat", "Haryana", "Himachal Pradesh", "Jammu and Kashmir", "Jharkhand", "Karnataka",
                                    "Kerala", "Ladakh", "Lakshadweep", "Madhya Pradesh", "Maharashtra", "Manipur", "Meghalaya",
                                    "Mizoram", "Nagaland", "Odisha", "Puducherry", "Punjab", "Rajasthan", "Sikkim", "Tamil Nadu",
                                    "Telangana", "Tripura", "Uttar Pradesh", "Uttarakhand", "West Bengal"
                                ];
                                foreach ($states as $s) {
                                    $selected = ($addr['state'] == $s) ? 'selected' : '';
                                    echo "<option value=\"$s\" $selected>$s</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Landmark + Alternate Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="text" name="landmark" value="<?php echo htmlspecialchars($addr['landmark'] ?? ''); ?>" class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500">Landmark (Optional)</label>
                        </div>

                        <div class="relative">
                            <input type="text" name="altphone" maxlength="10" value="<?php echo htmlspecialchars($addr['alt_phone'] ?? ''); ?>" class="peer w-full border rounded-md px-3 pt-5 pb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="absolute left-3 top-2 text-xs text-gray-500">Alternate Phone (Optional)</label>
                        </div>
                    </div>

                    <!-- Address Type -->
                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Address Type</p>
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="home" <?php echo ($addr['type'] == 'home') ? 'checked' : ''; ?>>
                                <span>Home</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="work" <?php echo ($addr['type'] == 'work') ? 'checked' : ''; ?>>
                                <span>Work</span>
                            </label>
                        </div>
                    </div>

                    <!-- Default Address -->
                    <?php if ($addr['is_default'] == 0) { ?>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="default" value="1">
                            <label class="text-sm text-gray-600">Set as default address</label>
                        </div>
                    <?php } else { ?>
                        <div class="text-sm text-green-600 font-medium">This is currently your default address.</div>
                    <?php } ?>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" name="submit-btn" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-md font-medium">SAVE</button>
                        <a href="userAddress.php" class="bg-gray-200 px-6 py-2 rounded-md">CANCEL</a>
                    </div>
                </form>
            </div>
        </div>

        <?php include "userfooter.php" ?>
    </div>

    <script src="usernavbar.js?v=1.3"></script>
</body>

</html>
