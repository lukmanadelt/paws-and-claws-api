<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require_once '../includes/DbOperation.php';

// Creating a new app with the config to show errors
$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => true
	]
]);

// Registering a new user
$app->post('/register', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('role_id', 'username', 'password', 'fullname', 'phone', 'address'))) {
        $requestData = $request->getParsedBody();
        $role_id = $requestData['role_id'];
        $username = $requestData['username'];        
        $password = $requestData['password'];
        $fullname = $requestData['fullname'];
        $phone = $requestData['phone'];
        $address = $requestData['address'];

        $db = new DbOperation();
        $responseData = array();
 
        $result = $db->registerUser($role_id, $username, $password, $fullname, $phone, $address);
 
        if ($result == USER_CREATED) {
            $responseData['error'] = false;
            $responseData['message'] = 'Registered successfully';
            $responseData['user'] = $db->getUserByUsername($username);
        } elseif ($result == USER_CREATION_FAILED) {
            $responseData['error'] = true;
            $responseData['message'] = 'Some error occurred';
        } elseif ($result == USER_EXIST) {
            $responseData['error'] = true;
            $responseData['message'] = 'This username already exist, please login';
        }
 
        $response->getBody()->write(json_encode($responseData));
    }
});
 
// User login route
$app->post('/login', function (Request $request, Response $response) {
    if (isTheseParametersAvailable(array('username', 'password'))) {
        $requestData = $request->getParsedBody();
        $username = $requestData['username'];
        $password = $requestData['password'];
 
        $db = new DbOperation();
 
        $responseData = array();
 
        if ($db->userLogin($username, $password)) {
            $responseData['error'] = false;
            $responseData['user'] = $db->getUserByUsername($username);
        } else {
            $responseData['error'] = true;
            $responseData['message'] = 'Invalid username or password';
        }
 
        $response->getBody()->write(json_encode($responseData));
    }
});
 
 
// Function to check parameters
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
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echo json_encode($response);
        return false;
    }
    return true;
}
  
$app->run();
