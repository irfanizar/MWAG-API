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
    if ($method == $CHECK_IN) {
        $response = checkIn($response, $input);
    }else if($method == $CHECK_OUT){
        $response = checkOut($response, $input);
    }else if($method == $ATTENDANCE_LIST){
        $response = getAttendanceList($response, $input);
    }
    else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

function getAttendanceList($response, $input)
{
    $userId = $input['userId'];
    $month = $input['month'];
    $year = $input['year'];
    $result = getAllAttendanceByMonth($userId, $month, $year);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "All records success";
        $response["data"] = $result;
    }else{
        $response["code"] = 110;
        $response["message"] = "No record available";
    }
    return $response;
}

function checkIn($response, $input)
{
    $location = $input['location'];               
    $lat = $input['lat'];
    $long = $input['long'];
    $userId = $input['userId'];
    $placeName = $input['placeName'];

    $result = checkInUser($userId, $location, $lat, $long,$placeName);
    if ($result == 200) {
        $response["code"] = 200;
        $response["message"] = "Check-in success.";
    } else if($result == 100) {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }else{
        $response["code"] = 110;
        $response["message"] = "Your season has expired. Please log in to continue.";
    }
    return $response;
}

function checkOut($response, $input)
{
    $location = $input['location'];               
    $lat = $input['lat'];
    $long = $input['long'];
    $userId = $input['userId'];
    $placeName = $input['placeName'];
    $result = checkOutUser($userId, $location, $lat, $long,$placeName);
     if ($result == 200) {
        $response["code"] = 200;
        $response["message"] = "Check-out success.";
    } else if($result == 100) {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }else{
        $response["code"] = 110;
        $response["message"] = "Your season has expired. Please log in to continue.";
    }
    return $response;
}

echo json_encode($response);
?>