<nav class="navbar">
        <div class="nav-left">
            <button class="icon-btn menu-toggle">
                <span class="material-icons">menu</span>
            </button>
        </div>

        <div class="nav-right">
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