<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if ($object->userlvl !== -1) {
    header("Location: index.php");
    exit();
}

$coreSlugs = ['terms-service', 'privacy-shield', 'refund-policy', 'shipping-guide'];

// Handle Add Page
if (isset($_POST['add_page'])) {
    $title = trim($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $_POST['slug'])));
    $content = $_POST['content'];

    if (empty($title) || empty($slug)) {
        $_SESSION['msg'] = "Title and slug are required!";
        $_SESSION['msg_type'] = "error";
    } else {
        // Check unique slug
        $chk = $object->conn->prepare("SELECT COUNT(*) FROM pages_content WHERE slug = :slug");
        $chk->execute([':slug' => $slug]);
        if ($chk->fetchColumn() > 0) {
            $_SESSION['msg'] = "A page with slug '{$slug}' already exists!";
            $_SESSION['msg_type'] = "error";
        } else {
            $ins = $object->conn->prepare("INSERT INTO pages_content (client_id, slug, title, content, section) VALUES (1, :slug, :title, :content, 'policy')");
            $res = $ins->execute([
                ':slug' => $slug,
                ':title' => $title,
                ':content' => $content
            ]);
            if ($res) {
                $_SESSION['msg'] = "Policy page '{$title}' created successfully!";
                $_SESSION['msg_type'] = "success";
                header("Location: adminpolicies.php?page=" . $slug);
                exit();
            } else {
                $_SESSION['msg'] = "Failed to create page.";
                $_SESSION['msg_type'] = "error";
            }
        }
    }
    header("Location: adminpolicies.php");
    exit();
}

// Handle Delete Page
if (isset($_GET['delete'])) {
    $delSlug = trim($_GET['delete']);
    if (in_array($delSlug, $coreSlugs)) {
        $_SESSION['msg'] = "Core storefront pages cannot be deleted!";
        $_SESSION['msg_type'] = "error";
    } else {
        $del = $object->conn->prepare("DELETE FROM pages_content WHERE slug = :slug AND section = 'policy' AND client_id = 1");
        $res = $del->execute([':slug' => $delSlug]);
        if ($res) {
            $_SESSION['msg'] = "Page deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Failed to delete page.";
            $_SESSION['msg_type'] = "error";
        }
    }
    header("Location: adminpolicies.php");
    exit();
}

// Handle Save Page (Edit)
if (isset($_POST['save_page'])) {
    $page_slug = trim($_POST['slug']);
    $page_title = trim($_POST['title']);
    $page_content = $_POST['content'];

    $up = $object->conn->prepare("UPDATE pages_content SET title = :title, content = :content WHERE slug = :slug AND section = 'policy' AND client_id = 1");
    $res = $up->execute([
        ':title' => $page_title,
        ':content' => $page_content,
        ':slug' => $page_slug
    ]);

    if ($res) {
        $_SESSION['msg'] = "Policy page '{$page_title}' updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Failed to update page.";
        $_SESSION['msg_type'] = "error";
    }
    header("Location: adminpolicies.php?page=" . $page_slug);
    exit();
}

// Get selected page slug (default to 'terms-service')
$slug = isset($_GET['page']) ? trim($_GET['page']) : 'terms-service';

