<?php

include "config.php";

if (isset($_POST['action'])) {

    // LOGIN
    if ($_POST['action'] == "postOne") {
        $payload = json_decode($_POST['payload']);

        $statement = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $statement->bind_param("s", $payload->email);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user['status'] == 'blacklist') {
                echo json_encode([
                    "status"  => "failed",
                    "code"    => "blacklisted",
                    "message" => "Account disabled. Please contact support."
                ]);
                exit;
            }

            if (password_verify($payload->password, $user['password'])) {
                $_SESSION['user'] = $user;
                echo json_encode([
                    "status" => "success",
                    "message" => "Successfully logged in",
                    "role" => $user['role']
                ]);
            } else {
                echo json_encode([
                    "status" => "failed",
                    "message" => "Invalid password"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "failed",
                "message" => "Account does not exist"
            ]);
        }
    }
}
