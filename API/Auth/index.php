<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include '../function.php';
include '../config.php';

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $REGISTER) {
        $response = registerMethod($response, $input);
    } else if ($method == $LOGIN) {
        $response = loginMethod($response, $input);
    } else if ($method == $LOGIN_BY_ID) {
        $response = loginByIdMethod($response, $input);
    } else if ($method == $FORGET_PASSWORD) {
        $response = mForgetPassword($response, $input);
    } else if ($method == $REMOVE_FCM_TOKEN) {
        $response = mRemoveFCMToken($response, $input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

function mRemoveFCMToken($response, $input)
{
    $userId = $input['userId'];
    removeFCMToken($userId);
}

function mForgetPassword($response, $input)
{
    $email = $input['email'];
    if (isset($email)) {
        $result = forgetPassword($email);
        if ($result != null) {
            if ($result['code'] == 200) {
            }
            $response = $result;
        } else {
            $response["code"] = 100;
            $response["message"] = "Unexpected error occured";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}


function registerMethod($response, $input)
{
    if (isset($input['UserName']) && isset($input['Password'])) {
        $userName = $input['UserName'];
        $password = $input['Password'];
        if (!userExists($userName)) {
            $salt = getSalt();
            $passwordHash = password_hash(concatPasswordWithSalt($password, $salt), PASSWORD_DEFAULT);
            if (registerNewUser($userName, $salt, $passwordHash)) {
                $response["code"] = 200;
                $response["message"] = "New user created";
            } else {
                $response["code"] = 100;
                $response["message"] = "Unable to create user";
            }
        } else {
            $response["code"] = 100;
            $response["message"] = "Username already exists";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}

function loginMethod($response, $input)
{
    if (isset($input['UserName']) && isset($input['Password'])) {
        $userName = $input['UserName'];
        $password = $input['Password'];
        $token = $input['FCMToken'];
        $data = loginUser($userName, $password, $token);
        if ($data['code'] == 200) {
            $response["code"] = 200;
            $response["message"] = "Log in successful";
            $response["data"] = $data['data'];
        } else {
            $response["code"] = $data['code'];
            $response["message"] = $data['message'];
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}


function loginByIdMethod($response, $input)
{
    if (isset($input['userId'])) {
        $userId = $input['userId'];
        if ($user = loginUserById($userId)) {
            $response["code"] = 200;
            $response["message"] = "Log in successful";
            $response["data"] = $user;
        } else {
            $response["code"] = 100;
            $response["message"] = "Wrong username or password";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}

echo json_encode($response);
