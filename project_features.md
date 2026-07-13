# Fruvive E-Commerce Project Research & Audit Report

This document contains a deep analysis of the pending **Fruvive** e-commerce project codebase. It audits the existing features, lists missing or incomplete pages, identifies architectural discrepancies, and flags critical bugs/security issues that need immediate resolution.

---

## 👥 User Level Architecture

The application defines two distinct logged-in user levels (stored in the `userlevel` column of the `users` table) and handles guest access:

### 1. Guest / Unauthenticated User
*   **Behavior:** Direct access to most pages is prevented. The `sessionCheck()` method in [userFunc.php](file:///C:/xampp/htdocs/fruvive/userFunc.php) checks for active session variables (`user_id` and `userlevel`). If not set, the user is redirected to `login.php`.
*   **Accessible Pages:** `login.php`, `signup.php`, `signlogFunc.php`.

### 2. Customer (Userlevel: `10`)
*   **Behavior:** Regular customers who buy products. If a customer attempts to access any admin file, they are redirected to `index.php`.
*   **Accessible Pages:** `index.php`, `userdailyfruits.php`, `userseasonalfruits.php`, `userdryfruits.php`, `usercart.php`, `userPayment.php`, `userAddress.php`, `userAddAddress.php`, `userProfile.php`, and associated AJAX endpoints (`useraddcartValue.php`, `userupdatecartValue.php`).

### 3. Admin (Userlevel: `-1`)
*   **Behavior:** Administrators who manage inventory, categories, products, and order assignments. If an admin attempts to access customer pages, they are automatically redirected to `admin.php`.
*   **Accessible Pages:** `admin.php`, `admincategory.php`, `adminproducts.php`, `adminstockin.php`, `adminReceiveorder.php`, `adminAssignDelivery.php`, and supporting endpoints (`admincatEditDiv.php`, `admincatDelete.php`, `adminproEditDiv.php`, `adminproDelete.php`, `admingetProDiv.php`, `admingetProQuantDiv.php`, `adminstockinEditDiv.php`, `adminstockinDelete.php`).

---

## 🗄️ Database Schema & Architectural Inconsistencies

There is a significant mismatch between the database schema defined in the SQL dump [oops_1.sql](file:///C:/xampp/htdocs/fruvive/oops_1.sql) and what the PHP codebase expects:

### 1. Missing Database Tables (Used in Code but Absent in SQL Dump)
*   **`user_address` Table:** Used for shipping details. (Resolved: Successfully created in local database).
*   **`order_items` Table:** Used in `userPayment.php` (line 38) to save individual items in an order, but is not defined in `oops_1.sql`.

### 2. Table Column Schema Mismatches
*   **`products` Table (`quant` vs `pack_quant`):** 
    *   (Resolved: Aligned all backend queries in userFunc.php to use the correct `quant` column and updated list templates).
*   **`orders` Table Structure Mismatch:** 
    *   In the SQL dump, `orders` has columns: `id`, `proid`, `order_date`, `expect_date`, `delivery_date`, `pack_quant`, `pack_price`, `total_packs`, `total_price`, `assign_man_id`, `status`, `edtm`.
    *   In the customer checkout code ([userPayment.php](file:///C:/xampp/htdocs/fruvive/userPayment.php) line 29), the code tries to insert: `userid`, `total`, `payment_method`, and `status`. These columns do not exist in the SQL dump structure.
*   **`users` Table (`phone`):** 
    *   (Resolved: Added `phone` and `user_img` columns to users table and fixed the undefined warnings).
*   **`cart` Table (`image`):**
    *   (Resolved: Updated fetchCart() method to JOIN cart with products table and fetch img_url dynamically, removing need for duplicate database column).

---

## 📋 Features Audit

Below is a detailed audit of the basic e-commerce features, separated by whether they are already implemented or still need to be built.

| Feature Area | Status | Existing Implementation details | Needs to be Implemented / Fixed |
| :--- | :--- | :--- | :--- |
| **Authentication & Registration** | ⚠️ Part-Ready | Beautiful, premium split layout pages (`login.php`, `signup.php`) with brand logo, sliding motivational thoughts/quotes, and password toggles. | ❌ Passwords are saved in plain text. Need to use `password_hash()` and `password_verify()`. Needs email/input validation. |
| **Home Page / Dashboard** | ⚠️ Part-Ready | Beautiful Tailwind layout exists (`index.php`) with hero section and a grid of popular items. | ❌ The product grid on the home page is completely static/hardcoded and buttons don't function. Needs to fetch actual featured items dynamically. |
| **Product Catalogs** | ✅ Ready | Daily, Dry, Seasonal, Cut Fruit Cups (`usercutfruits.php`), and Gift Baskets (`usergiftbasket.php`) category pages dynamically fetch items. Responsive premium grids, scale animations, brand colors. | None (Completed). |
| **Shopping Cart** | ✅ Ready | Redesigned with premium responsive layout, class-based dark/light theme support. Dynamic coupon input form, minimum purchase verification, and SweetAlert notifications. | None (Completed). |
| **Address Book** | ✅ Ready | Displays saved addresses, handles adding (`userAddAddress.php`), editing (`editAddress.php`), and deleting (`deleteAddress.php`) with full support for default address setting and auto-promotion. | None (Completed). |
| **Checkout & Payments** | ✅ Ready | Form allows choosing COD, UPI, or Card. Restructured to use PDO prepared statements, accounts for coupon discount deductions, and inserts orders with client_id. Renders to a custom orderSuccess.php page. | None (Completed). |
| **User Profile** | ✅ Ready | Custom split-dashboard layout displaying user details, dynamic uploaded profile pictures, email, customer role, and last login timestamps. Fully editable username/phone. | None (Completed). |
| **Admin Categories** | class="text-green-500"| Yes | CRUD fully implemented. Category names added, edited, and deleted using dynamic jQuery loads. | None (working as intended, except standard design updates). |
| **Admin Products** | ✅ Ready | Product CRUD forms exist (`adminproducts.php`). Dynamic category dropdown select works. Image upload and editing fully integrated. Columns aligned with DB schema. | None (Completed). |
| **Admin Stock-In** | ⚠️ Part-Ready | Handles adding batches of stock (quantity, pack price, date). Autoloads pack quantity via jQuery depending on product selected. | ❌ Display bugs caused by reading `quant` instead of `pack_quant` in `admingetProQuantDiv.php`. |
| **Admin Stock-Out** | ❌ Missing | Link present in the admin sidebar menu. | ❌ File `adminstockout.php` does not exist in the project directory. |
| **Admin Orders View** | ❌ Missing | Admin sidebar links to `adminReceiveorder.php`. | ❌ Page is a placeholder skeleton showing `<h1>Users</h1>` with no order list or details. |
| **Admin Assign Delivery** | ❌ Missing | Admin sidebar links to `adminAssignDelivery.php`. | ❌ File `adminAssignDelivery.php` is completely empty (0 bytes). |

---

## 🚨 Critical Bugs & Code Deficiencies

Here is a summary of the most severe code errors that will block execution or crash the server:

1.  **PDO vs. MySQLi Crash in [userPayment.php](file:///C:/xampp/htdocs/fruvive/userPayment.php) (Lines 41-59):**
    The database connection (`$object->conn`) is established using PDO, but the order processing loop uses mysqli procedural functions:
    ```php
    $q1 = mysqli_query($object->conn, "SELECT pack_quant FROM products WHERE id='$pid'"); // Fails!
    $d1 = mysqli_fetch_assoc($q1); // Fails!
    mysqli_query($object->conn, "UPDATE stock_in SET total_quant = total_quant - $reduce WHERE proid='$pid'"); // Fails!
    ```
    This results in a **Fatal PHP Error** and breaks checkout completely.
2.  **Undefined Class Method in [userPayment.php](file:///C:/xampp/htdocs/fruvive/userPayment.php) (Line 64):**
    ```php
    $object->deltCart($userid);
    ```
    The `data` class in `userFunc.php` does not have a `deltCart()` method. It is named `clearCart($userid)`. This causes a crash upon order placement.
3.  **Broken Key Name in [userPayment.php](file:///C:/xampp/htdocs/fruvive/userPayment.php) (Lines 112-114):**
    ```php
    <?= $address['pincode 
    
    '] ?>
    ```
    The linebreak inside the array key is a syntax error / notice hazard and will break address rendering in the invoice preview.
4.  **Plain Text Passwords in [signlogFunc.php](file:///C:/xampp/htdocs/fruvive/signlogFunc.php) (Lines 20 & 56):**
    Passwords are inserted and checked directly as plain text. This is a severe security risk. Passwords should be stored as hashes.
5.  **Broken AJAX Call in [admingetProQuantDiv.php](file:///C:/xampp/htdocs/fruvive/admingetProQuantDiv.php):**
    Line 12 references `$val['quant']`, but `profetch()` returns `pack_quant`. The input box will always show up blank.
6.  **Broken and Dead Code in [addToCart.php](file:///C:/xampp/htdocs/fruvive/addToCart.php):**
    This script contains numerous undefined variables (such as `$sql`), calls undefined methods, uses incorrect session keys (`$_SESSION['userid']`), and mixes database drivers. It should be cleaned up.

---

## 🛠️ Recommended Action Plan

To get this project ready for production, the following steps must be taken:
1.  **Repair the DB Schema:** Run SQL updates to align the database tables with the code (adding `user_address`, `order_items`, fixing `products.quant` name, adding `phone` to `users`, and adding `image` to `products`/`cart`).
2.  **Fix checkout logic:** Rewrite procedural `mysqli` queries in `userPayment.php` to use the standardized PDO syntax. Rename the `deltCart()` call to `clearCart()`.
3.  **Create the missing files:** Implement `usercutfruits.php`, `usergiftbasket.php`, `orderSuccess.php`, `editAddress.php`, `deleteAddress.php`, and `adminstockout.php`.
4.  **Flesh out Order Management:** Add actual tables and statuses to `adminReceiveorder.php` and build out `adminAssignDelivery.php`.
5.  **Secure Authentication:** Refactor login and signup scripts to hash passwords using standard cryptography.
