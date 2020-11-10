<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include './function.php';
include '../config.php';

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_ALL_SALES_STOCK_OUTSTANDING) {
        $response = m1($input);
    } else if ($method == $GET_ALL_SALES_PAYMENT_OUTSTANDING) {
        $response = m2($input);
    }  else if ($method == $GET_ALL_PURCHASE_STOCK_OUTSTANDING) {
        $response = m3($input);
    } else if ($method == $GET_ALL_PURCHASE_PAYMENT_OUTSTANDING) {
        $response = m4($input);
    }else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

function m1($input)
{
    return getAllSalesStockOutstanding();
}

function m2($input)
{
    return getAllSalesPaymentOutstanding();
}

function m3($input)
{
    return getAllPurchaseStockOutstanding();
}

function m4($input)
{
    return getAllPurchasePaymentOutstanding();
}

echo json_encode($response);
