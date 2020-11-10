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
    if ($method == $EDIT_PROFILE) {
        $response = editProfile($response, $input);
    }else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}


function editProfile($response, $input)
{
    $employeeName = $input['EmployeeName'];
    $employeeEmail = $input['EmployeeEmail'];
    $employeeAddress = $input['EmployeeAddress'];               
    $employeeHandphone = $input['EmployeeHandphone'];
    $UserId = $input['UserId'];
    if (updateProfile($UserId, $employeeName, $employeeEmail, $employeeAddress, $employeeHandphone)) {
        $response["code"] = 200;
        $response["message"] = "Update success.";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}



echo json_encode($response);
?>