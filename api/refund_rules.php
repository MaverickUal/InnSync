<?php

include "config.php";

if (isset($_POST['action'])) {

    if ($_POST['action'] == "store") {
        $payload = json_decode($_POST['payload']);

        $statement = $conn->prepare("INSERT INTO refund_rules (rule_name, days_before, refund_percent, description) VALUES (?, ?, ?, ?)");
        $statement->bind_param("sids", $payload->rule_name, $payload->days_before, $payload->refund_percent, $payload->description);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Refund rule added"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Failed"]);
        }
    }

    if ($_POST['action'] == "update") {
        $id = $_POST['id'];
        $payload = json_decode($_POST['payload']);

        $statement = $conn->prepare("UPDATE refund_rules SET rule_name=?, days_before=?, refund_percent=?, description=? WHERE rule_id=?");
        $statement->bind_param("sidsi", $payload->rule_name, $payload->days_before, $payload->refund_percent, $payload->description, $id);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Update failed"]);
        }
    }

    if ($_POST['action'] == "drop") {
        $id = $_POST['id'];

        $statement = $conn->prepare("DELETE FROM refund_rules WHERE rule_id=?");
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Deleted"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Delete failed"]);
        }
    }
}

if (isset($_GET['action'])) {

    if ($_GET['action'] == "get") {
        $statement = $conn->prepare("SELECT * FROM refund_rules ORDER BY days_before DESC");
        $statement->execute();
        $result = $statement->get_result();

        $rules = [];
        while ($row = $result->fetch_assoc()) {
            $rules[] = $row;
        }

        echo json_encode(["status" => "success", "data" => $rules]);
    }
}
