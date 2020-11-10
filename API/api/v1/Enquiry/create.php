<?php
include '../libs/FCM/index.php';

function createEnquiry($input)
{

    global $conn;
    $userId = $input['userId'];
    $status = $input['status'];
    $moduleId = $input['moduleId'];
    $enquiryItems = $input['enquiryItems'];

    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter UserId";
        return $responseData;
    } else if (!isset($status)) {
        $responseData['message'] = "Missing parameter Status";
        return $responseData;
    }else if (!isset($moduleId)) {
        $responseData['message'] = "Missing parameter ModuleId";
        return $responseData;
    }else if (!isset($enquiryItems)) {
        $responseData['message'] = "Missing parameter EnquiryItems";
        return $responseData;
    }

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $approvals = getApproverId($conn, null, $moduleId, $userId);
        if($approvals['code'] == 100){
            $responseData['message'] = $approvals['message'];
            return $responseData;
        }
        $stmt2 = $conn->prepare("INSERT INTO Request (UserId, Status, CreatedBy, Approver1, Approver2) VALUES (?,?,?,?,?)");
        $stmt2->bind_param("sssss", $userId, $status, $userId, $approvals['data']['Approval1'], $approvals['data']['Approval2']);
        $stmt2->execute();
        $requestId = $stmt2->insert_id;
        $stmt2->close();
        $stmt3 = $conn->prepare("INSERT INTO RequestItem (RequestId, ItemName, ItemDescription,Quantity,UnitPrice,EstimationCost,Remarks) VALUES (?,?,?,?,?,?,?)");
        // $stmt3->bind_param("sssssss", $requestId, $name, $desc,  $quantity, $unitPrice, $estimateCost, $remarks);
        $stmt3->bind_param("sssssss", $requestId, $name, $desc, $quantity, $unitPrice, $estimateCost, $remarks);

        foreach ($enquiryItems as $data) {
            $name = $data['ItemName'];
            $desc = $data['ItemDescription'];
            $quantity = $data['ItemQuantity'];
            $unitPrice = $data['UnitPrice'] == "" ? null : $data['UnitPrice'];
            $estimateCost = $data['EstimationCost'] == "" ? null : $data['EstimationCost'];
            $remarks = $data['Remarks'];
            $stmt3->execute();
        }
        $stmt3->close();
        if ($conn->autocommit(TRUE)) {
            createNotificationToOneUser($approvals['data']['Approval1'], null, "PENDING APPROVAL", "You have new enquiry need to approve.");
            $responseData['code'] = "200";
            $responseData['message'] = "Success create enquiries.";
            return $responseData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $responseData;
}
