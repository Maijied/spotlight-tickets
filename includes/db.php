<?php
/**
 * Database Configuration and Connection
 * Handles all database operations for the Spotlight Tickets system
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spotlight_tickets');

try {
    // Create database connection
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database Connection Failed: ' . $e->getMessage());
}

/**
 * Initialize Database Tables
 * Creates necessary tables if they don't exist
 */
function initializeDatabase() {
    global $pdo;
    
    // Events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        date DATETIME NOT NULL,
        location VARCHAR(255),
        organizer VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Slots table with prices field
    $pdo->exec("CREATE TABLE IF NOT EXISTS slots (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        slot_name VARCHAR(255) NOT NULL,
        total_capacity INT NOT NULL DEFAULT 0,
        available_seats INT NOT NULL DEFAULT 0,
        price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        INDEX idx_event_id (event_id)
    )");
    
    // Tickets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        slot_id INT NOT NULL,
        ticket_number VARCHAR(255) UNIQUE NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255),
        customer_phone VARCHAR(20),
        status ENUM('available', 'sold', 'reserved', 'cancelled') DEFAULT 'available',
        price DECIMAL(10, 2) NOT NULL,
        purchase_date DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (slot_id) REFERENCES slots(id) ON DELETE CASCADE,
        INDEX idx_slot_id (slot_id),
        INDEX idx_status (status)
    )");
    
    // Orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_number VARCHAR(255) UNIQUE NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20),
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        payment_method VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_email (customer_email)
    )");
    
    // Order items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        ticket_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        INDEX idx_order_id (order_id)
    )");
}

/**
 * Get all events
 */
function getAllEvents() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY date DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching events: " . $e->getMessage());
        return [];
    }
}

/**
 * Get event by ID
 */
function getEventById($eventId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching event: " . $e->getMessage());
        return null;
    }
}

/**
 * Create a new event
 */
function createEvent($name, $description, $date, $location, $organizer) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO events (name, description, date, location, organizer)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $date, $location, $organizer]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating event: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all slots for an event
 */
function getEventSlots($eventId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT id, event_id, slot_name, total_capacity, available_seats, price, description
            FROM slots
            WHERE event_id = ?
            ORDER BY slot_name ASC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching slots: " . $e->getMessage());
        return [];
    }
}

/**
 * Get slot by ID with price information
 */
function getSlotById($slotId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT id, event_id, slot_name, total_capacity, available_seats, price, description
            FROM slots
            WHERE id = ?
        ");
        $stmt->execute([$slotId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching slot: " . $e->getMessage());
        return null;
    }
}

/**
 * Create a new slot with price
 */
function createSlot($eventId, $slotName, $totalCapacity, $price, $description = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO slots (event_id, slot_name, total_capacity, available_seats, price, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$eventId, $slotName, $totalCapacity, $totalCapacity, $price, $description]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating slot: " . $e->getMessage());
        return false;
    }
}

/**
 * Update slot information including price
 */
function updateSlot($slotId, $slotName, $totalCapacity, $price, $description = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE slots
            SET slot_name = ?, total_capacity = ?, price = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$slotName, $totalCapacity, $price, $description, $slotId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating slot: " . $e->getMessage());
        return false;
    }
}

/**
 * Get available seats for a slot
 */
function getAvailableSeats($slotId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT available_seats FROM slots WHERE id = ?");
        $stmt->execute([$slotId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['available_seats'] : 0;
    } catch (PDOException $e) {
        error_log("Error fetching available seats: " . $e->getMessage());
        return 0;
    }
}

/**
 * Update available seats
 */
function updateAvailableSeats($slotId, $quantity) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE slots
            SET available_seats = available_seats - ?
            WHERE id = ? AND available_seats >= ?
        ");
        $stmt->execute([$quantity, $slotId, $quantity]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error updating available seats: " . $e->getMessage());
        return false;
    }
}

/**
 * Get slot price
 */
function getSlotPrice($slotId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT price FROM slots WHERE id = ?");
        $stmt->execute([$slotId]);
        $result = $stmt->fetch();
        return $result ? (float)$result['price'] : 0.00;
    } catch (PDOException $e) {
        error_log("Error fetching slot price: " . $e->getMessage());
        return 0.00;
    }
}

/**
 * Create a new ticket
 */
function createTicket($slotId, $ticketNumber, $customerName, $customerEmail, $customerPhone, $price) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tickets (slot_id, ticket_number, customer_name, customer_email, customer_phone, price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$slotId, $ticketNumber, $customerName, $customerEmail, $customerPhone, $price]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating ticket: " . $e->getMessage());
        return false;
    }
}

/**
 * Get ticket by ID
 */
function getTicketById($ticketId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching ticket: " . $e->getMessage());
        return null;
    }
}

/**
 * Update ticket status
 */
function updateTicketStatus($ticketId, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$status, $ticketId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating ticket status: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new order
 */
function createOrder($orderNumber, $customerName, $customerEmail, $customerPhone, $totalAmount, $paymentMethod = 'credit_card') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, total_amount, payment_method)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderNumber, $customerName, $customerEmail, $customerPhone, $totalAmount, $paymentMethod]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order by ID
 */
function getOrderById($orderId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
        return null;
    }
}

/**
 * Get order by order number
 */
function getOrderByNumber($orderNumber) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
        return null;
    }
}

/**
 * Update order status
 */
function updateOrderStatus($orderId, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

/**
 * Add item to order
 */
function addOrderItem($orderId, $ticketId, $quantity, $price) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, ticket_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $ticketId, $quantity, $price]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error adding order item: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order items
 */
function getOrderItems($orderId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT oi.*, t.ticket_number, t.customer_name
            FROM order_items oi
            JOIN tickets t ON oi.ticket_id = t.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching order items: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate unique ticket number
 */
function generateTicketNumber() {
    return 'TKT-' . date('YmdHis') . '-' . strtoupper(substr(md5(microtime()), 0, 6));
}

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(md5(microtime()), 0, 6));
}

/**
 * Close database connection (optional)
 */
function closeDatabase() {
    global $pdo;
    $pdo = null;
}

// Initialize database tables on include
initializeDatabase();

?>