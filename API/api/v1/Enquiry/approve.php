<?php
include '../../libs/FCM/index.php';

function approveEnquiry($input)
{
    global $conn;

    $requestId = $input['requestId'];
    $approverId = $input['approverId'];
    $newStatus = $input['newStatus'];
    $remarks = $input['remarks'];

    $approvalSelection = 1;
    $respondData['code'] = 100;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT Approver1, Approver2 FROM Request WHERE RequestId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $requestId);
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
            $query = "UPDATE Request SET UpdateDate = NOW(), UpdateBy = ?, Approver1Status = ?, Approver1Remark = ?
            , Approver1Date = NOW() WHERE RequestId = ?";
        } else {
            $query = "UPDATE Request SET UpdateDate = NOW(), UpdateBy = ?, Approver2Status = ?, Approver2Remark = ?
            , Approver2Date = NOW() WHERE RequestId = ?";
        }

        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param("ssss", $approverId, $newStatus, $remarks, $requestId);
        $stmt2->execute();
        $stmt2->close();
        if($approvalSelection == 1 && $newStatus == "33"){
            createNotificationToOneUser($requestResult['Approver2'], null, "PENDING APPROVAL", "You have new enquiry need to approve.");
        }
        if ($approvalSelection == 1 && $newStatus != "33") {
            $query = "UPDATE Request SET Status = ? WHERE RequestId = ?";
            $stmt3 = $conn->prepare($query);
            $stmt3->bind_param("ss", $newStatus, $requestId);
            $stmt3->execute();
            $stmt3->close();
        } else if ($approvalSelection == 2) {
            $query = "UPDATE Request SET Status = ? WHERE RequestId = ?";
            $stmt3 = $conn->prepare($query);
            $stmt3->bind_param("ss", $newStatus, $requestId);
            $stmt3->execute();
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

