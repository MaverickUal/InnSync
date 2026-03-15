<?php

include "config.php";

if (isset($_POST['action'])) {
	if ($_POST['action'] == "store") {
		//do insert
	}
	
	if ($_POST['action'] == "update") {
		$id = $_POST['id'];
		$payload = json_decode($_POST['payload']);
	}
	
	if ($_POST['action'] == "drop") {
		$id = $_POST['id'];

	}
	
	if ($_POST['action'] == "postOne") {
		$payload = json_decode($_POST['payload']);
		
		$statement = $conn->prepare("SELECT * from accounts where email = ?");
		$statement->bind_param("s", $payload->email);
		$statement->execute();
		$result = $statement->get_result();
		
		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();
			
			if (password_verify($payload->password, $user['password'])) {
				$_SESSION['user'] = $user;
				echo json_encode([
					"status" => "success",
					"message" => "Succesfully logged in"
				]);
			} else {
				echo json_encode([
					"status" => "failed",
					"message" => "Invalid Password"
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

if (isset($_GET['action'])) {
	if ($_GET['action'] == "get") {
		//select * from
	}
	
	if ($_GET['action'] == "getOne") {
		//select * from where id = 1;
	}
}