<?php
/**
 * Payment Callback / Verification Handler
 */

require_once __DIR__ . '/includes/LocalGateway.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/sms.php';

require_once __DIR__ . '/includes/db.php';

session_start();

// Gateway redirects back with 'transactionId'
$txnid = isset($_POST['transactionId']) ? $_POST['transactionId'] : (isset($_GET['transactionId']) ? $_GET['transactionId'] : null);

if (!$txnid) {
    header('Location: public/cancel.php');
    exit;
}

// 1. Verify payment status
$verification = LocalGateway::verifyPayment($txnid);

if (isset($verification['status']) && $verification['status'] === 'COMPLETED') {
    // 2. Payment is verified!
    $customer_name = $verification['cus_name'] ?? 'Customer';
    $customer_email = $verification['cus_email'] ?? null;
    $amount = $verification['amount'] ?? TICKET_PRICE;
    
    // Extract metadata
    $metadata = isset($verification['metadata']) ? $verification['metadata'] : (isset($verification['meta_data']) ? $verification['meta_data'] : []);
    $tier = $metadata['ticket_tier'] ?? 'General';
    $quantity = $metadata['quantity'] ?? 1;
    $customer_phone = $metadata['phone'] ?? null;
    $promo_used = $metadata['promo_used'] ?? 'NONE';
    $slot_id = $metadata['slot_id'] ?? 'slot_default';

    // 3. Trigger Post-Payment Actions
    
    // a) Save to Database
    Database::saveBooking([
        'name' => $customer_name,
        'email' => $customer_email,
        'phone' => $customer_phone,
        'txnid' => $txnid,
        'tier' => $tier,
        'quantity' => $quantity,
        'amount' => $amount,
        'promo_used' => $promo_used,
        'slot_id' => $slot_id,
        'status' => 'confirmed'
    ]);
    
    // b) Send Confirmation Email
    if ($customer_email) {
        TicketMailer::sendConfirmation($customer_email, $customer_name, $amount, $txnid, $tier, $slot_id);
    }
    
    // c) Trigger SMS notification (Dummy)
    if ($customer_phone) {
        $sms_msg = "Hello $customer_name, your ticket for " . EVENT_NAME . " ($tier) is confirmed! TXN: $txnid. BDT $amount.";
        SMSGateway::sendSMS($customer_phone, $sms_msg);
    }

    // 4. Redirect to Success Page
    header("Location: public/success.php?txnid=$txnid&amount=$amount&qty=$quantity&tier=" . urlencode($tier));
    exit;
} else {
    // Payment verification failed or pending
    header('Location: public/cancel.php');
    exit;
}
