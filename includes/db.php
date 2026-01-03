<?php
/**
 * Simple JSON Database Wrapper (Fallback for missing SQLite driver)
 */

class Database {
    private static $db_file = __DIR__ . '/../bookings.json';

    /**
     * Get bookings from JSON file
     */
    public static function getBookings() {
        if (!file_exists(self::$db_file)) {
            return [];
        }
        $data = file_get_contents(self::$db_file);
        return json_decode($data, true) ?: [];
    }

    /**
     * Initialize/Connect logic (Placeholder for JSON)
     */
    public static function connect() {
        if (!file_exists(self::$db_file)) {
            file_put_contents(self::$db_file, json_encode([]));
        }
        return true;
    }

    /**
     * Save a new booking
     */
    public static function saveBooking($data) {
        self::connect();
        $bookings = self::getBookings();
        
        $new_booking = [
            'id' => count($bookings) + 1,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'txnid' => $data['txnid'],
            'tier' => $data['tier'],
            'quantity' => $data['quantity'] ?? 1,
            'amount' => $data['amount'],
            'promo_used' => $data['promo_used'] ?? 'NONE',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $bookings[] = $new_booking;
        return file_put_contents(self::$db_file, json_encode($bookings, JSON_PRETTY_PRINT));
    }
}
