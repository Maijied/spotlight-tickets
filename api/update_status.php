<?php
/**
 * Update Booking Status via AJAX
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

header('Content-Type: application/json');

// Auth Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $txnid = $_POST['txnid'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($txnid && in_array($status, ['pending', 'confirmed', 'checked-in'])) {
        if (Database::updateBookingStatus($txnid, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
exit;
