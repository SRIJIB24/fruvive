document.addEventListener("DOMContentLoaded", () => {
  const isLoggedIn = !!document.getElementById("profileTrigger");

  // Cart Sync from localStorage if logged in
  if (isLoggedIn) {
    const localCart = JSON.parse(localStorage.getItem('fruvive_cart')) || [];
    if (localCart.length > 0) {
      fetch("syncCart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart: localCart })
      })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            localStorage.removeItem('fruvive_cart');
            location.reload();
          }
        });
    }

    // Wishlist Sync from localStorage if logged in
    const localWishlist = JSON.parse(localStorage.getItem('fruvive_wishlist')) || [];
    if (localWishlist.length > 0) {
      fetch("syncWishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ wishlist: localWishlist })
      })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            localStorage.removeItem('fruvive_wishlist');
            updateNavbarWishlistBadge(data.total_count);
          }
        });
    }
  }

  // Update navbar badges on load
  updateNavbarCartBadge();
  updateNavbarWishlistBadge();
  initProductHearts();

  // Add to cart click event
  document.querySelectorAll(".add-cart-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      const pid = this.getAttribute("data-pid");
      const card = this.closest(".group");
      const img = card ? card.querySelector("img").getAttribute("src") : "";
      handleAddToCart(pid, img);
    });
  });

  // Go to cart click event
  document.querySelectorAll(".go-cart-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      window.location.href = "usercart.php";
    });
  });

  // Wishlist heart toggle click event
  document.querySelectorAll(".wishlist-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      const pid = this.getAttribute("data-pid");
      toggleProductWishlist(pid, this);
    });
  });

  // Setup Quick View Modal element
  setupQuickViewModal();

  // Bind Quick View Triggers
  document.querySelectorAll(".quick-view-trigger").forEach((element) => {
    element.addEventListener("click", function (e) {
      e.stopPropagation();
      const pid = this.getAttribute("data-pid");
      const card = this.closest(".group");
      if (!card) return;

      const pname = card.querySelector("h3").innerText.trim();
      const img = card.querySelector("img").getAttribute("src");
      const quant = card.querySelector("p").innerText.trim();
      const priceText = card.querySelector(".text-green-600").innerText.replace("₹", "").trim();
      const oldPriceText = card.querySelector(".line-through") ? card.querySelector(".line-through").innerText : "";

      openQuickView(pid, pname, img, quant, priceText, oldPriceText);
    });
  });

});

// Update Cart Badge
function updateNavbarCartBadge() {
  const isLoggedIn = !!document.getElementById("profileTrigger");
  const badges = [document.getElementById("cartCount"), document.getElementById("mobileCartCount")];

  if (!isLoggedIn) {
    const cart = JSON.parse(localStorage.getItem('fruvive_cart')) || [];
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    badges.forEach(badge => {
      if (badge) {
        badge.innerText = totalQty;
        if (totalQty > 0) {
          badge.classList.remove("hidden");
        } else {
          badge.classList.add("hidden");
        }
      }
    });
  }
}

// Update Wishlist Badge
function updateNavbarWishlistBadge(directCount = null) {
  const badges = [document.getElementById("wishlistBadge"), document.getElementById("mobileWishlistBadge")];

  if (directCount !== null) {
    badges.forEach(badge => {
      if (badge) {
        badge.innerText = directCount;
        if (directCount > 0) {
          badge.classList.remove("hidden");
        } else {
          badge.classList.add("hidden");
        }
      }
    });
    return;
  }

  const isLoggedIn = !!document.getElementById("profileTrigger");
  if (!isLoggedIn) {
    const wishlist = JSON.parse(localStorage.getItem('fruvive_wishlist')) || [];
    badges.forEach(badge => {
      if (badge) {
        badge.innerText = wishlist.length;
        if (wishlist.length > 0) {
          badge.classList.remove("hidden");
        } else {
          badge.classList.add("hidden");
        }
      }
    });
  }
}

