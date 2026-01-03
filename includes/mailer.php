<?php
/**
 * Ticket Confirmation Mailer
 */

require_once __DIR__ . '/../config/config.php';

class TicketMailer {
    /**
     * Send confirmation email to customer
     */
    public static function sendConfirmation($customer_email, $customer_name, $amount, $txnid, $tier = 'General', $slot_id = 'slot_default') {
        require_once __DIR__ . '/db.php';
        $settings = Database::getSettings();
        $slot_info = ['time' => 'N/A', 'location' => 'N/A'];
        if (isset($settings['slots'])) {
            foreach ($settings['slots'] as $s) {
                if ($s['id'] === $slot_id) {
                    $slot_info = $s;
                    break;
                }
            }
        }

        $subject = "Ticket Confirmation - " . EVENT_NAME;
        
        $message = "
        <html>
        <head>
            <title>Ticket Confirmation</title>
        </head>
        <body>
            <h2>Thank you for your purchase, $customer_name!</h2>
            <p>Your ticket for <strong>" . EVENT_NAME . "</strong> has been confirmed.</p>
            <hr>
            <p><strong>Transaction Details:</strong></p>
            <ul>
                <li><strong>Show Time:</strong> " . $slot_info['time'] . "</li>
                <li><strong>Location:</strong> " . $slot_info['location'] . "</li>
                <li><strong>Ticket Type:</strong> $tier</li>
                <li><strong>Transaction ID:</strong> $txnid</li>
                <li><strong>Amount Paid:</strong> " . CURRENCY . " " . number_format($amount, 2) . "</li>
            </ul>
            
            <div style='text-align: center; margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <p style='margin-bottom: 15px; font-weight: bold;'>YOUR DIGITAL TICKET (SCAN AT ENTRANCE)</p>
                <img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($txnid) . "' alt='QR Code Ticket'>
                <p style='margin-top: 10px; font-family: monospace; color: #666;'>$txnid</p>
            </div>

            <p>Please keep this email for your records. You will need to show the QR code above at the event entrance.</p>
            
            <!-- TODO: Generate and attach QR Code / PDF Ticket -->
            <!-- TODO: Integrate SMS gateway for mobile notification -->
            
            <p>Best regards,<br>The " . EVENT_NAME . " Team</p>
        </body>
        </html>
        ";

        // Headers for HTML mail
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . EVENT_NAME . " <" . FROM_EMAIL . ">" . "\r\n";

        // Send email
        return mail($customer_email, $subject, $message, $headers);
    }
}
