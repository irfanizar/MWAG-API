<?php
header("Access-Control-Allow-Origin: *");

include '../config.php';
include '../function.php';
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array

$testType = $input['testType'];

$response = array();

if ($testType = 'TEST_STOCK_IN') {
    $response = stock_in_test();
} else if ($testType = 'TEST_ALL_STOCK_MODULE') {
}
echo json_encode($response);

function stock_in_test()
{
    $response = [];
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions

        $id = insertProduct($conn);
        $quantity = 100;

        updateStock($id, $quantity, 1, 1, 1, null, null);

        //-------------- [START TEST 1]-------------
        $qT1 = "SELECT Balance FROM Product WHERE ProductId = ?";
        $sT1 = $conn->prepare($qT1);
        $sT1->bind_param("s", $id);
        $sT1->execute();
        $rT1 = $sT1->get_result()->fetch_assoc();
        if ($rT1['Balance'] == 100) {
            $status = "PASSES";
        } else {
            $status = "FAILED";
            $error = "Table[Product] - Column[Balance] : UPDATE Failed";
            $temp['TEST 1']['error_log'] = $error;
        }
        $temp['TEST 1']['status'] = $status;
        $temp['TEST 1']['desc'] = 'Test UPDATE product balance';

        //-------------- [END TEST 1]-------------

        //-------------- [START TEST 2]-------------
        $qT2 = "SELECT StockId FROM Stock WHERE ProductId = ?";
        $sT2 = $conn->prepare($qT2);
        $sT2->bind_param("s", $id);
        $sT2->execute();
        $rT2 = $sT2->get_result()->fetch_assoc();
        if ($rT2['StockId']) {
            $status = "PASSES";
        } else {
            $status = "FAILED";
            $error = "Table[Stock]: INSERT Failed";
            $temp['TEST 2']['error_log'] = $error;
        }
        $temp['TEST 2']['status'] = $status;
        $temp['TEST 2']['desc'] = 'Test INSERT INTO Stock';

        //-------------- [END TEST 2]-------------
        $response[] = $temp;


        //-------------- [START DELETING]-------------
        $delQ1 = "DELETE FROM Product WHERE ProductId = ?";
        $delS1 = $conn->prepare($delQ1);
        $delS1->bind_param("s", $id);
        $delS1->execute();

        $delQ2 = "DELETE FROM Stock WHERE ProductId = ?";
        $delS2 = $conn->prepare($delQ2);
        $delS2->bind_param("s", $id);
        $delS2->execute();
        //-------------- [END DELETING]-------------

        return  $response;

        // if ($conn->autocommit(TRUE)) {
        // }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function insertProduct($conn)
{
    $query1 = "INSERT INTO Product(ProductCode, ProductName, Active, MinQuantity,
    RentalPriceMin, RentalPriceMax,SalesPriceMin, SalesPriceMax)
    VALUES ('100001', 'testXXProduct1', 1, 20, 100, 100, 100, 100)";
    $stmt1 = $conn->prepare($query1);
    $stmt1->execute();
    $id =  $stmt1->insert_id;
    return $id;
}
