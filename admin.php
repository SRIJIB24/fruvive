<?php
//session check
require "userFunc.php";
$object = new data();
$object->sessionCheck();
if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">

    <style>
        .welcome-banner {
            background: var(--bg-card);
            padding: 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            margin-bottom: 30px;
        }

        .welcome-banner h2 {
            margin: 0 0 6px 0;
            font-size: 22px;
        }

        .welcome-banner p {
            margin: 0;
            color: var(--text-muted);
        }


        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }


        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
        }

        /* Icon Colors */
        .stat-icon.blue {
            background: #e0e7ff;
            color: #4338ca;
        }

        .stat-icon.green {
            background: #dcfce7;
            color: #15803d;
        }

        .stat-icon.purple {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .stat-icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .stat-icon.teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        .stat-icon.yellow {
            background: #fef9c3;
            color: #ca8a04;
        }

        .stat-icon.emerald {
            background: #d1fae5;
            color: #059669;
        }

        .stat-label {
            display: block;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
        }
    </style>
</head>

<body class="light">

    <!-- SIDEBAR -->
    <?php include "adminsidebar.php" ?>

    <div class="main">

        <?php include "adminnavbar.php" ?>

        <div class="content">
            <div class="welcome-banner">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
                <p>This is your standard store overview and business performance indicators.</p>
            </div>

            <div class="stats-grid">
                <!-- Customers -->
                <div class="card stat-card">
                    <div class="stat-icon blue">
                        <span class="material-icons">group</span>
                    </div>
                    <div>
                        <span class="stat-label">Total Customers</span>
                        <span class="stat-value"><?= number_format($object->totalCustomers()) ?></span>
                    </div>
                </div>

                <!-- Products -->
                <div class="card stat-card">
                    <div class="stat-icon orange">
                        <span class="material-icons">inventory_2</span>
                    </div>
                    <div>
                        <span class="stat-label">Total Products</span>
                        <span class="stat-value"><?= number_format($object->totalProducts()) ?></span>
                    </div>
                </div>

                <!-- Orders -->
                <div class="card stat-card">
                    <div class="stat-icon purple">
                        <span class="material-icons">shopping_bag</span>
                    </div>
                    <div>
                        <span class="stat-label">Total Orders</span>
                        <span class="stat-value"><?= number_format($object->totalOrders()) ?></span>
                    </div>
                </div>

                <!-- Delivered -->
                <div class="card stat-card">
                    <div class="stat-icon green">
                        <span class="material-icons">done_all</span>
                    </div>
                    <div>
                        <span class="stat-label">Delivered Orders</span>
                        <span class="stat-value"><?= number_format($object->totalDelivered()) ?></span>
                    </div>
                </div>

                <!-- Today Delivered -->
                <div class="card stat-card">
                    <div class="stat-icon teal">
                        <span class="material-icons">today</span>
                    </div>
                    <div>
                        <span class="stat-label">Delivered Today</span>
                        <span class="stat-value"><?= number_format($object->todayDelivered()) ?></span>
                    </div>
                </div>

                <!-- Pending / Dispatched -->
                <div class="card stat-card">
                    <div class="stat-icon yellow">
                        <span class="material-icons">pending_actions</span>
                    </div>
                    <div>
                        <span class="stat-label">Pending / Transit</span>
                        <span class="stat-value"><?= number_format($object->totalPendingDispatched()) ?></span>
                    </div>
                </div>

                <!-- Sales Revenue -->
                <div class="card stat-card">
                    <div class="stat-icon emerald">
                        <span class="material-icons">payments</span>
                    </div>
                    <div>
                        <span class="stat-label">Sales Revenue</span>
                        <span class="stat-value">₹<?= number_format($object->totalRevenue(), 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="admin.js"></script>
</body>
</html>