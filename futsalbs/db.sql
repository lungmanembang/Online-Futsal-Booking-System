-- ============================================
-- FUTSAL BOOKING SYSTEM DATABASE SCHEMA
-- Database: futsal_db
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS futsal_db;
USE futsal_db;

-- ============================================
-- TABLE: users
-- Stores all registered players
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: courts
-- Stores futsal court information
-- ============================================
CREATE TABLE IF NOT EXISTS courts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: bookings
-- Stores all court bookings
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    court_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME GENERATED ALWAYS AS (ADDTIME(start_time, '01:00:00')) STORED,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Unpaid', 'Pending', 'Paid') DEFAULT 'Unpaid',
    payment_method ENUM('manual', 'khalti', 'esewa', 'admin') DEFAULT NULL,
    payment_txn_id VARCHAR(100) DEFAULT NULL,
    payment_screenshot VARCHAR(255) DEFAULT NULL,
    payment_verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_booking (court_id, booking_date, start_time, status),
    INDEX idx_user (user_id),
    INDEX idx_date (booking_date),
    INDEX idx_status (status),
    INDEX idx_payment (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: payment_logs
-- Stores payment transaction logs
-- ============================================
CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    txn_id VARCHAR(100) DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    response_data JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_txn (txn_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: admin_users (Optional)
-- For admin authentication
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert default admin user (password: futsal123)
-- Password hash: futsal123 hashed with password_hash()
INSERT INTO admin_users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$YourHashHere', 'admin@futsalarena.com', 'System Administrator', 'super_admin');

-- Insert sample courts
INSERT INTO courts (name, price_per_hour, description) VALUES
('Court A - Main Arena', 1500.00, 'Standard sized futsal court with professional flooring'),
('Court B - Premier', 2000.00, 'Premium court with better lighting and seating area'),
('Court C - Training', 1000.00, 'Smaller court suitable for training and casual games');

-- Insert sample users
INSERT INTO users (full_name, email, phone, password) VALUES
('John Doe', 'john@example.com', '9800000001', '$2y$10$samplehash1'),
('Jane Smith', 'jane@example.com', '9800000002', '$2y$10$samplehash2');

-- Insert sample bookings
INSERT INTO bookings (court_id, user_id, booking_date, start_time, status, payment_status, payment_method) VALUES
(1, 1, CURDATE(), '10:00:00', 'Confirmed', 'Paid', 'khalti'),
(2, 2, CURDATE(), '14:00:00', 'Pending', 'Unpaid', NULL),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 'Pending', 'Pending', NULL);

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Get available slots for a specific date and court
DELIMITER //
CREATE PROCEDURE GetAvailableSlots(
    IN p_court_id INT,
    IN p_booking_date DATE
)
BEGIN
    SELECT 
        TIME_FORMAT(start_time, '%h:%i %p') as time_label,
        start_time as raw_time,
        CASE 
            WHEN b.id IS NOT NULL AND b.status != 'Completed' THEN 'Booked'
            WHEN b.id IS NOT NULL AND b.status = 'Completed' THEN 'Available'
            ELSE 'Available'
        END as status
    FROM (
        SELECT '07:00:00' as start_time UNION SELECT '08:00:00' UNION SELECT '09:00:00'
        UNION SELECT '10:00:00' UNION SELECT '11:00:00' UNION SELECT '12:00:00'
        UNION SELECT '13:00:00' UNION SELECT '14:00:00' UNION SELECT '15:00:00'
        UNION SELECT '16:00:00' UNION SELECT '17:00:00' UNION SELECT '18:00:00'
        UNION SELECT '19:00:00' UNION SELECT '20:00:00'
    ) slots
    LEFT JOIN bookings b ON b.start_time = slots.start_time 
        AND b.court_id = p_court_id 
        AND b.booking_date = p_booking_date
        AND b.status NOT IN ('Cancelled')
    ORDER BY slots.start_time;
END //
DELIMITER ;

-- Get revenue report by month
DELIMITER //
CREATE PROCEDURE GetMonthlyRevenue(
    IN p_year INT
)
BEGIN
    SELECT 
        MONTHNAME(b.booking_date) as month_name,
        MONTH(b.booking_date) as month_num,
        COUNT(b.id) as total_bookings,
        SUM(c.price_per_hour) as total_revenue
    FROM bookings b
    JOIN courts c ON b.court_id = c.id
    WHERE b.payment_status = 'Paid' 
        AND b.status = 'Confirmed'
        AND YEAR(b.booking_date) = p_year
    GROUP BY MONTH(b.booking_date)
    ORDER BY month_num ASC;
END //
DELIMITER ;

-- ============================================
-- VIEWS
-- ============================================

-- View for admin dashboard stats
CREATE OR REPLACE VIEW admin_stats AS
SELECT 
    (SELECT COUNT(*) FROM bookings) as total_bookings,
    (SELECT COUNT(*) FROM bookings WHERE status = 'Pending') as pending_bookings,
    (SELECT COUNT(*) FROM bookings WHERE payment_status = 'Paid') as paid_bookings,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COALESCE(SUM(c.price_per_hour), 0) 
     FROM bookings b 
     JOIN courts c ON b.court_id = c.id 
     WHERE b.payment_status = 'Paid' AND b.status = 'Confirmed') as total_revenue;

