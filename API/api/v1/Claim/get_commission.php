<?php
include '../libs/FCM/index.php';

function getCommisionClaim($input)
{
    global $conn;
    $responseData = [];
    $userId = $input['userId'];

    $responseData['code'] = "100";
    if (!isset($claimDate)) {
        $responseData['message'] = "Missing parameter claimDate";
        return $responseData;
    }

    $data = [];
    $query = "SELECT * FROM InvoiceItem a INNER JOIN Invoice b ON b.InvoiceId = a.InvoiceId WHERE a.SalesPerson = 14";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
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
