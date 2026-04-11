<?php
include "config.php";

if (isset($_POST['action'])) {

    // UPDATE USER (admin can edit role too)
    if ($_POST['action'] == "update") {
        $id = $_POST['id'];
        $payload = json_decode($_POST['payload']);
        $admin_id = $_SESSION['user']['user_id'] ?? 0;

        $statement = $conn->prepare("UPDATE users SET fullname=?, email=?, contact_number=?, role=? WHERE user_id=?");
        $statement->bind_param("ssssi",
            $payload->fullname,
            $payload->email,
            $payload->contact_number,
            $payload->role,
            $id
        );

        if ($statement->execute()) {
            // Update session if editing own account
            if ($id == $_SESSION['user']['user_id']) {
                $_SESSION['user']['fullname']       = $payload->fullname;
                $_SESSION['user']['email']          = $payload->email;
                $_SESSION['user']['contact_number'] = $payload->contact_number;
            }
            // Log if admin
            if ($_SESSION['user']['role'] == 'admin') {
                $log = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description) VALUES (?, 'edit_user', ?)");
                $desc = "Edited user #$id";
                $log->bind_param("is", $admin_id, $desc);
                $log->execute();
            }
            echo json_encode(["status" => "success", "message" => "User updated successfully"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Update failed: " . $conn->error]);
        }
    }

    // DELETE USER
    if ($_POST['action'] == "drop") {
        $id = $_POST['id'];
        $admin_id = $_SESSION['user']['user_id'] ?? 0;

        $statement = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            $log = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description) VALUES (?, 'delete_user', ?)");
            $desc = "Deleted user #$id";
            $log->bind_param("is", $admin_id, $desc);
            $log->execute();
            echo json_encode(["status" => "success", "message" => "User deleted"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Delete failed"]);
        }
    }
}

if (isset($_GET['action'])) {

    // GET ALL USERS
    if ($_GET['action'] == "get") {
        $statement = $conn->prepare("SELECT user_id, fullname, email, contact_number, role, status, created_at FROM users ORDER BY created_at DESC");
        $statement->execute();
        $result = $statement->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) $users[] = $row;
        echo json_encode(["status" => "success", "data" => $users]);
    }

    // GET ONE USER
    if ($_GET['action'] == "getOne") {
        $id = $_GET['id'];
        $statement = $conn->prepare("SELECT user_id, fullname, email, contact_number, role, status, created_at FROM users WHERE user_id=?");
        $statement->bind_param("i", $id);
        $statement->execute();
        echo json_encode(["status" => "success", "data" => $statement->get_result()->fetch_assoc()]);
    }
}
