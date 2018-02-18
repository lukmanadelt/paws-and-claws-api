<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

require '../vendor/autoload.php';
require_once '../includes/DbOperation.php';

// Creating a new app with the config to show errors
$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => true
	]
]);

$container = $app->getContainer();
$container['upload_directory'] = __DIR__ . '/uploads';

// Method to check parameters
function isTheseParametersAvailable($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;
 
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        $response = array();
        $response['success'] = false;
        $response['message'] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echo json_encode($response);
        return false;
    }

    return true;
}

// Method to handle upload file
function moveUploadedFile($directory, UploadedFile $uploadedFile) {
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}
  
// User login route
$app->post('/login', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('username', 'password'))) {
        $requestData = $request->getParsedBody();
        $username = $requestData['username'];
        $password = $requestData['password'];
 
        $db = new DbOperation();

        $responseData = array();

        if ($db->checkUserAvailability($username) > 0) {
        	$user = $db->userLogin($username, $password);

        	if (isset($user['id'])) {
	        	if ($user['status'] == 1) {                    
	        		$responseData['success'] = true;
	        		$responseData['message'] = "Anda berhasil masuk";
	            	$responseData['user'] = $user;
	        	} else {
	        		$responseData['success'] = false;
	            	$responseData['message'] = "Akun anda tidak aktif. Silakan hubungi administrator.";
	        	}
	        } else {
				$responseData['success'] = false;
	            $responseData['message'] = "Nama Pengguna atau Kata Sandi salah";	        	
	        }      	            
        } else {
            $responseData['success'] = false;
            $responseData['message'] = "Akun anda belum terdaftar";
        }
 
        $response->getBody()->write(json_encode($responseData));
    }
});

// Getting all doctors
$app->get('/doctors', function (Request $request, Response $response) {
    $db = new DbOperation();
    $doctors = $db->getUsers(3);
    $response->getBody()->write(json_encode(array("doctors" => $doctors)));
});

// Getting a doctor
$app->get('/doctors/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $db = new DbOperation();
    $doctor = $db->getDoctor($id);
    $response->getBody()->write(json_encode(array("doctor" => $doctor)));
});

// Updating a doctor
$app->post('/doctors/update/{id}', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('username', 'fullname', 'status'))) {
        $id = $request->getAttribute('id');
 
        $requestData = $request->getParsedBody();
 
        $username = $requestData['username'];
        $fullname = $requestData['fullname'];
        $status = $requestData['status'];        
 
        $db = new DbOperation();
        $responseData = array();

        if ($db->checkUsernameAvailability($username, $id) > 0) {
        	$responseData['success'] = false;
            $responseData['message'] = 'Nama Pengguna sudah ada. Silahkan gunakan Nama Pengguna lain.';
        } else {
	        if ($db->updateDoctor($id, $username, $fullname, $status)) {
	            $responseData['success'] = true;
	            $responseData['message'] = 'Data berhasil diubah';            
	        } else {
	            $responseData['success'] = false;
	            $responseData['message'] = 'Data gagal diubah';
	        }
	    }
 
        $response->getBody()->write(json_encode($responseData));
    }
});

// Insert a new doctor
$app->post('/doctors/insert', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('username', 'password', 'fullname'))) {
        $requestData = $request->getParsedBody();
        $role_id = 3;
        $username = $requestData['username'];        
        $password = $requestData['password'];
        $fullname = $requestData['fullname'];
        $status = 1;        

        $db = new DbOperation();
        $responseData = array();
         
        if ($db->checkUserAvailability($username) == 0) {
        	if ($db->insertUser($role_id, $username, $password, $fullname, null, null, $status)) {
        		$responseData['success'] = true;
            	$responseData['message'] = 'Data berhasil dimasukkan';            
        	} else {
        		$responseData['success'] = false;
            	$responseData['message'] = 'Data gagal dimasukkan';            
        	}            
        } else {
        	$responseData['success'] = false;
            $responseData['message'] = 'Nama Pengguna sudah ada. Silahkan gunakan Nama Pengguna lain.';            
        }
         
        $response->getBody()->write(json_encode($responseData));
    }
});

// Getting all customers
$app->get('/customers', function (Request $request, Response $response) {
    $db = new DbOperation();
    $customers = $db->getUsers(2);
    $response->getBody()->write(json_encode(array("customers" => $customers)));
});

