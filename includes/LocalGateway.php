<?php
/**
 * Local Test Gateway
 * Simulates a payment provider for development and testing.
 */

require_once __DIR__ . '/../config/config.php';

class LocalGateway {

    /**
     * Create a mock payment request
     */
    public static function createPayment($data) {
        // In a real gateway, this would talk to an API.
        // Here, we just generate a transaction ID and redirect to our own success page.
        
        // Simulate a "Processing" state by storing intent if needed, 
        // but for this simple version, we just return the success URL.
        
        // Store metadata in session to simulate "callback" data persistence
        // (Since we are skipping the external provider roundtrip)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['last_booking_meta'] = $data['meta_data'];
        $_SESSION['last_booking_customer'] = [
            'name' => $data['full_name'],
            'email' => $data['email'],
            'amount' => $data['amount']
        ];

        return [
            'status' => true,
            'payment_url' => SUCCESS_URL . '?transactionId=LOC_TXN_' . time() . '&gateway=local'
        ];
    }

    /**
     * Verify a mock payment
     */
    public static function verifyPayment($txnid) {
        // Since we generated the ID ourselves, we treat any ID starting with LOC_TXN as valid.
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            'status' => 'COMPLETED',
            'cus_name' => $_SESSION['last_booking_customer']['name'] ?? 'Test Customer',
            'cus_email' => $_SESSION['last_booking_customer']['email'] ?? 'test@example.com',
            'amount' => $_SESSION['last_booking_customer']['amount'] ?? 0,
            'transaction_id' => $txnid,
            'metadata' => $_SESSION['last_booking_meta'] ?? []
        ];
    }
}
