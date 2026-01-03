<?php
/**
 * Export Bookings as CSV
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

// Auth Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../public/admin.php');
    exit;
}

Database::connect();
$bookings = Database::getBookings();

// Sort by date descending
usort($bookings, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

$filename = "bookings_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '";');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['Date', 'Name', 'Email', 'Phone', 'Ticket Tier', 'Quantity', 'Amount (BDT)', 'TXN ID', 'Promo Code', 'Status']);

// Data
foreach ($bookings as $b) {
    fputcsv($output, [
        $b['created_at'],
        $b['name'],
        $b['email'],
        $b['phone'],
        $b['tier'],
        $b['quantity'],
        $b['amount'],
        $b['txnid'],
        $b['promo_used'],
        $b['status']
    ]);
}

fclose($output);
exit;
