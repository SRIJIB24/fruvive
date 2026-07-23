<?php
require "config.php";
session_start();

class data extends database
{
    public $userlvl;
    private $table = "users";


    public $cname;
    public $return_policy;
    public $cid;
    public $cidedit;
    public $ciddel;

    public $catid;
    public $pname;
    public $pquant;
    public $pid;
    public $proedit;
    public $prodel;

    public $proid;
    public $sid;
    public $sdel;
    public $packquant;
    public $packprice;
    public $totalquant;
    public $date;

    public $qty;
    public $price;
    public $newQty;


    public function sessionCheck()
    {

        // If session not set → redirect
        if (!isset($_SESSION['user_id'], $_SESSION['userlevel'])) {
            header("Location: login.php");
            exit();
        }

        // OPTIONAL (extra safety): verify user still exists
        $sql = $this->conn->prepare("SELECT id,userlevel FROM {$this->table} WHERE id = :id");
        $sql->execute([':id' => $_SESSION['user_id']]);

        if ($sql->rowCount() !== 1) {
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();
        }
        $this->userlvl = (int)$_SESSION['userlevel'];
    }

    public function visitorSessionCheck()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'], $_SESSION['userlevel'])) {
            $this->userlvl = (int)$_SESSION['userlevel'];
            if ($this->userlvl === -1) {
                header("Location: admin.php");
                exit();
            }
            return true;
        }
        $this->userlvl = 0; // Default visitor level
        return false;
    }



    //Count total users
    public function totalUsers()
    {
        $sql = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE userlevel = '10' ");
        $sql->execute();
        return $sql->fetchColumn();
    }


    /*<----CATEGORY---->*/

    //category name submit
    public function catSubmit()
    {
        $edtm = date("Y-m-d H:i:s");
        $sql = $this->conn->prepare("INSERT INTO category(cname,return_policy,edtm,client_id) VALUES(:cnm,:return_policy,'$edtm',:client_id)");
        $sql->execute([':cnm' => $this->cname, ':return_policy' => $this->return_policy, ':client_id' => CLIENT_ID]);
    }

    //category data fetch
    public function category()
    {
        $sql = $this->conn->prepare("SELECT id,cname,return_policy FROM category WHERE client_id = :client_id");
        $sql->execute([':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    //category data fetch based on id
    public function catfetch()
    {
        $sql = $this->conn->prepare("SELECT id,cname,return_policy FROM category WHERE id = :id AND client_id = :client_id");
        $sql->execute([':id' => $this->cid, ':client_id' => CLIENT_ID]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    //category name update
    public function catUpdate()
    {
        $edtm = date("Y-m-d H:i:s");
        $sql = $this->conn->prepare("UPDATE category SET cname = :cnm, return_policy = :return_policy, edtm = '$edtm' WHERE id = :id AND client_id = :client_id");
        $sql->execute([':cnm' => $this->cname, ':return_policy' => $this->return_policy, ':id' => $this->cidedit, ':client_id' => CLIENT_ID]);
    }

    //category name delete
    public function catDelete()
    {
        $sql = $this->conn->prepare("DELETE FROM category WHERE id = :id AND client_id = :client_id");
        $sql->execute([':id' => $this->ciddel, ':client_id' => CLIENT_ID]);
    }


    /*<----PRODUCTS---->*/

    //products name submit
    public function proSubmit()
    {
        $edtm = date("Y-m-d H:i:s");
        $sql = $this->conn->prepare("INSERT INTO products(cid,pname,quant,edtm,client_id) VALUES(:cid,:pnm,:quant,'$edtm',:client_id)");
        $sql->execute([':cid' => $this->catid, ':pnm' => $this->pname, ':quant' => $this->pquant, ':client_id' => CLIENT_ID]);
        return $this->conn->lastInsertId();
    }

    //products data fetch
    public function products()
    {
        $sql = $this->conn->prepare("SELECT id,cid,pname,quant,img_url FROM products WHERE client_id = :client_id");
        $sql->execute([':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    //products data fetch based on id
    public function profetch()
    {
        $sql = $this->conn->prepare("SELECT id,cid,pname,quant,img_url FROM products WHERE id = :id AND client_id = :client_id");
        $sql->execute([':id' => $this->pid, ':client_id' => CLIENT_ID]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    //products data fetch based on (catid) category
    public function profetchcid()
    {
        $sql = $this->conn->prepare("SELECT id,cid,pname,quant,img_url FROM products WHERE cid = :id AND client_id = :client_id");
        $sql->execute([':id' => $this->cid, ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    //products name update
    public function proUpdate()
    {
        $edtm = date("Y-m-d H:i:s");
        $sql = $this->conn->prepare("UPDATE products SET cid = :cid, pname = :pnm, quant = :quant, edtm = '$edtm' WHERE id = :id AND client_id = :client_id");
        $sql->execute([':cid' => $this->catid, ':pnm' => $this->pname, ':quant' => $this->pquant, ':id' => $this->proedit, ':client_id' => CLIENT_ID]);
    }

    //update product image url
    public function updateProductImgUrl($pid, $img_url)
    {
        $sql = $this->conn->prepare("UPDATE products SET img_url = :img_url WHERE id = :id AND client_id = :client_id");
        $sql->execute([':img_url' => $img_url, ':id' => $pid, ':client_id' => CLIENT_ID]);
    }

    //products name delete
    public function proDelete()
    {
        $sql = $this->conn->prepare("DELETE FROM products WHERE id = :id AND client_id = :client_id");
        $sql->execute([':id' => $this->prodel, ':client_id' => CLIENT_ID]);
    }


    /*<----STOCK IN---->*/

    //stock in submit/insert
    public function stockIn()
    {
        $edtm = date("Y-m-d H:i:s");

        $total_price = $this->packprice * $this->totalquant;

        $sql = $this->conn->prepare("INSERT INTO stock_in(catid,proid,date,pack_quant,pack_price,total_quant,total_price,edtm,client_id)
        VALUES(:cid,:pid,:dt,:pq,:pp,:tq,:tp,'$edtm',:client_id)");

        $sql->execute([
            ':cid' => $this->catid,
            ':pid' => $this->proid,
            ':dt' => $this->date,
            ':pq' => $this->packquant,
            ':pp' => $this->packprice,
            ':tq' => $this->totalquant,
            ':tp' => $total_price,
            ':client_id' => CLIENT_ID
        ]);
    }

    //fetch stock in based on id
    public function stockfetch()
    {
        $sql = $this->conn->prepare("SELECT * FROM stock_in WHERE id=:id AND client_id=:client_id");
        $sql->execute([':id' => $this->sid, ':client_id' => CLIENT_ID]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    //fetch stock in
    public function stockAll()
    {
        $sql = $this->conn->prepare("SELECT * FROM stock_in WHERE client_id=:client_id");
        $sql->execute([':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    //stock in update
    public function stockUpdate()
    {
        $edtm = date("Y-m-d H:i:s");
        $total = $this->packprice * $this->totalquant;

        $sql = $this->conn->prepare(
            "UPDATE stock_in SET catid=:cid,proid=:pid,date=:dt,pack_quant=:pq,pack_price=:pp,total_quant=:tq,total_price=:tp,edtm='$edtm'
            WHERE id=:id AND client_id=:client_id"
        );

        $sql->execute([
            ':cid' => $this->catid,
            ':pid' => $this->proid,
            ':dt' => $this->date,
            ':pq' => $this->packquant,
            ':pp' => $this->packprice,
            ':tq' => $this->totalquant,
            ':tp' => $total,
            ':id' => $this->sid,
            ':client_id' => CLIENT_ID
        ]);
    }

    //stock in delete
    public function stockDelete()
    {
        $sql = $this->conn->prepare("DELETE FROM stock_in WHERE id=:id AND client_id=:client_id");
        $sql->execute([':id' => $this->sdel, ':client_id' => CLIENT_ID]);
    }

    public function stockinfetchpid($product_id)
    {
        $sql = $this->conn->prepare("SELECT s.pack_price, s.total_quant, p.pname FROM stock_in s INNER JOIN products p ON p.id = s.proid WHERE proid = :pid AND s.client_id = :client_id AND p.client_id = :client_id");
        $sql->execute([':pid' => $product_id, ':client_id' => CLIENT_ID]);
        $cartData = $sql->fetch(PDO::FETCH_ASSOC);
        return $cartData;
    }




    public function fetchFruits($catid)
    {
        $sql = $this->conn->prepare("SELECT s.*, p.pname, p.img_url FROM stock_in s
        JOIN products p ON s.proid = p.id
        WHERE s.catid = :catid AND s.client_id = :client_id AND p.client_id = :client_id");
        $sql->execute([':catid' => $catid, ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isincart($proid, $userid)
    {
        $sql = $this->conn->prepare("SELECT COUNT(*) FROM cart WHERE userid = :userid AND productid = :productid AND client_id = :client_id");
        $sql->execute([':userid' => $userid, ':productid' => $proid, ':client_id' => CLIENT_ID]);
        return $sql->fetchColumn();
    }


    /*<----CART---->*/

    //fetch cart items
    public function fetchCart()
    {
        $sql = $this->conn->prepare("SELECT c.*, p.img_url FROM cart c 
        LEFT JOIN products p ON c.productid = p.id 
        WHERE c.userid = :userid AND c.client_id = :client_id");
        $sql->execute([':userid' => $_SESSION['user_id'], ':client_id' => CLIENT_ID]);
        $cartItems = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $cartItems;
    }

    //insert items into cart
    public function insertCart($user_id, $product_id, $product_name, $price, $quantity)
    {
        $insert = $this->conn->prepare("INSERT INTO cart (userid, productid, pname, price, qty, client_id) VALUES (:userid, :pid, :pnm, :price, :qnty, :client_id)");
        $insert->execute([':userid' => $user_id, ':pid' => $product_id, ':pnm' => $product_name, ':price' => $price, ':qnty' => $quantity, ':client_id' => CLIENT_ID]);
    }

    //delete cart items
    public function deltcartproduct($id)
    {
        $sql = $this->conn->prepare("DELETE FROM cart WHERE id = :id ");
        $sql->execute([':id' => $id]);
    }

    // fetch by productid
    public function fetchCartpid($user_id, $product_id)
    {
        $sql = $this->conn->prepare("SELECT * FROM cart WHERE userid = :userid AND productid = :pid AND client_id = :client_id");
        $sql->execute([':userid' => $user_id, ':pid' => $product_id, ':client_id' => CLIENT_ID]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    // search products
    public function searchProducts($query)
    {
        $sql = $this->conn->prepare("SELECT s.*, p.pname, p.img_url FROM stock_in s
        JOIN products p ON s.proid = p.id
        WHERE p.pname LIKE :query AND s.client_id = :client_id AND p.client_id = :client_id");
        $sql->execute([':query' => "%$query%", ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cartCount($userid)
    {
        $sql = $this->conn->prepare("SELECT COUNT(*) FROM cart WHERE userid = :userid");
        $sql->execute([':userid' => $userid]);
        return $sql->fetchColumn();
    }

    //update cart item quantity
    public function updatecartQuant($cart_id, $qty)
    {
        $sql = $this->conn->prepare("UPDATE cart SET qty=:qty WHERE id=:id");
        $sql->execute([':qty' => $qty, ':id' => $cart_id]);
    }

    public function clearCart($userid)
    {
        $delete = $this->conn->prepare("DELETE FROM cart WHERE userid = :userid");
        $delete->execute([':userid' => $userid]);
    }

    //profile info
    public function getprofile($userid)
    {
        $sql = $this->conn->prepare("SELECT * FROM users WHERE id = :userid");
        $sql->execute([':userid' => $userid]);
        return $sql->fetch();
    }


    // add address
    public function insertAddress($userid, $name, $phone, $address, $city, $state, $pincode, $locality, $landmark, $altphone, $type, $default)
    {

        $check = $this->conn->prepare("SELECT COUNT(*) FROM user_address WHERE userid = :userid");
        $check->execute([':userid' => $userid]);
        $count = $check->fetchColumn();

        if ($count == 0) {
            $default = 1;
        }


        if ($default == 1) {
            $update = $this->conn->prepare("UPDATE user_address SET is_default = 0 WHERE userid = :userid");
            $update->execute([':userid' => $userid]);
        }


        $insert = $this->conn->prepare("INSERT INTO user_address(userid, name, phone, address, city, state, pincode, locality, landmark, alt_phone, type, is_default)
            VALUES(:userid, :name, :phone, :address, :city, :state, :pincode, :locality, :landmark, :altphone, :type, :default)");

        $insert->execute([
            ':userid' => $userid, 
            ':name' => $name, 
            ':phone' => $phone, 
            ':address' => $address, 
            ':city' => $city, 
            ':state' => $state, 
            ':pincode' => $pincode,
            ':locality' => $locality,
            ':landmark' => $landmark,
            ':altphone' => $altphone,
            ':type' => $type,
            ':default' => $default
        ]);
    }

    // fetch address by id
    public function fetchAddressById($id, $userid)
    {
        $sql = $this->conn->prepare("SELECT * FROM user_address WHERE id = :id AND userid = :userid");
        $sql->execute([':id' => $id, ':userid' => $userid]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    // update address
    public function updateAddress($id, $userid, $name, $phone, $address, $city, $state, $pincode, $locality, $landmark, $altphone, $type, $default)
    {
        if ($default == 1) {
            $update = $this->conn->prepare("UPDATE user_address SET is_default = 0 WHERE userid = :userid");
            $update->execute([':userid' => $userid]);
        }

        $sql = $this->conn->prepare("UPDATE user_address SET 
            name = :name,
            phone = :phone,
            address = :address,
            city = :city,
            state = :state,
            pincode = :pincode,
            locality = :locality,
            landmark = :landmark,
            alt_phone = :altphone,
            type = :type,
            is_default = :default
            WHERE id = :id AND userid = :userid");

        $sql->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':address' => $address,
            ':city' => $city,
            ':state' => $state,
            ':pincode' => $pincode,
            ':locality' => $locality,
            ':landmark' => $landmark,
            ':altphone' => $altphone,
            ':type' => $type,
            ':default' => $default,
            ':id' => $id,
            ':userid' => $userid
        ]);
    }

    // delete address
    public function deleteAddress($id, $userid)
    {
        // First check if the address being deleted is default
        $check = $this->conn->prepare("SELECT is_default FROM user_address WHERE id = :id AND userid = :userid AND client_id = :client_id");
        $check->execute([':id' => $id, ':userid' => $userid, ':client_id' => CLIENT_ID]);
        $is_default = $check->fetchColumn();

        $sql = $this->conn->prepare("DELETE FROM user_address WHERE id = :id AND userid = :userid AND client_id = :client_id");
        $sql->execute([':id' => $id, ':userid' => $userid, ':client_id' => CLIENT_ID]);

        // If it was default, make another address default if available
        if ($is_default) {
            $next = $this->conn->prepare("SELECT id FROM user_address WHERE userid = :userid AND client_id = :client_id LIMIT 1");
            $next->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
            $nextId = $next->fetchColumn();
            if ($nextId) {
                $makeDefault = $this->conn->prepare("UPDATE user_address SET is_default = 1 WHERE id = :id AND client_id = :client_id");
                $makeDefault->execute([':id' => $nextId, ':client_id' => CLIENT_ID]);
            }
        }
    }

    //fetch address based on user
    public function fetchAddress($userid)
    {
        $sql = $this->conn->prepare("SELECT * FROM user_address WHERE userid = :userid AND client_id = :client_id");
        $sql->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    //fetch select or default address in usercart.php based on user
    public function fetchSelectAddress($userid)
    {
        $sql = $this->conn->prepare("SELECT *
        FROM user_address
        WHERE userid = :userid
        AND (selected = 1 OR is_default = 1)
        AND client_id = :client_id
        ORDER BY selected DESC, is_default DESC
        LIMIT 1");
        $sql->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    //change select or default address in usercart.php based on user
    public function updateSelectAddress($id, $userid)
    {
        $sql = $this->conn->prepare("UPDATE user_address SET selected = 1 WHERE id = :id AND userid = :userid AND client_id = :client_id");
        $sql->execute([':id' => $id, ':userid' => $userid, ':client_id' => CLIENT_ID]);
        $sql = $this->conn->prepare("UPDATE user_address SET selected = 0 WHERE id != :id AND userid = :userid AND client_id = :client_id");
        $sql->execute([':id' => $id, ':userid' => $userid, ':client_id' => CLIENT_ID]);
    }

    //insert new order
    public function insertOrder($userid, $total, $payment)
    {
        $insert = $this->conn->prepare("INSERT INTO orders(userid, total, payment_method, status, client_id)
        VALUES(:userid, :total, :payment, :status, :client_id)");
        $insert->execute([':userid' => $userid, ':total' => $total, ':payment' => $payment, ':status' => 'Placed', ':client_id' => CLIENT_ID]);
        return $this->conn->lastInsertId();
    }

    //insert order items
    public function insertOrderItem($orderid, $pid, $qty, $price)
    {
        $insert = $this->conn->prepare("INSERT INTO order_items(orderid, productid, qty, price, client_id)
        VALUES(:orderid, :pid, :qty, :price, :client_id)");
        $insert->execute([':orderid' => $orderid, ':pid' => $pid, ':qty' => $qty, ':price' => $price, ':client_id' => CLIENT_ID]);
    }

    // update user profile
    public function updateProfile($userid, $username, $phone, $user_img)
    {
        $sql = $this->conn->prepare("UPDATE users SET username = :username, phone = :phone, user_img = :user_img WHERE id = :id AND client_id = :client_id");
        $sql->execute([
            ':username' => $username,
            ':phone' => $phone,
            ':user_img' => $user_img,
            ':id' => $userid,
            ':client_id' => CLIENT_ID
        ]);
    }

    // verify coupon code
    public function verifyCoupon($code, $userid = null)
    {
        $today = date("Y-m-d");
        $sql = $this->conn->prepare("SELECT * FROM coupons WHERE code = :code AND active = 1 AND (expiry_date IS NULL OR expiry_date >= :today) AND client_id = :client_id LIMIT 1");
        $sql->execute([':code' => $code, ':today' => $today, ':client_id' => CLIENT_ID]);
        $coupon = $sql->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon) {
            // Check allowed_users restriction
            if (!empty($coupon['allowed_users'])) {
                if ($userid === null && isset($_SESSION['user_id'])) {
                    $userid = $_SESSION['user_id'];
                }
                if ($userid === null) {
                    return false; // Restricted to specific users, but no user is logged in
                }
                $allowed = array_map('trim', explode(',', $coupon['allowed_users']));
                if (!in_array((string)$userid, $allowed)) {
                    return false; // User not allowed to use this coupon
                }
            }
            return $coupon;
        }
        return false;
    }

    // fetch top ordered products for a specific user
    public function fetchPersonalTopProducts($userid)
    {
        $sql = $this->conn->prepare("SELECT s.*, p.pname, p.img_url, SUM(oi.qty) as ordered_qty 
        FROM order_items oi
        JOIN orders o ON oi.orderid = o.id
        JOIN stock_in s ON oi.productid = s.proid
        JOIN products p ON oi.productid = p.id
        WHERE o.userid = :userid AND o.client_id = :client_id AND oi.client_id = :client_id AND s.client_id = :client_id AND p.client_id = :client_id
        GROUP BY oi.productid
        ORDER BY ordered_qty DESC
        LIMIT 10");
        $sql->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // fetch global top ordered products for all users
    public function fetchGlobalTopProducts()
    {
        $sql = $this->conn->prepare("SELECT s.*, p.pname, p.img_url, SUM(oi.qty) as ordered_qty 
        FROM order_items oi
        JOIN stock_in s ON oi.productid = s.proid
        JOIN products p ON oi.productid = p.id
        WHERE oi.client_id = :client_id AND s.client_id = :client_id AND p.client_id = :client_id
        GROUP BY oi.productid
        ORDER BY ordered_qty DESC
        LIMIT 10");
        $sql->execute([':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // fetch all products in stock
    public function fetchAllProducts()
    {
        $sql = $this->conn->prepare("SELECT s.*, p.pname, p.img_url 
        FROM stock_in s
        JOIN products p ON s.proid = p.id
        WHERE s.client_id = :client_id AND p.client_id = :client_id");
        $sql->execute([':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- WISHLIST METHODS ---
    public function addToWishlist($userid, $pid)
    {
        // Check if already exists to avoid duplicates
        if ($this->isInWishlist($userid, $pid)) {
            return true;
        }
        $sql = $this->conn->prepare("INSERT INTO wishlist(userid, productid, client_id) VALUES(:userid, :pid, :client_id)");
        return $sql->execute([':userid' => $userid, ':pid' => $pid, ':client_id' => CLIENT_ID]);
    }

    public function removeFromWishlist($userid, $pid)
    {
        $sql = $this->conn->prepare("DELETE FROM wishlist WHERE userid = :userid AND productid = :pid AND client_id = :client_id");
        return $sql->execute([':userid' => $userid, ':pid' => $pid, ':client_id' => CLIENT_ID]);
    }

    public function fetchWishlist($userid)
    {
        $sql = $this->conn->prepare("SELECT s.*, p.pname, p.img_url 
        FROM wishlist w
        JOIN stock_in s ON w.productid = s.proid
        JOIN products p ON w.productid = p.id
        WHERE w.userid = :userid AND w.client_id = :client_id AND s.client_id = :client_id AND p.client_id = :client_id");
        $sql->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isInWishlist($userid, $pid)
    {
        $sql = $this->conn->prepare("SELECT COUNT(*) FROM wishlist WHERE userid = :userid AND productid = :pid AND client_id = :client_id");
        $sql->execute([':userid' => $userid, ':pid' => $pid, ':client_id' => CLIENT_ID]);
        return $sql->fetchColumn() > 0;
    }

    public function wishlistCount($userid)
    {
        $sql = $this->conn->prepare("SELECT COUNT(*) FROM wishlist WHERE userid = :userid AND client_id = :client_id");
        $sql->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        return $sql->fetchColumn();
    }

    // --- REVIEWS METHODS ---
    public function addProductReview($userid, $pid, $rating, $comment)
    {
        $sql = $this->conn->prepare("INSERT INTO reviews(userid, productid, rating, comment, client_id) VALUES(:userid, :pid, :rating, :comment, :client_id)");
        return $sql->execute([
            ':userid' => $userid,
            ':pid' => $pid,
            ':rating' => $rating,
            ':comment' => $comment,
            ':client_id' => CLIENT_ID
        ]);
    }

    public function fetchProductReviews($pid)
    {
        $sql = $this->conn->prepare("SELECT r.*, u.username FROM reviews r
        JOIN users u ON r.userid = u.id
        WHERE r.productid = :pid AND r.client_id = :client_id AND u.client_id = :client_id
        ORDER BY r.created_at DESC");
        $sql->execute([':pid' => $pid, ':client_id' => CLIENT_ID]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductRatingSummary($pid)
    {
        $sql = $this->conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE productid = :pid AND client_id = :client_id");
        $sql->execute([':pid' => $pid, ':client_id' => CLIENT_ID]);
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        
        $avg = $res['avg_rating'] !== null ? round($res['avg_rating'], 1) : 0;
        return [
            'avg_rating' => $avg,
            'review_count' => (int)$res['review_count']
        ];
    }

    // --- NOTIFICATIONS METHODS ---
    public function addNotification($userid, $title, $message, $type)
    {
        $stmt = $this->conn->prepare("INSERT INTO notifications (userid, title, message, type, client_id) VALUES (:userid, :title, :message, :type, :client_id)");
        return $stmt->execute([
            ':userid' => $userid,
            ':title' => $title,
            ':message' => $message,
            ':type' => $type,
            ':client_id' => CLIENT_ID
        ]);
    }

    public function fetchNotifications($userid = null)
    {
        if ($userid === null) {
            // Fetch for admin
            $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE userid IS NULL AND client_id = :client_id ORDER BY id DESC LIMIT 20");
            $stmt->execute([':client_id' => CLIENT_ID]);
        } else {
            // Fetch for specific user
            $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE userid = :userid AND client_id = :client_id ORDER BY id DESC LIMIT 20");
            $stmt->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadNotificationsCount($userid = null)
    {
        if ($userid === null) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM notifications WHERE userid IS NULL AND is_read = 0 AND client_id = :client_id");
            $stmt->execute([':client_id' => CLIENT_ID]);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM notifications WHERE userid = :userid AND is_read = 0 AND client_id = :client_id");
            $stmt->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        }
        return (int)$stmt->fetchColumn();
    }

    public function markNotificationsRead($userid = null)
    {
        if ($userid === null) {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE userid IS NULL AND client_id = :client_id");
            $stmt->execute([':client_id' => CLIENT_ID]);
        } else {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE userid = :userid AND client_id = :client_id");
            $stmt->execute([':userid' => $userid, ':client_id' => CLIENT_ID]);
        }
        return true;
    }

    // --- STOCK-OUT METHODS ---
    public function addStockOut($proid, $qty, $reason, $sale_price = 0.00)
    {
        $stmt = $this->conn->prepare("INSERT INTO stock_out (proid, qty, reason, sale_price, client_id) VALUES (:proid, :qty, :reason, :sale_price, :client_id)");
        return $stmt->execute([
            ':proid' => $proid,
            ':qty' => $qty,
            ':reason' => $reason,
            ':sale_price' => $sale_price,
            ':client_id' => CLIENT_ID
        ]);
    }

    // --- DASHBOARD STATS METHODS ---
    public function totalCustomers()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE userlevel = 10 AND client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function totalOrders()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function totalProducts()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM products WHERE client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function totalDelivered()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'Delivered' AND client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function todayDelivered()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'Delivered' AND DATE(created_at) = CURRENT_DATE() AND client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function totalPendingDispatched()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('Placed', 'Pending Payment', 'Out for Delivery', 'Shipped') AND client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function totalRevenue()
    {
        $stmt = $this->conn->prepare("SELECT SUM(total) FROM orders WHERE status != 'Cancelled' AND client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (float)$stmt->fetchColumn();
    }

    // --- COUPON METHODS ---
    public function fetchCoupons($limit = null, $offset = null)
    {
        $sqlStr = "SELECT * FROM coupons WHERE client_id = :client_id ORDER BY id DESC";
        if ($limit !== null && $offset !== null) {
            $sqlStr .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        $stmt = $this->conn->prepare($sqlStr);
        $stmt->execute([':client_id' => CLIENT_ID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countCoupons()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM coupons WHERE client_id = :client_id");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return (int)$stmt->fetchColumn();
    }

    public function couponSubmit($code, $discount_type, $discount_value, $min_cart_amount, $expiry_date, $allowed_users, $active)
    {
        $edtm = date("Y-m-d H:i:s");
        $stmt = $this->conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_cart_amount, expiry_date, allowed_users, active, client_id, edtm) VALUES (:code, :discount_type, :discount_value, :min_cart_amount, :expiry_date, :allowed_users, :active, :client_id, '$edtm')");
        return $stmt->execute([
            ':code' => $code,
            ':discount_type' => $discount_type,
            ':discount_value' => $discount_value,
            ':min_cart_amount' => $min_cart_amount,
            ':expiry_date' => !empty($expiry_date) ? $expiry_date : null,
            ':allowed_users' => !empty($allowed_users) ? $allowed_users : null,
            ':active' => $active,
            ':client_id' => CLIENT_ID
        ]);
    }

    public function couponFetchById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM coupons WHERE id = :id AND client_id = :client_id");
        $stmt->execute([':id' => $id, ':client_id' => CLIENT_ID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function couponUpdate($id, $code, $discount_type, $discount_value, $min_cart_amount, $expiry_date, $allowed_users, $active)
    {
        $stmt = $this->conn->prepare("UPDATE coupons SET code = :code, discount_type = :discount_type, discount_value = :discount_value, min_cart_amount = :min_cart_amount, expiry_date = :expiry_date, allowed_users = :allowed_users, active = :active WHERE id = :id AND client_id = :client_id");
        return $stmt->execute([
            ':code' => $code,
            ':discount_type' => $discount_type,
            ':discount_value' => $discount_value,
            ':min_cart_amount' => $min_cart_amount,
            ':expiry_date' => !empty($expiry_date) ? $expiry_date : null,
            ':allowed_users' => !empty($allowed_users) ? $allowed_users : null,
            ':active' => $active,
            ':id' => $id,
            ':client_id' => CLIENT_ID
        ]);
    }

    public function couponDelete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM coupons WHERE id = :id AND client_id = :client_id");
        return $stmt->execute([':id' => $id, ':client_id' => CLIENT_ID]);
    }

    public function fetchCustomers()
    {
        $stmt = $this->conn->prepare("SELECT id, username, email FROM users WHERE userlevel = 10 AND client_id = :client_id ORDER BY username ASC");
        $stmt->execute([':client_id' => CLIENT_ID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
