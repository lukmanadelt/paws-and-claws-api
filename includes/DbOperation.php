<?php 

class DbOperation {
	private $con;

	function __construct() {
		require_once dirname(__FILE__) . '/DbConnect.php';
		$db = new DbConnect();
		$this->con = $db->connect();
	}

	// Method to check user availability
	function checkUserAvailability($username) {				
		$stmt = $this->con->prepare("SELECT COUNT(1) FROM users WHERE username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();

		return $count;
	}

	// Method to login user
	function userLogin($username, $password) {
		$hashPassword = md5($password);
		
		$stmt = $this->con->prepare("SELECT id, role_id, username, fullname, status FROM users WHERE username = ? AND password = ?");
		$stmt->bind_param("ss", $username, $hashPassword);
		$stmt->execute();
		$stmt->bind_result($id, $roleId, $username, $fullname, $status);
		$stmt->fetch();

		$user = array();
		$user['id'] = $id;
		$user['role_id'] = $roleId;
		$user['username'] = $username;		
		$user['fullname'] = $fullname;		
		$user['status'] = $status;		

		return $user;
	}
	
	// Method to insert a new user
	function insertUser($role_id, $username, $password, $fullname, $phone, $address, $status) {		
		$hashPassword = md5($password);		
		$now = date('Y-m-d H:i:s');

		$stmt = $this->con->prepare("INSERT INTO users (role_id, username, password, fullname, phone, address, status, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isssisiss", $role_id, $username, $hashPassword, $fullname, $phone, $address, $status, $now, $now);
		
		if ($stmt->execute()) return true;
		
		return false;		
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

	// Method to get all users based on role id
	function getUsers($role_id){
        $stmt = $this->con->prepare("SELECT id, fullname FROM users WHERE role_id = ?");
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $stmt->bind_result($id, $fullname);

        $users = array();

        while ($stmt->fetch()) {
            $temp = array();
            $temp['id'] = $id;
            $temp['fullname'] = $fullname;            

            array_push($users, $temp);
        }

        return $users;
    }

	// Method to get a doctor
    function getDoctor($id){
        $stmt = $this->con->prepare("SELECT id, username, fullname, status FROM users WHERE role_id = 3 AND id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $username, $fullname, $status);
        $stmt->fetch();

        $doctor = array();
		$doctor['id'] = $id;
		$doctor['username'] = $username;
		$doctor['fullname'] = $fullname;
		$doctor['status'] = $status;

		return $doctor;
    }

    // Method to update a doctor
    function updateDoctor($id, $username, $fullname, $status) {        
        $stmt = $this->con->prepare("UPDATE users SET username = ?, fullname = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssii", $username, $fullname, $status, $id);
        if ($stmt->execute()) return true;
        return false;
    }

    // Method to check username availability
	function checkUsernameAvailability($username, $id) {				
		$stmt = $this->con->prepare("SELECT COUNT(1) FROM users WHERE username = ? AND id != ?");
		$stmt->bind_param("si", $username, $id);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();

		return $count;
	}

	// Method to get a customer
    function getCustomer($id){
        $stmt = $this->con->prepare("SELECT id, username, fullname, phone, address, status FROM users WHERE role_id = 2 AND id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $username, $fullname, $phone, $address, $status);
        $stmt->fetch();

        $customer = array();
		$customer['id'] = $id;
		$customer['username'] = $username;
		$customer['fullname'] = $fullname;
		$customer['phone'] = $phone;
		$customer['address'] = $address;
		$customer['status'] = $status;

		return $customer;
    }

    // Method to update a customer
    function updateCustomer($id, $username, $fullname, $phone, $address, $status) {        
        $stmt = $this->con->prepare("UPDATE users SET username = ?, fullname = ?, phone = ?, address = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $username, $fullname, $phone, $address, $status, $id);
        if ($stmt->execute()) return true;
        return false;
    }

    // Method to update profile
    function updateProfile($id, $password) {
    	$hashPassword = md5($password);		        
        $stmt = $this->con->prepare("UPDATE users SET password = ?  WHERE id = ?");
        $stmt->bind_param("si", $hashPassword, $id);
        if ($stmt->execute()) return true;
        return false;
    }	
}
