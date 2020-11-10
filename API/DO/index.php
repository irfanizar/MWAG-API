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
    if ($method == $GET_MAIN_DO) {
        $response = method1($response);
    } else if ($method == $GET_DO_BY_ID) {
        $response = method2($response, $input);
    }else if ($method == $DO_UPDATE) {
        $response = method3($response, $input);
    }else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}


function method1($response)
{
    $result = getMainDO();
    if (count($result) >= 0) {
        $response["code"] = 200;
        $response["message"] = "Success";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

function method2($response, $input)
{
    $doId = $input['DOId'];
    $result = getDOByDOId($doId);
    if ($result != null) {
        $response["code"] = 200;
        $response["message"] = "Success get DO by ID";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

function method3($response, $input)
{
    $lorryDriver = $input['lorryDriver'];
    $lorryNumber = $input['lorryNumber'];
    $itemsData = $input['itemsData'];
    $userId = $input['userId'];
    $doId = $input['DOId'];

    $result = updateInDraftDO($lorryDriver,$lorryNumber,$itemsData,$userId,$doId);
    if ($result != null) {
        $response["code"] = 200;
        $response["message"] = "Success get DO by ID";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

echo json_encode($response);