-- View for user booking summary
CREATE OR REPLACE VIEW user_booking_summary AS
SELECT 
    u.id as user_id,
    u.full_name,
    u.email,
    u.phone,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_bookings,
    COALESCE(SUM(CASE WHEN b.payment_status = 'Paid' THEN c.price_per_hour ELSE 0 END), 0) as total_spent
FROM users u
LEFT JOIN bookings b ON u.id = b.user_id
LEFT JOIN courts c ON b.court_id = c.id
GROUP BY u.id;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger to prevent double booking
DELIMITER //
CREATE TRIGGER prevent_double_booking
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    DECLARE existing_count INT;
    
    SELECT COUNT(*) INTO existing_count
    FROM bookings
    WHERE court_id = NEW.court_id
        AND booking_date = NEW.booking_date
        AND start_time = NEW.start_time
        AND status NOT IN ('Cancelled', 'Completed');
    
    IF existing_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This time slot is already booked';
    END IF;
END //
DELIMITER ;

-- Trigger to log booking changes
CREATE TABLE IF NOT EXISTS booking_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    action VARCHAR(50),
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    changed_by VARCHAR(100),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //
CREATE TRIGGER log_booking_status_change
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status OR OLD.payment_status != NEW.payment_status THEN
        INSERT INTO booking_audit_log (booking_id, action, old_status, new_status, changed_by)
        VALUES (
            NEW.id, 
            'status_update', 
            CONCAT(OLD.status, '/', OLD.payment_status),
            CONCAT(NEW.status, '/', NEW.payment_status),
            IF(@session_user IS NOT NULL, @session_user, 'system')
        );
    END IF;
END //
DELIMITER ;

-- ============================================
-- INDEXES FOR OPTIMIZATION
-- ============================================

-- Additional indexes for better query performance
CREATE INDEX idx_bookings_user_date ON bookings(user_id, booking_date);
CREATE INDEX idx_bookings_status_date ON bookings(status, booking_date);
CREATE INDEX idx_payment_logs_booking ON payment_logs(booking_id, status);
CREATE INDEX idx_users_created ON users(created_at);

-- ============================================
-- FUNCTIONS
-- ============================================

-- Function to check if a slot is available
DELIMITER //
CREATE FUNCTION IsSlotAvailable(
    p_court_id INT,
    p_booking_date DATE,
    p_start_time TIME
) RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE booking_exists INT;
    
    SELECT COUNT(*) INTO booking_exists
    FROM bookings
    WHERE court_id = p_court_id
        AND booking_date = p_booking_date
        AND start_time = p_start_time
        AND status NOT IN ('Cancelled', 'Completed');
    
    RETURN booking_exists = 0;
END //
DELIMITER ;

-- Function to calculate total user spending
DELIMITER //
CREATE FUNCTION GetUserTotalSpent(p_user_id INT) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE total DECIMAL(10,2);
    
    SELECT COALESCE(SUM(c.price_per_hour), 0) INTO total
    FROM bookings b
    JOIN courts c ON b.court_id = c.id
    WHERE b.user_id = p_user_id
        AND b.payment_status = 'Paid'
        AND b.status = 'Confirmed';
    
    RETURN total;
END //
DELIMITER ;

-- ============================================
-- SAMPLE QUERIES FOR TESTING
-- ============================================

-- Get all bookings with user and court details
SELECT 
    b.id,
    u.full_name as player_name,
    u.phone,
    c.name as court_name,
    b.booking_date,
    TIME_FORMAT(b.start_time, '%h:%i %p') as start_time,
    b.status,
    b.payment_status,
    c.price_per_hour as amount
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN courts c ON b.court_id = c.id
ORDER BY b.id DESC;

-- Get daily revenue for current month
SELECT 
    DATE(booking_date) as date,
    COUNT(*) as bookings,
    SUM(c.price_per_hour) as revenue
FROM bookings b
JOIN courts c ON b.court_id = c.id
WHERE payment_status = 'Paid' 
    AND status = 'Confirmed'
    AND MONTH(booking_date) = MONTH(CURDATE())
    AND YEAR(booking_date) = YEAR(CURDATE())
GROUP BY DATE(booking_date)
ORDER BY date;

-- Get user booking statistics
SELECT 
    u.full_name,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_bookings,
    SUM(CASE WHEN b.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    GetUserTotalSpent(u.id) as total_spent
FROM users u
LEFT JOIN bookings b ON u.id = b.user_id
GROUP BY u.id
ORDER BY total_bookings DESC;

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================
