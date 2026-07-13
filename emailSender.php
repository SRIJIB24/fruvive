<?php
class emailSender {
    public static function sendInvoice($to_email, $to_name, $orderid, $pdf_content) {
        $url = "https://api.brevo.com/v3/smtp/email";
        if (file_exists(__DIR__ . "/credentials.php")) {
            require_once __DIR__ . "/credentials.php";
        } else {
            require_once __DIR__ . "/credentials.template.php";
        }
        $apikey = BREVO_API_KEY;

        $html = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>
                <div style='background-color: #16a34a; padding: 24px; text-align: center; color: white;'>
                    <h1 style='margin: 0; font-size: 24px;'>Fruvive Order Confirmation</h1>
                </div>
                <div style='padding: 24px;'>
                    <h3 style='color: #1e293b; margin-top: 0;'>Dear " . htmlspecialchars($to_name) . ",</h3>
                    <p>Thank you for shopping with Fruvive! We are excited to let you know that your order <strong>#" . htmlspecialchars($orderid) . "</strong> has been successfully placed.</p>
                    <p>We have attached the PDF invoice for your order to this email. You can also view and download your invoice at any time from your account dashboard.</p>
                    
                    <div style='margin: 24px 0; padding: 16px; background-color: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;'>
                        <h4 style='margin: 0 0 8px 0; color: #475569;'>Need help?</h4>
                        <p style='margin: 0; font-size: 14px; color: #64748b;'>If you have any questions or concerns, reply directly to this email or contact support at <a href='mailto:srijibpal948@gmail.com' style='color: #16a34a;'>srijibpal948@gmail.com</a>.</p>
                    </div>
                    
                    <p style='margin-bottom: 0;'>Eat Healthy, Stay Vibrant!</p>
                    <p style='margin-top: 4px; font-weight: bold;'>Fruvive Team</p>
                </div>
            </div>
        </body>
        </html>";

        $payload = [
            "sender" => ["name" => "Fruvive", "email" => "srijibpal948@gmail.com"],
            "to" => [["email" => $to_email, "name" => $to_name]],
            "subject" => "Order Confirmation - Fruvive (Order #" . $orderid . ")",
            "htmlContent" => $html,
            "attachments" => [[
                "name" => "invoice_" . $orderid . ".pdf",
                "content" => base64_encode($pdf_content)
            ]]
        ];

        $headers = [
            "accept: application/json",
            "api-key: " . $apikey,
            "content-type: application/json"
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("Brevo API Call Failed: " . $err);
            return false;
        }
        return json_decode($response, true);
    }
}
