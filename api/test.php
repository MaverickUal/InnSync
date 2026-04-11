<?php
// Raw diagnostic - shows exactly what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== STEP 1: PHP is running ===\n";

include "env.php";
echo "=== STEP 2: env.php loaded ===\n";
echo "HOST: " . DB_HOST . "\n";
echo "USER: " . DB_USER . "\n";
echo "PASS: " . (DB_PASSWORD ? "(set)" : "(empty)") . "\n";
echo "NAME: " . DB_NAME . "\n";
echo "PORT: " . DB_PORT . "\n";

echo "=== STEP 3: Connecting to MySQL ===\n";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    echo "CONNECTION FAILED: " . $conn->connect_error . "\n";
} else {
    echo "CONNECTION OK\n";
    
    echo "=== STEP 4: Querying rooms ===\n";
    $result = $conn->query("SELECT COUNT(*) as cnt FROM rooms");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Rooms count: " . $row['cnt'] . "\n";
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }

    echo "=== STEP 5: Querying room_images ===\n";
    $result2 = $conn->query("SELECT COUNT(*) as cnt FROM room_images");
    if ($result2) {
        $row2 = $result2->fetch_assoc();
        echo "Room images count: " . $row2['cnt'] . "\n";
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
}

echo "=== DONE ===\n";
