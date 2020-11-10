<?php

function createNewUser()
{
    global $conn;
    $salt =  getSalt();
    $username = "XxxX0119";
    $password = "xxx0019";
    $passwordHash = concatPasswordWithSalt($password, $salt);
    $query = "INSERT INTO User(EmployeeNumber, UserName, PasswordHash, Salt, EmployeeName
    , EmailAddress, Active, JoinedDate, Address, HandphoneNumber, Gender, Designation,
     CompanyId, Department, Manager, CreateDate, LastUpdate) 
     VALUES ('Test', ?, ?, ?, 'T1', 'T1', '1'
     , CURRENT_TIME(), 'T1', '019', 'M', 'T1', '1', 'T1', '1',CURRENT_TIME(),CURRENT_TIME())";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("sss", $username, $passwordHash, $salt);
    $stmt1->execute();
    if ($stmt1->affected_rows === 0) {
        $responseData['message'] = "Unable to create new claim. Please try again";
        return $responseData;
    }
}
