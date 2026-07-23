<nav class="navbar">
        <div class="nav-left">
            <button class="icon-btn menu-toggle">
                <span class="material-icons">menu</span>
            </button>
        </div>

        <div class="nav-right" style="gap: 16px;">
            <!-- Notifications Bell (Admin Panel) -->
            <div class="relative" id="adminNotifContainer" style="position: relative;">
                <button id="adminNotifBellBtn" class="icon-btn" style="position: relative; cursor: pointer; border: none; background: transparent; padding: 4px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: background 0.2s;">
                    <span class="material-icons" style="font-size: 22px; color: var(--text-color);">notifications</span>
                    <span id="adminNotifBadge" style="position: absolute; top: -3px; right: -3px; background: #f97316; color: #fff; font-size: 8px; font-weight: bold; height: 14px; width: 14px; border-radius: 50%; display: none; align-items: center; justify-content: center; border: 1px solid #fff;">0</span>
                </button>
                <div id="adminNotifDropdown" style="position: absolute; right: 0; top: 125%; width: 280px; background: var(--card-bg, #fff); border-radius: 12px; shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border: 1px solid var(--border, #e5e7eb); padding: 8px 0; display: none; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <div style="padding: 6px 12px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: var(--text-color);">Admin Alerts Log</span>
                        <button id="adminMarkReadBtn" style="font-size: 9px; font-weight: bold; color: #16a34a; background: transparent; border: none; cursor: pointer; text-decoration: underline;">Mark read</button>
                    </div>
                    <div id="adminNotifList" style="max-height: 220px; overflow-y: auto; font-size: 12px;">
                        <p style="text-align: center; padding: 16px 0; color: var(--text-muted); font-style: italic; margin: 0;">No alerts logged yet.</p>
                    </div>
                </div>
            </div>

            <button class="icon-btn mode-toggle">
                <span class="material-icons">dark_mode</span>
            </button>

            <div class="profile-container">
                <div class="profile-trigger">
                    <div class="avatar" id="profileAvatar"></div>
                    <span id="userName"><?= $_SESSION['username'] ?></span>
                    <span class="material-icons">expand_more</span>
                </div>

                <div class="profile-dropdown">
                    <a href="#"><span class="material-icons">person</span> Profile</a>
                    <a href="#"><span class="material-icons">settings</span> Settings</a>
                    <hr>
                    <a href="logout.php" class="danger">
                        <span class="material-icons">logout</span> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const container = document.getElementById("adminNotifContainer");
        const bellBtn = document.getElementById("adminNotifBellBtn");
        const dropdown = document.getElementById("adminNotifDropdown");
        const markReadBtn = document.getElementById("adminMarkReadBtn");
        const badge = document.getElementById("adminNotifBadge");
        const list = document.getElementById("adminNotifList");

        if (!bellBtn || !dropdown) return;

        bellBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener("click", (e) => {
            if (container && !container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function fetchAdminAlerts() {
            fetch("getNotifications.php?action=fetch")
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        if (data.unread_count > 0) {
                            badge.innerText = data.unread_count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }

                        if (data.notifications.length === 0) {
                            list.innerHTML = `<p style="text-align: center; padding: 16px 0; color: var(--text-muted); font-style: italic; margin: 0;">No alerts logged yet.</p>`;
                        } else {
                            let html = "";
                            data.notifications.forEach(item => {
                                const bg = item.is_read == 0 ? 'background: rgba(249, 115, 22, 0.05);' : '';
                                const borderStyle = 'border-bottom: 1px solid var(--border);';
                                const color = item.is_read == 0 ? 'color: #f97316;' : 'color: var(--text-muted);';
                                const icon = getIcon(item.type);
                                html += `
                                    <div style="padding: 10px 12px; display: flex; gap: 8px; ${bg} ${borderStyle}">
                                        <span class="material-icons" style="${color} font-size: 15px; margin-top: 1px;">${icon}</span>
                                        <div style="flex-grow: 1;">
                                            <h4 style="margin: 0; font-size: 12px; font-weight: bold; color: var(--text-color);">${item.title}</h4>
                                            <p style="margin: 3px 0 0 0; font-size: 11px; color: var(--text-muted); line-height: 1.4;">${item.message}</p>
                                            <span style="font-size: 9px; color: var(--text-muted); display: block; margin-top: 4px;">${item.created_at}</span>
                                        </div>
                                    </div>
                                `;
                            });
                            list.innerHTML = html;
                        }
                    }
                });
        }

        function getIcon(type) {
            switch(type) {
                case 'user_created': return 'person_add';
                case 'order_created': return 'shopping_bag';
                case 'delivered': return 'done_all';
                case 'cancelled': return 'cancel';
                default: return 'info';
            }
        }

        markReadBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            fetch("getNotifications.php?action=read")
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        badge.style.display = 'none';
                        fetchAdminAlerts();
                    }
                });
        });

        fetchAdminAlerts();
        setInterval(fetchAdminAlerts, 30000);
    });
    </script>