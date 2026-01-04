<?php
/**
 * FlexPayBD Ticket System Configuration
 */

// --- Internal Test Gateway Configuration ---
// No API keys required for local simulation.

// --- Environment Loader ---
require_once __DIR__ . '/../includes/DotEnvLoader.php';
DotEnvLoader::load(__DIR__ . '/../.env');

// --- Manual Payment Configuration ---
define('BKASH_NUMBER', getenv('BKASH_NUMBER') ?: '01968833917');
define('PAYMENT_MODE', getenv('PAYMENT_MODE') ?: 'MANUAL');

// Production Security: Disable error reporting (unless debug mode is on)
error_reporting(0);
ini_set('display_errors', 0);


// --- MySQL Database Configuration ---
define('DB_HOST', getenv('DB_HOST') ?: 'sql303.infinityfree.com');
define('DB_NAME', getenv('DB_NAME') ?: 'if0_40819537_shiddarth');
define('DB_USER', getenv('DB_USER') ?: 'if0_40819537');
define('DB_PASS', getenv('DB_PASS') ?: 'fWNDOUzsifw8yGh');

// --- Dynamic Event Configuration ---
require_once __DIR__ . '/../includes/db.php';
$DYNAMIC_SETTINGS = Database::getSettings();

define('EVENT_NAME', $DYNAMIC_SETTINGS['event_name'] ?? 'Siddhartha Live 2026');
define('UI_TAGLINE', $DYNAMIC_SETTINGS['ui_tagline'] ?? 'এক কালজয়ী নাট্য গাথা');

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

// Promo Codes (Dynamic), Early Bird Rules (Dynamic), Bundle Rules (Dynamic)
$PROMO_CODES = $DYNAMIC_SETTINGS['promo_codes'] ?? [];
$EARLY_BIRD_RULES = $DYNAMIC_SETTINGS['early_bird_rules'] ?? [];
$BUNDLE_RULES = $DYNAMIC_SETTINGS['bundle_rules'] ?? [];

// --- Application Settings ---
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define('SUCCESS_URL', BASE_URL . '/callback.php');
define('CANCEL_URL', BASE_URL . '/callback.php');

// --- Notification Settings ---
define('ADMIN_EMAIL', 'admin@example.com');
define('FROM_EMAIL', 'noreply@example.com');
