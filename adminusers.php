<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();
if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

// Pagination setup
$limit = 10;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

// Fetch count
$stmtCount = $object->conn->query("SELECT COUNT(*) FROM users WHERE userlevel = 10");
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch paginated
$stmt = $object->conn->prepare("SELECT id, username, email, phone, user_img, active, lastlogin FROM users WHERE userlevel = 10 ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>

    <!-- Material Icons & Admin Stylesheets -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "adminsidebar.php" ?>
    
    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content p-6">
                <!-- Header -->
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
                        <p class="text-sm text-gray-500 mt-1">Activate or deactivate customer accounts.</p>
                    </div>
                    <div class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full border border-green-200">
                        Total Customers: <?php echo count($users); ?>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm font-semibold">
                                <th class="p-4">User ID</th>
                                <th class="p-4">Avatar</th>
                                <th class="p-4">Username</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Phone</th>
                                <th class="p-4">Last Login</th>
                                <th class="p-4 text-center">Active Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700 text-sm">
                            <?php if (!empty($users)) : ?>
                                <?php foreach ($users as $row) : ?>
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-4 font-mono text-gray-500">#<?php echo $row['id']; ?></td>
                                        <td class="p-4">
                                            <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shadow-sm bg-gradient-to-tr from-green-500 to-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                                                <?php if (!empty($row['user_img']) && file_exists($row['user_img'])) { ?>
                                                    <img src="<?php echo htmlspecialchars($row['user_img']); ?>" class="w-full h-full object-cover">
                                                <?php } else { ?>
                                                    <?php echo strtoupper($row['username'][0] ?? 'U'); ?>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td class="p-4 font-semibold text-gray-900"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class="p-4 text-gray-600"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="p-4 text-gray-600"><?php echo htmlspecialchars($row['phone'] ?? 'Not added'); ?></td>
                                        <td class="p-4 text-gray-500">
                                            <?php echo !empty($row['lastlogin']) ? date("d M Y, h:i A", strtotime($row['lastlogin'])) : 'Never'; ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer toggle-status" data-userid="<?php echo $row['id']; ?>" <?php echo ($row['active'] == 1) ? 'checked' : ''; ?>>
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-400">
                                        <span class="material-icons text-4xl block mb-2">people_outline</span>
                                        No registered customers found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1) { ?>
                <div class="flex justify-center items-center gap-2 mt-6">
                    <?php if ($page > 1) { ?>
                        <a href="?p=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold no-underline">Prev</a>
                    <?php } ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <a href="?p=<?= $i ?>" class="px-3 py-1.5 rounded-lg border <?= $i === $page ? 'border-green-600 bg-green-600 text-white' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-700' ?> text-xs font-semibold no-underline"><?= $i ?></a>
                    <?php } ?>
                    
                    <?php if ($page < $totalPages) { ?>
                        <a href="?p=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold no-underline">Next</a>
                    <?php } ?>
                </div>
                <?php } ?>

            </div> <!-- Close .content -->
        </div> <!-- Close .main -->

    <script>
        $(document).ready(function() {
            $('.toggle-status').change(function() {
                var userid = $(this).data('userid');
                var status = this.checked ? 1 : 0;
                
                $.ajax({
                    url: 'adminToggleUser.php',
                    type: 'POST',
                    data: { userid: userid, status: status },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: 'Failed to update user status: ' + (response.message || 'Unknown error'),
                                confirmButtonColor: '#ea580c'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating user status.',
                            confirmButtonColor: '#ea580c'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
