<?php
include '../libs/FCM/index.php';

function createClaim($input)
{

    global $conn;
    global $STATUS_PENDING;
    $fileArrayIndex = 0;
    $moduleId = 5;
    $userId = $input['userId'];
    $status = $input['status'];
    $totalAmount = $input['totalAmount'];
    $claimData = $input['claimData'];
    $fileURLArray = $input['fileURLArray'];
    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter UserId";
        return $responseData;
    } else if (!isset($status)) {
        $responseData['message'] = "Missing parameter Status";
        return $responseData;
    } else if (!isset($totalAmount)) {
        $responseData['message'] = "Missing parameter Total Amount";
        return $responseData;
    } else if (!isset($claimData)) {
        $responseData['message'] = "Missing parameter Claim Data";
        return $responseData;
    } else if (!isset($fileURLArray)) {
        $responseData['message'] = "Missing parameter File URL Array";
        return $responseData;
    }

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $approvals = getApproverId($conn, null, $moduleId, $userId);
        if ($approvals['code'] == 100) {
            $responseData['message'] = $approvals['message'];
            return $responseData;
        }
        // return $approvals;

        $stmt1 = $conn->prepare("INSERT INTO Claim (UserId, Status, TotalAmount, CreatedBy, UpdateBy, Approver1, Approver2) VALUES (?,?,?,?,?,?,?)");
        $stmt1->bind_param("sssssss", $userId, $STATUS_PENDING, $totalAmount, $userId, $userId, $approvals['data']['Approval1'], $approvals['data']['Approval2']);
        $stmt1->execute();
        if ($stmt1->affected_rows === 0) {
            $responseData['message'] = "Unable to create new claim. Please try again";
            return $responseData;
        }
        $claimId = $stmt1->insert_id;
        $stmt1->close();

        $stmt2 = $conn->prepare("INSERT INTO ClaimItem (ClaimId, Description, ReceiptDate,  Amount, Reason, Category) VALUES (?,?,?,?,?,?)");
        $stmt2->bind_param("ssssss", $claimId, $description, $receiptDate,  $amount, $reason, $category);
        foreach ($claimData as $data) {
            $description = $data['Description'];
            $amount = $data['Amount'];
            $reason = $data['Reason'];
            $category = $data['Category'];
            $receiptDate = $data['ReceiptDate'];
            $totalReceipt = $data['totalReceipt'];
            $stmt2->execute();
            $tableId = $stmt2->insert_id;

            if ($stmt2->affected_rows === 0) {
                $responseData['message'] = "Unable to create new claim. Please try again";
                return $responseData;
            }
            for ($i = 0; $i < $totalReceipt; $i++) {
                if (!createNewAttachment("CLAIM", $tableId, $fileURLArray[$fileArrayIndex])) {
                    $responseData['message'] = "Unable to create new claim. Please try again";
                    return $responseData;
                }
                $fileArrayIndex++;
            }
        }
        $stmt2->close();

        if ($conn->autocommit(TRUE)) {
            createNotificationToOneUser($approvals['data']['Approval1'], null, "PENDING APPROVAL", "You have new claim need to approve.");
            $responseData['code'] = "200";
            $responseData['message'] = "Success create claim.";
            return $responseData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $responseData;
}


// Create Commission Claim

function createCommissionClaim($input)
{

    global $conn;
    global $STATUS_PENDING;
    $fileArrayIndex = 0;
    $moduleId = 5;
    $userId = $input['userId'];
    $status = $input['status'];
    $totalClaimAmount = $input['totalCommissionClaimAmount'];

    $commissionClaimData = $input['claimItemArray'];

    $fileURLArray = $input['fileURLArray'];
    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter UserId";
        return $responseData;
    } else if (!isset($status)) {
        $responseData['message'] = "Missing parameter Status";
        return $responseData;
    } else if (!isset($totalClaimAmount)) {
        $responseData['message'] = "Missing parameter Total Commission Claim Amount";
        return $responseData;
    }

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $approvals = getApproverId($conn, null, $moduleId, $userId);
        if ($approvals['code'] == 100) {
            $responseData['message'] = $approvals['message'];
            return $responseData;
        }
        // return $approvals;

        $stmt1 = $conn->prepare("INSERT INTO Claim (UserId, Status, TotalAmount, CreatedBy, UpdateBy, Approver1, Approver2) VALUES (?,?,?,?,?,?,?)");
        $stmt1->bind_param("sssssss", $userId, $STATUS_PENDING, $totalClaimAmount, $userId, $userId, $approvals['data']['Approval1'], $approvals['data']['Approval2']);
        $stmt1->execute();
        if ($stmt1->affected_rows === 0) {
            $responseData['message'] = "Unable to create new claim. Please try again";
            return $responseData;
        }
        $claimId = $stmt1->insert_id;
        $stmt1->close();

        // $query2 = "INSERT INTO ClaimItem (ClaimId, Description, ReceiptDate,  Amount, Reason, Category,Quantity,CommissionPerUnit,InvoiceItemId) VALUES (?,?,?,?,?,?,?,?,?)";
        // // Prepare Connection to Db
        // $stmt2 = $conn->prepare($query2);
        // // Replace ? in query with the inputs
        // $stmt2->bind_param("sssssssss", $claimId, $productName, $createdDate, $quantityCPU, $reason, $category, $quantity, $cpu, $invoiceItemId);
        // // Run query
        // $stmt2->execute();

        $stmt2 = $conn->prepare("INSERT INTO ClaimItem (ClaimId, Description, ReceiptDate,  Amount, Reason, Category,Quantity,CommissionPerUnit,InvoiceItemId) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt2->bind_param("sssssssss", $claimId, $productName, $createdDate, $quantityCPU, $reason, $category, $quantity, $cpu, $invoiceItemId);
        foreach ($commissionClaimData as $data) {
            $productName = $data['productName'];
            $cpu = $data['cpu'];
            $quantity = $data['quantity'];
            $reason = $data['remarks'];
            $category = $data['category'];
            $invoiceItemId = $data['invoiceItemId'];
            $quantityCPU = $cpu * $quantity;
            $stmt2->execute();
            // $tableId = $stmt2->insert_id;

            if ($stmt2->affected_rows === 0) {
                $responseData['message'] = "Unable to create new claim. Please try again";
                return $responseData;
            }
            // for ($i = 0; $i < $totalReceipt; $i++) {
            //     if (!createNewAttachment("CLAIM", $tableId, $fileURLArray[$fileArrayIndex])) {
            //         $responseData['message'] = "Unable to create new claim. Please try again";
            //         return $responseData;
            //     }
            //     $fileArrayIndex++;
            // }
        }
        $stmt2->close();

        if ($conn->autocommit(TRUE)) {
            // createNotificationToOneUser($approvals['data']['Approval1'], null, "PENDING APPROVAL", "You have new claim need to approve.");
            $responseData['code'] = "200";
            $responseData['message'] = "Success create claim.";
            return $responseData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $responseData;
}
