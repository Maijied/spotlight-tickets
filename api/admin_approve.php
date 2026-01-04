<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die("Access Denied");
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['txn_id'])) {
    $txn = $_POST['txn_id'];
    $pdo = Database::connect();
    
    // Fetch booking details
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE txnid = ?");
    $stmt->execute([$txn]);
    $booking = $stmt->fetch();

    if ($booking) {
        // Update Status
        $update = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE txnid = ?");
        $update->execute([$txn]);

        // Send Email (We can reuse TicketMailer)
        // Note: TicketMailer::sendConfirmation expects specific params
        // public static function sendConfirmation($to, $name, $amount, $txnId, $tier, $slot)
        
        TicketMailer::sendConfirmation(
            $booking['email'],
            $booking['name'],
            $booking['amount'],
            $booking['txnid'],
            $booking['tier'],
            $booking['slot_id']
        );

        header('Location: ../public/admin.php?success=approved');
        exit;
    }
}
header('Location: ../public/admin.php?error=failed');
