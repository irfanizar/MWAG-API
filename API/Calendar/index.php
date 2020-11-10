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
    if ($method == $GET_CALENDAR_LIST) {
        $response = getCalendarList($response, $input);
    } else if($method == $SET_CALENDAR_EVENT){
        $response = setEvent($response, $input);
    }else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}


function getCalendarList($response, $input)
{
    if (isset($input['userId'])) {
        $userId = $input['userId'];
        $result = getAllCalendarList($userId);
        if($result){
            $response["code"] = 200;
            $response["message"] = "Success get all calendar list";
            $response["data"] = $result;
        }else{
            $response["code"] = 100;
            $response["message"] = "Unexpected error occurred";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}

function setEvent($response, $input)
{
    if (isset($input['userId'])) {
        $userId = $input['userId'];
        $title = $input['Title'];
        $desc = $input['Location'];
        $loc = $input['Description'];
        $sTime = $input['StartTime'];
        $eTime = $input['EndTime'];
        $result = setCalendarEvent($userId, $title, $desc, $loc, $sTime, $eTime);
        if($result == "200"){
            $response["code"] = 200;
            $response["message"] = "Success get all calendar list";

        }else{
            $response["code"] = 100;
            $response["message"] = "Unexpected error occurred";
        }
    } else {
        $response["code"] = 104;
        $response["message"] = "Missing required parameters";
    }
    return $response;
}
echo json_encode($response);
?>