// Insert a new customer
$app->post('/customers/insert', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('username', 'password', 'fullname', 'phone', 'address'))) {
        $requestData = $request->getParsedBody();
        $role_id = 2;
        $username = $requestData['username'];        
        $password = $requestData['password'];
        $fullname = $requestData['fullname'];
        $phone = $requestData['phone'];
        $address = $requestData['address'];
        $status = 1;        

        $db = new DbOperation();
        $responseData = array();
         
        if ($db->checkUserAvailability($username) == 0) {
        	if ($db->insertUser($role_id, $username, $password, $fullname, $phone, $address, $status)) {
        		$responseData['success'] = true;
            	$responseData['message'] = 'Data berhasil dimasukkan';            
        	} else {
        		$responseData['success'] = false;
            	$responseData['message'] = 'Data gagal dimasukkan';            
        	}            
        } else {
        	$responseData['success'] = false;
            $responseData['message'] = 'Nama Pengguna sudah ada. Silahkan gunakan Nama Pengguna lain.';            
        }
         
        $response->getBody()->write(json_encode($responseData));
    }
});

// Getting a customer
$app->get('/customers/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $db = new DbOperation();
    $customer = $db->getCustomer($id);
    $response->getBody()->write(json_encode(array("customer" => $customer)));
});

// Updating a customer
$app->post('/customers/update/{id}', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('username', 'fullname', 'phone', 'address', 'status'))) {
        $id = $request->getAttribute('id');
 
        $requestData = $request->getParsedBody();
 
        $username = $requestData['username'];
        $fullname = $requestData['fullname'];
        $phone = $requestData['phone'];
        $address = $requestData['address'];
        $status = $requestData['status'];        
 
        $db = new DbOperation();
        $responseData = array();

        if ($db->checkUsernameAvailability($username, $id) > 0) {
        	$responseData['success'] = false;
            $responseData['message'] = 'Nama Pengguna sudah ada. Silahkan gunakan Nama Pengguna lain.';
        } else {
	        if ($db->updateCustomer($id, $username, $fullname, $phone, $address, $status)) {
	            $responseData['success'] = true;
	            $responseData['message'] = 'Data berhasil diubah';            
	        } else {
	            $responseData['success'] = false;
	            $responseData['message'] = 'Data gagal diubah';
	        }
	    }
 
        $response->getBody()->write(json_encode($responseData));
    }
});

// Updating a profile
$app->post('/profiles/update/{id}', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('password'))) {
        $id = $request->getAttribute('id');
 
        $requestData = $request->getParsedBody();
 
        $username = $requestData['username'];
        $fullname = $requestData['fullname'];
        $phone = $requestData['phone'];
        $address = $requestData['address'];
        $password = $requestData['password'];
 
        $db = new DbOperation();
        $responseData = array();
        
        if ($db->updateProfile($id, $username, $fullname, $phone, $address, $password)) {
            $responseData['success'] = true;
            $responseData['message'] = 'Data berhasil diubah';            
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Data gagal diubah';
        }
	     
        $response->getBody()->write(json_encode($responseData));
    }
});
 
// Insert a new pet
$app->post('/pets/insert', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('pet_category_id', 'user_id', 'name', 'sex', 'dob', 'breed', 'color'))) {
        $requestData = $request->getParsedBody();        
        $pet_category_id = $requestData['pet_category_id'];
        $user_id = $requestData['user_id'];
        $name = $requestData['name'];
        $sex = $requestData['sex'];
        $dob = $requestData['dob'];        
        $breed = $requestData['breed'];
        $color = $requestData['color'];
        $photo = $requestData['photo'];
        
        $db = new DbOperation();
        $responseData = array();
                 
        if ($db->insertPet($pet_category_id, $user_id, $name, $sex, $dob, $breed, $color, $photo)) {            
            $responseData['success'] = true;
            $responseData['message'] = 'Data berhasil dimasukkan';            
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Data gagal dimasukkan';
        }
                 
        $response->getBody()->write(json_encode($responseData));
    }
});

// Upload a new pet image
$app->post('/pets/upload', function (Request $request, Response $response) {
    $directory = $this->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['file'];

    $responseData = array();

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);

        if ($filename != null) {
            $responseData['success'] = true;
            $responseData['message'] = 'Foto berhasil diunggah';    
            $responseData['photo'] = $filename;
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Foto gagal diunggah';                
        }
    }

    $response->getBody()->write(json_encode($responseData));    
});

// Getting all pets based on customer
$app->get('/customer/pets/{user_id}', function (Request $request, Response $response) {
    $user_id = $request->getAttribute('user_id');
    $db = new DbOperation();
    $pets = $db->getPets($user_id);
    $response->getBody()->write(json_encode(array("pets" => $pets)));
});

