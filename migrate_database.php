<?php
/**
 * Database Migration Script
 * Updates existing database to new schema without losing data
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';

echo "Starting database migration...\n\n";

$pdo = Database::connect();

if (!$pdo) {
    die("ERROR: Could not connect to database. Check your .env credentials.\n");
}

echo "✓ Database connection successful\n\n";

// 1. Add missing columns to bookings table
echo "Updating bookings table...\n";
try {
    // Check if columns exist first
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN status VARCHAR(50) DEFAULT 'confirmed' AFTER promo_used");
        echo "  ✓ Added 'status' column\n";
    } else {
        echo "  - 'status' column already exists\n";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'slot_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN slot_id VARCHAR(100) DEFAULT 'slot_default' AFTER status");
        echo "  ✓ Added 'slot_id' column\n";
    } else {
        echo "  - 'slot_id' column already exists\n";
    }
    
    // Update old bookings to have confirmed status if null
    $pdo->exec("UPDATE bookings SET status = 'confirmed' WHERE status IS NULL OR status = ''");
    echo "  ✓ Updated existing bookings to 'confirmed' status\n";
    
} catch (PDOException $e) {
    echo "  ⚠ Warning: " . $e->getMessage() . "\n";
}

// 2. Create events table
echo "\nCreating events table...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  ✓ Events table created\n";
    
    // Insert default event if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO events (name, date_time, location, capacity_regular, capacity_vip, capacity_front) 
            VALUES ('Siddhartha Live 2026', '2026-01-25 18:30:00', 'National Theatre, Dhaka', 300, 100, 100)
        ");
        echo "  ✓ Added default event\n";
    }
} catch (PDOException $e) {
    echo "  ⚠ Warning: " . $e->getMessage() . "\n";
}

// 3. Create checkins table
echo "\nCreating checkins table...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS checkins (
            booking_id INT PRIMARY KEY,
            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  ✓ Checkins table created\n";
} catch (PDOException $e) {
    echo "  ⚠ Warning: " . $e->getMessage() . "\n";
}

// 4. Verify admin account exists
echo "\nVerifying admin account...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        // Password: admin123
        $pdo->exec("
            INSERT INTO admins (username, password) 
            VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
        ");
        echo "  ✓ Created default admin account\n";
    } else {
        echo "  - Admin account already exists\n";
    }
} catch (PDOException $e) {
    echo "  ⚠ Warning: " . $e->getMessage() . "\n";
}

echo "\n✓ Migration complete!\n\n";
echo "Summary:\n";
echo "- Bookings table: Updated with status and slot_id columns\n";
echo "- Events table: Created with default event\n";
echo "- Checkins table: Created for QR scanner\n";
echo "- Admin account: Verified\n\n";
echo "You can now access:\n";
echo "- Admin Panel: https://shiddarth.xo.je/public/admin.php\n";
echo "- Public Page: https://shiddarth.xo.je/public/index.php\n\n";
echo "Default credentials: admin / admin123\n";
