<?php
include '../../libs/FCM/index.php';

function approveClaim($input)
{
    global $conn;
    global $STATUS_REJECTED;
    global $STATUS_APPROVED;
    global $STATUS_IN_PREPARATION;

    $claimId = $input['claimId'];
    $approverId = $input['approverId'];
    $newStatus = $input['newStatus'];
    $remarks = $input['remarks'];

    $approvalSelection = 1;
    $respondData['code'] = 100;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT Approver1, Approver2 FROM Claim WHERE ClaimId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $claimId);
        $stmt->execute();
        $requestResult = $stmt->get_result()->fetch_assoc();
        if ($requestResult['Approver1'] != null) {
            if (($requestResult['Approver1']) == $approverId) {
                $approvalSelection = 1;
            } else {
                $approvalSelection = 2;
            }
        }
        $stmt->close();
        if ($approvalSelection == 1) {
            $query = "UPDATE Claim SET UpdateDate = NOW(), UpdateBy = ?, Approver1Status = ?, Approver1Remark = ?
            , Approver1Date = NOW() WHERE claimId = ?";
        } else {
            $query = "UPDATE Claim SET UpdateDate = NOW(), UpdateBy = ?, Approver2Status = ?, Approver2Remark = ?
            , Approver2Date = NOW() WHERE claimId = ?";
        }
        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param("ssss", $approverId, $newStatus, $remarks, $claimId);
        $stmt2->execute();
        $stmt2->close();

        if($newStatus == $STATUS_REJECTED){
            $query3 = "UPDATE Claim SET Status = ? WHERE claimId = ?";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("ss", $newStatus, $claimId);
            $stmt3->execute();
            if ($stmt3->affected_rows === 0) {
                $respondData['message'] = "Unable to reject claim. Please try again";
                return $respondData;
            }
            $stmt3->close();
        }
        if($approvalSelection == 1 && $newStatus == $STATUS_APPROVED){
            createNotificationToOneUser($requestResult['Approver2'], null,  "PENDING APPROVAL", "You have new claim need to approve.");
        }

        if($approvalSelection == 2 && $newStatus == $STATUS_APPROVED){
            $query3 = "UPDATE Claim SET Status = ? WHERE claimId = ?";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("ss", $STATUS_IN_PREPARATION, $claimId);
            $stmt3->execute();
            if ($stmt3->affected_rows === 0) {
                $respondData['message'] = "Unable to approve claim. Please try again";
                return $respondData;
            }
            $stmt3->close();
        }


        if ($conn->autocommit(TRUE)) {
            $respondData['code'] = 200;
            $respondData['message'] = "Success";
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $respondData;
}

