<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$qr = $_POST['qrcode'] ?? '';

if (!$qr) {
    echo json_encode(['status' => 'error', 'message' => 'No QR data']);
    exit;
}

$pdo = Database::connect();
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE txnid = ?");
$stmt->execute([$qr]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo json_encode(['status' => 'error', 'message' => 'Ticket not found in system']);
    exit;
}

// 1. Check Status
if ($booking['status'] === 'pending') {
    echo json_encode(['status' => 'pending', 'booking' => $booking]);
    exit;
}

if ($booking['status'] !== 'confirmed') {
    echo json_encode(['status' => 'error', 'message' => 'Ticket Status: ' . $booking['status']]);
    exit;
}

// 2. Check if already used (Implementing 'is_used' column logic on the fly or assuming single use)
// For now, let's assume we want to track check-ins. We need a column for this.
// If column doesn't exist, we might fail. Let's add it or use a separate table?
// To keep it simple, we'll check `meta_data` if we stored it there, OR just add a checkin_time column.
// Ideally, we add a column. I will try to add it gracefully or check if it exists.
// Since I can't easily alter table now without risk, I'll allow multiple scans but warn "Already Scanned".
// Actually, let's just Log it in a new table `checkins` if possible, OR just return valid.
// User requested: "show reason for not let him in". "Used" is a valid reason.
// Let's UPDATE the booking row to add `checked_in_at` timestamp.

try {
    // Attempt to check if checked_in_at is null
    // If column doesn't exist, this might error.
    // I previously created `bookings` table in `database.sql`. Let's check it.
    // It has: `id, name, email, phone, ticket_type, quantity, total_amount, payment_status, transaction_id, created_at`.
    // Wait, my `Database::saveBooking` uses `bookings` table.
    
    // I will ADD the column dynamically if missing, or just use a logic file.
    // Let's use `status` = 'used' ? No, we want to keep record of payment.
    // Let's add `checkin_status` column.
    
    // Quick fix: Use a specific `checkins` table to track usage.
    /*
    CREATE TABLE IF NOT EXISTS checkins (
        booking_id INT,
        scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (booking_id)
    );
    */
    $pdo->exec("CREATE TABLE IF NOT EXISTS checkins (booking_id INT PRIMARY KEY, scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    
    // Check if already checked in
    $chk = $pdo->prepare("SELECT scanned_at FROM checkins WHERE booking_id = ?");
    $chk->execute([$booking['id']]);
    $existing = $chk->fetch();
    
    if ($existing) {
        echo json_encode([
            'status' => 'used', 
            'booking' => $booking, 
            'scanned_at' => $existing['scanned_at']
        ]);
        exit;
    }
    
    // Mark as used
    $ins = $pdo->prepare("INSERT INTO checkins (booking_id) VALUES (?)");
    $ins->execute([$booking['id']]);
    
    echo json_encode(['status' => 'valid', 'booking' => $booking]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
