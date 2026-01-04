-- SQL Setup for Spotlight Tickets

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    txnid VARCHAR(100) NOT NULL UNIQUE,
    tier VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 1,
    amount DECIMAL(10, 2) NOT NULL,
    promo_used VARCHAR(50) DEFAULT 'NONE',
    slot_id VARCHAR(50) DEFAULT 'slot_default',
    status ENUM('pending', 'confirmed', 'checked-in') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    date_time DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity_regular INT DEFAULT 0,
    capacity_vip INT DEFAULT 0,
    capacity_front INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Event Data (Siddhartha Live 2026)
INSERT INTO events (name, date_time, location, capacity_regular, capacity_vip, capacity_front)
VALUES ('Siddhartha Live 2026', '2026-01-25 18:30:00', 'National Theatre, Dhaka', 300, 100, 100)
ON DUPLICATE KEY UPDATE name=name;

-- Initial default admin (admin / admin123)
-- Hash: $2y$10$GsXdPGs4CJJwmi1NHnpYK.W38odyRfGnaV3h/NjqQELlBkAzXXRjW
INSERT INTO admins (username, password) 
VALUES ('admin', '$2y$10$GsXdPGs4CJJwmi1NHnpYK.W38odyRfGnaV3h/NjqQELlBkAzQXRjW')
ON DUPLICATE KEY UPDATE username=username;
