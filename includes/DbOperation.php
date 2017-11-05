<?php 

class DbOperation {
	private $con;

	function __construct() {
		require_once dirname(__FILE__) . '/DbConnect.php';
		$db = new DbConnect();
		$this->con = $db->connect();
	}

	// Method to create a new user
	function registerUser($role_id, $username, $password, $fullname, $phone, $address) {
		if (!$this->isUsernameExist($username)) {
			$hashPassword = md5($password);
			$status = 1;			
			$now = date('Y-m-d H:i:s');

			$stmt = $this->con->prepare("INSERT INTO users (role_id, username, password, fullname, phone, address, status, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("isssisiss", $role_id, $username, $hashPassword, $fullname, $phone, $address, $status, $now, $now);
			
			if ($stmt->execute()) return USER_CREATED;
			
			return USER_CREATION_FAILED;
		}

		return USER_EXIST;
	}

	// Method for user login
	function userLogin($username, $password) {
		$hashPassword = md5($password);

		$stmt = $this->con->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
		$stmt->bind_param("ss", $username, $hashPassword);
		$stmt->execute();
		$stmt->store_result();

		return $stmt->num_rows > 0;
	}

	// Method to get user by username
	function getUserByUsername($username) {
		$stmt = $this->con->prepare("SELECT id, role_id, username FROM users WHERE username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$stmt->bind_result($id, $role_id, $username);
		$stmt->fetch();

		$user = array();
		$user['id'] = $id;
		$user['role_id'] = $role_id;
		$user['username'] = $username;		

		return $user;
	}
	
	// Method to check if username already exist
	function isUsernameExist($username) {
		$stmt = $this->con->prepare("SELECT id FROM users WHERE username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$stmt->store_result();

		return $stmt->num_rows > 0;
	}
}
