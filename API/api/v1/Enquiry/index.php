<?php
header("Access-Control-Allow-Origin: *");
include '../config/config.php';
include '../resource/method.php';
include './read.php';
include './create.php';
include './approve.php';
include '../Approval/read.php';

$response = array();

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
$method = $input['Method'];
if (isset($method)) {
    if ($method == $READ_ALL_ENQUIRY_BY_USER_ID) {
        $response = readAllEnquiryByUserId($input);
    } else if ($method == $READ_ALL_TO_APPROVE_ENQUIRY) {
        $response = readAllRequestToApprove($input);
    }else if ($method == $READ_ALL_ENQUIRY_BY_ENQUIRY_ID) {
        $response = readAllEnquiryByEnquiryId($input);
    } else if ($method == $APPROVE_ENQUIRY) {
        $response = approveEnquiry($input);
    } else if ($method == $CREATE_ENQUIRY) {
        $response = createEnquiry($input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

echo json_encode($response);
