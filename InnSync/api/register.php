<?php

include "config.php";

if (isset($_POST['action'])) {
	if ($_POST['action'] == "store") {
		$payload = json_decode($_POST['payload']);
		$hashedPassword = password_hash($payload->password, PASSWORD_DEFAULT);
		
		//s string
		//i integer
		//d double
		$statement = $conn->prepare("INSERT INTO accounts(email, password) VALUES(?,?)");
		$statement->bind_param("ss", $payload->email, $hashedPassword);
		
		if ($statement->execute()) {
			echo json_encode([
				"status" => "success",
				"message" => "Successfully registerd"
			]);
		} else {
			echo json_encode([
				"status" => "failed",
				"message" => "Registration Failed"
			]);
		}
	}
	
	if ($_POST['action'] == "update") {
		$id = $_POST['id'];
		$payload = json_decode($_POST['payload']);
	}
	
	if ($_POST['action'] == "drop") {
		$id = $_POST['id'];

	}
}

if (isset($_GET['action'])) {
	if ($_GET['action'] == "get") {
		//select * from
	}
	
	if ($_GET['action'] == "getOne") {
		//select * from where id = 1;
	}
}