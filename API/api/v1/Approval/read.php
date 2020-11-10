<?php


function getApproverId($conn, $approvalNumber, $moduleId, $userId){
    $stmt1 = $conn->prepare("SELECT a.Approval1, a.Approval2
        FROM ApprovalLink al
        INNER JOIN Approval a
        ON a.ApprovalId = al.ApprovalId
        WHERE al.ObjectTypeId = ? AND al.UserId = ?");
    $stmt1->bind_param("ss", $moduleId, $userId);
    $stmt1->execute();
    $approvals = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();
    $responseData['code'] = "100";
    if(!$approvals){
        $responseData['message'] = "Enquiry approvers not set.";
        return $responseData;
    }else{
        $responseData['code'] = "200";
        $responseData['message'] = "Success get approvers";
        if($approvalNumber != null){
        }else{
            $responseData['data'] = $approvals;
        }
        return $responseData;
    }

}

?>