<?php

include "config.php";

if (isset($_POST['action'])) {

    if ($_POST['action'] == "store") {
        $payload = json_decode($_POST['payload']);

        $statement = $conn->prepare("INSERT INTO room_types (type_name, description) VALUES (?, ?)");
        $statement->bind_param("ss", $payload->type_name, $payload->description);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Room type added"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Failed to add"]);
        }
    }

    if ($_POST['action'] == "update") {
        $id = $_POST['id'];
        $payload = json_decode($_POST['payload']);

        $statement = $conn->prepare("UPDATE room_types SET type_name=?, description=? WHERE type_id=?");
        $statement->bind_param("ssi", $payload->type_name, $payload->description, $id);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Room type updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Update failed"]);
        }
    }

    if ($_POST['action'] == "drop") {
        $id = $_POST['id'];

        $statement = $conn->prepare("DELETE FROM room_types WHERE type_id=?");
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Room type deleted"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Delete failed"]);
        }
    }
}

if (isset($_GET['action'])) {

    if ($_GET['action'] == "get") {
        $statement = $conn->prepare("SELECT * FROM room_types ORDER BY type_name");
        $statement->execute();
        $result = $statement->get_result();

        $types = [];
        while ($row = $result->fetch_assoc()) {
            $types[] = $row;
        }

        echo json_encode(["status" => "success", "data" => $types]);
    }
}
