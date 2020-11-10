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
    if ($method == $SET_NEW_CLAIM) {
        $response = createNewClaim($response, $input);
    } else if ($method == $GET_ALL_MAIN_CLAIM) {
        $response = createGetAllMainClaim($response, $input);
    } else if ($method == $GET_ALL_SUB_CLAIM) {
        $response = createGetAllSubclaim($response, $input);
    } else if ($method == $GET_ALL_CLAIM_TO_APPROVE) {
        $response = getClaimToApprove($response, $input);
    } else if ($method == $APPROVE_CLAIM) {
        $response = approveNewClaim($response, $input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}
function approveNewClaim($response, $input)
{
    if (
        isset($input['claimId'])
        && isset($input['approverId'])
        && isset($input['newStatus'])
    ) {
        $claimId = $input['claimId'];
        $approverId = $input['approverId'];
        $newStatus = $input['newStatus'];

        $result = approveClaim($claimId, $approverId, $newStatus);
        if ($result == 200) {
            $response["code"] = 200;
            $response["message"] = "Success";
        } else if ($result == 100) {
            $response["code"] = 100;
            $response["message"] = "Unexpected error occured";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}

function getClaimToApprove($response, $input)
{
    if (
        isset($input['approver1Id']) && isset($input['isShowAll'])
    ) {
        $approver1Id = $input['approver1Id'];
        $isShowAll = $input['isShowAll'];
        $result = getAllClaimToApprove($approver1Id, $isShowAll);
        if (count($result) >= 0) {
            $response["code"] = 200;
            $response["message"] = "Success";
            $response["data"] = $result;
        } else if ($result == 100) {
            $response["code"] = 100;
            $response["message"] = "Unexpected error occured";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}

function createNewClaim($response, $input)
{
    if (
        isset($input['userId'])
        && isset($input['status'])
        && isset($input['totalAmount'])
        && isset($input['claimData'])
    ) {
        $userId = $input['userId'];
        $status = $input['status'];
        $totalAmount = $input['totalAmount'];
        $claimData = $input['claimData'];

        $result = addNewClaim($userId, $status, $totalAmount, $claimData);
        if ($result == 200) {
            $response["code"] = 200;
            $response["message"] = "Success";
        } else if ($result == 100) {
            $response["code"] = 100;
            $response["message"] = "Unexpected error occured";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}

function createGetAllMainClaim($response, $input)
{
    if (isset($input['userId'])) {
        $userId = $input['userId'];
        $result = getAllMainClaim($userId);
        if (count($result) >= 0) {
            $response["code"] = 200;
            $response["message"] = "Success";
            $response["data"] = $result;
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
function createGetAllSubclaim($response, $input)
{
    if (isset($input['claimId'])) {
        $claimId = $input['claimId'];
        $result = getAllsubclaim($claimId);
        if (count($result) >= 0) {
            $response["code"] = 200;
            $response["message"] = "Success";
            $response["data"] = $result;
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


echo json_encode($response);
