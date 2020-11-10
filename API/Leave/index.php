<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include '../function.php';
include '../config.php';
include '../libs/FCM/fcm.php';

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_ALL_LEAVE_AVAILABILITY) {
        $response = method1($response, $input);
    } else if ($method == $APPLY_LEAVE) {
        $response = method2($response, $input);
    } else if ($method == $GET_LEAVE_TO_APPROVE) {
        $response = method3($response, $input);
    } else if ($method == $GET_LEAVE_BY_TRANS_ID) {
        $response = method4($response, $input);
    } else if ($method == $APPROVE_LEAVE) {
        $response = method5($response, $input);
    } else if ($method == $CANCEL_LEAVE) {
        $response = method6($response, $input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}


function method1($response, $input)
{
    if (isset($input['userId'])) {
        $userId = $input['userId'];
        $result = getAllLeaveAvailability($userId);
        if ($result != null) {
            $response["code"] = 200;
            $response["message"] = "Success get leave availability";
            $response['data'] = $result;
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

function method2($response, $input)
{

    $leaveCode = $input['leaveCode'];
    $userId = $input['userId'];
    $leaveData = $input['leaveData'];
    $imgUrl = $input['imageURL'];

    $result = applyLeave($leaveCode, $userId, $leaveData, $imgUrl);
    if ($result["code"] == 200) {
        $response["code"] = 200;
        $response["message"] = "success";
        $response['data'] = $result['data'];
    } else {
        $response["code"] = $result["code"];
        $response["message"] = $result["message"];
        $response['data'] = $result['data'];
    }

    return $response;
}


function method3($response, $input)
{

    $approver1Id = $input['approver1Id'];
    $isShowAll = $input['isShowAll'];

    $result = getLeaveToApprove($approver1Id, $isShowAll);
    if ($result != null || count($result) == 0) {
        $response["code"] = 200;
        $response["message"] = "success";
        $response['data'] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }

    return $response;
}


function method4($response, $input)
{

    $leaveTransactionId = $input['leaveTransactionId'];

    $result = getLeaveByTransactionId($leaveTransactionId);
    if ($result != null) {
        $response["code"] = 200;
        $response["message"] = "success";
        $response['data'] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }

    return $response;
}

function method5($response, $input)
{

    $leaveTransactionId = $input['leaveTransactionId'];
    $status = $input['status'];
    $userId = $input['userId'];

    $result = approveLeave($leaveTransactionId, $status, $userId);
    if ($result != null) {
        $response["code"] = 200;
        $response["message"] = "success";
        $response['data'] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }

    return $response;
}

function method6($response, $input)
{

    $leaveTransactionId = $input['transactionId'];


    $result = cancelLeave($leaveTransactionId);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "success";
        $response['data'] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured. Unable to delete leave";
        $response['data'] = $result;
    }

    return $response;
}

echo json_encode($response);
