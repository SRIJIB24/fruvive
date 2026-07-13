document.addEventListener("DOMContentLoaded", () => {

    const body = document.body;
    const sidebar = document.querySelector(".sidebar");
    const main = document.querySelector(".main");
    const menuToggle = document.querySelector(".menu-toggle");
    const modeToggle = document.querySelector(".mode-toggle");
    const profile = document.querySelector(".profile-container");

    /* SIDEBAR TOGGLE */
    menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
        main.classList.toggle("collapsed");
    });

    /* THEME */
    const modeIcon = modeToggle ? modeToggle.querySelector(".material-icons") : null;
    
    function updateAdminToggleButton(isDark) {
        if (modeIcon) {
            modeIcon.textContent = isDark ? "light_mode" : "dark_mode";
        }
    }

    const savedTheme = localStorage.getItem("theme") || "light";
    body.classList.add(savedTheme);
    if (savedTheme === "dark") {
        body.classList.remove("light");
        document.documentElement.classList.add("dark");
        updateAdminToggleButton(true);
    } else {
        body.classList.add("light");
        document.documentElement.classList.remove("dark");
        updateAdminToggleButton(false);
    }

    if (modeToggle) {
        modeToggle.addEventListener("click", () => {
            const isDark = body.classList.toggle("dark");
            body.classList.toggle("light", !isDark);
            document.documentElement.classList.toggle("dark", isDark);
            updateAdminToggleButton(isDark);
            localStorage.setItem("theme", isDark ? "dark" : "light");
        });
    }

    /* PROFILE DROPDOWN */
    profile.addEventListener("click", (e) => {
        e.stopPropagation();
        profile.classList.toggle("active");
    });

    document.addEventListener("click", () => {
        profile.classList.remove("active");
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const avatar = document.getElementById("profileAvatar");
    const userName = document.getElementById("userName");

    if (!avatar || !userName) return;

    const nameParts = userName.textContent.trim().split(" ");

    const initials = nameParts
        .map(word => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join("");

    avatar.textContent = initials;
});
