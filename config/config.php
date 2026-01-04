<?php
/**
 * FlexPayBD Ticket System Configuration
 */

// --- FlexPayBD API Credentials ---
define('FLEXPAY_API_KEY', 'YOUR_API_KEY_HERE');
define('FLEXPAY_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('FLEXPAY_BRAND_KEY', 'YOUR_BRAND_KEY_HERE');

// Production Security: Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Set to true for dummy/sandbox testing without real API keys
define('DUMMY_MODE', false);

// --- MySQL Database Configuration ---
define('DB_HOST', 'sql303.infinityfree.com');
define('DB_NAME', 'if0_40819537_shiddarth');
define('DB_USER', 'if0_40819537');
define('DB_PASS', 'fWNDOUzsifw8yGh');

// --- Dynamic Event Configuration ---
require_once __DIR__ . '/../includes/db.php';
$DYNAMIC_SETTINGS = Database::getSettings();

define('EVENT_NAME', $DYNAMIC_SETTINGS['event_name'] ?? 'Siddhartha Live 2026');

// Slots Management
$SLOTS = $DYNAMIC_SETTINGS['slots'] ?? [];
$FIRST_SLOT = $SLOTS[0] ?? [
    'time' => 'N/A', 
    'location' => 'N/A', 
    'capacities' => ['regular' => 0, 'vip' => 0, 'front' => 0]
];

define('EVENT_DATE_TIME', $FIRST_SLOT['time']);
define('EVENT_LOCATION', $FIRST_SLOT['location']);
define('TOTAL_CAPACITY', array_sum($FIRST_SLOT['capacities'])); 
define('CURRENCY', 'BDT');

// Individual Tier Capacities (Legacy support/default)
$TIER_CAPACITIES = $FIRST_SLOT['capacities'];

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