// Getting a pet
$app->get('/pets/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $db = new DbOperation();
    $pet = $db->getPet($id);
    $response->getBody()->write(json_encode(array("pet" => $pet)));
});

// Updating a pet
$app->post('/pets/update/{id}', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('pet_category_id', 'name', 'sex', 'dob', 'breed', 'color'))) {
        $id = $request->getAttribute('id');

        $requestData = $request->getParsedBody();        
        
        $pet_category_id = $requestData['pet_category_id'];        
        $name = $requestData['name'];
        $sex = $requestData['sex'];
        $dob = $requestData['dob'];        
        $breed = $requestData['breed'];
        $color = $requestData['color'];
        $photo = $requestData['photo'];
        
        $db = new DbOperation();
        $responseData = array();
                 
        if ($db->updatePet($id, $pet_category_id, $name, $sex, $dob, $breed, $color, $photo)) {            
            $responseData['success'] = true;
            $responseData['message'] = 'Data berhasil diubah';            
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Data gagal diubah';
        }
                 
        $response->getBody()->write(json_encode($responseData));
    }
});

// Getting a vaccine recommendation based on customer 
$app->get('/pets/vaccine_recommendation/{user_id}', function (Request $request, Response $response) {
    $user_id = $request->getAttribute('user_id');
    $db = new DbOperation();
    $pets = $db->getVaccineRecommendation($user_id);
    $response->getBody()->write(json_encode(array("pets" => $pets)));
});

// Getting all customers who have pets
$app->get('/customer/havePets', function (Request $request, Response $response) {
    $db = new DbOperation();
    $customers = $db->getCustomerHavePets();
    $response->getBody()->write(json_encode(array("customers" => $customers)));
});

// Getting all vaccines each pet
$app->get('/vaccines/{pet_id}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $db = new DbOperation();
    $vaccines = $db->getVaccines($pet_id);
    $response->getBody()->write(json_encode(array("vaccines" => $vaccines)));
});

// Getting all medicals
$app->get('/medicals', function (Request $request, Response $response) {
    $db = new DbOperation();
    $medicals = $db->getMedicals();
    $response->getBody()->write(json_encode(array("medicals" => $medicals)));
});

// Getting pet examinations history
$app->get('/examinations/{pet_id}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $db = new DbOperation();
    $examinations = $db->getExaminations($pet_id);
    $response->getBody()->write(json_encode(array("examinations" => $examinations)));
});

// Getting pet vaccine examination based on period
$app->get('/examinations/vaccine/{pet_id}/{period}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $period = $request->getAttribute('period');
    $db = new DbOperation();
    $examination = $db->getExamination($pet_id, $period, 'v');
    $response->getBody()->write(json_encode(array("examination" => $examination)));
});

// Getting pet medical examination based on period
$app->get('/examinations/medical/{pet_id}/{period}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $period = $request->getAttribute('period');
    $db = new DbOperation();
    $examination = $db->getExamination($pet_id, $period, 'm');
    $response->getBody()->write(json_encode(array("examination" => $examination)));
});

// Getting pet vaccine examination detail based on period
$app->get('/examination_details/vaccine/{pet_id}/{period}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $period = $request->getAttribute('period');
    $db = new DbOperation();
    $examination_details = $db->getExaminationDetails($pet_id, $period, 'v');
    $response->getBody()->write(json_encode(array("examination_details" => $examination_details)));
});

// Getting pet medical examination detail based on period
$app->get('/examination_details/medical/{pet_id}/{period}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $period = $request->getAttribute('period');
    $db = new DbOperation();
    $examination_details = $db->getExaminationDetails($pet_id, $period, 'm');
    $response->getBody()->write(json_encode(array("examination_details" => $examination_details)));
});

// Getting vaccines report
$app->get('/reports/vaccines/{period_start}/{period_end}', function (Request $request, Response $response) {
    $period_start = $request->getAttribute('period_start');
    $period_end = $request->getAttribute('period_end');
    $db = new DbOperation();
    $vaccines = $db->getReportVaccines($period_start, $period_end);
    $response->getBody()->write(json_encode(array("vaccines" => $vaccines)));
});

// Getting pets report
$app->get('/reports/pets/{period_start}/{period_end}', function (Request $request, Response $response) {
    $period_start = $request->getAttribute('period_start');
    $period_end = $request->getAttribute('period_end');
    $db = new DbOperation();
    $pets = $db->getReportPets($period_start, $period_end);
    $response->getBody()->write(json_encode(array("pets" => $pets)));
});

