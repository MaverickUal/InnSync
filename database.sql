-- ============================================================
--  InnSync Hotel Management System - Database
--  Database: dbinnsync
-- ============================================================

CREATE DATABASE IF NOT EXISTS dbinnsync;
USE dbinnsync;

-- ============================================================
-- PHASE 2 & 7 — USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    fullname    VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('customer', 'admin') DEFAULT 'customer',
    status      ENUM('approved', 'rejected', 'blacklist') DEFAULT 'approved',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PHASE 4 & 7 — ROOM TYPES
-- ============================================================
CREATE TABLE IF NOT EXISTS room_types (
    type_id     INT AUTO_INCREMENT PRIMARY KEY,
    type_name   VARCHAR(100) NOT NULL,
    description TEXT
);

-- ============================================================
-- PHASE 4 & 7 — ROOMS
-- ============================================================
CREATE TABLE IF NOT EXISTS rooms (
    room_id             INT AUTO_INCREMENT PRIMARY KEY,
    room_name           VARCHAR(100) NOT NULL,
    type_id             INT,
    reservation_type_id INT DEFAULT NULL
        COMMENT 'Optional promo/reservation type assigned to this room',
    price               DECIMAL(10,2) NOT NULL DEFAULT 0,
    capacity            INT NOT NULL DEFAULT 1,
    status              ENUM('available','occupied','unavailable','maintenance') DEFAULT 'available',
    description         TEXT,
    FOREIGN KEY (type_id) REFERENCES room_types(type_id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_type_id) REFERENCES reservation_types(reservation_type_id) ON DELETE SET NULL
);

-- ============================================================
-- PHASE 4 — ROOM IMAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS room_images (
    image_id    INT AUTO_INCREMENT PRIMARY KEY,
    room_id     INT NOT NULL,
    image_path  VARCHAR(255),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
);

-- ============================================================
-- PHASE 5 & 7 — RESERVATION TYPES (PROMOS)
-- ============================================================
CREATE TABLE IF NOT EXISTS reservation_types (
    reservation_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name           VARCHAR(100) NOT NULL,
    description         TEXT,
    discount_percent    DECIMAL(5,2) NOT NULL DEFAULT 0.00
        COMMENT 'Discount applied to total booking cost (0–100)'
);

-- ============================================================
-- PHASE 5, 6, 7, 8 — BOOKINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    booking_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT NOT NULL,
    room_id             INT NOT NULL,
    reservation_type_id INT,
    check_in            DATE NOT NULL,
    check_out           DATE NOT NULL,
    status              ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_type_id) REFERENCES reservation_types(reservation_type_id) ON DELETE SET NULL
);

-- ============================================================
-- PHASE 6, 7, 8 — PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    payment_id              INT AUTO_INCREMENT PRIMARY KEY,
    booking_id              INT NOT NULL,
    total_amount            DECIMAL(10,2) NOT NULL DEFAULT 0,
    downpayment_amount      DECIMAL(10,2) NOT NULL DEFAULT 0,
    downpayment_method      ENUM('gcash','bank_transfer','credit_card') DEFAULT NULL,
    downpayment_status      ENUM('confirmed','refunded') DEFAULT 'confirmed',
    downpayment_reference   VARCHAR(100) DEFAULT NULL,
    downpayment_date        DATETIME DEFAULT NULL,
    remaining_balance       DECIMAL(10,2) NOT NULL DEFAULT 0,
    remaining_method        ENUM('cash','gcash','bank_transfer','credit_card') DEFAULT NULL,
    remaining_status        ENUM('pending','confirmed','refunded') DEFAULT 'pending',
    remaining_reference     VARCHAR(100) DEFAULT NULL,
    remaining_date          DATETIME DEFAULT NULL,
    payment_type            ENUM('downpayment_only','full_payment') DEFAULT 'downpayment_only',
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- ============================================================
-- PHASE 6, 7 — RECEIPTS
-- ============================================================
CREATE TABLE IF NOT EXISTS receipts (
    receipt_id      INT AUTO_INCREMENT PRIMARY KEY,
    payment_id      INT NOT NULL,
    receipt_number  VARCHAR(50) UNIQUE,
    file_path       VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE CASCADE
);

