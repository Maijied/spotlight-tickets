<?php
/**
 * Handle Ticket Purchase Request (Advanced Discounts Edition)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/LocalGateway.php';

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
// 1.5. Capacity Check (Real-time)
require_once __DIR__ . '/../includes/db.php';
$bookings = Database::getBookings();
$sold_count = 0;
foreach($bookings as $b) {
    // Check if booking belongs to this slot and tier
    // Note: DB stores tier Name (e.g. 'Regular Seat'), input is Key (e.g. 'regular')
    $b_slot = $b['slot_id'] ?? 'slot_default';
    $b_tier = $b['tier'] ?? '';
    
    if($b_slot === $slot_id && $b['status'] === 'confirmed') {
        // Flexible matching since DB stores Name but we have Key
        if (stripos($b_tier, $ticket_type) !== false) {
             $sold_count += $b['quantity'];
        }
    }
}

// Find Capacity for requested slot/tier
$tier_capacity = 0;
foreach($SLOTS as $s) {
    if($s['id'] === $slot_id) {
        $tier_capacity = (int)($s['capacities'][$ticket_type] ?? 0);
        break;
    }
}

if (($sold_count + $quantity) > $tier_capacity) {
    $remaining = max(0, $tier_capacity - $sold_count);
    die("Error: Not enough seats available. Only $remaining seats left for this category.");
}

// 2. Server-side Price Calculation (Security)
$base_price_per_ticket = $TICKET_TIERS[$ticket_type]['price'];

// Override price from slot config if exists
if ($slot_id) {
    foreach ($SLOTS as $s) {
        if ($s['id'] === $slot_id && isset($s['prices'][$ticket_type])) {
            $base_price_per_ticket = (float)$s['prices'][$ticket_type];
            break;
        }
    }
}

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

// 4. Call Local Gateway
$response = LocalGateway::createPayment($payment_data);

// 5. Handle Response
if (isset($response['status']) && $response['status'] === true && isset($response['payment_url'])) {
    header('Location: ' . $response['payment_url']);
    exit;
} else {
    $error_msg = isset($response['message']) ? $response['message'] : 'Payment initiation failed.';
    die("Error: " . $error_msg);
}
