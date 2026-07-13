document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;
  const avatar = document.getElementById("profileAvatar");
  const userName = document.getElementById("userName");

  //----nav active tag a -----
  const links = document.querySelectorAll(".nav-link");
  const currentPage = window.location.pathname.split("/").pop();
  links.forEach((link) => {
    const linkPage = link.getAttribute("href");

    if (linkPage === currentPage) {
      link.classList.add("text-green-600", "border-b-2", "border-green-600");
    }
  });


  //----Profile Dropdown----
  const trigger = document.getElementById("arrowIcon");
  const dropdown = document.getElementById("profileDropdown");
  // 🔹 Toggle Dropdown
  if (trigger && dropdown) {
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      dropdown.classList.toggle("hidden");
    });
    // 🔹 Close when clicking outside
    document.addEventListener("click", function () {
      dropdown.classList.add("hidden");
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

  // Load saved theme
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
    toggleBtn.addEventListener("click", () => {
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
    });
  }

  /* AVATAR INITIALS */
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

  
  /*----open cart page----*/
  const cartbtn = document.querySelector(".cart-btn .material-icons");

  if (cartbtn) {
    cartbtn.addEventListener("click", () => {
      window.location.href = "usercart.php";
    });
  }
});
