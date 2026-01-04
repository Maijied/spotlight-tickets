<?php
/**
 * Database Class for Spotlight Tickets System
 * Handles all database operations with static methods
 */

class Database {
    private static $pdo = null;
    
    /**
     * Get database connection (singleton pattern)
     */
    public static function connect() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                
                // Ensure tables exist
                self::initializeTables();
                
            } catch (PDOException $e) {
                error_log("Database Connection Failed: " . $e->getMessage());
                return null;
            }
        }
        return self::$pdo;
    }
    
    /**
     * Initialize database tables if they don't exist
     */
    private static function initializeTables() {
        $pdo = self::$pdo;
        
        // Settings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) UNIQUE NOT NULL,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Admins table
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Bookings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            txnid VARCHAR(255) UNIQUE NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(50),
            tier VARCHAR(100) DEFAULT 'regular',
            quantity INT DEFAULT 1,
            amount DECIMAL(10,2) NOT NULL,
            promo_used VARCHAR(100),
            status VARCHAR(50) DEFAULT 'pending',
            slot_id VARCHAR(100) DEFAULT 'slot_default',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_txnid (txnid)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Events table
        $pdo->exec("CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            date_time DATETIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            capacity_regular INT DEFAULT 300,
            capacity_vip INT DEFAULT 100,
            capacity_front INT DEFAULT 100,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_active_date (is_active, date_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Checkins table
        $pdo->exec("CREATE TABLE IF NOT EXISTS checkins (
            booking_id INT PRIMARY KEY,
            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Insert default admin if none exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
        if ($stmt->fetchColumn() == 0) {
            // Default: admin / admin123
            $pdo->exec("INSERT INTO admins (username, password) VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')");
        }
        
        // Insert default settings if none exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'event_name'");
        if ($stmt->fetchColumn() == 0) {
            $defaultSettings = [
                'event_name' => 'Siddhartha Live 2026',
                'slots' => json_encode([
                    [
                        'id' => 'slot_1',
                        'time' => '২৫ জানুয়ারি ২০২৬, সন্ধ্যা ৬:৩০',
                        'location' => 'জাতীয় নাট্যশালা, ঢাকা',
                        'capacities' => ['regular' => 300, 'vip' => 100, 'front' => 100],
                        'prices' => ['regular' => 500, 'vip' => 1200, 'front' => 2500]
                    ]
                ])
            ];
            
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaultSettings as $key => $value) {
                $stmt->execute([$key, $value]);
            }
        }
    }
    
    /**
     * Get all settings as associative array
     */
    public static function getSettings() {
        try {
            $pdo = self::connect();
            if (!$pdo) return self::getDefaultSettings();
            
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $value = $row['setting_value'];
                // Try to decode JSON values
                $decoded = json_decode($value, true);
                $settings[$row['setting_key']] = ($decoded !== null) ? $decoded : $value;
            }
            
            return array_merge(self::getDefaultSettings(), $settings);
        } catch (PDOException $e) {
            error_log("Error getting settings: " . $e->getMessage());
            return self::getDefaultSettings();
        }
    }
    
    /**
     * Get default settings
     */
    private static function getDefaultSettings() {
        return [
            'event_name' => 'Siddhartha Live 2026',
            'slots' => [
                [
                    'id' => 'slot_default',
                    'time' => 'TBD',
                    'location' => 'TBD',
                    'capacities' => ['regular' => 300, 'vip' => 100, 'front' => 100],
                    'prices' => ['regular' => 500, 'vip' => 1200, 'front' => 2500]
                ]
            ]
        ];
    }
    
    /**
     * Get all bookings
     */
    public static function getBookings() {
        try {
            $pdo = self::connect();
            if (!$pdo) return [];
            
            $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting bookings: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save a new booking
     */
    public static function saveBooking($data) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("
                INSERT INTO bookings (txnid, full_name, email, phone, tier, quantity, amount, promo_used, status, slot_id)
                VALUES (:txnid, :full_name, :email, :phone, :tier, :quantity, :amount, :promo_used, :status, :slot_id)
            ");
            
            return $stmt->execute([
                'txnid' => $data['txnid'] ?? uniqid('TXN'),
                'full_name' => $data['full_name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'tier' => $data['tier'] ?? 'regular',
                'quantity' => $data['quantity'] ?? 1,
                'amount' => $data['amount'] ?? 0,
                'promo_used' => $data['promo_used'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'slot_id' => $data['slot_id'] ?? 'slot_default'
            ]);
        } catch (PDOException $e) {
            error_log("Error saving booking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update booking status
     */
    public static function updateBookingStatus($txnid, $status) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE txnid = ?");
            return $stmt->execute([$status, $txnid]);
        } catch (PDOException $e) {
            error_log("Error updating booking status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all admins
     */
    public static function getAdmins() {
        try {
            $pdo = self::connect();
            if (!$pdo) return [];
            
            $stmt = $pdo->query("SELECT username, password FROM admins");
            $admins = [];
            while ($row = $stmt->fetch()) {
                $admins[$row['username']] = $row['password'];
            }
            return $admins;
        } catch (PDOException $e) {
            error_log("Error getting admins: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save a new admin
     */
    public static function saveAdmin($username, $password) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = ?");
            return $stmt->execute([$username, $password, $password]);
        } catch (PDOException $e) {
            error_log("Error saving admin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete an admin
     */
    public static function deleteAdmin($username) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("DELETE FROM admins WHERE username = ?");
            return $stmt->execute([$username]);
        } catch (PDOException $e) {
            error_log("Error deleting admin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update event name in settings
     */
    public static function updateEventName($name) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('event_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            return $stmt->execute([$name, $name]);
        } catch (PDOException $e) {
            error_log("Error updating event name: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a new event/slot
     */
    public static function addEvent($time, $location, $capacities, $prices = []) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            // Get current slots
            $settings = self::getSettings();
            $slots = $settings['slots'] ?? [];
            
            // Add new slot
            $newSlot = [
                'id' => 'slot_' . (count($slots) + 1),
                'time' => $time,
                'location' => $location,
                'capacities' => $capacities,
                'prices' => $prices ?: ['regular' => 500, 'vip' => 1200, 'front' => 2500]
            ];
            $slots[] = $newSlot;
            
            // Save back to settings
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('slots', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $json = json_encode($slots);
            return $stmt->execute([$json, $json]);
        } catch (PDOException $e) {
            error_log("Error adding event: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete an event/slot by index
     */
    public static function deleteEvent($index) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            // Get current slots
            $settings = self::getSettings();
            $slots = $settings['slots'] ?? [];
            
            // Remove slot at index
            if (isset($slots[$index])) {
                array_splice($slots, $index, 1);
                
                // Save back to settings
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('slots', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $json = json_encode($slots);
                return $stmt->execute([$json, $json]);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error deleting event: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get booking by transaction ID
     */
    public static function getBookingByTxnId($txnid) {
        try {
            $pdo = self::connect();
            if (!$pdo) return null;
            
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE txnid = ?");
            $stmt->execute([$txnid]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting booking: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if transaction ID exists
     */
    public static function txnExists($txnid) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE txnid = ?");
            $stmt->execute([$txnid]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking txn: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record check-in
     */
    public static function recordCheckin($bookingId) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("INSERT INTO checkins (booking_id) VALUES (?) ON DUPLICATE KEY UPDATE scanned_at = CURRENT_TIMESTAMP");
            return $stmt->execute([$bookingId]);
        } catch (PDOException $e) {
            error_log("Error recording checkin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if booking is already checked in
     */
    public static function isCheckedIn($bookingId) {
        try {
            $pdo = self::connect();
            if (!$pdo) return false;
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM checkins WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking checkin status: " . $e->getMessage());
            return false;
        }
    }
}