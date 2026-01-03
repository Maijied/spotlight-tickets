<?php
/**
 * Database Wrapper supporting both MySQL and JSON Storage
 */

class Database {
    private static $pdo = null;
    private static $bookings_json = __DIR__ . '/../bookings.json';
    private static $admins_json   = __DIR__ . '/../admins.json';
    private static $settings_json = __DIR__ . '/../settings.json';

    /**
     * Connect to MySQL if configured, otherwise stay in JSON mode
     */
    public static function connect() {
        if (self::$pdo !== null) return self::$pdo;

        // Only attempt MySQL if the config has been changed from placeholders
        if (defined('DB_HOST') && DB_HOST !== 'sqlXXXX.infinityfree.com' && !empty(DB_PASS)) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                return self::$pdo;
            } catch (Exception $e) {
                error_log("DB Connection failed: " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * --- BOOKINGS LOGIC ---
     */

    public static function getBookings() {
        $pdo = self::connect();
        if ($pdo) {
            $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
            return $stmt->fetchAll();
        }

        // Fallback to JSON
        if (!file_exists(self::$bookings_json)) return [];
        return json_decode(file_get_contents(self::$bookings_json), true) ?: [];
    }

    public static function saveBooking($data) {
        $pdo = self::connect();
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO bookings (name, email, phone, txnid, tier, quantity, amount, promo_used, status, slot_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['name'], 
                $data['email'], 
                $data['phone'], 
                $data['txnid'], 
                $data['tier'], 
                $data['quantity'] ?? 1, 
                $data['amount'], 
                $data['promo_used'] ?? 'NONE',
                $data['status'] ?? 'pending',
                $data['slot_id'] ?? 'slot_default'
            ]);
        }

        // Fallback to JSON
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
            'status' => $data['status'] ?? 'pending',
            'slot_id' => $data['slot_id'] ?? 'slot_default',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $bookings[] = $new_booking;
        return file_put_contents(self::$bookings_json, json_encode($bookings, JSON_PRETTY_PRINT));
    }

    public static function updateBookingStatus($txnid, $status) {
        $pdo = self::connect();
        if ($pdo) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE txnid = ?");
            return $stmt->execute([$status, $txnid]);
        }

        // Fallback to JSON
        $bookings = self::getBookings();
        $updated = false;
        foreach ($bookings as &$b) {
            if ($b['txnid'] === $txnid) {
                $b['status'] = $status;
                $updated = true;
                break;
            }
        }
        if ($updated) {
            return file_put_contents(self::$bookings_json, json_encode($bookings, JSON_PRETTY_PRINT));
        }
        return false;
    }

    /**
     * --- ADMINS LOGIC ---
     */

    public static function getAdmins() {
        $pdo = self::connect();
        if ($pdo) {
            $stmt = $pdo->query("SELECT * FROM admins ORDER BY id ASC");
            return $stmt->fetchAll();
        }

        // Fallback to JSON
        if (!file_exists(self::$admins_json)) return [];
        return json_decode(file_get_contents(self::$admins_json), true) ?: [];
    }

    public static function saveAdmin($username, $password_hash) {
        $pdo = self::connect();
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)");
            return $stmt->execute([$username, $password_hash]);
        }

        // Fallback to JSON
        $admins = self::getAdmins();
        $exists = false;
        foreach($admins as &$a) {
            if ($a['username'] === $username) {
                $a['password'] = $password_hash;
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $admins[] = ['username' => $username, 'password' => $password_hash];
        }
        return file_put_contents(self::$admins_json, json_encode($admins, JSON_PRETTY_PRINT));
    }

    public static function deleteAdmin($username) {
        $pdo = self::connect();
        if ($pdo) {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE username = ?");
            return $stmt->execute([$username]);
        }

        // Fallback to JSON
        $admins = self::getAdmins();
        $admins = array_filter($admins, function($a) use ($username) {
            return $a['username'] !== $username;
        });
        return file_put_contents(self::$admins_json, json_encode(array_values($admins), JSON_PRETTY_PRINT));
    }

    /**
     * --- SETTINGS LOGIC ---
     */

    public static function getSettings() {
        if (!file_exists(self::$settings_json)) {
            // Default initial settings
            return [
                'event_name' => 'Siddhartha Live 2026',
                'slots' => [
                    [
                        'id' => 'slot_default',
                        'time' => 'January 25, 2026 | 06:30 PM',
                        'location' => 'National Theatre, Dhaka',
                        'capacities' => [
                            'regular' => 300,
                            'vip' => 100,
                            'front' => 100
                        ]
                    ]
                ]
            ];
        }
        return json_decode(file_get_contents(self::$settings_json), true) ?: [];
    }

    public static function saveSettings($data) {
        return file_put_contents(self::$settings_json, json_encode($data, JSON_PRETTY_PRINT));
    }
}
