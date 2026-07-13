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

        .stat-label {
            display: block;
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
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
                <h2> Welcome </h2>
                <p>This is your dashboard overview.</p>
            </div>

            <div class="stats-grid">
                <div class="card stat-card">
                    <div class="stat-icon blue">
                        <span class="material-icons">group</span>
                    </div>
                    <div>
                        <span class="stat-label">Total Users</span>
                        <span class="stat-value"><?php echo $object->totalUsers() ?></span>
                    </div>
                </div>

                <div class="card stat-card">
                    <div class="stat-icon green">
                        <span class="material-icons">payments</span>
                    </div>
                    <div>
                        <span class="stat-label">Revenue</span>
                        <span class="stat-value">$42,800</span>
                    </div>
                </div>

                <div class="card stat-card">
                    <div class="stat-icon purple">
                        <span class="material-icons">trending_up</span>
                    </div>
                    <div>
                        <span class="stat-label">Growth</span>
                        <span class="stat-value">+14.2%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>

</html>

<body>