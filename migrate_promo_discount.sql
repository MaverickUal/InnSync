-- ============================================================
--  InnSync — Migration: Promo / Discount on Reservation Types
--  Run this ONCE on existing installs that already have the
--  database set up from database.sql
-- ============================================================

USE dbinnsync;

-- 1. Add discount_percent column to reservation_types
ALTER TABLE reservation_types
    ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00
        COMMENT 'Discount applied to total booking cost (0–100)';

-- 2. Add reservation_type_id (promo) column to rooms
ALTER TABLE rooms
    ADD COLUMN IF NOT EXISTS reservation_type_id INT DEFAULT NULL
        COMMENT 'Optional promo/reservation type assigned to this room'
        AFTER type_id;

-- 3. Add foreign key only if it does not already exist
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'dbinnsync'
      AND TABLE_NAME        = 'rooms'
      AND CONSTRAINT_NAME   = 'fk_rooms_res_type'
      AND CONSTRAINT_TYPE   = 'FOREIGN KEY'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE rooms ADD CONSTRAINT fk_rooms_res_type
     FOREIGN KEY (reservation_type_id) REFERENCES reservation_types(reservation_type_id)
     ON DELETE SET NULL',
    'SELECT ''FK already exists, skipping'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Update seed reservation types with example discounts
--    (only updates rows that still have the original seed names)
UPDATE reservation_types SET discount_percent = 0.00  WHERE type_name = 'Standard Stay'   AND discount_percent = 0;
UPDATE reservation_types SET discount_percent = 10.00 WHERE type_name = 'Long Stay'        AND discount_percent = 0;
UPDATE reservation_types SET discount_percent = 5.00  WHERE type_name = 'Weekend Package'  AND discount_percent = 0;
UPDATE reservation_types SET discount_percent = 15.00 WHERE type_name = 'Early Bird'       AND discount_percent = 0;

SELECT 'Migration complete.' AS status;
