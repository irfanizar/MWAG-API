<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include '../function.php';
include '../config.php';
include '../AutoNumbering/function.php';


//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_MAIN_CO) {
        $response = method1($response, $input);
    } else if ($method == $GET_CO_BY_CO_ID) {
        $response = method2($response, $input);
    } else if ($method == $GET_ALL_LORRY_DRIVER) {
        $response = method3($response, $input);
    } else if ($method == $GET_ALL_LORRY_NUMBER) {
        $response = method4($response, $input);
    } else if ($method == $STOCK_OUT_NO_CO) {
        $response = method5($response, $input);
    } else if ($method == $GET_ALL_WAREHOUSE) {
        $response = method6($response);
    } else if ($method == $CO_UPDATE) {
        $response = method7($response, $input);
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
    $productId = $input['productId'];
    $result = getMainCO($productId);
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


    if (
        isset($input['COId'])
    ) {
        $coId = $input['COId'];
        $result = getCOByCOId($coId);
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

function method3($response)
{
    $result = getAllLorryDriver();
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

function method4($response)
{
    $result = getAllLorryNumber();
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

function method5($response, $input)
{
    $fromWareHouse = $input['warehouse'];
    $remarks = $input['Remarks'];
    $lorryDriver = $input['lorryDriver'];
    $lorryNumber = $input['lorryNumber'];
    $company = $input['company'];
    $userId = $input['UserId'];
    $products = $input['products'];
    $category = $input['category'];
    $CONumber = generateAutoNumberNumber($company['CompanyId'], 8);
    $DONumber = generateAutoNumberNumber($company['CompanyId'], 9);

    if ($CONumber['code'] == 200 && $DONumber['code'] == 200) {
        $result = insertInDraftCO(
            $fromWareHouse,
            $remarks,
            $lorryDriver,
            $lorryNumber,
            $userId,
            $products,
            $company,
            $CONumber['data'],
            $DONumber['data'],
            $category
        );
        if ($result) {
            $response["code"] = 200;
            $response["message"] = "Success";
        } else {
            $response["code"] = 100;
            $response["message"] = "Unexpected error occured";
        }
    } else {
        if ($CONumber['code'] != 200) {
            $response["code"] = $CONumber['code'];
            $response["message"] = $CONumber['message'];
        } else if ($DONumber['code'] != 200) {
            $response["code"] = $DONumber['code'];
            $response["message"] = $DONumber['message'];
        }
    }


    return $response;
}

function method6($response)
{
    $result = getAllWarehouse();
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

function method7($response, $input)
{
    $remarks = $input['Remarks'];
    $lorryDriver = $input['lorryDriver'];
    $lorryNumber = $input['lorryNumber'];
    $userId = $input['userId'];
    $itemsData = $input['itemsData'];
    $fromWareHouse = $input['warehouse'];
    $doID = $input['DOId'];
    $coId = $input['coId'];
    $CONumber = $input['CONumber'];
    $companyId = $input['companyId'];
    $DONumber = generateAutoNumberNumber($companyId, 21);
    $category = $input['category'];

    if ($DONumber['code'] == 200) {
        $result = stockOutFromCO(
            $fromWareHouse,
            $remarks,
            $lorryDriver,
            $lorryNumber,
            $userId,
            $itemsData,
            $doID,
            $coId,
            $DONumber['data'],
            $CONumber,
            $companyId,
            $category
        );
        if ($result['code'] == 200) {
            $response["code"] = 200;
            $response["message"] = "Success";
        } else {
            $response["code"] = 100;
            $response["message"] = $result["message"];
        }
    } else {
        $response["code"] = 100;
        $response["message"] = $DONumber['message'];
    }


    return $response;
}

echo json_encode($response);
