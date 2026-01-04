<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die("Access Denied");
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['txn_id'])) {
    $txn = $_POST['txn_id'];
    
    // 1. Find Booking (Supports DB or JSON)
    $all = Database::getBookings();
    $booking = null;
    foreach($all as $b) {
        if($b['txnid'] === $txn) {
            $booking = $b;
            break;
        }
    }

    if ($booking) {
        // 2. Update Status (Supports DB or JSON)
        Database::updateBookingStatus($txn, 'confirmed');

        // 3. Send Email
        TicketMailer::sendConfirmation(
            $booking['email'],
            $booking['name'],
            $booking['amount'],
            $booking['txnid'],
            $booking['tier'] ?? 'General',
            $booking['slot_id'] ?? 'slot_default'
        );

        header('Location: ../public/admin.php?success=approved');
        exit;
    }
}
header('Location: ../public/admin.php?error=failed');
