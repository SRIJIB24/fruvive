document.addEventListener("DOMContentLoaded", () => {
  //add to cart
  document.querySelectorAll(".add-cart-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const pid = this.getAttribute("data-pid");
      fetch("useraddcartValue.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `pid=${pid}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            showPopup(data.pname);
          } else {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'error',
                title: 'Stock Limit',
                text: data.message,
                confirmButtonColor: '#ea580c'
              });
            } else {
              alert(data.message || "Insufficient stock available.");
            }
          }
        });
    });
  });

  document.querySelectorAll(".go-cart-btn").forEach((button) => {
    button.addEventListener("click",function() {
      window.location.href = "usercart.php";
    });
  });
});

//add to cart popup show
function showPopup(productName) {
  const popup = document.getElementById("cartPopup");
  const name = document.getElementById("popupProduct");
  name.innerText = productName + " has been added successfully.";
  popup.classList.remove("translate-y-[150%]");
  popup.classList.add("translate-y-0");
  setTimeout(() => {
    hidePopup();
  }, 5000);
}
//add to cart popup hide
function hidePopup() {
  const popup = document.getElementById("cartPopup");
  popup.classList.remove("translate-y-0");
  popup.classList.add("translate-y-[150%]");
  location.reload();
}

//cart quantity control
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
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Stock Limit',
            text: data.message,
            confirmButtonColor: '#ea580c'
          });
        } else {
          alert(data.message || "Insufficient stock available.");
        }
      }
    });
}

function openOrderAddressPopup()
{
    document.getElementById("addressPopup").classList.remove("hidden");

    setTimeout(()=>{
        document
        .getElementById("addressPanel")
        .classList.remove("translate-x-full");
    },10);
}

function closeOrderAddressPopup()
{
    document
    .getElementById("addressPanel")
    .classList.add("translate-x-full");

    setTimeout(()=>{
        document
        .getElementById("addressPopup")
        .classList.add("hidden");
    },300);
}


