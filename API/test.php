<?php
header("Access-Control-Allow-Origin: *");
$response = array();

include('function.php');
include('config.php');
include './libs/FCM/fcm.php';


$leaveType = 'AL';
$userId = 18;


try {
    $dataLeaveIds = [];
    $leaveDatas = [];
    $conn->autocommit(FALSE); //turn on transactions
    $query = "SELECT * 
    FROM LeaveType 
    WHERE LeaveTypeCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $leaveType);
    $stmt->execute();
    $leaveType = $stmt->get_result()->fetch_assoc();
    echo json_encode($leaveType);
    echo '<br>';

    $query2 = "SELECT *, MONTH(CURRENT_TIME()) AS CurMonth
    FROM LeaveEntitlement
    WHERE LeaveTypeId = ?
    AND UserId = ? 
    AND Year = YEAR(CURRENT_TIME()) 
    AND (CASE WHEN ExpiryDate IS NOT NULL THEN ExpiryDate > DATE(CURRENT_TIME()) ELSE 1 = 1 END)";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("ss", $leaveType['LeaveTypeId'], $userId);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
        $leaveDatas[] = $row;
    }
    echo json_encode($leaveDatas);


    $stmt2->close();
    if ($leaveDatas == null || count($leaveDatas) == 0) {
        return null;
    } else {
        $totalAvailable = 0;
        foreach ($leaveDatas as $data) {
            if ($data['checkForHalfYear']) {
                if ($data['CurMonth'] <= 6) {
                    $totalAvailable = $totalAvailable + $data['Days'] / 2;
                } else {
                    $totalAvailable = $totalAvailable + $data['Days'];
                }
            } else {
                $totalAvailable = $totalAvailable + $data['Days'];
            }
        }
    }
    $query3 = "SELECT LeaveTypeId FROM LeaveType WHERE DeductLeave = 1 AND DeductFromLeave = ?";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("s", $leaveType['DeductFromLeave']);
    $stmt3->execute();
    $resultIds = $stmt3->get_result();

    $query4 = "SELECT SUM(TotalDays) As total
    FROM LeaveTransactions
    WHERE UserId = ? AND LeaveTypeId = ? AND Status != 320 AND YEAR(DateFrom) = YEAR(CURRENT_TIME())";
    $stmt4 = $conn->prepare($query4);
    $stmt4->bind_param("ss", $userId, $leaveTransId);
    $totalLeaveUsed = 0;
    while ($row = $resultIds->fetch_assoc()) {
        $leaveTransId = $row['LeaveTypeId'];
        $stmt4->execute();
        $res = $stmt4->get_result()->fetch_assoc();
        if ($res != null) {
            $totalLeaveUsed = $totalLeaveUsed + $res['total'];
        }
    }

    if ($conn->autocommit(TRUE)) {
        echo $totalAvailable - $totalLeaveUsed;
    }
} catch (Exception $e) {
    $conn->rollback(); //remove all queries from queue if error (undo)
    throw $e;
}
echo null;

?>