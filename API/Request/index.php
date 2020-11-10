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
    if ($method == $CREATE_NEW_REQUEST) {
        $response = makeNewRequest($response, $input);
    } else if ($method == $GET_ALL_MAIN_REQUEST) {
        $response = getAllRequest($response, $input);
    } else if ($method == $GET_ALL_SUB_REQUEST) {
        $response = getSubRequest($response, $input);
    } else if ($method == $GET_ALL_REQUEST_TO_APPROVE) {
        $response = getAllApproveRequest($response, $input);
    } else if ($method == $APPROVE_REQUEST) {
        $response = approveNewRequest($response, $input);
    } else if ($method == $SET_ENQUIRY_RECEIVED) {
        $response = enquiryReceived($response, $input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}
function enquiryReceived($response, $input)
{
    if (
        isset($input['requestItemId'])
        && isset($input['userId'])
    ) {
        $requestItemId = $input['requestItemId'];
        $userId = $input['userId'];
       

        $result = markedAsReceived($requestItemId, $userId);
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
function approveNewRequest($response, $input)
{
    if (
        isset($input['requestId'])
        && isset($input['approverId'])
        && isset($input['remarks'])
        && isset($input['newStatus'])
    ) {
        $requestId = $input['requestId'];
        $approverId = $input['approverId'];
        $newStatus = $input['newStatus'];
        $remarks = $input['remarks'];

        $result = approveRequest($requestId, $approverId, $newStatus, $remarks);
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

function getAllApproveRequest($response, $input)
{
    if (isset($input['approver1Id']) && isset($input['isShowAll'])) {
        $approver1Id = $input['approver1Id'];
        $isShowAll = $input['isShowAll'];

        $result = getAllRequestToApprove($approver1Id, $isShowAll);
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

function makeNewRequest($response, $input)
{
    if (
        isset($input['userId'])
        && isset($input['status'])
        && isset($input['moduleId'])
        && isset($input['requestData'])
    ) {
        $userId = $input['userId'];
        $status = $input['status'];
        $moduleId = $input['moduleId'];
        $requestData = $input['requestData'];

        $result = createNewRequest($userId, $status, $moduleId, $requestData);
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

function getAllRequest($response, $input)
{
    if (
        isset($input['userId'])
        && isset($input['limit'])
    ) {
        $userId = $input['userId'];
        $limit = $input['limit'];

        $result = getAllMainRequest($userId, $limit);
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

function getSubRequest($response, $input)
{
    if (
        isset($input['requestId'])
    ) {
        $requesId = $input['requestId'];

        $result = getAllsubRequest($requesId);
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


echo json_encode($response);