-- ============================================================
-- PHASE 5, 6, 7 — CANCELLATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS cancellations (
    cancel_id       INT AUTO_INCREMENT PRIMARY KEY,
    booking_id      INT NOT NULL,
    reason          TEXT,
    refund_amount   DECIMAL(10,2) DEFAULT 0,
    status          ENUM('pending','refunded','denied') DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- ============================================================
-- PHASE 7 — REFUND RULES
-- ============================================================
CREATE TABLE IF NOT EXISTS refund_rules (
    rule_id         INT AUTO_INCREMENT PRIMARY KEY,
    rule_name       VARCHAR(100) NOT NULL,
    days_before     INT NOT NULL,
    refund_percent  DECIMAL(5,2) NOT NULL,
    description     TEXT
);

-- ============================================================
-- PHASE 8 — REPORTS
-- ============================================================
CREATE TABLE IF NOT EXISTS reports (
    report_id   INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(50),
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- ADMIN LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_logs (
    log_id      INT AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT NOT NULL,
    action      VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin account (temporary placeholder password)
-- IMPORTANT: After importing this SQL, visit http://localhost/innsync/fix_admin.php
-- That script will properly set the password to: admin123
INSERT INTO users (fullname, email, password, role, status) VALUES
('Admin InnSync', 'admin@innsync.com', 'PLACEHOLDER_RUN_FIX_ADMIN_PHP', 'admin', 'approved');

-- Room Types
INSERT INTO room_types (type_name, description) VALUES
('Standard', 'Comfortable standard room with basic amenities'),
('Deluxe', 'Spacious deluxe room with premium furnishings'),
('Suite', 'Luxurious suite with separate living area'),
('Family', 'Large family room suitable for groups');

-- Reservation Types (Promos)
INSERT INTO reservation_types (type_name, description, discount_percent) VALUES
('Standard Stay',    'Regular nightly reservation',               0.00),
('Long Stay',        'Extended stay of 7 nights or more',        10.00),
('Weekend Package',  'Friday to Sunday package rate',             5.00),
('Early Bird',       'Booked at least 30 days in advance',       15.00);

-- Rooms
INSERT INTO rooms (room_name, type_id, price, capacity, status, description) VALUES
('Standard Room 101', 1, 1500.00, 2, 'available', 'Cozy standard room with city view, queen bed, and free WiFi.'),
('Standard Room 102', 1, 1500.00, 2, 'available', 'Standard room on second floor with garden view.'),
('Deluxe Room 201', 2, 2500.00, 2, 'available', 'Spacious deluxe room with king bed, bathtub, and city view.'),
('Deluxe Room 202', 2, 2500.00, 3, 'available', 'Deluxe room with extra sofa bed, perfect for small families.'),
('Junior Suite 301', 3, 4500.00, 2, 'available', 'Junior suite with separate lounge area and ocean view.'),
('Executive Suite 302', 3, 6000.00, 2, 'available', 'Premium executive suite with full amenities and panoramic view.'),
('Family Room 401', 4, 3500.00, 5, 'available', 'Large family room with 2 queen beds and bunk bed.'),
('Family Room 402', 4, 3500.00, 6, 'available', 'Extra-large family room with kitchenette and dining area.');

-- Refund Rules
INSERT INTO refund_rules (rule_name, days_before, refund_percent, description) VALUES
('Full Refund', 14, 100.00, 'Cancel 14 or more days before check-in for a full refund.'),
('Partial Refund', 7, 50.00, 'Cancel 7-13 days before check-in for a 50% refund.'),
('No Refund', 0, 0.00, 'Cancellations within 7 days of check-in are non-refundable.');

-- ============================================================
-- UPDATES FOR NEW FEATURES
-- ============================================================

-- Add contact_number to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) DEFAULT NULL AFTER email;

-- Add downpayment fields to bookings
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS downpayment_amount DECIMAL(10,2) DEFAULT 0 AFTER status;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS downpayment_paid TINYINT(1) DEFAULT 0 AFTER downpayment_amount;

-- Add discount to reservation_types (for existing installs)
ALTER TABLE reservation_types ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER description;

-- Add reservation_type_id to rooms (for existing installs)
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS reservation_type_id INT DEFAULT NULL AFTER type_id;
ALTER TABLE rooms ADD CONSTRAINT IF NOT EXISTS fk_rooms_res_type
    FOREIGN KEY (reservation_type_id) REFERENCES reservation_types(reservation_type_id) ON DELETE SET NULL;