// Fetch selected page details
$stmt = $object->conn->prepare("SELECT * FROM pages_content WHERE slug = :slug AND section = 'policy' AND client_id = 1");
$stmt->execute([':slug' => $slug]);
$currentPageDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all policy pages
$allPolicyStmt = $object->conn->query("SELECT slug, title FROM pages_content WHERE section = 'policy' AND client_id = 1 ORDER BY id ASC");
$policyPages = $allPolicyStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Help & Policies - Admin</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <link rel="stylesheet" href="adminnavbar.css">

    <script src="jquery-1.9.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .pages-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .pages-grid {
                grid-template-columns: 1fr;
            }
        }

        .list-panel {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }

        .page-list-group {
            margin-bottom: 20px;
        }

        .page-list-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            margin-bottom: 10px;
            display: block;
        }

        .page-item-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 4px;
            background: transparent;
        }

        .page-item-link:hover {
            background: var(--border, #f1f5f9);
        }

        .page-item-link.active {
            background: rgba(22, 163, 74, 0.1);
            color: #16a34a;
            font-weight: bold;
        }

        .page-item-delete {
            opacity: 0;
            color: #ef4444;
            cursor: pointer;
            transition: opacity 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .page-item-link:hover .page-item-delete {
            opacity: 1;
        }

        .editor-panel {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        .form-row label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .form-row input, .form-row select, .form-row textarea {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--background);
            color: var(--text-color);
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
            width: 100%;
        }

        .form-row textarea {
            font-family: inherit;
            resize: vertical;
        }

        .btn-primary {
            background: #16a34a;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            box-shadow: 0 4px 10px rgba(22, 163, 74, 0.15);
        }

        .btn-secondary {
            background: var(--border, #e5e7eb);
            color: var(--text-color);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            text-decoration: none;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="light">
    <?php include "adminsidebar.php" ?>

    <div class="main">
        <?php include "adminnavbar.php" ?>

        <div class="content">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px;">Policies & Guidelines Manager</h1>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0 0;">Create and customize dynamic legal policies, terms, and storefront guides.</p>
                </div>
                <button type="button" onclick="openAddModal()" class="btn-primary">
                    <span class="material-icons" style="font-size: 18px;">add_circle</span> Add Custom Policy
                </button>
            </div>

            <div class="pages-grid">
                <!-- Left Panel: Pages List -->
                <div class="list-panel">
                    <div class="page-list-group">
                        <span class="page-list-title">Legal & Help Policies</span>
                        <?php foreach ($policyPages as $p) { ?>
                            <a href="adminpolicies.php?page=<?= urlencode($p['slug']) ?>" class="page-item-link <?= $slug === $p['slug'] ? 'active' : '' ?>">
                                <span><?= htmlspecialchars($p['title']) ?></span>
                                <?php if (!in_array($p['slug'], $coreSlugs)) { ?>
                                    <span class="page-item-delete material-icons" onclick="event.preventDefault(); confirmDelete('<?= htmlspecialchars($p['slug']) ?>');" style="font-size: 16px;">delete</span>
                                <?php } ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Right Panel: Editor -->
                <div class="editor-panel">
                    <?php if ($currentPageDetails) { ?>
                        <form action="" method="POST" style="display: flex; flex-direction: column;">
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($currentPageDetails['slug']) ?>">

                            <div class="form-row">
                                <label>Policy Title</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($currentPageDetails['title']) ?>" required>
                            </div>

                            <div class="form-row">
                                <label>Page URL Slug</label>
                                <input type="text" value="<?= htmlspecialchars($currentPageDetails['slug']) ?>" disabled style="background: var(--border, #f1f5f9); cursor: not-allowed;">
                                <span style="font-size: 10px; color: var(--text-muted);">URLs for policy pages are fixed once created.</span>
                            </div>

                            <div class="form-row">
                                <label>HTML Content Editor</label>
                                <textarea name="content" rows="15" required style="font-family: monospace; font-size: 13px; line-height: 1.5;"><?= htmlspecialchars($currentPageDetails['content']) ?></textarea>
                                <span style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Standard HTML markup tags are supported (e.g. &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;img&gt;).</span>
                            </div>

                            <button type="submit" name="save_page" class="btn-primary" style="align-self: flex-start;">
                                <span class="material-icons" style="font-size: 16px;">save</span> Save Policy Changes
                            </button>
                        </form>
                    <?php } else { ?>
                        <div style="text-align: center; color: var(--text-muted); padding: 48px;">
                            <span class="material-icons" style="font-size: 48px; margin-bottom: 12px;">article</span>
                            <p>No page selected. Please click a page from the left navigation panel.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Page Modal -->
    <div id="addPageModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card, #fff); width: 100%; max-width: 480px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 1px solid var(--border); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: var(--text-color);">Create Policy Page</h3>
                <button type="button" onclick="closeAddModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <div style="padding: 24px;">
                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="form-row">
                        <label>Policy Title</label>
                        <input type="text" name="title" placeholder="e.g. Cookie Policy" required oninput="generateSlug(this.value)">
                    </div>

                    <div class="form-row">
                        <label>URL Slug</label>
                        <input type="text" id="add_slug" name="slug" placeholder="e.g. cookie-policy" required style="font-weight: bold;">
                    </div>

                    <div class="form-row">
                        <label>Initial Content (HTML)</label>
                        <textarea name="content" rows="6" placeholder="<p>Write your page content here...</p>" required></textarea>
                    </div>

                    <button type="submit" name="add_page" class="btn-primary" style="justify-content: center; width: 100%;">Create Page</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            $('#addPageModal').css('display', 'flex');
        }

        function closeAddModal() {
            $('#addPageModal').hide();
        }

        function generateSlug(val) {
            const slug = val.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            $('#add_slug').val(slug);
        }

        function confirmDelete(slug) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently remove this page from the legal profile and sidebar navigation.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete page'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'adminpolicies.php?delete=' + slug;
                }
            });
        }

        // Show session flash alerts
        <?php if (isset($_SESSION['msg'])) { ?>
            Swal.fire({
                icon: '<?= $_SESSION['msg_type'] ?>',
                title: '<?= $_SESSION['msg'] ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
        <?php } ?>
    </script>
    <script src="admin.js"></script>
</body>

</html>
