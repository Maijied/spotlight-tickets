<?php
/**
 * Dummy SMS Service Provider
 */

require_once __DIR__ . '/../config/config.php';

class SMSGateway {
    /**
     * Simulate sending an SMS
     */
    public static function sendSMS($phone, $message) {
        // In a real production environment, you would use cURL here to call
        // an API like Twilio, Nexmo, or a local BD gateway (e.g., BoomCast, SSLWireless).
        
        // Log the SMS attempt (Simulation)
        $log_entry = "[" . date('Y-m-d H:i:s') . "] To: $phone | Body: $message" . PHP_EOL;
        file_put_contents(__DIR__ . '/../sms_log.txt', $log_entry, FILE_APPEND);

        // Simulate a tiny delay
        usleep(500000); 

        return [
            'status' => 'success',
            'message' => 'SMS sent successfully (Simulated)',
            'provider_response' => 'DUMMY_OK_200'
        ];
    }
}