// Initialize Guest Hearts
function initProductHearts() {
  const isLoggedIn = !!document.getElementById("profileTrigger");
  if (isLoggedIn) return; // Database renders server-side correctly

  const wishlist = JSON.parse(localStorage.getItem('fruvive_wishlist')) || [];
  document.querySelectorAll(".wishlist-btn").forEach((btn) => {
    const pid = btn.getAttribute("data-pid");
    if (wishlist.includes(pid)) {
      btn.setAttribute("data-wishlisted", "1");
      const icon = btn.querySelector("span");
      if (icon) {
        icon.innerText = "favorite";
        icon.classList.remove("text-gray-400");
        icon.classList.add("text-red-500");
      }
    }
  });
}

// Toggle Wishlist Status
function toggleProductWishlist(pid, btn) {
  const isWishlisted = btn.getAttribute("data-wishlisted") === "1";
  const action = isWishlisted ? "remove" : "add";

  fetch("toggleWishlist.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `pid=${pid}&action=${action}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "guest_success") {
        // Guest mode localStorage updates
        let wishlist = JSON.parse(localStorage.getItem('fruvive_wishlist')) || [];
        if (action === "add") {
          if (!wishlist.includes(pid)) wishlist.push(pid);
          Swal.fire({
            icon: 'success',
            title: 'Wishlisted!',
            text: 'Product added to your wishlist.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          wishlist = wishlist.filter(id => id != pid);
          Swal.fire({
            icon: 'info',
            title: 'Removed',
            text: 'Product removed from your wishlist.',
            timer: 1500,
            showConfirmButton: false
          });
        }
        localStorage.setItem('fruvive_wishlist', JSON.stringify(wishlist));
        
        // Update elements
        btn.setAttribute("data-wishlisted", action === "add" ? "1" : "0");
        const icon = btn.querySelector("span");
        if (icon) {
          icon.innerText = action === "add" ? "favorite" : "favorite_border";
          if (action === "add") {
            icon.classList.remove("text-gray-400");
            icon.classList.add("text-red-500");
          } else {
            icon.classList.remove("text-red-500");
            icon.classList.add("text-gray-400");
          }
        }
        updateNavbarWishlistBadge();
      } else if (data.status === "success") {
        // Authenticated database updates
        btn.setAttribute("data-wishlisted", action === "add" ? "1" : "0");
        const icon = btn.querySelector("span");
        if (icon) {
          icon.innerText = action === "add" ? "favorite" : "favorite_border";
          if (action === "add") {
            icon.classList.remove("text-gray-400");
            icon.classList.add("text-red-500");
          } else {
            icon.classList.remove("text-red-500");
            icon.classList.add("text-gray-400");
          }
        }
        
        Swal.fire({
          icon: 'success',
          title: action === 'add' ? 'Wishlisted!' : 'Removed',
          text: action === 'add' ? 'Product added to your wishlist.' : 'Product removed from your wishlist.',
          timer: 1500,
          showConfirmButton: false
        });
        updateNavbarWishlistBadge(data.count);
      } else {
        showError(data.message || "An error occurred.");
      }
    });
}

// Add to Cart Logic
function handleAddToCart(pid, img = "") {
  fetch("useraddcartValue.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `pid=${pid}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        showPopup(data.pname);
      } else if (data.status === "guest_success") {
        // Guest mode addition to localStorage
        let cart = JSON.parse(localStorage.getItem('fruvive_cart')) || [];
        const idx = cart.findIndex(item => item.pid == data.pid);
        if (idx !== -1) {
          const newQty = cart[idx].qty + 1;
          if (newQty > data.max_stock) {
            showError("Only " + data.max_stock + " items left in stock.");
            return;
          }
          if (newQty > 5) {
            showError("You can add up to 5 items of a single product to your cart.");
            return;
          }
          cart[idx].qty = newQty;
        } else {
          if (data.max_stock <= 0) {
            showError("This item is currently out of stock.");
            return;
          }
          cart.push({ pid: data.pid, pname: data.pname, price: data.price, qty: 1, img: img });
        }
        localStorage.setItem('fruvive_cart', JSON.stringify(cart));
        showPopup(data.pname);
        updateNavbarCartBadge();
      } else {
        showError(data.message || "Insufficient stock available.");
      }
    });
}

// add to cart popup show
function showPopup(productName) {
  const popup = document.getElementById("cartPopup");
  if (!popup) return;
  const name = document.getElementById("popupProduct");
  if (name) name.innerText = productName + " has been added successfully.";
  popup.classList.remove("translate-y-[150%]");
  popup.classList.add("translate-y-0");
  setTimeout(() => {
    hidePopup();
  }, 3000);
}

