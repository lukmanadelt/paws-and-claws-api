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
    if (isTheseParametersAvailable(array('pet_category_id', 'user_id', 'name', 'sex', 'dob', 'age', 'breed', 'color'))) {
        $requestData = $request->getParsedBody();        
        $pet_category_id = $requestData['pet_category_id'];
        $user_id = $requestData['user_id'];
        $name = $requestData['name'];
        $sex = $requestData['sex'];
        $dob = $requestData['dob'];
        $age = $requestData['age'];
        $breed = $requestData['breed'];
        $color = $requestData['color'];
        $photo = $requestData['photo'];
        
        $db = new DbOperation();
        $responseData = array();
                 
        if ($db->insertPet($pet_category_id, $user_id, $name, $sex, $dob, $age, $breed, $color, $photo)) {            
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

function moveUploadedFile($directory, UploadedFile $uploadedFile) {
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// Getting all pets
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

$app->run();