<?php
include "config.php";

if (isset($_POST['action'])) {
    if ($_POST['action'] == "store") {
        $payload = json_decode($_POST['payload']);

        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $payload->email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            echo json_encode(["status" => "failed", "message" => "Email already registered"]);
            exit;
        }

        $hashedPassword = password_hash($payload->password, PASSWORD_DEFAULT);
        $role   = "customer";
        $status = "approved";
        $contact = $payload->contact_number ?? "";

        $statement = $conn->prepare("INSERT INTO users (fullname, email, contact_number, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $statement->bind_param("ssssss", $payload->fullname, $payload->email, $contact, $hashedPassword, $role, $status);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Account created! You can now sign in."]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Registration failed: " . $conn->error]);
        }
    }
}
