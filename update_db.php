<?php
// =============================================
// InnSync - Database Update Script v2
// Visit: http://localhost/innsync/update_db.php
// DELETE this file after running!
// =============================================
include "api/config.php";

$queries = [
    // Users table
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) DEFAULT NULL AFTER email",
    // Bookings table
    "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS downpayment_amount DECIMAL(10,2) DEFAULT 0 AFTER status",
    "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS downpayment_paid TINYINT(1) DEFAULT 0 AFTER downpayment_amount",
    // Payments table - drop old columns and add new structure
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER booking_id",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS downpayment_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total_amount",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS downpayment_method ENUM('gcash','bank_transfer','credit_card') DEFAULT NULL AFTER downpayment_amount",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS downpayment_status ENUM('confirmed','refunded') DEFAULT 'confirmed' AFTER downpayment_method",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS downpayment_reference VARCHAR(100) DEFAULT NULL AFTER downpayment_status",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS downpayment_date DATETIME DEFAULT NULL AFTER downpayment_reference",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS remaining_balance DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER downpayment_date",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS remaining_method ENUM('cash','gcash','bank_transfer','credit_card') DEFAULT NULL AFTER remaining_balance",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS remaining_status ENUM('pending','confirmed','refunded') DEFAULT 'pending' AFTER remaining_method",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS remaining_reference VARCHAR(100) DEFAULT NULL AFTER remaining_status",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS remaining_date DATETIME DEFAULT NULL AFTER remaining_reference",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS payment_type ENUM('downpayment_only','full_payment') DEFAULT 'downpayment_only' AFTER remaining_date",
    // Auto-approve existing pending customers
    "UPDATE users SET status = 'approved' WHERE status = 'pending' AND role = 'customer'",
];

echo "<!DOCTYPE html><html><head><title>DB Update</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='p-4'>";
echo "<h3>InnSync Database Update v2</h3><ul class='list-group mb-3'>";
foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "<li class='list-group-item list-group-item-success small'>✅ " . htmlspecialchars($q) . "</li>";
    } else {
        echo "<li class='list-group-item list-group-item-warning small'>⚠️ " . htmlspecialchars($q) . " — " . $conn->error . "</li>";
    }
}
echo "</ul><div class='alert alert-success'>✅ Update complete! <strong>Delete this file now.</strong></div>";
echo "<a href='./' class='btn btn-primary'>← Go to Home</a></body></html>";