// add to cart popup hide
function hidePopup() {
  const popup = document.getElementById("cartPopup");
  if (!popup) return;
  popup.classList.remove("translate-y-0");
  popup.classList.add("translate-y-[150%]");
  location.reload();
}

function showError(message) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'error',
      title: 'Stock Limit',
      text: message,
      confirmButtonColor: '#ea580c'
    });
  } else {
    alert(message);
  }
}

// Setup Quick View Modal element
function setupQuickViewModal() {
  if (document.getElementById("quickViewModal")) return;
  const modalHtml = `
    <div id="quickViewModal" class="fixed inset-0 bg-black/50 hidden z-50 items-center justify-center p-4 transition-all duration-300">
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-700 relative animate-in fade-in zoom-in duration-200 flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-850">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="material-icons text-green-600">visibility</span>
            Product Details
          </h2>
          <button id="closeQuickView" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
            <span class="material-icons text-2xl">close</span>
          </button>
        </div>
        
        <!-- Body: scrollable -->
        <div class="p-6 overflow-y-auto flex-grow space-y-6">
          <div class="flex flex-col md:flex-row gap-6">
            <!-- Left: Image -->
            <div class="w-full md:w-1/2 flex items-center justify-center bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-4 aspect-square border border-gray-100 dark:border-gray-700">
              <img id="qvImage" src="" class="h-44 object-contain">
            </div>
            
            <!-- Right: Details summary -->
            <div class="w-full md:w-1/2 flex flex-col justify-between">
              <div>
                <span class="text-[10px] uppercase font-bold tracking-wide bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 px-2.5 py-1 rounded-full">100% Organic</span>
                <h3 id="qvTitle" class="text-xl font-bold text-gray-900 dark:text-white mt-3"></h3>
                <p id="qvQty" class="text-xs font-semibold text-orange-500 mt-1 uppercase tracking-wider"></p>
                
                <!-- Rating indicator -->
                <div class="flex items-center gap-1 mt-2">
                  <span id="qvStars" class="flex text-yellow-500"></span>
                  <span id="qvRatingText" class="text-xs text-gray-500 dark:text-gray-400 ml-1"></span>
                </div>
                
                <div class="text-xs space-y-2 mt-4 text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-3">
                  <p><strong class="text-gray-800 dark:text-gray-100">Shelf Life:</strong> Best consumed within 4-5 days</p>
                  <p><strong class="text-gray-800 dark:text-gray-100">Origin:</strong> Local farm fresh harvest</p>
                </div>
              </div>
              
              <div class="mt-6">
                <div class="flex items-baseline gap-2">
                  <span id="qvPrice" class="text-2xl font-extrabold text-green-600 dark:text-green-400"></span>
                  <span id="qvOldPrice" class="text-sm text-gray-400 line-through"></span>
                </div>
                <button id="qvAddCart" class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md shadow-green-600/10 active:scale-98">
                  Add to Cart
                </button>
              </div>
            </div>
          </div>
          
          <!-- Reviews section -->
          <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
            <h4 class="text-sm font-bold text-gray-950 dark:text-white mb-4 flex items-center gap-1.5">
              <span class="material-icons text-sm text-yellow-500">rate_review</span>
              Customer Reviews
            </h4>
            
            <!-- List of Reviews -->
            <div id="qvReviewsList" class="space-y-3 max-h-[160px] overflow-y-auto pr-2">
              <!-- Dynamically populated -->
            </div>
            
            <!-- Add Review Form -->
            <div id="qvReviewFormContainer" class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4 hidden">
              <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Add Your Review</h5>
              <div class="space-y-3">
                <!-- Star selection (interactive) -->
                <div class="flex items-center gap-1.5">
                  <span class="text-xs text-gray-500 dark:text-gray-400">Your Rating:</span>
                  <div id="reviewStarSelector" class="flex gap-1 text-gray-300 dark:text-gray-650 cursor-pointer">
                    <span class="material-icons star-select text-lg" data-value="1">star_border</span>
                    <span class="material-icons star-select text-lg" data-value="2">star_border</span>
                    <span class="material-icons star-select text-lg" data-value="3">star_border</span>
                    <span class="material-icons star-select text-lg" data-value="4">star_border</span>
                    <span class="material-icons star-select text-lg" data-value="5">star_border</span>
                  </div>
                  <input type="hidden" id="reviewRatingInput" value="0">
                </div>
                <!-- Text comment -->
                <textarea id="reviewCommentInput" rows="2" placeholder="Write your feedback..." class="w-full text-xs border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50 dark:bg-gray-900 outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition dark:text-white resize-none"></textarea>
                
                <button id="submitReviewBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition shadow-md shadow-green-600/10">
                  Submit Review
                </button>
              </div>
            </div>
            
            <!-- Login prompt if guest -->
            <div id="qvReviewLoginPrompt" class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4 text-center hidden">
              <p class="text-xs text-gray-500 dark:text-gray-400">Please <a href="login.php" class="text-green-600 dark:text-green-400 font-bold hover:underline">login</a> to write a product review.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  
  // Bind close events
  document.getElementById("closeQuickView").addEventListener("click", () => {
    document.getElementById("quickViewModal").classList.add("hidden");
    document.getElementById("quickViewModal").classList.remove("flex");
  });

  document.getElementById("quickViewModal").addEventListener("click", function(e) {
    if (e.target === this) {
      this.classList.add("hidden");
      this.classList.remove("flex");
    }
  });

  // Bind interactive rating stars selector
  const starSelects = document.querySelectorAll(".star-select");
  starSelects.forEach(star => {
    star.addEventListener("click", function() {
      const val = parseInt(this.getAttribute("data-value"));
      document.getElementById("reviewRatingInput").value = val;
      
      starSelects.forEach(s => {
        const sVal = parseInt(s.getAttribute("data-value"));
        if (sVal <= val) {
          s.innerText = "star";
          s.classList.remove("text-gray-300", "dark:text-gray-600");
          s.classList.add("text-yellow-500");
        } else {
          s.innerText = "star_border";
          s.classList.remove("text-yellow-500");
          s.classList.add("text-gray-300", "dark:text-gray-600");
        }
      });
    });
  });
}

function openQuickView(pid, pname, img, quant, price, oldPrice) {
  document.getElementById("qvTitle").innerText = pname;
  document.getElementById("qvImage").setAttribute("src", img);
  document.getElementById("qvQty").innerText = quant;
  document.getElementById("qvPrice").innerText = "₹" + price;
  document.getElementById("qvOldPrice").innerText = oldPrice ? oldPrice : "";
  
  const addBtn = document.getElementById("qvAddCart");
  addBtn.setAttribute("data-pid", pid);
  
  // Re-bind click event to Add to Cart
  const newAddBtn = addBtn.cloneNode(true);
  addBtn.parentNode.replaceChild(newAddBtn, addBtn);
  
  newAddBtn.addEventListener("click", function() {
    handleAddToCart(pid, img);
    document.getElementById("quickViewModal").classList.add("hidden");
    document.getElementById("quickViewModal").classList.remove("flex");
  });

  // Reset review form inputs
  document.getElementById("reviewRatingInput").value = "0";
  document.getElementById("reviewCommentInput").value = "";
  document.querySelectorAll(".star-select").forEach(s => {
    s.innerText = "star_border";
    s.classList.remove("text-yellow-500");
    s.classList.add("text-gray-300", "dark:text-gray-600");
  });

  // Load ratings and review records from database
  fetch(`getProductDetails.php?pid=${pid}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        // Draw star list
        const starsContainer = document.getElementById("qvStars");
        starsContainer.innerHTML = "";
        const avg = parseFloat(data.avg_rating);
        
        for (let i = 1; i <= 5; i++) {
          if (avg >= i) {
            starsContainer.innerHTML += `<span class="material-icons text-sm">star</span>`;
          } else if (avg >= i - 0.5) {
            starsContainer.innerHTML += `<span class="material-icons text-sm">star_half</span>`;
          } else {
            starsContainer.innerHTML += `<span class="material-icons text-sm text-gray-300 dark:text-gray-650">star_border</span>`;
          }
        }
        
        document.getElementById("qvRatingText").innerText = data.review_count > 0 
          ? `${data.avg_rating} (${data.review_count} reviews)`
          : "No reviews yet";

        // Display list of user comments
        const listContainer = document.getElementById("qvReviewsList");
        listContainer.innerHTML = "";
        
        if (data.reviews.length === 0) {
          listContainer.innerHTML = `<p class="text-xs text-gray-500 dark:text-gray-400 italic">No reviews has been posted for this product yet. Be the first to share your thoughts!</p>`;
        } else {
          data.reviews.forEach(rev => {
            let userStars = "";
            for (let i = 1; i <= 5; i++) {
              userStars += `<span class="material-icons text-[11px] ${i <= rev.rating ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600'}">star</span>`;
            }
            
            listContainer.innerHTML += `
              <div class="bg-gray-50/50 dark:bg-gray-900/30 p-3 rounded-2xl border border-gray-100 dark:border-gray-800/80">
                <div class="flex justify-between items-center text-xs">
                  <span class="font-bold text-gray-900 dark:text-white">${rev.username}</span>
                  <span class="text-[10px] text-gray-400 dark:text-gray-500">${rev.created_at}</span>
                </div>
                <div class="flex gap-0.5 my-1">
                  ${userStars}
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">${rev.comment || '<i>No comments provided.</i>'}</p>
              </div>
            `;
          });
        }

        // Show/hide review forms based on login status
        const formContainer = document.getElementById("qvReviewFormContainer");
        const loginPrompt = document.getElementById("qvReviewLoginPrompt");
        
        if (data.isLoggedIn) {
          formContainer.classList.remove("hidden");
          loginPrompt.classList.add("hidden");
          
          // Re-bind submit button click
          const submitBtn = document.getElementById("submitReviewBtn");
          const newSubmitBtn = submitBtn.cloneNode(true);
          submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
          
          newSubmitBtn.addEventListener("click", () => {
            submitProductReview(pid);
          });
        } else {
          formContainer.classList.add("hidden");
          loginPrompt.classList.remove("hidden");
        }
      }
    });

  const modal = document.getElementById("quickViewModal");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function submitProductReview(pid) {
  const rating = parseInt(document.getElementById("reviewRatingInput").value);
  const comment = document.getElementById("reviewCommentInput").value.trim();

  if (rating <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Rating Required',
      text: 'Please select a rating score between 1 and 5 stars.',
      confirmButtonColor: '#ea580c'
    });
    return;
  }

  fetch("submitReview.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `pid=${pid}&rating=${rating}&comment=${encodeURIComponent(comment)}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        Swal.fire({
          icon: 'success',
          title: 'Submitted!',
          text: data.message,
          confirmButtonColor: '#16a34a'
        });
        // Reload modal content
        const title = document.getElementById("qvTitle").innerText;
        const img = document.getElementById("qvImage").getAttribute("src");
        const qty = document.getElementById("qvQty").innerText;
        const price = document.getElementById("qvPrice").innerText.replace("₹", "");
        const oldPrice = document.getElementById("qvOldPrice").innerText;
        openQuickView(pid, title, img, qty, price, oldPrice);
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Submission Failed',
          text: data.message,
          confirmButtonColor: '#ea580c'
        });
      }
    });
}

