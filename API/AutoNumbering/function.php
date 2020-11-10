<?php
function generateAutoNumberNumber($companyID, $objectTypeId)
{
    global $conn;
    $response = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions

        $query1 =   "SELECT REPLACE(
            REPLACE(
                REPLACE(a.Format, '{YY}',DATE_FORMAT(CURRENT_DATE, '%y'))
            ,'{MM}', DATE_FORMAT(CURRENT_DATE, '%m'))
        ,'{CompanyCode}',(SELECT CompanyCode FROM Company WHERE CompanyId = ?))
            AS AutoNumber, Value, AutoNumberingId
            FROM AutoNumbering a 
            WHERE a.ObjectTypeId = ?";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("ss",  $companyID, $objectTypeId);
        $stmt1->execute();
        $result = $stmt1->get_result()->fetch_assoc();
        if (!$result) {
            $response['code'] = 100;
            $response['message'] = 'Autonumber not set';
            return $response;
        }
        $stmt1->close();
        $p1 = strrpos($result['AutoNumber'], "{");
        $curValue = $result['Value'];
        $curValueLength = strlen($curValue);
        $nextValue = $curValue . "";
        ++$nextValue;
        $nextValueLength = strlen($nextValue);
        $prefixZero = "";
        for ($i = 0; $i < $curValueLength - $nextValueLength; $i++) {
            $prefixZero = $prefixZero . "0";
        }
        $nextValue = $prefixZero . $nextValue;
        $query2 = "UPDATE AutoNumbering SET Value = ? WHERE AutoNumberingId = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("ss",  $nextValue, $result['AutoNumberingId']);
        $stmt2->execute();
        if ($stmt2->affected_rows === 0) {
            $response['code'] = 100;
            $response['message'] = 'Unexpected error occured.';
            return $response;
        }
        $stmt2->close();




        if ($conn->autocommit(TRUE)) {
            $response['code'] = 200;
            $response['message'] = 'Success generate autonumber.';
            $response['data'] = substr($result['AutoNumber'], 0, $p1) . "" . $curValue;
            return $response;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    $response['code'] = 100;
    $response['message'] = 'Autonumber not set ';
    return $response;
}
