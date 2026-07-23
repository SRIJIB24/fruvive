<?php
require "userFunc.php";
$object = new data();
$object->sessionCheck();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

if (isset($_POST['id']) && isset($_POST['qty'])) {
    $id = (int)$_POST['id'];
    $qty = (int)$_POST['qty'];
    
    // 1. Fetch item to get product ID and price
    $stmt = $object->conn->prepare("SELECT productid, price FROM cart WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cartItem) {
        $pid = $cartItem['productid'];
        $price = (float)$cartItem['price'];
        
        // 2. Check stock
        $products = $object->stockinfetchpid($pid);
        if ($products && $qty > $products['total_quant']) {
            echo json_encode(["status" => "error", "message" => "Only " . $products['total_quant'] . " items left in stock."]);
            exit;
        }
        
        // 3. Update database quantity
        $object->updatecartQuant($id, $qty);
        
        // 4. Calculate new totals
        $cart = $object->fetchCart();
        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += $item['price'] * $item['qty'];
        }
        
        // 5. Recalculate coupon discount
        $coupon_discount = 0;
        $coupon_code = '';
        if (isset($_SESSION['applied_coupon'])) {
            $coupon = $object->verifyCoupon($_SESSION['applied_coupon']);
            if ($coupon) {
                $coupon_code = $coupon['code'];
                if ($cartTotal >= $coupon['min_cart_amount']) {
                    if ($coupon['discount_type'] === 'percentage') {
                        $coupon_discount = round(($cartTotal * $coupon['discount_value']) / 100);
                    } else {
                        $coupon_discount = min($coupon['discount_value'], $cartTotal);
                    }
                    $_SESSION['discount_amount'] = $coupon_discount;
                } else {
                    unset($_SESSION['applied_coupon']);
                    unset($_SESSION['coupon_type']);
                    unset($_SESSION['coupon_value']);
                    unset($_SESSION['discount_amount']);
                }
            } else {
                unset($_SESSION['applied_coupon']);
                unset($_SESSION['coupon_type']);
                unset($_SESSION['coupon_value']);
                unset($_SESSION['discount_amount']);
            }
        }
        
        $final_total = max(0, $cartTotal - $coupon_discount);
        
        echo json_encode([
            "status" => "success",
            "qty" => $qty,
            "item_subtotal" => ($price * $qty),
            "cart_subtotal" => $cartTotal,
            "coupon_discount" => $coupon_discount,
            "coupon_code" => $coupon_code,
            "final_total" => $final_total
        ]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Item not found in cart."]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}
