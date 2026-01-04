<?php
/**
 * FlexPayBD API Helper
 */

require_once __DIR__ . '/../config/config.php';

class FlexPay {
    private static $create_url = 'https://pay.flexpaybd.com/api/payment/create';
    private static $verify_url = 'https://pay.flexpaybd.com/api/payment/verify';

    /**
     * Create a payment request
     */
    public static function createPayment($data) {
        if (defined('DUMMY_MODE') && DUMMY_MODE === true) {
            return [
                'status' => true,
                'payment_url' => SUCCESS_URL . '?transactionId=TEST_GATEWAY_' . time() . '&dummy=1'
            ];
        }

        $headers = [
            'Content-Type: application/json',
            'API-KEY: ' . FLEXPAY_API_KEY,
            'SECRET-KEY: ' . FLEXPAY_SECRET_KEY,
            'BRAND-KEY: ' . FLEXPAY_BRAND_KEY
        ];

        // Format amount: skip trailing zeros for natural numbers per FlexPayBD docs
        $formatted_amount = (strpos((string)$data['amount'], '.') !== false) ? rtrim(rtrim((string)$data['amount'], '0'), '.') : (string)$data['amount'];

        $payload = json_encode([
            'amount' => (string)$formatted_amount,
            'cus_name' => $data['full_name'],
            'cus_email' => $data['email'],
            'success_url' => SUCCESS_URL,
            'cancel_url' => CANCEL_URL,
            'metadata' => $data['meta_data'] ?? []
        ]);

        return self::makeRequest(self::$create_url, $payload, $headers);
    }

    /**
     * Verify a payment using transaction ID
     */
    public static function verifyPayment($txnid) {
        if (defined('DUMMY_MODE') && DUMMY_MODE === true) {
            // In dummy mode, we simulate a successful response
            return [
                'status' => 'COMPLETED',
                'cus_name' => 'Dummy Customer',
                'cus_email' => 'dummy@example.com',
                'amount' => '500',
                'transaction_id' => $txnid,
                'metadata' => $_SESSION['last_booking_meta'] ?? []
            ];
        }

        $headers = [
            'Content-Type: application/json',
            'API-KEY: ' . FLEXPAY_API_KEY,
            'SECRET-KEY: ' . FLEXPAY_SECRET_KEY,
            'BRAND-KEY: ' . FLEXPAY_BRAND_KEY
        ];

        $payload = json_encode([
            'transaction_id' => $txnid
        ]);

        return self::makeRequest(self::$verify_url, $payload, $headers);
    }

    /**
     * Helper to make cURL requests
     */
    private static function makeRequest($url, $payload, $headers) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing if needed

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'error', 'message' => $error];
        }

        return json_decode($response, true);
    }
}
