<?php

include "config.php";

if (isset($_POST['action'])) {

    if ($_POST['action'] == "store") {
        $payload = json_decode($_POST['payload']);

        $discount = isset($payload->discount_percent)
            ? max(0, min(100, (float) $payload->discount_percent))
            : 0.00;

        $statement = $conn->prepare(
            "INSERT INTO reservation_types (type_name, description, discount_percent) VALUES (?, ?, ?)"
        );
        $statement->bind_param("ssd", $payload->type_name, $payload->description, $discount);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Reservation type added"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Failed: " . $statement->error]);
        }
    }

    if ($_POST['action'] == "update") {
        $id      = (int) $_POST['id'];
        $payload = json_decode($_POST['payload']);

        $discount = isset($payload->discount_percent)
            ? max(0, min(100, (float) $payload->discount_percent))
            : 0.00;

        $statement = $conn->prepare(
            "UPDATE reservation_types SET type_name=?, description=?, discount_percent=? WHERE reservation_type_id=?"
        );
        $statement->bind_param("ssdi", $payload->type_name, $payload->description, $discount, $id);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Update failed: " . $statement->error]);
        }
    }

    if ($_POST['action'] == "drop") {
        $id = (int) $_POST['id'];

        // Unlink any rooms using this promo before deleting
        $unlink = $conn->prepare("UPDATE rooms SET reservation_type_id = NULL WHERE reservation_type_id = ?");
        $unlink->bind_param("i", $id);
        $unlink->execute();

        $statement = $conn->prepare("DELETE FROM reservation_types WHERE reservation_type_id=?");
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Deleted"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Delete failed: " . $statement->error]);
        }
    }
}

if (isset($_GET['action'])) {

    if ($_GET['action'] == "get") {
        $statement = $conn->prepare(
            "SELECT reservation_type_id, type_name, description, discount_percent
             FROM reservation_types
             ORDER BY type_name"
        );
        $statement->execute();
        $result = $statement->get_result();

        $types = [];
        while ($row = $result->fetch_assoc()) {
            $types[] = $row;
        }

        echo json_encode(["status" => "success", "data" => $types]);
    }
}
