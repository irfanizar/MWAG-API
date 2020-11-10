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
    if ($method == $GET_ALL_PRODUCT_NAME) {
        $response = method1($response);
    } else if ($method == $GET_ALL_PRODUCT_TYPE) {
        $response = method2($response, $input);
    } else if ($method == $GET_PRODUCT_DETAILS) {
        $response = method3($response, $input);
    } else if ($method == $UPDATE_PRODUCT_BALANCE) {
        $response = method4($response, $input);
    } else if ($method == $GET_HIT_MIN_AND_TRANS) {
        $response = method5($response);
    } else if ($method == $GET_PRODUCT_WITH_SPECS) {
        $response = method6($response, $input);
    } else if ($method == $UPDATE_STOCK) {
        $response = method7($response, $input);
    } else if ($method == $ALERT_MIN) {
        $response = method8($response, $input);
    } else if ($method == $GET_ALL_CUSTOMER) {
        $response = method9($response, $input);
    } else if ($method == $GET_CUSTOMER_PRODUCT_BALANCE) {
        $response = method10($response, $input);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}


function method1($response)
{
    $result = getAllProductName();
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
        isset($input['productName'])
    ) {
        $productName = $input['productName'];
        $result = getAllProductSpecs($productName);
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

function method3($response, $input)
{

    $productName = $input['productName'];
    $productType = $input['productType'];
    $productSize = $input['productSize'];
    $productThkRemark = $input['productThkRemark'];
    $productThickness = $input['productThickness'];
    $productCondition = $input['productCondition'];
    $productRemark = $input['productRemark'];
    $productColor = $input['productColor'];

    $result = getProductDetails(
        $productName,
        $productType,
        $productSize,
        $productThkRemark,
        $productThickness,
        $productCondition,
        $productRemark,
        $productColor
    );
    if ($result == null) {
        $response["code"] = 100;
        $response["message"] = "No product available";
    } else {
        if (count($result) >= 0) {
            $response["code"] = 200;
            $response["message"] = "Success";
            $response["data"] = $result;
        } else {
            $response["code"] = 100;
            $response["message"] = "Unexpected error occured";
        }
    }


    return $response;
}


function method4($response, $input)
{
    if (
        isset($input['id']) && isset($input['balance'])
    ) {
        $id = $input['id'];
        $balance = $input['balance'];

        $result = updateProductBalance($id, $balance);
        if ($result) {
            $response["code"] = 200;
            $response["message"] = "Success update new product balance";
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

function method5($response)
{
    $result = getIsHitMinAndProductTrans();
    if ($result != null) {
        $response["code"] = 200;
        $response["message"] = "Success get all data";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

function method6($response, $input)
{
    if (
        isset($input['product'])
    ) {
        $specs = $input['product'];
        $detail = $input['details'];
        $isOwn = $input['isOwn'];

        $result = getProductWithSpecs($specs, $detail, $isOwn);
        if (count($result) >= 0) {
            $response["code"] = 200;
            $response["message"] = "Success get product";
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

function method7($response, $input)
{

    $productId = $input['ProductId'];
    $quantity = $input['Quantity'];
    $category = $input['Category'];
    $type = $input['Type'];
    $userId = $input['UserId'];
    $customerId = $input['CustomerId'];
    $attachment = $input['Attachment'];



    $result = updateStock($productId, $quantity, $category, $type, $userId, $customerId, $attachment);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

function method8($response, $input)
{
    $productId = $input['ProductId'];
    $result = alertMinQuantity($productId);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

function method9($response, $input)
{
    $result = getAllCustomer();
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}

function method10($response, $input)
{
    $customerId = $input['CustomerId'];
    $productId = $input['ProductId'];

    $result = getCustomerStockBalanceByCustId($customerId, $productId);
    if ($result) {
        if ($result['balance'] == 0) {
            $response["code"] = 100;
            $response["message"] = "This customer do not have this product in storage";
        } else {
            $response["code"] = 200;
            $response["message"] = "Success";
            $response["data"] = $result;
        }
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occured";
    }
    return $response;
}
echo json_encode($response);
