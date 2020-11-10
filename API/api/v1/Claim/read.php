<?php
function getAllClaimByUserId($input)
{
    global $conn;
    $responseData = [];
    $userId = $input['userId'];
    $limit = 30;

    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter UserId";
        return $responseData;
    }

    $data = [];
    $query = "SELECT c.ClaimId, DATE_FORMAT(c.CreatedDate, '%b %d')  AS CreatedDate , c.Status,
    (SELECT GROUP_CONCAT(gCI.Description SEPARATOR ', ') FROM ClaimItem gCI WHERE gCI.ClaimId = c.ClaimId GROUP BY gCI.ClaimId) AS Description
     FROM Claim c
     WHERE c.UserId = ?
     ORDER BY c.UpdateDate DESC
     LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['Status'] = getStatusDescription($row['Status']);
        $data[] = $row;
    }
    $responseData['code'] = "200";
    $responseData['message'] = "Success get all claim";
    $responseData['data'] = $data;

    return $responseData;
}

function getClaimByClaimId($input)
{
    global $conn;
    $responseData = [];
    $claimId = $input['claimId'];

    $responseData['code'] = "100";
    if (!isset($claimId)) {
        $responseData['message'] = "Missing parameter ClaimId";
        return $responseData;
    }

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT c.Status, c.Approver1Status, c.Approver2Status, c.TotalAmount,
                    DATE_FORMAT(c.Approver1Date, '%a, %d-%b-%Y, %h:%i%p') AS Approver1Date, c.Approver1Remark,
                    DATE_FORMAT(c.Approver2Date, '%a, %d-%b-%Y, %h:%i%p') AS Approver2Date , c.Approver2Remark
                    FROM Claim c
                    WHERE c.ClaimId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $claimId);
        $stmt->execute();
        $claimMainResult = $stmt->get_result()->fetch_assoc();
        $claimMainResult['Status'] = getStatusDescription($claimMainResult['Status']);
        $claimMainResult['Approver1Status'] = getStatusDescription($claimMainResult['Approver1Status']);
        $claimMainResult['Approver2Status'] = getStatusDescription($claimMainResult['Approver2Status']);

        $stmt->close();
        $data['mainClaim'] = $claimMainResult;
        $query = "SELECT * FROM ClaimItem WHERE ClaimId = ?";
        $stmt1 = $conn->prepare($query);
        $stmt1->bind_param("s", $claimId);
        $stmt1->execute();
        $subClaimResult = $stmt1->get_result();
        while ($row = $subClaimResult->fetch_assoc()) {
            $row['Attachment'] = getAllAttachment("CLAIM", $row['ClaimItemId']);
            $subClaim[] = $row;
        }
        $data['subClaim'] = $subClaim;
        $stmt1->close();
        if ($conn->autocommit(TRUE)) {
            $responseData['code'] = "200";
            $responseData['message'] = "Success get all enquiry by id";
            $responseData['data'] = $data;
            return $responseData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    $responseData['message'] = "Unexpected error occurred.";
    return $responseData;
}


function getAllClaimToApprove($input)
{
    global $conn;
    global $STATUS_PENDING;
    global $STATUS_APPROVED;
    global $STATUS_REJECTED;
    global $STATUS_PENDING;

    $responseData = [];
    $userId = $input['userId'];

    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter userId";
        return $responseData;
    }

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT c.ClaimId, u.EmployeeName,c.Status, c.Approver1Status, c.Approver2Status, c.TotalAmount,
                    DATE_FORMAT(c.CreatedDate, '%b %d')  AS CreatedDate , 
                    (SELECT GROUP_CONCAT(gCI.Description SEPARATOR ', ') FROM ClaimItem gCI 
                        WHERE gCI.ClaimId = c.ClaimId GROUP BY gCI.ClaimId) AS Description,
                    DATE_FORMAT(c.Approver1Date, '%a, %d-%b-%Y, %h:%i%p') AS Approver1Date, c.Approver1Remark,
                    DATE_FORMAT(c.Approver2Date, '%a, %d-%b-%Y, %h:%i%p') AS Approver2Date , c.Approver2Remark
                    FROM Claim c
                    INNER JOIN User u
                    ON c.UserId = u.UserId
                    WHERE (c.Status = ?) AND  ((c.Approver1 = ? && c.Approver1Status = ?) OR (c.Approver2 = ?  && c.Approver1Status = ?))
                    ORDER BY c.CreatedDate DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss",$STATUS_PENDING, $userId, $STATUS_PENDING,$userId, $STATUS_APPROVED);
        $stmt->execute();
        $claimResult = $stmt->get_result();
        while ($row = $claimResult->fetch_assoc()) {
            $row['Status'] = getStatusDescription($row['Status']);
            $row['Approver1Status'] = getStatusDescription($row['Approver1Status']);
            $row['Approver2Status'] = getStatusDescription($row['Approver2Status']);
            $claimMainResult[] = $row;
        }
       
        if ($conn->autocommit(TRUE)) {
            $responseData['code'] = "200";
            $responseData['message'] = "Success get all claim";
            $responseData['data'] = $claimMainResult;
            return $responseData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    $responseData['message'] = "Unexpected error occurred.";
    return $responseData;
}

function getInvoiceData($input)
{
    global $conn;
    $responseData = [];
    $userId = $input['userId'];

    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter UserId";
        return $responseData;
    }

    $data = [];
    $query = "SELECT * FROM InvoiceItem a INNER JOIN Invoice b ON b.InvoiceId = a.InvoiceId INNER JOIN Company c ON c.CompanyId = b.Company WHERE a.SalesPerson = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $responseData['code'] = "200";
    $responseData['message'] = "Success get all invoice data";
    $responseData['data'] = $data;

    return $responseData;
}