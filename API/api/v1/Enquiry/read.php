<?php
function readAllEnquiryByUserId($input)
{
    global $conn;
    $responseData = [];
    $userId = $input['userId'];
    $limit = $input['limit'];

    $responseData['code'] = "100";
    if (!isset($userId)) {
        $responseData['message'] = "Missing parameter UserId";
        return $responseData;
    } else if (!isset($limit)) {
        $responseData['message'] = "Missing parameter Limit";
        return $responseData;
    }

    $data = [];
    $query = "SELECT r.RequestId, DATE_FORMAT(r.CreatedDate, '%b %d')  AS CreatedDate , con.ConfigDescription AS Status, 
    (SELECT GROUP_CONCAT(gRI.ItemName SEPARATOR ', ') FROM RequestItem gRI WHERE gRI.RequestId = r.RequestId GROUP BY gRI.RequestId) AS ItemName
     FROM Request r
     INNER JOIN Config con
     ON r.Status = con.ConfigNumber AND con.ConfigModule = 'REQUEST'
     WHERE r.UserId = ?
     ORDER BY r.UpdateDate DESC
     LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $responseData['code'] = "200";
    $responseData['message'] = "Success get all enquiry";
    $responseData['data'] = $data;

    return $responseData;
}

function readAllEnquiryByEnquiryId($input)
{
    global $conn;
    $responseData = [];
    $requestId = $input['requestId'];

    $responseData['code'] = "100";
    if (!isset($requestId)) {
        $responseData['message'] = "Missing parameter requestId";
        return $responseData;
    }

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT con1.ConfigDescription AS Status, con2.ConfigDescription AS Approver1Status, 
                    con3.ConfigDescription AS Approver2Status,
                    DATE_FORMAT(r.Approver1Date, '%a, %d-%b-%Y, %h:%i%p') AS Approver1Date, r.Approver1Remark,
                    DATE_FORMAT(r.Approver2Date, '%a, %d-%b-%Y, %h:%i%p') AS Approver2Date , r.Approver2Remark
                    FROM Request r
                    INNER JOIN Config con1
                    ON r.Status = con1.ConfigNumber
                    INNER JOIN Config con2
                    ON r.Approver1Status = con2.ConfigNumber
                    INNER JOIN Config con3
                    ON r.Approver2Status = con3.ConfigNumber
                    WHERE r.RequestId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $requestId);
        $stmt->execute();
        $requestMainResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $data['mainRequest'] = $requestMainResult;
        $query = "SELECT * FROM RequestItem WHERE RequestId = ?";
        $stmt1 = $conn->prepare($query);
        $stmt1->bind_param("s", $requestId);
        $stmt1->execute();
        $subRequestResult = $stmt1->get_result();
        while ($row = $subRequestResult->fetch_assoc()) {
            $subRequest[] = $row;
        }
        $data['subRequest'] = $subRequest;
        $stmt1->close();
        if($conn->autocommit(TRUE)){
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

function readAllRequestToApprove($input)
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
    $query = "SELECT r.RequestId, DATE_FORMAT(r.CreatedDate, '%b %d')  AS CreatedDate ,
    con.ConfigDescription AS Status, u.EmployeeName AS EmployeeName, r.Approver1 AS Approver1, r.Approver2 AS Approver2
    , r.Approver1Status AS Approver1Status, r.Approver2Status AS Approver2Status, (SELECT GROUP_CONCAT(gRI.ItemName SEPARATOR ', ') 
    FROM RequestItem gRI WHERE gRI.RequestId = r.RequestId GROUP BY gRI.RequestId) AS ItemName
    FROM Request r
    INNER JOIN Config con
    ON r.Status = con.ConfigNumber AND con.ConfigModule = 'REQUEST'
    INNER JOIN User u
    ON r.UserId = u.UserId
    WHERE (r.Approver1 = ? && r.Approver1Status = 32) OR (r.Approver2 = ? &&  r.Approver1Status = 33 &&  r.Approver2Status = 32)
    ORDER BY r.CreatedDate DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $responseData['code'] = "200";
    $responseData['message'] = "Success get all enquiry to approve";
    $responseData['data'] = $data;

    return $responseData;
}
