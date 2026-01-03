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
    status ENUM('pending', 'confirmed', 'checked-in') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial default admin (admin / admin123)
-- Hash: $2y$10$GsXdPGs4CJJwmi1NHnpYK.W38odyRfGnaV3h/NjqQELlBkAzQXRjW
INSERT INTO admins (username, password) 
VALUES ('admin', '$2y$10$GsXdPGs4CJJwmi1NHnpYK.W38odyRfGnaV3h/NjqQELlBkAzQXRjW')
ON DUPLICATE KEY UPDATE username=username;
