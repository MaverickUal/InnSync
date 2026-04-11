<?php

include "env.php";

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die(json_encode(["status" => "failed", "message" => "DB Connection failed: " . $conn->connect_error]));
}

session_start();
