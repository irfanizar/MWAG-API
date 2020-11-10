<?php
header("Access-Control-Allow-Origin: *");
include '../config/config.php';
include '../resource/method.php';
include '../resource/status.php';
include '../File/create.php';
include '../File/read.php';
include './read.php';
include './create.php';
include './approve.php';
include '../Approval/read.php';
include '../get_invoice.php';


$response = array();

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
$method = $input['Method'];
if (isset($method)) {
    if ($method == $CREATE_CLAIM) {
        $response = createClaim($input);
    } else if ($method == $GET_ALL_CLAIM_BY_USER_ID) {
        $response = getAllClaimByUserId($input);
    } else if ($method == $GET_CLAIM_BY_CLAIM_ID) {
        $response = getClaimByClaimId($input);
    } else if ($method == $GET_ALL_CLAIM_TO_APPROVE) {
        $response = getAllClaimToApprove($input);
    } else if ($method == $APPROVE_CLAIM) {
        $response = approveClaim($input);
    } else if ($method == $GET_INVOICE_DATA) {
        $response = getInvoiceData($input);
    } else if ($method == $CREATE_COMMISION_CLAIM) {
        $response = createCommissionClaim($input);
    }else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

echo json_encode($response);
