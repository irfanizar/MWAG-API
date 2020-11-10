<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include '../function.php';
include '../config.php';

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_INBOX) {
        $response = method1($response, $input);
    } else if ($method == $UPDATE_INBOX_READ) {
        $response = method2($input);
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
    $limit = 20;
    if (isset($input['limit'])) {
        $limit = $input['limit'];
    }
    $userId = $input['userId'];

    $result = getInbox($limit, $userId);
    if (count($result) >= 0) {
        $response["code"] = 200;
        $response["message"] = "synced";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unable to sync inbox";
    }
    return $response;
}

function method2($input)
{
    $userId = $input['userId'];
    $inboxId = $input['inboxId'];
    updateInboxRead($inboxId, $userId);
}



echo json_encode($response);
