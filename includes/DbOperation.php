<?php 
date_default_timezone_set("Asia/Bangkok");

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

	// Method to handle user login
	function userLogin($username, $password) {
		$hashPassword = md5($password);
		
		$stmt = $this->con->prepare("SELECT id, role_id, username, fullname, status, (SELECT COUNT(1) FROM pets WHERE user_id = users.id) count_pets FROM users WHERE username = ? AND password = ?");
		$stmt->bind_param("ss", $username, $hashPassword);
		$stmt->execute();
		$stmt->bind_result($id, $roleId, $username, $fullname, $status, $count_pets);
		$stmt->fetch();

		$user = array();
		$user['id'] = $id;
		$user['role_id'] = $roleId;
		$user['username'] = $username;		
		$user['fullname'] = $fullname;		
		$user['status'] = $status;		
		$user['count_pets'] = $count_pets;		

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

	// Method to get all users based on role
	function getUsers($role_id) {
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
    function getDoctor($id) {
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
    function getCustomer($id) {
        $stmt = $this->con->prepare("SELECT id, username, fullname, phone, address, status, (SELECT COUNT(1) FROM pets WHERE user_id = users.id) count_pets FROM users WHERE role_id = 2 AND id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $username, $fullname, $phone, $address, $status, $count_pets);
        $stmt->fetch();

        $customer = array();
		$customer['id'] = $id;
		$customer['username'] = $username;
		$customer['fullname'] = $fullname;
		$customer['phone'] = $phone;
		$customer['address'] = $address;
		$customer['status'] = $status;
		$customer['count_pets'] = $count_pets;

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
    function updateProfile($id, $username, $fullname, $phone, $address, $password) {
		$hashPassword = md5($password);
		
		if ($this->getRoleId($id) == 1) {
			$stmt = $this->con->prepare("UPDATE users SET password = ? WHERE id = ?");
			$stmt->bind_param("si", $hashPassword, $id);
		} else {
			$stmt = $this->con->prepare("UPDATE users SET username = ?, fullname = ?, phone = ?, address = ?, password = ?  WHERE id = ?");
			$stmt->bind_param("sssssi", $username, $fullname, $phone, $address, $hashPassword, $id);
		}

        if ($stmt->execute()) return true;
        return false;
	}
	
	// Method to get role id
    function getRoleId($id) {
    	$stmt = $this->con->prepare("SELECT role_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id);
		$stmt->fetch();

		return $id;		        
	}

	// Method to insert a new pet
	function insertPet($pet_category_id, $user_id, $name, $sex, $dob, $breed, $color, $photo) {						
		$now = date('Y-m-d H:i:s');

		$stmt = $this->con->prepare("INSERT INTO pets (pet_category_id, user_id, name, sex, dob, breed, color, photo, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("iissssssss", $pet_category_id, $user_id, $name, $sex, $dob, $breed, $color, $photo, $now, $now);
		
		if ($stmt->execute()) return true;
		
		return false;		
	}

	// Method to get pets based on customer id
	function getPets($user_id) {
		$stmt = $this->con->prepare("SELECT id, pet_category_id, name FROM pets WHERE user_id = ?");
		$stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($id, $pet_category_id, $name);

        $pets = array();

        while ($stmt->fetch()) {
            $temp = array();
            $temp['id'] = $id;
			$temp['pet_category_id'] = $pet_category_id;			
			$temp['name'] = $name;			

            array_push($pets, $temp);
        }

        return $pets;
	}
	
	// Method to get a pet
    function getPet($id) {
        $stmt = $this->con->prepare("SELECT pet_category_id, name, sex, dob, breed, color, photo FROM pets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($pet_category_id, $name, $sex, $dob, $breed, $color, $photo);
        $stmt->fetch();

        $pet = array();
		$pet['pet_category_id'] = $pet_category_id;
		$pet['name'] = $name;
		$pet['sex'] = $sex;
		$pet['dob'] = $dob;
		$pet['breed'] = $breed;
		$pet['color'] = $color;
		$pet['photo'] = $photo;

		return $pet;
	}
	
	// Method to update a pet
    function updatePet($id, $pet_category_id, $name, $sex, $dob, $breed, $color, $photo) {
        $stmt = $this->con->prepare("UPDATE pets SET pet_category_id = ?, name = ?, sex = ?, dob = ?, breed = ?, color = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("issssssi", $pet_category_id, $name, $sex, $dob, $breed, $color, $photo, $id);
        if ($stmt->execute()) return true;
        return false;
	}
	
	// Method to get vaccine recommendation based on customer id
	function getVaccineRecommendation($user_id) {
		$stmt = $this->con->prepare("
			SELECT a.name AS name, b.name AS vaccine, b.period AS period, b.description AS description,
				(
					CASE WHEN 
						(SELECT COUNT(c.pet_id)
						FROM examinations c
						INNER JOIN examination_details d ON c.id = d.examination_id
						WHERE c.pet_id = a.id
						AND d.vaccination_medical_id = b.id
						AND next_date IS NULL) > 0 			
					THEN 
						1 
					ELSE 
						0 
					END
				) AS completed
			FROM pets a
			INNER JOIN vaccination_medicals b ON a.pet_category_id = b.pet_category_id
			WHERE a.user_id = ?
			AND b.type = 'v'
			ORDER BY a.id, b.period
		");
		$stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($name, $vaccine, $period, $description, $completed);

		$pets = array();		

        while ($stmt->fetch()) {
			$temp = array();
			$temp['name'] = $name;
			$temp['vaccine'] = $vaccine;
			$temp['period'] = $period;
			$temp['description'] = $description;
			$temp['completed'] = $completed;

            array_push($pets, $temp);
        }

        return $pets;
	}

	// Method to get all customers who have pets
	function getCustomerHavePets() {
        $stmt = $this->con->prepare("
			SELECT a.id, a.fullname 
			FROM users a 
			INNER JOIN pets b ON a.id = b.user_id 
			WHERE a.role_id = 2 
			GROUP BY a.id
		");     
        $stmt->execute();
        $stmt->bind_result($id, $fullname);

        $customers = array();

        while ($stmt->fetch()) {
            $temp = array();
            $temp['id'] = $id;
            $temp['fullname'] = $fullname;            

            array_push($customers, $temp);
        }

        return $customers;
	}
		
	// Method to all vaccines based on pet
	function getVaccines($pet_id) {
		$stmt = $this->con->prepare("
			SELECT id, name, current
			FROM
			(
				SELECT b.id AS id, b.name AS name, b.period AS period,
					(
						CASE WHEN 
							(SELECT COUNT(c.pet_id)
							FROM examinations c
							INNER JOIN examination_details d ON c.id = d.examination_id
							WHERE c.pet_id = a.id
							AND d.vaccination_medical_id = b.id
							AND next_date IS NULL) > 0 			
						THEN 
							1 
						ELSE 
							0 
						END
					) AS completed,
					(
						CASE WHEN 
							(SELECT COUNT(c.pet_id)
							FROM examinations c
							INNER JOIN examination_details d ON c.id = d.examination_id
							WHERE c.pet_id = a.id
							AND d.vaccination_medical_id = b.id
							AND next_date IS NOT NULL) > 0 			
						THEN 
							1 
						ELSE 
							0 
						END
					) AS current
				FROM pets a
				INNER JOIN vaccination_medicals b ON a.pet_category_id = b.pet_category_id
				WHERE a.id = ?
				AND b.type = 'v'	
			) AS c
			WHERE completed = 0
			ORDER BY period ASC
		");
		$stmt->bind_param("i", $pet_id);
        $stmt->execute();
        $stmt->bind_result($id, $name, $current);

        $vaccines = array();

        while ($stmt->fetch()) {
			$temp = array();
						
            $temp['id'] = $id;
			$temp['name'] = $name;			
			$temp['current'] = (in_array($id, array(1, 2, 15))) ? 1 : $current;

            array_push($vaccines, $temp);
        }

        return $vaccines;
	}
	
	// Method to get all medicals
	function getMedicals() {
        $stmt = $this->con->prepare("SELECT id, name FROM vaccination_medicals WHERE type = 'm'");
        $stmt->execute();
        $stmt->bind_result($id, $name);

        $medicals = array();

        while ($stmt->fetch()) {
            $temp = array();
            $temp['id'] = $id;
            $temp['name'] = $name;

            array_push($medicals, $temp);
        }

        return $medicals;
	}
	
	// Method to get pet examinations history
	function getExaminations($pet_id) {
		$stmt = $this->con->prepare("SELECT given_date FROM examinations WHERE pet_id = ? GROUP BY given_date ORDER BY given_date ASC");
		$stmt->bind_param("i", $pet_id);
        $stmt->execute();
        $stmt->bind_result($given_date);

        $examinations = array();

        while ($stmt->fetch()) {
            $temp = array();
            $temp['given_date'] = $given_date;

            array_push($examinations, $temp);
        }

        return $examinations;
	}

	// Method to get examination based on pet, period, and examination type
	function getExamination($pet_id, $period, $vaccination_medical_type) {
		$stmt = $this->con->prepare("SELECT id, weight, temperature, size, due_date, given_date FROM examinations WHERE pet_id = ? AND given_date = ? AND vaccination_medical_type = ?");
		$stmt->bind_param("iss", $pet_id, $period, $vaccination_medical_type);
        $stmt->execute();
        $stmt->bind_result($id, $weight, $temperature, $size, $due_date, $given_date);
        $stmt->fetch();

		$examination = array();
		$examination['id'] = $id;
		$examination['weight'] = $weight;
		$examination['temperature'] = $temperature;
		$examination['size'] = $size;
		$examination['due_date'] = ($due_date == null) ? "-" : $due_date;
		$examination['given_date'] = $given_date;		

		return $examination;
	}
	
	// Method to get examination detail based on pet, period, and examination type
	function getExaminationDetails($pet_id, $period, $vaccination_medical_type) {
		$stmt = $this->con->prepare("
			SELECT a.name, b.remark, b.medicine
			FROM vaccination_medicals a
			INNER JOIN examination_details b ON a.id = b.vaccination_medical_id
			INNER JOIN examinations c ON b.examination_id = c.id
			WHERE c.pet_id = ?
			AND c.given_date = ?
			AND c.vaccination_medical_type = ?
			AND b.next_date IS NULL
		");
		$stmt->bind_param("iss", $pet_id, $period, $vaccination_medical_type);
        $stmt->execute();
        $stmt->bind_result($name, $remark, $medicine);

		$examination_details = array();		

        while ($stmt->fetch()) {
			$temp = array();
			$temp['name'] = $name;			
			$temp['remark'] = ($remark == null) ? "Tidak ada" : $remark;
			$temp['medicine'] = ($medicine == null) ? "-" : $medicine;						

            array_push($examination_details, $temp);
        }

        return $examination_details;
	}

	// Method to get vaccines report based on period
	function getReportVaccines($period_start, $period_end) {
		$stmt = $this->con->prepare("
			SELECT a.given_date AS date, e.name AS pet_category, c.name AS vaccine, COUNT(c.id) AS amount
			FROM examinations a
			INNER JOIN examination_details b ON a.id = b.examination_id
			INNER JOIN vaccination_medicals c ON c.id = b.vaccination_medical_id
			INNER JOIN pets d ON d.id = a.pet_id
			INNER JOIN pet_categories e ON e.id = d.pet_category_id
			WHERE a.vaccination_medical_type = 'v'
			AND a.given_date BETWEEN ? AND ?
			AND b.next_date IS NULL
			GROUP BY a.given_date, c.id
		");
        $stmt->bind_param("ss", $period_start, $period_end);
        $stmt->execute();
        $stmt->bind_result($date, $pet_category, $vaccine, $amount);

        $vaccines = array();

        while ($stmt->fetch()) {
            $temp = array();
			$temp['date'] = $date;
			
			switch ($pet_category) {
				case "Dogs":
					$pet_category = "Anjing";
					break;
				case "Cats":
					$pet_category = "Kucing";
					break;
			}

			$temp['pet_category'] = $pet_category;
			$temp['vaccine'] = $vaccine;
			$temp['amount'] = $amount;

            array_push($vaccines, $temp);
        }

        return $vaccines;
	}
	
	// Method to get pets report based on period
	function getReportPets($period_start, $period_end) {
		$stmt = $this->con->prepare("
			SELECT a.given_date AS date, c.name AS pet_category, COUNT(DISTINCT(b.id)) AS amount
			FROM examinations a
			INNER JOIN pets b ON b.id = a.pet_id
			INNER JOIN pet_categories c ON c.id = b.pet_category_id
			WHERE a.given_date BETWEEN ? AND ?
			GROUP BY a.given_date, c.id
		");
        $stmt->bind_param("ss", $period_start, $period_end);
        $stmt->execute();
        $stmt->bind_result($date, $pet_category, $amount);

        $pets = array();

        while ($stmt->fetch()) {
            $temp = array();
			$temp['date'] = $date;
			
			switch ($pet_category) {
				case "Dogs":
					$pet_category = "Anjing";
					break;
				case "Cats":
					$pet_category = "Kucing";
					break;
			}
			
			$temp['pet_category'] = $pet_category;			
			$temp['amount'] = $amount;

            array_push($pets, $temp);
        }

        return $pets;
	}
	
	// Method to get notifications based on user_id
	function getNotifications($user_id) {
		$stmt = $this->con->prepare("SELECT description FROM notifications WHERE user_id = ? ORDER BY id DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($description);

        $notifications = array();

        while ($stmt->fetch()) {
            $temp = array();
			$temp['description'] = $description;
						
            array_push($notifications, $temp);
        }

        return $notifications;
	}
	
	// Method to get pet next examinations
	function getNextExaminations($pet_id) {
		$stmt = $this->con->prepare("
			SELECT c.`name`, b.next_date
			FROM examinations a
			INNER JOIN examination_details b ON a.id = b.examination_id
			INNER JOIN vaccination_medicals c ON b.vaccination_medical_id = c.id
			WHERE a.pet_id = ?
			AND a.vaccination_medical_type = 'v'
			AND a.given_date = (SELECT MAX(given_date) FROM examinations WHERE pet_id = a.pet_id AND vaccination_medical_type = a.vaccination_medical_type)
			AND a.id = (SELECT MAX(id) FROM examinations WHERE pet_id = a.pet_id AND vaccination_medical_type = a.vaccination_medical_type AND given_date = a.given_date)
			AND b.next_date IS NOT NULL
			ORDER BY b.next_date ASC
		");
		$stmt->bind_param("i", $pet_id);
        $stmt->execute();
        $stmt->bind_result($name, $next_date);

        $examination_details = array();

        while ($stmt->fetch()) {
            $temp = array();
			$temp['name'] = $name;
			$temp['next_date'] = $next_date;

            array_push($examination_details, $temp);
        }

        return $examination_details;
	}

	// Method to insert a new examination
	function insertExamination($id, $pet_id, $vaccination_medical_type, $weight, $temperature, $size, $due_date, $given_date) {						
		$now = date('Y-m-d');

		$stmt = $this->con->prepare("INSERT INTO examinations (doctor_id, pet_id, vaccination_medical_type, weight, temperature, size, due_date, given_date, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("iisdddssss", $id, $pet_id, $vaccination_medical_type, $weight, $temperature, $size, $due_date, $given_date, $now, $now);
		
		if ($stmt->execute()) return $this->con->insert_id;
		
		return false;		
	}

	// Method to insert a new examination detail
	function insertExaminationDetail($examination_id, $vaccination_medical_id, $next_date, $remark, $medicine) {						
		$now = date('Y-m-d');
		$next_date = empty($next_date) ? null : $next_date;
		$remark = empty($remark) ? null : $remark;
		$medicine = empty($medicine) ? null : $medicine;

		$stmt = $this->con->prepare("INSERT INTO examination_details (examination_id, vaccination_medical_id, next_date, remark, medicine, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("iisssss", $examination_id, $vaccination_medical_id, $next_date, $remark, $medicine, $now, $now);
		
		if ($stmt->execute()) return true;
		
		return false;		
	}	

	// Method to insert a new notification
	function insertNotification($pet_id, $vaccination_medical_id, $next_date) {
		$now = date('Y-m-d');		
		$pet = array();
		$pet = $this->getPetInfo($pet_id);
		$vaccine = $this->getVaccineInfo($vaccination_medical_id);
		$description = $pet['name'] . " disarankan untuk melakukan pemeriksaan vaksin " . $vaccine . " pada tanggal " . $next_date;
				
		$stmt = $this->con->prepare("INSERT INTO notifications (user_id, description, created) VALUES (?, ?, ?)");
		$stmt->bind_param("iss", $pet['user_id'], $description, $now);
		
		if ($stmt->execute()) return true;
		
		return false;		
	}

	// Method to get pet information
	function getPetInfo($pet_id) {
		$stmt = $this->con->prepare("SELECT user_id, name FROM pets WHERE id = ?");
        $stmt->bind_param("i", $pet_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $name);
		$stmt->fetch();

		$pet = array();
		$pet['user_id'] = $user_id;
		$pet['name'] = $name;

		return $pet;
	}

	// Method to get vaccine information
	function getVaccineInfo($vaccination_medical_id) {
		$stmt = $this->con->prepare("SELECT name FROM vaccination_medicals WHERE id = ?");
        $stmt->bind_param("i", $vaccination_medical_id);
        $stmt->execute();
        $stmt->bind_result($name);
		$stmt->fetch();
				
		return $name;
	}

	// Method to delete a pet
    function deletePet($id) {
        $stmt = $this->con->prepare("DELETE FROM pets WHERE id = ?");
		$stmt->bind_param("i", $id);
		
		if ($stmt->execute()) return true;
		
        return false;
	}
}
