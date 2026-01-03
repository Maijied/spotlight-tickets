<?php
/**
 * Handle Ticket Purchase Request (Advanced Discounts Edition)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/flexpay.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/index.php');
    exit;
}

// 1. Input Sanitization
$full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_SPECIAL_CHARS);
$ticket_type = filter_input(INPUT_POST, 'ticket_type', FILTER_SANITIZE_SPECIAL_CHARS);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$promo_code = filter_input(INPUT_POST, 'promo_code', FILTER_SANITIZE_SPECIAL_CHARS);
$slot_id = filter_input(INPUT_POST, 'slot_id', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$full_name || !$email || !$phone_number || !isset($TICKET_TIERS[$ticket_type]) || $quantity < 1) {
    die("Error: Invalid booking information.");
}

// 2. Server-side Price Calculation (Security)
$base_price_per_ticket = $TICKET_TIERS[$ticket_type]['price'];
$total_base = $base_price_per_ticket * $quantity;
$final_amount = (float)$total_base;

// A) Early Bird Discount
$today = date('Y-m-d');
$date_discount = 0;
ksort($EARLY_BIRD_RULES);
foreach ($EARLY_BIRD_RULES as $date_limit => $percent) {
    if ($today <= $date_limit) {
        $date_discount = $percent;
        break;
    }
}
if ($date_discount > 0) {
    $final_amount -= ($total_base * ($date_discount / 100));
}

// B) Bundle Discount
$bundle_discount = 0;
krsort($BUNDLE_RULES);
foreach ($BUNDLE_RULES as $min_qty => $percent) {
    if ($quantity >= $min_qty) {
        $bundle_discount = $percent;
        break;
    }
}
if ($bundle_discount > 0) {
    $final_amount -= ($total_base * ($bundle_discount / 100));
}

// C) Promo Code Discount
$promo_discount = 0;
$clean_promo = strtoupper(trim($promo_code));
if (!empty($clean_promo) && isset($PROMO_CODES[$clean_promo])) {
    $promo_discount = $PROMO_CODES[$clean_promo];
    $final_amount -= ($total_base * ($promo_discount / 100));
}

// 3. Prepare Payment Data
$payment_data = [
    'amount' => round($final_amount),
    'full_name' => $full_name,
    'email' => $email,
    'phone_number' => $phone_number,
    'meta_data' => [
        'event_name' => EVENT_NAME,
        'ticket_tier' => $TICKET_TIERS[$ticket_type]['name'],
        'quantity' => $quantity,
        'phone' => $phone_number,
        'promo_used' => !empty($clean_promo) ? $clean_promo : 'NONE',
        'slot_id' => $slot_id,
        'discounts' => [
            'early_bird' => $date_discount . '%',
            'bundle' => $bundle_discount . '%',
            'promo' => $promo_discount . '%'
        ]
    ]
];

if (defined('DUMMY_MODE') && DUMMY_MODE === true) {
    $_SESSION['last_booking_meta'] = $payment_data['meta_data'];
    $_SESSION['last_booking_customer'] = [
        'name' => $full_name,
        'email' => $email,
        'phone' => $phone_number,
        'amount' => round($final_amount)
    ];
}

// 4. Call FlexPayBD API
$response = FlexPay::createPayment($payment_data);

// 5. Handle Response
if (isset($response['result']) && $response['result'] === 'success' && isset($response['payment_url'])) {
    header('Location: ' . $response['payment_url']);
    exit;
} else {
    $error_msg = isset($response['message']) ? $response['message'] : 'Payment initiation failed.';
    die("Error: " . $error_msg);
}
