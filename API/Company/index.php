<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include './function.php';
include '../config.php';

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_ALL_COMPANY) {
        $response = m1($response);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

function m1($response)
{
    $result = getAllCompanyList();
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

echo json_encode($response);
