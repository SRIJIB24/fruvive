<?php
require "userFunc.php";
$object = new data();
$isLoggedIn = $object->visitorSessionCheck();

$slug = isset($_GET['page']) ? trim($_GET['page']) : '';

$stmt = $object->conn->prepare("SELECT * FROM pages_content WHERE slug = :slug AND client_id = 1");
$stmt->execute([':slug' => $slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    // Falls back to a 404 block
    $page = [
        'title' => 'Page Not Found',
        'content' => '<p class="text-center italic text-gray-500">The requested informational page could not be located. Please check the address or return home.</p>'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?> - Fruvive</title>
    
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
        /* Custom styled prose to format seeded content neatly */
        .prose h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .dark .prose h2 {
            color: #f3f4f6;
        }
        .prose h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .dark .prose h3 {
            color: #e5e7eb;
        }
        .prose p {
            font-size: 0.95rem;
            line-height: 1.625;
            color: #4b5563;
            margin-bottom: 1rem;
        }
        .dark .prose p {
            color: #9ca3af;
        }
        .prose ul, .prose ol {
            margin-bottom: 1rem;
            padding-left: 1.25rem;
            list-style-type: disc;
        }
        .prose li {
            font-size: 0.95rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }
        .dark .prose li {
            color: #9ca3af;
        }
        .prose article {
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .dark .prose article {
            border-bottom-color: #1f2937;
        }
        .prose article:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen transition-colors duration-300">

    <div class="main flex flex-col min-h-screen">
        <?php include "usernavbar.php" ?>

        <!-- Content Page Header -->
        <div class="text-center py-12 px-4 bg-gradient-to-b from-green-50/50 to-transparent dark:from-green-950/20">
            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight"><?php echo htmlspecialchars($page['title']); ?></h1>
            <p class="max-w-2xl mx-auto mt-2 text-xs text-gray-400 dark:text-gray-500 uppercase tracking-widest font-semibold">Fruvive Company Information</p>
        </div>

        <!-- Main Content Area -->
        <main class="flex-grow max-w-4xl mx-auto w-full px-4 md:px-6 pb-20">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 p-8 md:p-12 shadow-sm prose transition duration-350">
                <?php echo $page['content']; ?>
            </div>
        </main>

        <?php include "userfooter.php" ?>
    </div>

    <script src="userglobal.js?v=1.3"></script>
    <script src="usernavbar.js?v=1.3"></script>
</body>

</html>
