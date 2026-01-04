-- Spotlight Tickets Database Schema
-- Run this once on your MySQL database

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    txnid VARCHAR(255) UNIQUE NOT NULL,
    tier VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    amount DECIMAL(10,2) NOT NULL,
    promo_used VARCHAR(50) DEFAULT 'NONE',
    status VARCHAR(50) DEFAULT 'pending',
    slot_id VARCHAR(100) DEFAULT 'slot_default',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_txnid (txnid),
    INDEX idx_status (status),
    INDEX idx_slot (slot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create events table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create checkins table for QR scanner
CREATE TABLE IF NOT EXISTS checkins (
    booking_id INT PRIMARY KEY,
    scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default event
INSERT INTO events (name, date_time, location, capacity_regular, capacity_vip, capacity_front) VALUES
('Siddhartha Live 2026', '2026-01-25 18:30:00', 'National Theatre, Dhaka', 300, 100, 100)
ON DUPLICATE KEY UPDATE name=name;
