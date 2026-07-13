<?php
require_once "fpdf.php";
require_once "userFunc.php";

class invoiceGenerator {
    public function generate($orderid) {
        $db = new data();
        
        // Fetch order details
        $stmt = $db->conn->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.userid = u.id WHERE o.id = :orderid");
        $stmt->execute([':orderid' => $orderid]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return false;
        }
        
        $userid = $order['userid'];
        
        // Fetch default/active address
        $address = $db->fetchSelectAddress($userid);
        
        // Fetch order items
        $stmt_items = $db->conn->prepare("SELECT oi.*, p.pname, p.quant FROM order_items oi JOIN products p ON oi.productid = p.id WHERE oi.orderid = :orderid");
        $stmt_items->execute([':orderid' => $orderid]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Branded Header
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->SetTextColor(22, 163, 74); // Fruvive green #16a34a (RGB: 22, 163, 74)
        $pdf->Cell(0, 10, 'Fruvive', 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100, 116, 139); // Gray color
        $pdf->Cell(0, 5, 'Vibrant Health, Delivered Fresh.', 0, 1, 'L');
        $pdf->Ln(5);
        
        // Invoice Title
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(15, 23, 42); // Dark slate
        $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'R');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, 'Invoice No: INV-FRV-' . $order['id'], 0, 1, 'R');
        $pdf->Cell(0, 5, 'Date: ' . date('d-M-Y', strtotime($order['created_at'])), 0, 1, 'R');
        $pdf->Ln(10);
        
        // Delivery & Billing details side-by-side
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 7, 'Billed To:', 0, 0, 'L');
        $pdf->Cell(95, 7, 'Shipped To:', 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(51, 65, 85);
        
        // Customer name/email
        $pdf->Cell(95, 5, iconv('UTF-8', 'windows-1252', $order['username']), 0, 0, 'L');
        $pdf->Cell(95, 5, iconv('UTF-8', 'windows-1252', $address ? $address['name'] : $order['username']), 0, 1, 'L');
        
        $pdf->Cell(95, 5, iconv('UTF-8', 'windows-1252', $order['email']), 0, 0, 'L');
        $pdf->Cell(95, 5, iconv('UTF-8', 'windows-1252', $address ? $address['address'] : 'No Address Provided'), 0, 1, 'L');
        
        $pdf->Cell(95, 5, '', 0, 0, 'L');
        $pdf->Cell(95, 5, iconv('UTF-8', 'windows-1252', $address ? $address['city'] . ', ' . $address['state'] . ' - ' . $address['pincode'] : ''), 0, 1, 'L');
        
        $pdf->Cell(95, 5, '', 0, 0, 'L');
        $pdf->Cell(95, 5, iconv('UTF-8', 'windows-1252', $address ? 'Phone: ' . $address['phone'] : ''), 0, 1, 'L');
        $pdf->Ln(10);
        
        // Payment Method details
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 7, 'Payment Method', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Order Status', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 7, iconv('UTF-8', 'windows-1252', $order['payment_method']), 1, 0, 'C');
        $pdf->Cell(50, 7, iconv('UTF-8', 'windows-1252', $order['status']), 1, 1, 'C');
        $pdf->Ln(10);
        
        // Items Table Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->Cell(80, 8, 'Product Name', 1, 0, 'L', true);
        $pdf->Cell(30, 8, 'Unit Price', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Quantity', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Subtotal', 1, 1, 'R', true);
        
        // Table Body
        $pdf->SetFont('Arial', '', 10);
        $subtotal_sum = 0;
        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['qty'];
            $subtotal_sum += $subtotal;
            
            $pdf->Cell(80, 8, iconv('UTF-8', 'windows-1252', $item['pname'] . ' (' . $item['quant'] . ')'), 1, 0, 'L');
            $pdf->Cell(30, 8, 'Rs. ' . number_format($item['price'], 2), 1, 0, 'C');
            $pdf->Cell(30, 8, htmlspecialchars($item['qty']), 1, 0, 'C');
            $pdf->Cell(50, 8, 'Rs. ' . number_format($subtotal, 2), 1, 1, 'R');
        }
        
        // Totals block
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(140, 8, 'Subtotal (MRP)', 1, 0, 'R');
        $pdf->Cell(50, 8, 'Rs. ' . number_format($subtotal_sum, 2), 1, 1, 'R');
        
        $discount = $subtotal_sum - $order['total'];
        if ($discount > 0) {
            $pdf->Cell(140, 8, 'Discount', 1, 0, 'R');
            $pdf->Cell(50, 8, '- Rs. ' . number_format($discount, 2), 1, 1, 'R');
        }
        
        $pdf->Cell(140, 8, 'Delivery Charges', 1, 0, 'R');
        $pdf->Cell(50, 8, 'FREE', 1, 1, 'R');
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->Cell(140, 9, 'Total Amount Paid', 1, 0, 'R', true);
        $pdf->Cell(50, 9, 'Rs. ' . number_format($order['total'], 2), 1, 1, 'R', true);
        
        // Footer Thank You
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 10, 'Thank you for choosing Fruvive! Stay Healthy, Eat Fresh.', 0, 1, 'C');
        
        return $pdf->Output('S'); // Return as binary string
    }
}
