-- ============================================================
-- InnSync Migration
-- Run this ONCE on your existing database
-- ============================================================
USE dbinnsync;

-- 1. Add 'occupied' to rooms status ENUM
ALTER TABLE rooms
    MODIFY COLUMN status ENUM('available','occupied','unavailable','maintenance') DEFAULT 'available';

-- 2. Convert check_in and check_out from DATE to DATETIME
ALTER TABLE bookings
    MODIFY COLUMN check_in  DATETIME NOT NULL,
    MODIFY COLUMN check_out DATETIME NOT NULL;

-- 3. Backfill existing DATE rows with correct times
UPDATE bookings
SET check_in = CONCAT(DATE(check_in), ' 14:00:00')
WHERE TIME(check_in) = '00:00:00';

UPDATE bookings
SET check_out = CONCAT(DATE(check_out), ' 12:00:00')
WHERE TIME(check_out) = '00:00:00';

-- 4. Create admin_logs table if it doesn't exist
CREATE TABLE IF NOT EXISTS admin_logs (
    log_id      INT AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT NOT NULL,
    action      VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE
);
