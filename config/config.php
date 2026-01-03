<?php
/**
 * FlexPayBD Ticket System Configuration
 */

// --- FlexPayBD API Credentials ---
define('FLEXPAY_API_KEY', 'YOUR_API_KEY_HERE');
define('FLEXPAY_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('FLEXPAY_BRAND_KEY', 'YOUR_BRAND_KEY_HERE');

// Set to true for dummy/sandbox testing without real API keys
define('DUMMY_MODE', true);

// --- Drama Show Configuration ---
define('EVENT_NAME', 'সিদ্ধার্থ');
define('CURRENCY', 'BDT');

// Ticket Tiers
$TICKET_TIERS = [
    'regular' => ['name' => 'Regular Seat', 'price' => 500],
    'vip'     => ['name' => 'VIP Lounge', 'price' => 1200],
    'front'   => ['name' => 'Front Row Premium', 'price' => 2500]
];

// Promo Codes (direct code => percentage discount)
$PROMO_CODES = [
    'OFFER10' => 10,
    'OFFER20' => 20
];

// --- Advanced Discount Rules ---

// Early Bird Rules (Date => Percentage)
// Rules are checked in order: if current date <= key, that discount applies.
$EARLY_BIRD_RULES = [
    '2026-01-10' => 20, // 20% discount until Jan 10
    '2026-01-15' => 15, // 15% discount until Jan 15
    '2026-01-20' => 10  // 10% discount until Jan 20
];

// Bundle (Quantity) Rules (Min Quantity => Percentage)
$BUNDLE_RULES = [
    10 => 20, // 20% discount for 10 or more tickets
    5  => 10  // 10% discount for 5 or more tickets
];

// --- Application Settings ---
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define('SUCCESS_URL', BASE_URL . '/callback.php');
define('CANCEL_URL', BASE_URL . '/callback.php');

// --- Notification Settings ---
define('ADMIN_EMAIL', 'admin@example.com');
define('FROM_EMAIL', 'noreply@example.com');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
