<?php
include '../libs/FCM/index.php';

function getInvoiceData($input)
{
    global $conn;
    $responseData = [];
    $userId = $input['userId'];

    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter getInvoiceData";
        return $responseData;
    }

    $data = [];
    $query = "SELECT * FROM InvoiceItem a INNER JOIN Invoice b ON b.InvoiceId = a.InvoiceId WHERE a.SalesPerson = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $responseData['code'] = "200";
    $responseData['message'] = "Success get all claim";
    $responseData['data'] = $data;

    return $responseData;
}