// Getting notifications
$app->get('/notifications/{user_id}', function (Request $request, Response $response) {
    $user_id = $request->getAttribute('user_id');
    $db = new DbOperation();
    $notifications = $db->getNotifications($user_id);
    $response->getBody()->write(json_encode(array("notifications" => $notifications)));
});

// Getting pet next examinations 
$app->get('/examinations/next/{pet_id}', function (Request $request, Response $response) {
    $pet_id = $request->getAttribute('pet_id');
    $db = new DbOperation();
    $examination_details = $db->getNextExaminations($pet_id);
    $response->getBody()->write(json_encode(array("examination_details" => $examination_details)));
});

// Insert a new vaccine examination
$app->post('/examinations/vaccine/insert', function (Request $request, Response $response) {    
    if (isTheseParametersAvailable(array('id', 'pet_id', 'weight', 'temperature', 'given_date', 'details'))) {
        $requestData = $request->getParsedBody();        
        $id = $requestData['id'];
        $pet_id = $requestData['pet_id'];
        $weight = $requestData['weight'];
        $temperature = $requestData['temperature'];
        $due_date = $requestData['due_date'];
        $given_date = $requestData['given_date'];
        $details = $requestData['details'];
        $examination_details = json_decode($details, true);
            
        $db = new DbOperation();
        $responseData = array();
                    
        if ($examination_id = $db->insertExamination($id, $pet_id, 'v', $weight, $temperature, 0, $due_date, $given_date)) {            
            try {            
                foreach ($examination_details as $detail) {
                    if ($db->insertExaminationDetail($examination_id, $detail['vaccination_medical_id'], $detail['next_date'], $detail['remark'], null)) {
                        if (!empty($detail['next_date'])) {
                            if (!$db->insertNotification($pet_id, $detail['vaccination_medical_id'], $detail['next_date'])) {
                                throw new Exception("Data detail pemeriksaan gagal dimasukkan");
                            }
                        }
                    } else {
                        throw new Exception("Data detail pemeriksaan gagal dimasukkan");
                    }
                }

                $responseData['success'] = true;
                $responseData['message'] = 'Data berhasil dimasukkan';
            } catch (Exception $e) {
                $responseData['success'] = false;
                $responseData['message'] = 'Data gagal dimasukkan';
            }            
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Data gagal dimasukkan';
        }
                    
        $response->getBody()->write(json_encode($responseData));    
    }
});

// Insert a new medical examination
$app->post('/examinations/medical/insert', function (Request $request, Response $response) {    
    if (isTheseParametersAvailable(array('id', 'pet_id', 'weight', 'temperature', 'size', 'given_date', 'details'))) {
        $requestData = $request->getParsedBody();        
        $id = $requestData['id'];
        $pet_id = $requestData['pet_id'];
        $weight = $requestData['weight'];
        $temperature = $requestData['temperature'];
        $size = $requestData['size'];
        $due_date = $requestData['due_date'];
        $given_date = $requestData['given_date'];
        $details = $requestData['details'];
        $examination_details = json_decode($details, true);
            
        $db = new DbOperation();
        $responseData = array();
                    
        if ($examination_id = $db->insertExamination($id, $pet_id, 'm', $weight, $temperature, $size, $due_date, $given_date)) {            
            try {            
                foreach ($examination_details as $detail) {                    
                    if (!$db->insertExaminationDetail($examination_id, $detail['vaccination_medical_id'], null, $detail['remark'], $detail['medicine'])) {
                        throw new Exception("Data detail pemeriksaan gagal dimasukkan");
                    }
                }

                $responseData['success'] = true;
                $responseData['message'] = 'Data berhasil dimasukkan';
            } catch (Exception $e) {
                $responseData['success'] = false;
                $responseData['message'] = 'Data gagal dimasukkan';
            }            
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Data gagal dimasukkan';
        }
                    
        $response->getBody()->write(json_encode($responseData));    
    }
});

// Deleting a pet
$app->post('/pets/delete', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('id'))) {        
        $requestData = $request->getParsedBody();                
        $id = $requestData['id'];
        
        $db = new DbOperation();
        $responseData = array();
                 
        if ($db->deletePet($id)) {
            $responseData['success'] = true;
            $responseData['message'] = 'Data berhasil dihapus';
        } else {
            $responseData['success'] = false;
            $responseData['message'] = 'Data gagal dihapus';
        }
                 
        $response->getBody()->write(json_encode($responseData));
    }
});

$app->run();