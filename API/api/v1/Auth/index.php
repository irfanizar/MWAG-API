<?php
header("Access-Control-Allow-Origin: *");
include '../config/config.php';
include '../resource/method.php';
include '../Model/user.php';
include './read.php';
include './test.php';
include './create.php';

$response = array();

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
$method = $input['Method'];
if (isset($method)) {
    if ($method == $LOGIN_WITH_USERNAME_AND_PASSWORD) {
        $response = loginWithUsernameAndPassword($input);
    } else if ($method == $CREATE_NEW_PASSWORD_HASH) {
        $response = createNewPasswordHash($input);
    } else if ($method == $TEST_AUTH) {
        $response = createNewUser($input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

echo json_encode($response);