// cart quantity control
document.querySelectorAll(".qty-control").forEach((box) => {
  const minus = box.querySelector(".minus");
  const plus = box.querySelector(".plus");
  const input = box.querySelector(".qty-input");
  const cartid = box.dataset.id;

  plus.addEventListener("click", () => {
    let qty = parseInt(input.value) || 1;
    qty = Math.min(qty + 1, 5);
    updateQty(cartid, qty, input);
  });
  
  minus.addEventListener("click", () => {
    let qty = parseInt(input.value) || 1;
    qty = Math.max(qty - 1, 1);
    updateQty(cartid, qty, input);
  });
});

function updateQty(cartid, qty, input) {
  fetch("userupdatecartValue.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `cartid=${cartid}&qty=${qty}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        input.value = qty;
        location.reload();
      } else {
        showError(data.message || "Insufficient stock available.");
      }
    });
}

function openOrderAddressPopup() {
  document.getElementById("addressPopup").classList.remove("hidden");
  setTimeout(() => {
    document.getElementById("addressPanel").classList.remove("translate-x-full");
  }, 10);
}

function closeOrderAddressPopup() {
  document.getElementById("addressPanel").classList.add("translate-x-full");
  setTimeout(() => {
    document.getElementById("addressPopup").classList.add("hidden");
  }, 300);
}


