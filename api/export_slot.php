<?php
/**
 * Export specific slot data to CSV
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Access Denied");
}

$slotId = $_GET['slot_id'] ?? '';
if(!$slotId) die("Slot ID required");

// Fetch data
$allBookings = Database::getBookings();
$slotBookings = array_filter($allBookings, function($b) use ($slotId) {
    // If legacy booking has no slot_id, assume first slot if needed, or skip
    // For now we match explicitly
    return ($b['slot_id'] ?? '') === $slotId;
});

// Get slot details for filename
$settings = Database::getSettings();
$slots = $settings['slots'] ?? [];
$slotName = $slotId;
foreach($slots as $s) {
    if(($s['id']??'') === $slotId) {
        $slotName = preg_replace('/[^a-z0-9]/i', '_', $s['time']);
        break;
    }
}

// Headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_'.$slotName.'_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');

// BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Header
fputcsv($output, ['Txn ID', 'Date', 'Name', 'Phone', 'Email', 'Tier', 'Qty', 'Amount', 'Status', 'Promo Used', 'Scanned At']);

foreach ($slotBookings as $b) {
    fputcsv($output, [
        $b['txnid'],
        $b['created_at'],
        $b['full_name'],
        $b['phone'],
        $b['email'],
        $b['tier'],
        $b['quantity'],
        $b['amount'],
        $b['status'],
        $b['promo_used'] ?? '-',
        $b['scanned_at'] ?? '-' // this might need a join or checkins lookup if not in table
    ]);
}

fclose($output);
exit;
