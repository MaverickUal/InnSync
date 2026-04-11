<?php
// =============================================
// InnSync - Admin Password Fix
// =============================================
// INSTRUCTIONS:
// 1. Put this file in: C:/xampp/htdocs/innsync/fix_admin.php
// 2. Open browser: http://localhost/innsync/fix_admin.php
// 3. It will fix the admin password to: admin123
// 4. DELETE this file after running it!
// =============================================

include "api/config.php";

$newPassword = "admin123";
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@innsync.com' AND role = 'admin'");
$stmt->bind_param("s", $hashed);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<h2 style='color:green'>✅ Admin password fixed!</h2>";
        echo "<p>Email: <strong>admin@innsync.com</strong></p>";
        echo "<p>Password: <strong>admin123</strong></p>";
        echo "<p><a href='./'>Go to Login</a></p>";
        echo "<p style='color:red'><strong>⚠️ DELETE this file (fix_admin.php) now!</strong></p>";
    } else {
        echo "<h2 style='color:orange'>⚠️ Admin user not found.</h2>";
        echo "<p>Creating admin account now...</p>";

        $insertStmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, status) VALUES (?, ?, ?, 'admin', 'approved')");
        $fullname = "Admin InnSync";
        $email = "admin@innsync.com";
        $insertStmt->bind_param("sss", $fullname, $email, $hashed);

        if ($insertStmt->execute()) {
            echo "<h2 style='color:green'>✅ Admin account created!</h2>";
            echo "<p>Email: <strong>admin@innsync.com</strong></p>";
            echo "<p>Password: <strong>admin123</strong></p>";
            echo "<p><a href='./'>Go to Login</a></p>";
            echo "<p style='color:red'><strong>⚠️ DELETE this file (fix_admin.php) now!</strong></p>";
        } else {
            echo "<h2 style='color:red'>❌ Failed: " . $conn->error . "</h2>";
        }
    }
} else {
    echo "<h2 style='color:red'>❌ Error: " . $conn->error . "</h2>";
}
?>
