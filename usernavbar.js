document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;
  const avatar = document.getElementById("profileAvatar");
  const userName = document.getElementById("userName");
  const currentPage = window.location.pathname.split("/").pop();

  //----nav active tag a (Desktop) -----
  const links = document.querySelectorAll(".nav-link");
  links.forEach((link) => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.remove("text-gray-500", "dark:text-gray-300");
      link.classList.add("text-green-600", "dark:text-green-400", "border-b-2", "border-green-600", "dark:border-green-400");
    }
  });

  //----nav active tag a (Mobile Drawer) -----
  const mobileLinks = document.querySelectorAll(".nav-link-mobile");
  mobileLinks.forEach((link) => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.remove("text-gray-600", "dark:text-gray-300");
      link.classList.add("bg-green-50", "dark:bg-green-950/20", "text-green-600", "dark:text-green-400");
    }
  });

  //----Profile Dropdown Toggle----
  const trigger = document.getElementById("profileTrigger");
  const dropdown = document.getElementById("profileDropdown");
  if (trigger && dropdown) {
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      dropdown.classList.toggle("hidden");
    });
    // Close when clicking outside
    document.addEventListener("click", function () {
      dropdown.classList.add("hidden");
    });
  }

  //----Burger Drawer (Mobile) ----
  const burgerBtn = document.getElementById("burgerBtn");
  const mobileDrawer = document.getElementById("mobileDrawer");
  const mobileDrawerPanel = document.getElementById("mobileDrawerPanel");
  const closeBurgerBtn = document.getElementById("closeBurgerBtn");

  const mobileDrawerBtn = document.getElementById("mobileDrawerBtn");

  if (mobileDrawer && mobileDrawerPanel && closeBurgerBtn) {
    const openDrawer = (e) => {
      e.stopPropagation();
      mobileDrawer.classList.remove("hidden");
      setTimeout(() => {
        mobileDrawerPanel.classList.remove("-translate-x-full");
      }, 10);
    };

    if (burgerBtn) burgerBtn.addEventListener("click", openDrawer);
    if (mobileDrawerBtn) mobileDrawerBtn.addEventListener("click", openDrawer);

    const closeDrawer = () => {
      mobileDrawerPanel.classList.add("-translate-x-full");
      setTimeout(() => {
        mobileDrawer.classList.add("hidden");
      }, 300);
    };

    closeBurgerBtn.addEventListener("click", closeDrawer);
    mobileDrawer.addEventListener("click", function (e) {
      if (e.target === this) {
        closeDrawer();
      }
    });
  }

  //----Dark Mode----
  const toggleBtn = document.getElementById("darkToggle");
  
  function updateToggleButton(isDark) {
    if (toggleBtn) {
      if (isDark) {
        toggleBtn.innerHTML = '<span class="material-icons text-[18px]">light_mode</span>Light Mode';
      } else {
        toggleBtn.innerHTML = '<span class="material-icons text-[18px]">dark_mode</span>Dark Mode';
      }
    }
  }

  // Load theme preference
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    body.classList.add("dark");
    document.documentElement.classList.add("dark");
    updateToggleButton(true);
  } else {
    body.classList.remove("dark");
    document.documentElement.classList.remove("dark");
    updateToggleButton(false);
  }

  // Toggle theme on click
  if (toggleBtn) {
    toggleBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isDark = !document.documentElement.classList.contains("dark");
      if (isDark) {
        body.classList.add("dark");
        document.documentElement.classList.add("dark");
        localStorage.setItem("theme", "dark");
      } else {
        body.classList.remove("dark");
        document.documentElement.classList.remove("dark");
        localStorage.setItem("theme", "light");
      }
      updateToggleButton(isDark);
      
      // Keep dropdown open if clicked inside it
      if (dropdown) dropdown.classList.remove("hidden");
    });
  }

  // Initials generator if placeholder needed
  if (avatar && userName) {
    const name = userName.textContent.trim();
    const parts = name.split(" ");
    const initials = parts
      .map((p) => p[0])
      .slice(0, 2)
      .join("")
      .toUpperCase();
    avatar.textContent = initials;
  }

  /* Navbar Search Trigger */
  const searchInput = document.getElementById("navbarSearch");
  if (searchInput) {
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        const query = encodeURIComponent(this.value.trim());
        if (query) {
          window.location.href = "usersearch.php?q=" + query;
        }
      }
    });
  }

  // Initialize notifications log polling
  initNotifications();
});

function initNotifications() {
  const container = document.getElementById("notifContainer");
  const bellBtn = document.getElementById("notifBellBtn");
  const dropdown = document.getElementById("notifDropdown");
  const markReadBtn = document.getElementById("markReadBtn");
  const badge = document.getElementById("notifBadge");
  const list = document.getElementById("notifList");

  if (!bellBtn || !dropdown) return;

  // Toggle Dropdown
  bellBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.classList.toggle("hidden");
  });

  document.addEventListener("click", (e) => {
    if (container && !container.contains(e.target)) {
      dropdown.classList.add("hidden");
    }
  });

  // Fetch alerts
  function fetchAlerts() {
    fetch("getNotifications.php?action=fetch")
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          // Update badge
          if (data.unread_count > 0) {
            badge.innerText = data.unread_count;
            badge.classList.remove("hidden");
          } else {
            badge.classList.add("hidden");
          }

          // Build list
          if (data.notifications.length === 0) {
            list.innerHTML = `<p class="text-center py-6 text-xs text-gray-400 italic">No alerts logged yet.</p>`;
          } else {
            let html = "";
            data.notifications.forEach(item => {
              const bg = item.is_read == 0 ? 'bg-orange-50/40 dark:bg-orange-950/10' : '';
              const icon = getNotifIcon(item.type);
              const color = item.is_read == 0 ? 'text-orange-500' : 'text-gray-400';
              html += `
                <div class="px-4 py-3 flex gap-3 text-xs leading-relaxed transition ${bg}">
                  <span class="material-icons ${color} mt-0.5" style="font-size: 16px;">${icon}</span>
                  <div>
                    <h4 class="font-bold text-gray-800 dark:text-gray-100">${item.title}</h4>
                    <p class="text-gray-500 dark:text-gray-400 mt-0.5">${item.message}</p>
                    <span class="text-[9px] text-gray-400 dark:text-gray-500 mt-1 block">${item.created_at}</span>
                  </div>
                </div>
              `;
            });
            list.innerHTML = html;
          }
        }
      });
  }

  function getNotifIcon(type) {
    switch(type) {
      case 'user_created': return 'person_add';
      case 'order_created': return 'shopping_bag';
      case 'order_confirm': return 'check_circle';
      case 'outfor_delivery': return 'local_shipping';
      case 'order_delivered': return 'done_all';
      case 'order_cancelled': return 'cancel';
      default: return 'info';
    }
  }

  // Mark read
  markReadBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    fetch("getNotifications.php?action=read")
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          badge.classList.add("hidden");
          fetchAlerts();
        }
      });
  });

  // Initial fetch
  fetchAlerts();
  // Poll every 30 seconds
  setInterval(fetchAlerts, 30000);
}
