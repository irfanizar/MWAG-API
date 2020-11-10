<?php
include('constant.php');
/*-----------------
	1. Auth
-----------------------*/
$random_salt_length = 32;
function getFCMTokenByUserId($conn, $userId)
{
    if ($conn == null) {
        global $conn;
    }
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT FCMToken FROM User WHERE UserId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($conn->autocommit(TRUE)) {
            return $result['FCMToken'];
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function removeFCMToken($userId)
{
    global $conn;
    $query = "UPDATE User SET FCMToken = null WHERE UserId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function forgetPassword($email)
{
    global $conn;
    $response = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT UserId, EmployeeName FROM User WHERE EmailAddress = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result == null || count($result) == 0) {
            $response['code'] = 100;
            $response['message'] = "Email not exist";
            return $response;
        } else {
            $code = generateRandomString();
            $query2 = "UPDATE User SET reset_code=? WHERE UserId=?";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("ss", $code, $result['UserId']);
            $stmt2->execute();
        }


        if ($conn->autocommit(TRUE)) {
            $response['code'] = 200;
            $response['message'] = "Success";
            sendForgetEmail($result['EmployeeName'], $result['UserId'], $code, $email);
            return $response;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function sendForgetEmail($username, $id, $code, $email)
{
    $url = "eticketing2u.com.my/JSMS/Website/recovery_password.php?id=" . $id . "&code=" . $code;
    $to = $email;
    $subject = "Reset Password";
    $message = '
						<html>
						<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
						
						<title>Reset password</title>
						</head>
                            						
                                           
                        <body style="margin: 0; padding: 0; font-family: &quot; HelveticaNeueLight&quot;,&quot;HelveticaNeue-Light&quot;,&quot;HelveticaNeueLight&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 300; font-stretch: normal; font-size: 14px; letter-spacing: .35px; background: #EFF3F6; color: #333333;">
                         <br><br>
                          <table border="1" cellpadding="0" cellspacing="0" align="center" class="c-v84rpm" style="border: 0 none; border-collapse: separate; width: 720px;" width="720">
                            <tbody>
                        
                              <tr class="c-7bgiy1" style="border: 0 none; border-collapse: separate; -webkit-box-shadow: 0 3px 5px rgba(0,0,0,0.04); -moz-box-shadow: 0 3px 5px rgba(0,0,0,0.04); box-shadow: 0 3px 5px rgba(0,0,0,0.04);">
                                <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
                                  <table align="center" border="1" cellpadding="0" cellspacing="0" class="c-f1bud4" style="border: 0 none; border-collapse: separate; width: 100%;" width="100%">
                                    <tbody>
                                      <tr class="c-pekv9n" style="border: 0 none; border-collapse: separate; text-align: center;" align="center">
                                        <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
                                          <table border="1" cellpadding="0" cellspacing="0" width="100%" class="c-1qv5bbj" style="border: 0 none; border-collapse: separate; border-color: #E3E3E3; border-style: solid; width: 100%; border-width: 1px 1px 0; background: #FBFCFC; padding: 40px 54px 42px;">
                                            <tbody>
                                              <tr style="border: 0 none; border-collapse: separate;">
                                                <td class="c-1m9emfx c-zjwfhk" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeueLight&quot;,&quot;HelveticaNeue-Light&quot;,&quot;HelveticaNeueLight&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 300; color: #1D2531; font-size: 25.45455px;"
                                                  valign="middle">' . $username . ' , Recover your password.</td>
                                              </tr>
                                              <tr style="border: 0 none; border-collapse: separate;">
                                                <td class="c-46vhq4 c-4w6eli" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeueRoman&quot;,&quot;HelveticaNeue-Roman&quot;,&quot;HelveticaNeueRoman&quot;,&quot;HelveticaNeue-Regular&quot;,&quot;HelveticaNeueRegular&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 400; color: #7F8FA4; font-size: 15.45455px; padding-top: 20px;"
                                                  valign="middle">Looks like you lost your password?</td>
                                              </tr>
                                              <tr style="border: 0 none; border-collapse: separate;">
                                                <td class="c-eitm3s c-16v5f34" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeueMedium&quot;,&quot;HelveticaNeue-Medium&quot;,&quot;HelveticaNeueMedium&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,sans-serif;font-weight: 500; font-size: 13.63636px; padding-top: 12px;"
                                                  valign="middle">We’re here to help. Click on the button below to change your password.</td>
                                              </tr>
                                              <tr style="border: 0 none; border-collapse: separate;">
                                                <td class="c-rdekwa" style="border: 0 none; border-collapse: separate; vertical-align: middle; padding-top: 38px;" valign="middle">
                    
                                                <a href="' . $url . '" 
                                                    class="c-1eb43lc c-1sypu9p c-16v5f34" style="color: #000000; -webkit-border-radius: 4px; font-family: &quot; HelveticaNeueMedium&quot;,&quot;HelveticaNeue-Medium&quot;,&quot;HelveticaNeueMedium&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,sans-serif;font-weight: 500; font-size: 13.63636px; line-height: 15px; display: inline-block; letter-spacing: .7px; text-decoration: none; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; border-radius: 4px; background-color: #288BD5; background-image: url(&quot;https://mail.crisp.chat/images/linear-gradient(-1deg,#137ECE2%,#288BD598%)&quot; );color: #ffffff; padding: 12px 24px;">Recover my password</a></td>
                                              </tr>
                                              <tr style="border: 0 none; border-collapse: separate;">
                                                <td class="c-ryskht c-zjwfhk" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeueLight&quot;,&quot;HelveticaNeue-Light&quot;,&quot;HelveticaNeueLight&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 300; font-size: 12.72727px; font-style: italic; padding-top: 52px;"
                                                  valign="middle">If you didn’t ask to recover your password, please ignore this email.</td>
                                              </tr>
                                            </tbody>
                                          </table>
                                        </td>
                                      </tr>
                                   
                                <tr class="c-183lp8j" style="border: 0 none; border-collapse: separate;">
                                  <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
                                    <table border="1" cellpadding="0" cellspacing="0" width="100%" class="c-1qv5bbj" style="border: 0 none; border-collapse: separate; border-color: #E3E3E3; border-style: solid; width: 100%; background: #FFFFFF; border-width: 1px; font-size: 11.81818px; text-align: center; padding: 18px 40px 20px;"
                                      align="center">
                                      <tbody>
                                        <tr style="border: 0 none; border-collapse: separate;">
                                          <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle"><span class="c-1w4lcwx">You receive this email because you or someone initiated a password recovery operation on your JS Hardware Scaffolding Management System account.</span></td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                                </tbody>
                                </table>
                                </td>
                              </tr>
                              <tr class="c-ry4gth" style="border: 0 none; border-collapse: separate;">
                                <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
                                  <table border="1" cellpadding="0" cellspacing="0" width="100%" class="c-1vld4cz" style="border: 0 none; border-collapse: separate; padding-top: 26px; padding-bottom: 26px;">
                                    <tbody>
                                      <tr style="border: 0 none; border-collapse: separate;">
                                        <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
                                       
                                          <table border="1" cellpadding="0" cellspacing="0" width="100%" class="c-15u37ze" style="border: 0 none; border-collapse: separate; font-size: 10.90909px; text-align: center; color: #7F8FA4; padding-top: 22px;" align="center">
                                            <tbody>
                                              <tr style="border: 0 none; border-collapse: separate;">
                                                <td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">All rights reserved. JS Hardware Scaffolding Sdn Bhd </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                               
                        
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </body>
						</html>
                    ';
    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // More headers
    $headers .= 'From: <jsmanagementsystem@mail.com>' . "\r\n";
    if (mail($to, $subject, $message, $headers)) {
        return 200;
    } else {
        return 100;
    }
}

function userExists($UserName)
{
    $query = "SELECT UserName FROM User WHERE UserName = ?";
    global $conn;
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $UserName);
        $stmt->execute();
        $stmt->store_result();
        $stmt->fetch();
        if ($stmt->num_rows == 1) {
            $stmt->close();
            return true;
        }
        $stmt->close();
    }
    return false;
}

function getSalt()
{
    global $random_salt_length;
    return bin2hex(openssl_random_pseudo_bytes($random_salt_length));
}

function concatPasswordWithSalt($password, $salt)
{
    global $random_salt_length;
    if ($random_salt_length % 2 == 0) {
        $mid = $random_salt_length / 2;
    } else {
        $mid = ($random_salt_length - 1) / 2;
    }

    return
        substr($salt, 0, $mid - 1) . $password . substr($salt, $mid, $random_salt_length - 1);
}

function registerNewUser($userName, $salt, $passwordHash)
{
    $employeeNumber = "00112";
    $employeeName = "Irfan Nizar";
    $emailAddress = "irfan@.com";
    $active = 1;
    $address = "Kapar, Selangor";
    $handphoneNumber = '01234555544';
    $gender = "M";
    $designation = "Engineer";
    $company = 1;
    $department = "Technical";
    $query  = "INSERT INTO User(EmployeeNumber, UserName, PasswordHash, Salt
                , EmployeeName, EmailAddress, Active, Address, HandphoneNumber
                , Gender, Designation, CompanyId, Department ) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssssssssssis",
        $employeeNumber,
        $userName,
        $passwordHash,
        $salt,
        $employeeName,
        $emailAddress,
        $active,
        $address,
        $handphoneNumber,
        $gender,
        $designation,
        $company,
        $department
    );
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }
    return false;
}
function loginUser($userName, $password, $token)
{
    $query = "SELECT UserId, Salt, PasswordHash, Active FROM User u
                WHERE u.UserName = ? ";
    global $conn;
    $data = [];
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if (!$user['Active']) {
            $data['code'] = 110;
            $data['message'] = "This account is inactive";
        } else {
            if (password_verify(concatPasswordWithSalt($password, $user['Salt']), $user['PasswordHash'])) {
                $data['code'] = 200;
                $data['data'] = loginTransaction($user, $conn, $token);
            } else {
                $data['code'] = 100;
                $data['message'] = "Wrong username or password";
            }
        }
        $stmt->close();
    }
    return $data;
}

function loginUserById($userId)
{
    $query = "SELECT UserId, Active FROM User 
                WHERE UserId = ? ";
    global $conn;
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user['Active']) {
            return loginTransaction($user, $conn, null);
        } else {
            return null;
        }
        $stmt->close();
    }
    return null;
}

function loginTransaction($user, $conn, $token)
{
    $moduleData = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions

        // get user data
        $stmt1 = $conn->prepare("SELECT * FROM User WHERE UserId = ?");
        $stmt1->bind_param("s", $user['UserId']);
        $stmt1->execute();
        $userData = $stmt1->get_result()->fetch_assoc();
        unset($userData['Salt']);
        unset($userData['PasswordHash']);
        $stmt1->close();

        // get company data
        $stmt2 = $conn->prepare("SELECT * FROM Company c
        INNER JOIN UserCompany uc ON
        uc.UserId = ? AND uc.CompanyId = c.CompanyId");
        $stmt2->bind_param("s", $user['UserId']);
        $stmt2->execute();
        $companyResult = $stmt2->get_result();
        while ($row = $companyResult->fetch_assoc()) {
            $companyData[] = $row;
        }
        $stmt2->close();

        // get user roles
        $stmt3 = $conn->prepare("SELECT r.RoleName, r.RoleId
                                    FROM Roles r 
                                    INNER JOIN UserRoles ur
                                    ON ur.RoleId = r.RoleId AND ur.UserId = ?");
        $stmt3->bind_param("s", $user['UserId']);
        $stmt3->execute();
        $roleData = $stmt3->get_result()->fetch_assoc();
        $userData['Designation'] = $roleData['RoleName'];
        $stmt3->close();

        // get user permissions
        $stmt4 = $conn->prepare("SELECT ot.ObjectTypeName AS Module, ot.ObjectTypeId AS ModuleId, GROUP_CONCAT(pt.PermissionTypeName SEPARATOR ' ') AS Permission
                                    FROM Permission p
                                    INNER JOIN ObjectType ot
                                    ON ot.ObjectTypeId = p.ObjectTypeId AND ot.Active = TRUE
                                    INNER JOIN PermissionType pt
                                    ON pt.PermissionTypeId = p.PermissionTypeId AND pt.Active = TRUE
                                    WHERE p.RoleId = ? AND p.Enable = TRUE GROUP BY p.ObjectTypeId");
        $stmt4->bind_param("s", $roleData['RoleId']);
        $stmt4->execute();
        $result = $stmt4->get_result();
        while ($row = $result->fetch_assoc()) {
            $moduleData[] = $row;
        }
        $stmt4->close();

        $stmt5 =  $conn->prepare("Select Status FROM Attendance WHERE UserId = ? AND Date = curdate()");
        $stmt5->bind_param("s", $user['UserId']);
        $stmt5->execute();
        $stmt5->store_result();
        $stmt5->bind_result($status);
        $stmt5->fetch();
        $stmt5->close();

        if ($token != null) {
            $stmt6 = $conn->prepare("UPDATE User SET FCMToken = ? WHERE UserId = ?");
            $stmt6->bind_param("ss", $token, $user['UserId']);
            $stmt6->execute();
            $stmt6->close();
        }

        if ($conn->autocommit(TRUE)) {
            $data["userData"] = $userData;
            $data["companyData"] = $companyData;
            $data["userRole"] = $roleData;
            $data["userPermission"] = $moduleData;
            $data['isCanCheckOut'] = $status;
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}


/*-----------------
	2. Edit Profile
-----------------------*/

function updateProfile($UserId, $employeeName, $employeeEmail, $employeeAddress, $employeeHandphone)
{
    $query = "UPDATE User
                SET EmployeeName = ? , EmailAddress = ? , Address = ? , HandphoneNumber = ?
                WHERE UserId = ?";
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $employeeName, $employeeEmail, $employeeAddress, $employeeHandphone, $UserId);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
    $stmt->close();
}

/*-----------------
	3. Attendance
-----------------------*/
// Status 0 - Check-in only
// Status 1 - Check-out only
function getCheckInStatus($userId)
{
    $query = "Select Status FROM Attendance WHERE UserId = ? AND Date = curdate()";
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();
    return $status;
}
function getAttendanceId($userId)
{
    $data = isCheckedIn($userId);
    if ($data['id'] == -1) {
        return writeAttendance($userId);
    } else {
        return $data;
    }
    $stmt->close();
}

function isCheckedIn($userId)
{
    $result = array();
    $result['id'] = -1;
    $query = "Select AttendanceId, Status FROM Attendance WHERE UserId = ? AND Date = curdate()";
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        return $result;
    } else {
        $stmt->bind_result($attendanceId, $status);
        $stmt->fetch();
        $result['id'] = $attendanceId;
        $result['status'] = $status;
        return $result;
    }
    $stmt->close();
}

function writeAttendance($userId)
{
    $result = array();
    global $conn;
    $status = '0';
    $query = "INSERT INTO Attendance(UserId, Date , Status ) 
                VALUES (?,curdate() , ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userId, $status);
    $stmt->execute();
    $id =  $stmt->insert_id;
    $result['id'] = $id;
    $result['status'] = $status;
    $stmt->close();
    return $result;
}

function updateAttendanceStatus($status, $attendanceId)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE Attendance SET Status = ? WHERE AttendanceId = ?");
    $stmt->bind_param("ss", $status, $attendanceId);
    $stmt->execute();
    $stmt->close();
}

function checkInUser($userId, $location, $lat, $long, $placeName)
{
    $query = "INSERT INTO AttendanceDetails(AttendanceId, TimeIn,PlaceNameIn, LocationIn, LatitudeIn, LongitudeIn ) 
                VALUES (?,CURTIME(),?,?,?,?)";
    global $conn;
    $data = getAttendanceId($userId);
    $attendanceId = $data['id'];
    if ($data['status'] == '1') {
        return -1;
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $attendanceId,$placeName, $location, $lat, $long);
        if ($stmt->execute()) {
            updateAttendanceStatus('1', $attendanceId);
            return 200;
        } else {
            return 100;
        }
    }
    $stmt->close();
}

function checkOutUser($userId, $location, $lat, $long, $placeName)
{
    global $conn;
    $query = "SELECT b.AttendanceId, b.AttendanceDetailId, b.TimeOut FROM Attendance a, 
                AttendanceDetails b WHERE a.UserId = ? AND a.AttendanceId = b.AttendanceId 
                GROUP BY AttendanceDetailId ORDER BY AttendanceDetailId DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($attendanceId, $attendanceDetailId, $timeOut);
    $stmt->fetch();
    $id =  $attendanceDetailId;
    $stmt->close();
    if ($timeOut == null || $timeOut == '') {
        $updateCheckOut = "UPDATE AttendanceDetails SET TimeOut = CURTIME(), LocationOut = ?
        , LatitudeOut = ?, LongitudeOut = ?,PlaceNameOut = ?  WHERE AttendanceDetailId = ?";
        $stmt2 = $conn->prepare($updateCheckOut);
        $stmt2->bind_param("sssss", $location, $lat, $long, $placeName, $attendanceDetailId);
        if ($stmt2->execute()) {
            updateAttendanceStatus('0', $attendanceId);
            return 200;
        } else {
            return 100;
        }
    } else {
        return -1;
    }
    $stmt2->close();
}

function getAllAttendanceByMonth($userId, $month, $year)
{
    global $conn;
    $arr = [];
    if ($month == null || $month == '') {
        $query = "SELECT * FROM AttendanceDetails b
                    INNER JOIN Attendance a
                    ON a.UserId = ? AND a.AttendanceId = b.AttendanceId AND 
                    MONTH(a.Date) = MONTH(CURRENT_DATE) AND YEAR(a.Date) = 
                    YEAR(CURRENT_DATE) ORDER BY b.AttendanceDetailId DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userId);
    } else {
        $query = "SELECT * FROM AttendanceDetails b
                    INNER JOIN Attendance a
                    ON a.UserId = ? AND a.AttendanceId = b.AttendanceId AND 
                    MONTH(a.Date) = ? AND YEAR(a.Date) = 
                    ? ORDER BY b.AttendanceDetailId DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $userId, $month, $year);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $arr[] = $row;
    }
    return $arr;
    $stmt->close();
}

/*-----------------
	4. Calendar
-----------------------*/
//CalendarType : 1 - All, 0 - Specific User
function getAllCalendarList($userId)
{
    global $conn;
    $data = [];
    $query = "SELECT c.CalendarType, c.UserId, u.EmployeeName, c.Title, c.Location, c.Description, c.AllDay, c.StartTime, c.EndTime FROM Calendar c
LEFT JOIN User u 
ON u.UserId = c.UserId
                WHERE (YEAR(c.StartTime) < YEAR(CURDATE()) + 2) AND (c.CalendarType != 'APPT' OR (c.CalendarType = 'APPT' AND (c.UserId IN (SELECT UserId FROM User WHERE Manager = ?)OR c.UserId = ?)))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
    $stmt->close();
}

function setCalendarEvent($userId, $title, $desc, $loc, $sTime, $eTime)
{
    global $conn;
    $query = "INSERT INTO Calendar(CalendarType, UserId, Title, Location, Description, AllDay, StartTime, EndTime ) 
                VALUES ('APPT',?,?,?,?,0,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $userId, $title, $loc, $desc, $sTime, $eTime);
    if ($stmt->execute()) {
        return 200;
    } else {
        return 100;
    }
    $stmt->close();
}

/*-----------------
	5. Task
-----------------------*/
function getDashboardTaskInfo($conn, $userId)
{
    $taskData = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT t.Status, COUNT(t.TaskItemId) AS count
        FROM TaskItem t
        INNER JOIN TaskMember tm
        ON tm.TaskItemId = t.TaskItemId AND tm.UserId = ?
        GROUP BY (t.Status)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $data0 = $stmt->get_result();
        while ($row =  $data0->fetch_assoc()) {
            $data[] = $row;
        }

        $query2 = "SELECT t.Status,COUNT(t.TaskId) AS count FROM Task t
        WHERE AssignedToLead = ? GROUP BY (t.Status)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("s", $userId);
        $stmt2->execute();
        $data2 = $stmt2->get_result();
        while ($row2 =  $data2->fetch_assoc()) {
            $data3[] = $row2;
        }
        $taskItemInProgress = 0;
        $taskItemCompleted = 0;
        $taskItemPending = 0;

        foreach ($data as $item) {
            if ($item['Status'] == 21) {
                $taskItemInProgress = $item['count'];
            } else if ($item['Status'] == 23) {
                $taskItemCompleted = $item['count'];
            } else if ($item['Status'] == 22) {
                $taskItemPending = $item['count'];
            }
        }
        foreach ($data3 as $item) {
            if ($item['Status'] == 21) {
                $taskItemInProgress = $taskItemInProgress + $item['count'];
            } else if ($item['Status'] == 23) {
                $taskItemCompleted = $taskItemCompleted + $item['count'];
            } else if ($item['Status'] == 22) {
                $taskItemPending = $taskItemPending +  $item['count'];
            }
        }

        // $totalInProgress = $data0['inProgress'] + $data2['inProgress'];

        if ($conn->autocommit(TRUE)) {
            $taskData['inProgress'] = $taskItemInProgress;
            $taskData['completed'] = $taskItemCompleted;
            $taskData['pending'] = $taskItemPending;

            return $taskData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}
function getAllTaskMember()
{
    global $conn;
    $data = [];
    $query = "SELECT u.UserId, u.EmployeeName, r.RoleName, u.Department FROM User u
                INNER JOIN UserRoles ur ON  u.UserId = ur.UserId
                INNER JOIN Roles r ON  r.RoleId = ur.RoleId";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row["isPicked"] = false;
        $row["taskRole"] = "none";
        $data[] = $row;
    }
    return $data;
}

function getAllCategory()
{
    global $conn;
    $data = [];
    $query = "SELECT  a.TaskCategoryId, a.TC_Code, a.Description FROM TaskCategory a 
                WHERE a.Active = 1 AND a.DeleteAction = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function createTask(
    $title,
    $desc,
    $status,
    $purpose,
    $category,
    $priority,
    $lead,
    $estimateCompleted,
    $createdId
) {
    global $conn;
    $query = "INSERT INTO Task(TaskTitle, Description, Status, Purpose, Category ,Priority
                ,AssignedToLead, EstimateCompletion, CreatedBy, UpdateBy) 
             VALUES (?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssssssss",
        $title,
        $desc,
        $status,
        $purpose,
        $category,
        $priority,
        $lead,
        $estimateCompleted,
        $createdId,
        $createdId
    );
    if ($stmt->execute()) {
        return 200;
    } else {
        return 100;
    }
    $stmt->close();
}

function createSubtask(
    $taskId,
    $title,
    $desc,
    $status,
    $comment,
    $createdId,
    $memberList
) {
    global $conn;
    $query = "INSERT INTO TaskItem(TaskId, SubtaskTitle, SubtaskDescription, Status, Comments ,CreatedBy
                , UpdateBy) 
             VALUES (?,?,?,?,?,?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssssss",
        $taskId,
        $title,
        $desc,
        $status,
        $comment,
        $createdId,
        $createdId
    );
    if ($stmt->execute()) {
        $taskItemId =  $stmt->insert_id;
        addMember($taskItemId, $memberList);
        return 200;
    } else {
        return 100;
    }
    $stmt->close();
}

function addMember($taskItemId, $memberList)
{
    foreach ($memberList as &$userId) {
        addOneMember($taskItemId, $userId['UserId']);
    }
}

function addOneMember($taskItemId, $UserId)
{
    global $conn;
    $query = "INSERT INTO TaskMember(TaskItemId, UserId) VALUES (?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $taskItemId, $UserId);
    $stmt->execute();
    $stmt->close();
}

function getAllCreatedTask($conn, $userId)
{
    $data = [];
    $query = "SELECT t.TaskId, t.TaskTitle, c.ConfigDescription AS Priority FROM Task t
                INNER JOIN Config c 
                ON t.Priority = c.ConfigNumber
                WHERE t.CreatedBy = ? ORDER BY t.UpdateDate DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
    $stmt->close();
}

function getAllLead($conn, $userId)
{
    $data = [];
    $query = "SELECT t.TaskId, t.TaskTitle, c.ConfigDescription AS Priority FROM Task t
                INNER JOIN Config c 
                ON t.Priority = c.ConfigNumber
                WHERE t.AssignedToLead = ? ORDER BY t.UpdateDate DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
    $stmt->close();
}

function getAllAssigned($conn, $userId)
{
    $data = [];
    $query = "SELECT t.TaskId, t.TaskTitle, c.ConfigDescription AS Priority FROM Task t
                INNER JOIN Config c 
                ON t.Priority = c.ConfigNumber
                WHERE t.TaskId IN (SELECT ti.TaskId FROM TaskItem ti WHERE 
                ti.TaskItemId IN (SELECT tm.TaskItemId FROM TaskMember tm WHERE tm.UserId = ?
                ))
                ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
    $stmt->close();
}

function getAllTaskByUser($userId)
{
    $data = [];
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions try {
        $data["createdTask"] = getAllCreatedTask($conn, $userId);
        $data["leadTask"] = getAllLead($conn, $userId);
        $data["assignedTask"] = getAllAssigned($conn, $userId);
        $data["taskDashboard"] = getDashboardTaskInfo($conn, $userId);
        if ($conn->autocommit(TRUE)) {
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return false;
}

function getAllTaskByTaskId($taskId)
{
    global $conn;
    $data = [];
    $query = "SELECT t.TaskId, t.TaskTitle, c.ConfigDescription AS Priority
                , t.Description, d.ConfigDescription AS Category, e.ConfigDescription AS Status
                , t.EstimateCompletion, f.EmployeeName AS AssignedToLead, g.EmployeeName AS CreatedBy
                , h.EmployeeName AS  UpdateBy, DATE_FORMAT(t.UpdateDate, '%a, %b %d, %h:%i%p') AS UpdateDate
                FROM Task t
                INNER JOIN Config c 
                ON t.Priority = c.ConfigNumber
                INNER JOIN Config d
                ON t.Category = d.ConfigNumber
                INNER JOIN Config e
                ON t.Status = e.ConfigNumber
                INNER JOIN User f
                ON t.AssignedToLead = f.UserId
                INNER JOIN User g
                ON t.CreatedBy = g.UserId
                INNER JOIN User h
                ON t.UpdateBy = h.UserId
                WHERE TaskId = ? ORDER BY t.CreatedDate ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $taskId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $data["mainTask"] = $result;
    $data["subTask"] = getAllSubtaskByTaskId($taskId);
    $data["mainTaskLog"] = getAllMainTakLogByTaskId($taskId);
    $stmt->close();
    return $data;
}

function updateMainTaskComment($conn, $userId, $taskId, $comment)
{
    global $conn;
    $query = "INSERT INTO TaskCommentLog(TaskId, Comment, UpdateDate, UpdateBy) 
    VALUES (?, ?, CURRENT_TIME(), ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $taskId, $comment, $userId);
    $stmt->execute();
}

function getAllMainTakLogByTaskId($taskId)
{
    global $conn;
    $data = [];
    $query = "SELECT t.Comment, 
    DATE_FORMAT(t.UpdateDate, '%a, %b %d, %h:%i%p') AS DateTime, ub.EmployeeName AS UpdateBy
    FROM TaskCommentLog t 
    INNER JOIN User ub 
    ON t.UpdateBy = ub.UserId WHERE t.TaskId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    return $data;
}

function getAllSubtaskByTaskId($taskId)
{
    global $conn;
    $subTask = [];
    $query = "SELECT t.TaskItemId,t.TaskId, t.SubtaskTitle, t.SubtaskDescription, c.ConfigDescription AS Status 
    ,t.Comments, ua.EmployeeName AS CreatedBy, t.CreatedDate, ub.EmployeeName AS UpdateBy
    , t.UpdateDate 
    FROM TaskItem t 
    INNER JOIN Config c 
    ON t.Status = c.ConfigNumber 
    INNER JOIN User ua 
    ON t.CreatedBy = ua.UserId 
    INNER JOIN User ub 
    ON t.UpdateBy = ub.UserId 
    WHERE TaskId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['commentLog'] = getAllCommentLog($row["TaskItemId"]);
        $row['member'] = getAllMember($row["TaskItemId"]);
        $subTask[] = $row;
    }
    $stmt->close();
    return $subTask;
}

function getAllCommentLog($taskItemId)
{
    global $conn;
    $data = [];
    $query = "SELECT t.TaskItemId, t.Comment, DATE_FORMAT(t.UpdateDate, '%a, %b %d, %h:%i%p') AS DateTime, ub.EmployeeName AS UpdateBy
    FROM TaskCommentLog t 
    INNER JOIN User ub 
    ON t.UpdateBy = ub.UserId WHERE t.TaskItemId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $taskItemId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    return $data;
}

function getAllMember($taskItemId)
{
    global $conn;
    $query = "SELECT ub.EmployeeName as EmployeeName
    FROM TaskMember t
    INNER JOIN User ub 
    ON t.UserId = ub.UserId 
    WHERE t.TaskItemId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $taskItemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $allName = "";
    $index = 0;

    while ($row = $result->fetch_assoc()) {
        $name = $row['EmployeeName'];
        if ($index == 0) {
            $allName =  $allName . $name;
        } else {
            $allName =  $allName . ', ' .  $name;
        }
        $index++;
    }
    $stmt->close();

    return $allName;
}

function updateTaskCommentLog($taskItemId, $comment, $userId, $status)
{
    global $conn;
    $query = "INSERT INTO TaskCommentLog(TaskItemId, Comment, UpdateBy) VALUES (?,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $taskItemId, $comment, $userId);
    $stmt->execute();
    $stmt->close();
    return updateSubtaskStatus($status, $taskItemId);
}

function updateSubtaskStatus($status, $taskItemId)
{
    global $conn;
    $query = "UPDATE TaskItem SET Status = ? WHERE TaskItemId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $status, $taskItemId);
    if ($stmt->execute()) {
        return 200;
    } else {
        return 100;
    }

    $stmt->close();
}

/*-----------------
	6. Claim
-----------------------*/
function addNewClaim($userId, $status, $totalAmount, $claimData)
{
    global $conn;
    $result = 100;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $stmt = $conn->prepare("SELECT a.Approval1, a.Approval2
                                    FROM ApprovalLink al
                                    INNER JOIN Approval a
                                    ON a.ApprovalId = al.ApprovalId
                                    WHERE al.ObjectTypeId = 5 AND al.UserId = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $approvals = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $stmt1 = $conn->prepare("INSERT INTO Claim (UserId, Status, TotalAmount, CreatedBy, UpdateBy, Approver1, Approver2) VALUES (?,?,?,?,?,?,?)");
        $stmt1->bind_param("sssssss", $userId, $status, $totalAmount, $userId, $userId, $approvals['Approval1'], $approvals['Approval2']);
        $stmt1->execute();
        $taskId = $stmt1->insert_id;
        $stmt1->close();
        if ($stmt1->affected_rows === 0) {
            return $result;
        }
        $currentYear = date("Y");
        $module = "/CLAIM/";

        $baseURL = "https://eticketing2u.com.my/JSMS/uploads/" . $currentYear . $module;
        $stmt2 = $conn->prepare("INSERT INTO ClaimItem (ClaimId, Description, ReceiptDate,  Amount, Attachment, Reason) VALUES (?,?,?,?,?,?)");
        $stmt2->bind_param("ssssss", $taskId, $description, $date,  $amount, $attachment, $reason);
        foreach ($claimData as $data) {
            $description = $data['Description'];
            $amount = $data['Amount'];
            $reason = $data['Reason'];
            $attachment = $baseURL . $data['fileName'];
            $date = $data['ReceiptDate'];
            $stmt2->execute();
        }
        $stmt2->close();
        if ($conn->autocommit(TRUE)) {
            $result = 200;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $result;
}

function getAllMainClaim($userId)
{
    global $conn;
    $data = [];
    $query = "SELECT c.ClaimId, con.ConfigDescription AS Status, DATE(c.CreatedDate) AS CreatedDate , c.TotalAmount
     FROM Claim c 
     INNER JOIN Config con
     ON c.Status = con.ConfigNumber
     WHERE c.UserId = ?
     ORDER BY c.CreatedDate DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}
function getAllClaimToApprove($approver1Id, $isShowAll)
{
    global $conn;
    $data = [];
    if ($isShowAll) {
        $query = "SELECT c.ClaimId, DATE(c.CreatedDate) AS CreatedDate , con.ConfigDescription AS Status, 
         u.EmployeeName AS EmployeeName, c.Approver1 AS Approver1, c.Approver2 AS Approver2,
         c.Approver1Status AS Approver1Status, c.Approver2Status AS Approver2Status
         FROM Claim c
         INNER JOIN Config con
         ON c.Status = con.ConfigNumber
         INNER JOIN User u
         ON c.UserId = u.UserId
         WHERE c.Approver1 = ? OR (c.Approver2 = ?  && c.Approver1Status = 30)
         ORDER BY c.CreatedDate DESC
         ";
    } else {
        $query = "SELECT c.ClaimId, DATE( c.CreatedDate) AS CreatedDate ,
         con.ConfigDescription AS Status, u.EmployeeName AS EmployeeName, c.Approver1 AS Approver1, c.Approver2 AS Approver2
         , c.Approver1Status AS Approver1Status, c.Approver2Status AS Approver2Status
         FROM Claim c
         INNER JOIN Config con
         ON c.Status = con.ConfigNumber
         INNER JOIN User u
         ON c.UserId = u.UserId
         WHERE (c.Approver1 = ? && c.Approver1Status = 29) OR (c.Approver2 = ? &&  c.Approver1Status = 30 &&  c.Approver2Status = 29)
         ORDER BY c.CreatedDate DESC
         ";
    }
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $approver1Id, $approver1Id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}
function getAllsubclaim($claimId)
{
    global $conn;
    $data = [];
    $subClaim = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT con1.ConfigDescription AS Status, con2.ConfigDescription AS Approver1Status, 
                    con3.ConfigDescription AS Approver2Status, c.Approver1Date, c.Approver2Date, c.TotalAmount AS TotalClaim
                    FROM Claim c
                    INNER JOIN Config con1
                    ON c.Status = con1.ConfigNumber
                    INNER JOIN Config con2
                    ON c.Approver1Status = con2.ConfigNumber
                    INNER JOIN Config con3
                    ON c.Approver2Status = con3.ConfigNumber
                    WHERE c.ClaimId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $claimId);
        $stmt->execute();
        $claimMainResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $data['mainClaim'] = $claimMainResult;

        $query = "SELECT * FROM ClaimItem WHERE ClaimId = ?";
        $stmt1 = $conn->prepare($query);
        $stmt1->bind_param("s", $claimId);
        $stmt1->execute();
        $subRequestResult = $stmt1->get_result();
        while ($row = $subRequestResult->fetch_assoc()) {
            $subClaim[] = $row;
        }
        $data['subClaim'] = $subClaim;
        $stmt1->close();
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $data;
}

function approveClaim($claimId, $approverId, $newStatus)
{
    global $conn;
    $approvalSelection = 1;
    $result = 100;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT Approver1, Approver2 FROM Claim WHERE ClaimId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $claimId);
        $stmt->execute();
        $claimResult = $stmt->get_result()->fetch_assoc();
        if ($claimResult['Approver1'] != null) {
            if (($claimResult['Approver1']) == $approverId) {
                $approvalSelection = 1;
            } else {
                $approvalSelection = 2;
            }
        }
        $stmt->close();
        if ($approvalSelection == 1) {
            $query = "UPDATE Claim SET UpdateDate = NOW(), UpdateBy = ?, Approver1Status = ?
            , Approver1Date = NOW() WHERE ClaimId = ?";
        } else {
            $query = "UPDATE Claim SET UpdateDate = NOW(), UpdateBy = ?, Approver2Status = ?
            , Approver2Date = NOW() WHERE ClaimId = ?";
        }

        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param("sss", $approverId, $newStatus, $claimId);
        $stmt2->execute();
        $stmt2->close();
        if ($approvalSelection == 1 && $newStatus != "30") {
            $query = "UPDATE Claim SET Status = ? WHERE ClaimId = ?";
            $stmt3 = $conn->prepare($query);
            $stmt3->bind_param("ss", $newStatus, $claimId);
            $stmt3->execute();
            $stmt3->close();
        } else if ($approvalSelection == 2) {
            $query = "UPDATE Claim SET Status = ? WHERE ClaimId = ?";
            $stmt3 = $conn->prepare($query);
            $stmt3->bind_param("ss", $newStatus, $claimId);
            $stmt3->execute();
            $stmt3->close();
        }


        if ($conn->autocommit(TRUE)) {
            $result = 200;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $result;
}

/*-----------------
	7. Request
-----------------------*/
function createNewRequest($userId, $status, $moduleId, $requestData)
{
    global $conn;
    $result = 100;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $stmt1 = $conn->prepare("SELECT a.Approval1, a.Approval2
                                    FROM ApprovalLink al
                                    INNER JOIN Approval a
                                    ON a.ApprovalId = al.ApprovalId
                                    WHERE al.ObjectTypeId = ? AND al.UserId = ?");
        $stmt1->bind_param("ss", $moduleId, $userId);
        $stmt1->execute();
        $approvals = $stmt1->get_result()->fetch_assoc();
        $stmt1->close();
        $stmt2 = $conn->prepare("INSERT INTO Request (UserId, Status, CreatedBy, Approver1, Approver2) VALUES (?,?,?,?,?)");
        $stmt2->bind_param("sssss", $userId, $status, $userId, $approvals['Approval1'], $approvals['Approval2']);
        $stmt2->execute();
        $requestId = $stmt2->insert_id;
        $stmt2->close();
        $stmt3 = $conn->prepare("INSERT INTO RequestItem (RequestId, ItemName, ItemDescription,Quantity,UnitPrice,EstimationCost,Remarks) VALUES (?,?,?,?,?,?,?)");
        // $stmt3->bind_param("sssssss", $requestId, $name, $desc,  $quantity, $unitPrice, $estimateCost, $remarks);
        $stmt3->bind_param("sssssss", $requestId, $name, $desc, $quantity, $unitPrice, $estimateCost, $remarks);

        foreach ($requestData as $data) {
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
            $result = 200;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $result;
}

function getAllMainRequest($userId, $limit)
{
    global $conn;
    $data = [];
    $query = "SELECT r.RequestId, DATE(r.CreatedDate) AS CreatedDate , con.ConfigDescription AS Status
     FROM Request r
     INNER JOIN Config con
     ON r.Status = con.ConfigNumber
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
    return $data;
}

function getAllsubRequest($requestId)
{
    global $conn;
    $data = [];
    $subRequest = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT con1.ConfigDescription AS Status, con2.ConfigDescription AS Approver1Status, 
                    con3.ConfigDescription AS Approver2Status,
                    r.Approver1Date, r.Approver1Remark, r.Approver2Date, r.Approver2Remark
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
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $data;
}

function getAllRequestToApprove($approver1Id, $isShowAll)
{
    global $conn;
    $data = [];
    if ($isShowAll) {
        $query = "SELECT r.RequestId, DATE(r.CreatedDate) AS CreatedDate , con.ConfigDescription AS Status, 
         u.EmployeeName AS EmployeeName, r.Approver1 AS Approver1, r.Approver2 AS Approver2,
         r.Approver1Status AS Approver1Status, r.Approver2Status AS Approver2Status
         FROM Request r
         INNER JOIN Config con
         ON r.Status = con.ConfigNumber
         INNER JOIN User u
         ON r.UserId = u.UserId
         WHERE r.Approver1 = ? OR (r.Approver2 = ?  && r.Approver1Status = 33)
         ORDER BY r.CreatedDate DESC
         ";
    } else {
        $query = "SELECT r.RequestId, DATE(r.CreatedDate) AS CreatedDate ,
         con.ConfigDescription AS Status, u.EmployeeName AS EmployeeName, r.Approver1 AS Approver1, r.Approver2 AS Approver2
         , r.Approver1Status AS Approver1Status, r.Approver2Status AS Approver2Status
         FROM Request r
         INNER JOIN Config con
         ON r.Status = con.ConfigNumber AND con.ConfigModule = 'REQUEST'
         INNER JOIN User u
         ON r.UserId = u.UserId
         WHERE (r.Approver1 = ? && r.Approver1Status = 32) OR (r.Approver2 = ? &&  r.Approver1Status = 33 &&  r.Approver2Status = 32)
         ORDER BY r.CreatedDate DESC
         ";
    }
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $approver1Id, $approver1Id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function approveRequest($requestId, $approverId, $newStatus, $remarks)
{
    global $conn;
    $approvalSelection = 1;
    $result = 100;
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
            $result = 200;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $result;
}

function markedAsReceived($requestItemId, $userId)
{
    global $conn;
    $query = "UPDATE RequestItem SET ReceivedDate = NOW(), ReceivedBy = ? WHERE RequestItemId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $userId, $requestItemId);
    if ($stmt->execute()) {
        return 200;
    } else {
        return 100;
    }
    $stmt->close();
}

/*-----------------
	8. CO
-----------------------*/
function stockOutFromCO(
    $fromWareHouse,
    $remarks,
    $lorryDriver,
    $lorryNumber,
    $userId,
    $itemsData,
    $doID,
    $coId,
    $DONumber,
    $CONumber,
    $companyId,
    $category
) {
    global $conn;
    $response = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        if ($lorryDriver != null) {
            if ($lorryDriver['isNewAdd']) {
                $lorryDriver =  addLorryDriver($lorryDriver, $userId, $conn);
            }
        }
        if ($lorryNumber != null) {
            if ($lorryNumber['isNewAdd']) {
                $lorryNumber =  addLorryNumber($lorryNumber, $userId, $conn);
            }
        }
        $query4 = "INSERT INTO DeliveryOrder(DONumber, CompanyId, COId, CONumber, Status, SalesPerson, 
        CreatedBy, UpdateBy, FromWareHouse, LorryDriver, LorryId, Remarks)
        VALUES(?, ?, ?, ?, '200', ?, ?, ?, ?, ?, ?, ?)";
        $stmt4 = $conn->prepare($query4);
        $stmt4->bind_param(
            "sssssssssss",
            $DONumber,
            $companyId,
            $coId,
            $CONumber,
            $userId,
            $userId,
            $userId,
            $fromWareHouse,
            $lorryDriver['LorryDriverId'],
            $lorryNumber['LorryId'],
            $remarks
        );
        $stmt4->execute();
        $doId = $stmt4->insert_id;
        $stmt4->close();

        $doCategory = $category == '1' ? '5' : '7';

        $query6 = "UPDATE Product SET Balance = Balance - ? WHERE ProductId = ?";
        $stmt6 = $conn->prepare($query6);
        $stmt6->bind_param("ss",  $quantity, $productId);

        foreach ($itemsData as $subProduct) {
            $productId = $subProduct['ProductId'];
            $quantity = $subProduct['tValue'];
            $stmt6->execute();
        }

        $query5 = "INSERT INTO DeliveryOrderItem(DOId, ProductId, ProductThkRemarks
        , ProductColor, Description, Quantity)
        VALUES(?, ?, ?, ?, '-', ?)";
        $stmt5 = $conn->prepare($query5);
        $stmt5->bind_param("sssss",  $doId, $productId, $productThkRemarks, $productColor, $quantity);
        foreach ($itemsData as $subProduct) {
            $productId = $subProduct['ProductId'];
            $productThkRemarks = $subProduct['ProductThkRemarks'];
            $productColor = $subProduct['ProductColor'];
            $quantity = $subProduct['tValue'];
            $stmt5->execute();
            updateStock($productId, $quantity, $doCategory, 2, $userId, null, null);
        }




        updateCOStatus($conn, $coId);

        if ($conn->autocommit(TRUE)) {
            $response['code'] = 200;
            return $response;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    $response['code'] = 100;
    $response['message'] = 'Unexpected error occured';
    return $response;
}

function updateCOStatus($conn, $coId)
{
    try {
        $conn->autocommit(FALSE); //turn on transactions

        $query2 = "SELECT coi.Quantity, doi.ProductId, SUM(doi.Quantity) AS Quantity,
        		(coi.Quantity - SUM(doi.Quantity)) AS Balance
        FROM DeliveryOrderItem doi
        INNER JOIN DeliveryOrder d 
        ON d.DOId = doi.DOId
        INNER JOIN CustomerOrderItem coi
        ON coi.COId = ?  AND coi.ProductId = doi.ProductId
        WHERE d.COId = ?
        GROUP BY (doi.ProductId)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("ss", $coId, $coId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $completed = true;
        while ($row2 = $result2->fetch_assoc()) {
            if ($row2['Balance'] > 0) {
                $completed = false;
            }
        }
        $stmt2->close();
        if ($completed) {
            $query3 = "UPDATE CustomerOrder SET Status = 200 WHERE COId = ?";
        } else {
            $query3 = "UPDATE CustomerOrder SET Status = 110 WHERE COId = ?";
        }
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("s", $coId);
        $stmt3->execute();
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
}

function insertIntoDOItem($conn, $products, $doId)
{
    $query = "INSERT INTO DeliveryOrderItem(DOId, ProductId, ProductThkRemarks
        , ProductColor, Description, Quantity)
        VALUES(?, ?, ?, ?, '-', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss",  $doId, $productId, $productThkRemarks, $productColor, $quantity);
    foreach ($products as $subProduct) {
        $productId = $subProduct['ProductId'];
        $productThkRemarks = $subProduct['ProductThkRemarks'];
        $productColor = $subProduct['ProductColor'];
        $quantity = $subProduct['tValue'];
        $stmt->execute();
    }
}

function insertInDraftCO($fromWareHouse, $remarks, $lorryDriver, $lorryNumber, $userId, $products, $company, $CONumber, $DONumber, $category)
{
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions


        $coCategory = $category == '5' ? 1 : 2;

        $query = "INSERT INTO CustomerOrder(CONumber, Company, Status, SalesPerson, CreatedBy, UpdatedBy, Delivery, Category)
        VALUES(?, ?, '120', ?, ?, ?, '1', ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $CONumber, $company['CompanyId'],  $userId, $userId, $userId, $coCategory);
        $stmt->execute();
        $coId = $stmt->insert_id;
        $stmt->close();


        $query1 = "INSERT INTO CustomerOrderItem(COId, ProductId, ProductThkRemarks, ProductColor
        , Quantity, Currency, TotalPrice, TaxAmount, TaxCode, Discount, DiscountType, UnitPrice)
        VALUES(?, ?, ?, ?, ?, '0', '0', '0', '0', '0', '0', '0')";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("sssss",  $coId, $productId, $productThkRemarks, $productColor, $quantity);
        foreach ($products as $subProduct) {
            $productId = $subProduct['ProductId'];
            $productThkRemarks = $subProduct['ProductThkRemarks'];
            $productColor = $subProduct['ProductColor'];
            $quantity = $subProduct['Quantity'];
            $stmt1->execute();
            updateStock($productId, $quantity, $category, 2, $userId, null, null);
        }

        if ($lorryDriver['isNewAdd']) {
            $query2 = "INSERT INTO LorryDriver(LorryDriverCode, LorryDriverName, CreatedBy, UpdateBy, Active)
                VALUES('NEW', ?, ?, ?, '1')";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("sss",  $lorryDriver['LorryDriverName'], $userId, $userId);
            $stmt2->execute();
            $lorryDriverId = $stmt2->insert_id;
            $lorryDriver['LorryDriverId'] = $lorryDriverId;
            $stmt2->close();
        }

        if ($lorryNumber['isNewAdd']) {
            $query3 = "INSERT INTO Lorry(LorryNumber, Active, CreatedBy, UpdateBy)
                VALUES(?, '1', ?, ?)";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("sss",  $lorryNumber['LorryNumber'], $userId, $userId);
            $stmt3->execute();
            $lorryNumberId = $stmt3->insert_id;
            $lorryNumber['LorryId'] = $lorryNumberId;
            $stmt3->close();
        }

        $query4 = "INSERT INTO DeliveryOrder(DONumber, COId, CONumber, Status, SalesPerson, 
        CreatedBy, UpdateBy, FromWareHouse, LorryDriver, LorryId, Remarks)
        VALUES(?, ?, ?, '120', ?, ?, ?, ?, ?, ?, ?)";
        $stmt4 = $conn->prepare($query4);
        $stmt4->bind_param(
            "ssssssssss",
            $DONumber,
            $coId,
            $CONumber,
            $userId,
            $userId,
            $userId,
            $fromWareHouse['warehouseId'],
            $lorryDriver['LorryDriverId'],
            $lorryNumber['LorryId'],
            $remarks
        );
        $stmt4->execute();
        $doId = $stmt4->insert_id;
        $stmt4->close();

        $query5 = "INSERT INTO DeliveryOrderItem(DOId, ProductId, ProductThkRemarks
        , ProductColor, Description, Quantity)
        VALUES(?, ?, ?, ?, '-', ?)";
        $stmt5 = $conn->prepare($query5);
        $stmt5->bind_param("sssss",  $doId, $productId1, $productThkRemarks1, $productColor1, $quantity1);
        foreach ($products as $subProduct) {
            $productId1 = $subProduct['ProductId'];
            $productThkRemarks1 = $subProduct['ProductThkRemarks'];
            $productColor1 = $subProduct['ProductColor'];
            $quantity1 = $subProduct['Quantity'];
            $stmt5->execute();
        }
        $stmt5->close();

        // $query6 = "UPDATE Product SET Balance = Balance - 5 WHERE ProductId = ?";
        // $stmt6 = $conn->prepare($query6);
        // $stmt6->bind_param("ss",  $quantity2, $productId2);
        // $stmt6->bind_param("s", $productId2);

        // foreach ($products as $subProduct) {
        //     $productId2 = $subProduct['ProductId'];
        //     $quantity2 = $subProduct['Quantity'];
        //     $stmt6->execute();
        // }
        // return false;

        if ($conn->autocommit(TRUE)) {
            return true;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return false;
}

function addLorryNumber($lorryNumber, $userId, $conn)
{
    $query3 = "INSERT INTO Lorry(LorryNumber, Active, CreatedBy, UpdateBy)
                VALUES(?, '1', ?, ?)";
    $stmt3 = $conn->prepare($query3);
    $stmt3->bind_param("sss",  $lorryNumber['LorryNumber'], $userId, $userId);
    $stmt3->execute();
    $lorryNumberId = $stmt3->insert_id;
    $lorryNumber['LorryId'] = $lorryNumberId;
    $stmt3->close();
    return $lorryNumber;
}

function addLorryDriver($lorryDriver, $userId, $conn)
{
    $query2 = "INSERT INTO LorryDriver(LorryDriverCode, LorryDriverName, CreatedBy, UpdateBy, Active)
                VALUES('NEW', ?, ?, ?, '1')";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("sss",  $lorryDriver['LorryDriverName'], $userId, $userId);
    $stmt2->execute();
    $lorryDriverId = $stmt2->insert_id;
    $lorryDriver['LorryDriverId'] = $lorryDriverId;
    $stmt2->close();
    return $lorryDriver;
}

function updateInDraftDO($lorryDriver, $lorryNumber, $itemsData, $userId, $doId)
{
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        if ($lorryDriver != null) {
            if ($lorryDriver['isNewAdd']) {
                $lorryDriver =  addLorryDriver($lorryDriver, $userId, $conn);
            }
            $query = "UPDATE DeliveryOrder SET LorryDriver = ? WHERE DOId = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss",  $lorryDriver['LorryDriverId'], $doId);
            $stmt->execute();
            $stmt->close();
        }

        if ($lorryNumber != null) {
            if ($lorryNumber['isNewAdd']) {
                $lorryNumber =  addLorryNumber($lorryNumber, $userId, $conn);
            }
            $query1 = "UPDATE DeliveryOrder SET LorryId = ? WHERE DOId = ?";
            $stmt1 = $conn->prepare($query1);
            $stmt1->bind_param("ss",  $lorryNumber['LorryId'], $doId);
            $stmt1->execute();
            $stmt1->close();
        }

        if (count($itemsData) > 0) {
            $query2 = "UPDATE DeliveryOrderItem SET Quantity = ? WHERE DOId = ? 
            AND ProductId = ?";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("sss", $quantity, $doId, $ProductId);

            foreach ($itemsData as $item) {
                $quantity = $item['UpdatedQuantity'];
                $ProductId = $item['ProductId'];
                $stmt2->execute();
            }
            $stmt2->close();

            $query3 = "UPDATE Product SET Balance = Balance + ? WHERE ProductId = ?";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("ss", $balance, $ProductId);
            foreach ($itemsData as $item) {
                $ProductId = $item['ProductId'];
                $balance = $item['UpdatedStock'];
                $stmt3->execute();
            }

            $stmt3->close();
        }
        if ($conn->autocommit(TRUE)) {
            return true;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function getMainCO($productId)
{
    global $conn;
    $data = [];
    $new = [];
    $partial = [];
    if ($productId == null) {
        $query = "SELECT co.COId, co.CONumber, c.CustomerName,
        DATE_FORMAT(co.CreatedDate, '%a, %b %d, %h:%i%p')  AS CreatedDate, co.Status 
        FROM CustomerOrder co
        LEFT JOIN Customer c
        ON c.CustomerId = co.CustomerId
        WHERE co.Status = 100 OR co.Status = 110";
        $stmt = $conn->prepare($query);
    } else {
        $query = "SELECT co.COId, co.CONumber, c.CustomerName,SUM(doi.Quantity), coi.Quantity,
        DATE_FORMAT(co.CreatedDate, '%a, %b %d, %h:%i%p')  AS CreatedDate, co.Status,
        (CASE WHEN SUM(doi.Quantity) IS NULL THEN 0 ELSE coi.Quantity - SUM(doi.Quantity) END ) AS totalBalance
        FROM CustomerOrder co
        LEFT JOIN Customer c
        ON c.CustomerId = co.CustomerId
        INNER JOIN CustomerOrderItem coi
        ON co.COId = coi.COId AND coi.ProductId = ?
        LEFT JOIN DeliveryOrder delOrder
        ON delOrder.COId = co.COId
        LEFT JOIN DeliveryOrderItem doi
        ON doi.DOId = delOrder.DOId AND doi.ProductId = ?
        WHERE co.Status = 100 OR co.Status = 110 HAVING totalBalance > 0";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $productId, $productId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['Status'] == 110) {
            $partial[] = $row;
        } else {
            $new[] = $row;
        }
    }
    $data['new'] = $new;
    $data['partial'] = $partial;
    return $data;
}

function getMainDO()
{
    global $conn;
    $data = [];
    $query = "SELECT DOId, DONumber, 
    DATE_FORMAT(CreatedDate, '%a, %b %d, %h:%i%p')  AS CreatedDate,Status 
    FROM DeliveryOrder WHERE Status = 120";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getDOByDOId($doId)
{
    global $conn;
    $data = [];
    $mainData = [];
    $itemsData = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT *
        FROM DeliveryOrder doi
        INNER JOIN LorryDriver LD
        ON LD.LorryDriverId = doi.LorryDriver
        INNER JOIN Lorry L
        ON L.LorryId = doi.LorryId
        WHERE doi.DOId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $doId);
        $stmt->execute();
        $mainData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $query1 = "SELECT *
        FROM DeliveryOrderItem doi
        INNER JOIN Product p
        ON p.ProductId = doi.ProductId
        WHERE doi.DOId = ?";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("s", $doId);
        $stmt1->execute();
        $result = $stmt1->get_result();
        while ($row = $result->fetch_assoc()) {
            $name = formatProductName($row);
            $row['name'] = $name;
            $itemsData[] = $row;
        }
        $stmt1->close();

        // $query = "SELECT i.ProductId, i.Quantity, CONCAT(p.ProductType, ' ' , p.ProductName, ' ' ,p.ProductSize
        // , ' - ' , CASE WHEN  i.ProductThkRemarks IS NULL THEN ' ' ELSE CONCAT(i.ProductThkRemarks, ' ') END, p.ProductThickness, ' (', p.ProductCondition , ') ', 
        // CASE WHEN  p.ProductRemark IS NULL THEN ' ' ELSE CONCAT('- ', p.ProductRemark) END, ' ',
        // CASE WHEN  i.ProductColor IS NULL THEN ' ' ELSE i.ProductColor END) AS ProductName 
        // , p.Picture
        // FROM CustomerOrderItem i
        //    INNER JOIN Product p
        //    ON p.ProductId = i.ProductId 
        //    WHERE i.COId = ?";


        // $stmt1 = $conn->prepare($query);
        // $stmt1->bind_param("s", $coId);
        // $stmt1->execute();
        // $result = $stmt1->get_result();
        // while ($row = $result->fetch_assoc()) {
        //     $subData[] = $row;
        // }
        // $stmt1->close();

        // if ($mainData['Status'] == 110 && $mainData['Status'] == 120) {
        //     $query = "SELECT di.ProductId, di.Quantity FROM DeliveryOrder d
        //     INNER JOIN DeliveryOrderItem di
        //     ON di.DOId = d.DOId
        //     WHERE d.COId = ?";
        //     $stmt2 = $conn->prepare($query);
        //     $stmt2->bind_param("s", $coId);
        //     $stmt2->execute();
        //     $result = $stmt2->get_result();
        //     while ($row = $result->fetch_assoc()) {
        //         $deliveredData[] = $row;
        //     }

        //     $sum = array_reduce($deliveredData, function ($a, $b) {
        //         isset($a[$b['ProductId']]) ? $a[$b['ProductId']]['Quantity'] += $b['Quantity'] : $a[$b['ProductId']] = $b;
        //         return $a;
        //     });
        // }
        if ($conn->autocommit(TRUE)) {
            $data['mainData'] = $mainData;
            $data['itemData'] = $itemsData;

            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function formatProductName($item)
{
    $name = "";
    if ($item['ProductType'] != null) {
        $name = $name .  $item['ProductType'] . ' ' . $item['ProductName'] . '<br/>';
    } else {
        $name = $name . $item['ProductName'] . '<br/>';
    }
    if ($item['ProductSize'] != null) {
        $name = $name . ' ' . $item['ProductSize'] . '<br/>';
    }
    if ($item['ProductThkRemarks'] != null && $item['ProductThickness']) {
        $name = $name . ' ' . $item['ProductThkRemarks'] . ' ' . $item['ProductThickness'] . '<br/>';
    } else {
        if ($item['ProductCondition'] != null) {
            $name = $name . ' - ' . '<br/>';
        }
    }
    if ($item['ProductCondition'] != null) {
        $name = $name . '(' . $item['ProductCondition'] . ')';
    }

    if ($item['ProductRemark'] != null && $item['ProductColor']) {
        $name = $name . ' ' . $item['ProductRemark'] . ' ' . $item['ProductColor'];
    }
    return $name;
}

function getCOByCOId($coId)
{
    global $conn;
    $subData = [];
    $delData = [];
    $deliveredData = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT c.COId, c.CONumber, DATE_FORMAT(c.CreatedDate, '%a, %b %d, %h:%i%p')  AS CreatedDate, 
        c.Status,  c.Company, Category,
        DATE_FORMAT(c.UpdatedDate, '%a, %b %d, %h:%i%p')  AS UpdatedDate, c.Remarks, cu.CustomerName, cu.ShippingAddress
        FROM CustomerOrder c
        INNER JOIN User u1
        ON c.CreatedBy = u1.UserId
        INNER JOIN User u2
        ON c.UpdateBy = u2.UserId
        LEFT JOIN Customer cu
        ON c.CustomerId = cu.CustomerId 
        WHERE COId = ? AND c.Status != 200";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $coId);
        $stmt->execute();
        $mainData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $query = "SELECT i.ProductId, 
       (CASE WHEN p.ProductType IS NULL 
                    THEN p.ProductName 
                    ELSE CONCAT(p.ProductType
                    , ' '
                    , p.ProductName
                    ,(CASE WHEN p.ProductSize IS NULL THEN ''ELSE CONCAT(' ', p.ProductSize) END)
                    ,(CASE WHEN i.ProductThkRemarks IS NULL THEN '' ELSE CONCAT(' - ', i.ProductThkRemarks) END)
                    ,(CASE WHEN p.ProductThickness IS NULL THEN '' ELSE CONCAT(' ', p.ProductThickness) END)
                    ,(CASE WHEN p.ProductCondition IS NULL THEN '' ELSE CONCAT(' (', p.ProductCondition, ')') END)
                    ,(CASE WHEN p.ProductRemark IS NULL THEN '' ELSE CONCAT(' ', p.ProductRemark) END)
                    ,(CASE WHEN i.ProductColor IS NULL THEN '' ELSE CONCAT(' ', i.ProductColor) END)
                        ) 
                    END) AS ProductName
        , p.Picture,  p.Balance, i.ProductColor, i.ProductThkRemarks,
        i.Quantity + (SELECT (CASE WHEN SUM(Exch.ExchQuantity) IS NULL THEN 0 ELSE SUM(Exch.ExchQuantity) END)
            FROM ItemExchangeLog Exch
            WHERE Exch.COid = ? AND Exch.ExchProductId = i.ProductId AND Exch.OldProductThkRemarks =  i.ProductThkRemarks AND Exch.OldProductColor = i.ProductColor
            ) - (SELECT (CASE WHEN SUM(Exch.ExchQuantity) IS NULL THEN 0 ELSE SUM(Exch.ExchQuantity) END)
            FROM ItemExchangeLog Exch
            WHERE Exch.COid = ? AND Exch.OldProductId = i.ProductId AND Exch.OldProductThkRemarks =  i.ProductThkRemarks AND Exch.OldProductColor = i.ProductColor
            ) AS Quantity
        FROM CustomerOrderItem i
        INNER JOIN Product p
        ON p.ProductId = i.ProductId
        WHERE i.COId = ?
        UNION
        SELECT Exch.ExchProductId, 
       (CASE WHEN p.ProductType IS NULL 
                    THEN p.ProductName 
                    ELSE CONCAT(p.ProductType
                    , ' '
                    , p.ProductName
                    ,(CASE WHEN p.ProductSize IS NULL THEN ''ELSE CONCAT(' ', p.ProductSize) END)
                    ,(CASE WHEN Exch.ExchProductThkRemarks IS NULL THEN '' ELSE CONCAT(' - ', Exch.ExchProductThkRemarks) END)
                    ,(CASE WHEN p.ProductThickness IS NULL THEN '' ELSE CONCAT(' ', p.ProductThickness) END)
                    ,(CASE WHEN p.ProductCondition IS NULL THEN '' ELSE CONCAT(' (', p.ProductCondition, ')') END)
                    ,(CASE WHEN p.ProductRemark IS NULL THEN '' ELSE CONCAT(' ', p.ProductRemark) END)
                    ,(CASE WHEN Exch.ExchProductColor IS NULL THEN '' ELSE CONCAT(' ', Exch.ExchProductColor) END)
                        ) 
                    END) AS ProductName
        , p.Picture,  p.Balance, Exch.ExchProductColor, Exch.ExchProductThkRemarks,
        SUM(Exch.ExchQuantity)
        FROM ItemExchangeLog Exch
        INNER JOIN Product p
        ON p.ProductId = Exch.ExchProductId
        WHERE Exch.COId =  ? AND (Exch.ExchProductId NOT IN
        (SELECT coi.ProductId FROM CustomerOrderItem coi 
        INNER JOIN CustomerOrder co
        ON co.COId = coi.COId
        WHERE co.COId = ?))
        GROUP BY Exch.ExchProductId
        ";
        $stmt1 = $conn->prepare($query);
        $stmt1->bind_param("sssss", $coId, $coId, $coId, $coId, $coId);
        $stmt1->execute();
        $result = $stmt1->get_result();
        while ($row = $result->fetch_assoc()) {
            $subData[] = $row;
        }
        $stmt1->close();

        $query2 = "SELECT  doi.ProductId, SUM(doi.Quantity) AS TotalDelivered,
                (coi.Quantity - SUM(doi.Quantity)) AS Balance
                FROM DeliveryOrderItem doi
                INNER JOIN DeliveryOrder d 
                ON d.DOId = doi.DOId
                INNER JOIN CustomerOrderItem coi
                ON coi.COId = ?  AND coi.ProductId = doi.ProductId
                WHERE d.COId = ?
                GROUP BY (doi.ProductId)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("ss", $coId, $coId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $balance = [];
        while ($row2 = $result2->fetch_assoc()) {
            $balance[] = $row2;
        }

        $query3 = "SELECT LD.LorryDriverId, LD.LorryDriverName, L.LorryId, L.LorryNumber, W.WareHouseId, W.WareHouseName
            FROM DeliveryOrder d
            LEFT JOIN LorryDriver LD
            ON d.LorryDriver = LD.LorryDriverID
            LEFT JOIN Lorry L
            ON d.LorryId = L.LorryId
            INNER JOIN WareHouse W
            ON W.WareHouseId = d.FromWareHouse
            WHERE d.COId = ? AND d.Status != 200";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("s", $coId);
        $stmt3->execute();
        $delData = $stmt3->get_result()->fetch_assoc();

        if ($conn->autocommit(TRUE)) {
            $data['mainData']  = $mainData;
            $data['subData']  = $subData;
            $data['deliveryData']  = $balance;
            $data['deliveryInfo']  = $delData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $data;
}

function getAllLorryDriver()
{
    global $conn;
    $data = [];
    $query = "SELECT * FROM LorryDriver 
    WHERE Active = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getAllLorryNumber()
{
    global $conn;
    $data = [];
    $query = "SELECT * FROM Lorry
    WHERE Active = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getAllWarehouse()
{
    global $conn;
    $data = [];
    $query = "SELECT * FROM WareHouse
    WHERE Active = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/*-----------------
	8. STOCK & PRODUCT
-----------------------*/
function getAllCustomer()
{
    global $conn;
    $query = "SELECT CustomerName, CustomerId FROM Customer WHERE Active = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function updateStock($productId, $quantity, $category, $type, $userId, $customerId, $attachment)
{
    global $conn;

    try {
        $conn->autocommit(FALSE); //turn on transactions\

        $query = "INSERT INTO Stock(ProductId, Category, Type, Quantity,  CreatedBy, CustomerId, Attachment)
        VALUES (?, ?, ?, ?, ?, ?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $productId, $category, $type, $quantity, $userId, $customerId, $attachment);
        $stmt->execute();
        $stmt->close();
        if ($customerId == null) {
            if ($type == '1') {
                $query1 = "UPDATE Product SET Balance = Balance + ? WHERE ProductId = ?";
            } else {
                $query1 = "UPDATE Product SET Balance = Balance - ? WHERE ProductId = ?";
            }
            $stmt1 = $conn->prepare($query1);
            $stmt1->bind_param("is", $quantity, $productId);
            $stmt1->execute();
            $stmt1->close();
        }


        if ($conn->autocommit(TRUE)) {
            return true;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return false;
}

function alertMinQuantity($productId)
{
    global $conn;
    $query = "UPDATE Product SET isHitMin = TRUE WHERE ProductId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $productId);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
    $stmt->close();
}

function getProductWithSpecs($specs, $detail, $isOwn)
{
    global $conn;
    $detailLength = count($detail);
    if ($detailLength == 0) {
        if ($isOwn) {
            $query = "SELECT SUM(Balance) AS Total, " . $specs . " , 
            ProductType, ProductSize, ProductThickness, ProductCondition, ProductRemark, ProductId, MinQuantity
            FROM Product WHERE Active = TRUE AND isOwn = TRUE
            GROUP BY " . $specs;
        } else {
            $query = "SELECT SUM(Balance) AS Total, " . $specs . " , 
            ProductType, ProductSize, ProductThickness, ProductCondition, ProductRemark, ProductId, MinQuantity
            FROM Product WHERE Active = TRUE
            GROUP BY " . $specs;
        }

        $data = [];
        $productData = [];
        $stmt = $conn->prepare($query);
        // $stmt->bind_param("s", $specs);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productData[] = $row;
        }
        $data['product'] = $productData;
        return $data;
    } else {
        $whereArgs = "";
        for ($i = 0; $i < $detailLength; $i++) {
            if ($detailLength - $i == 1) {
                $whereArgs = $whereArgs . $detail[$i]['key'] . " = '" . $detail[$i]['value'] . "'";
            } else {

                $whereArgs = $whereArgs . $detail[$i]['key'] . " = '" . $detail[$i]['value'] . "' AND ";
            }
        }
        if ($isOwn) {
            $query = "SELECT SUM(Balance) AS Total, " . $specs . " , 
            ProductType, ProductSize, ProductThickness, ProductCondition, ProductRemark , ProductId, MinQuantity
            FROM Product
            WHERE " . $whereArgs . " AND Active = TRUE AND isOwn = TRUE" .
                " GROUP BY " . $specs;
        } else {
            $query = "SELECT SUM(Balance) AS Total, " . $specs . " , 
            ProductType, ProductSize, ProductThickness, ProductCondition, ProductRemark , ProductId, MinQuantity
            FROM Product
            WHERE " . $whereArgs . " AND Active = TRUE" .
                " GROUP BY " . $specs;
        }

        $data = [];
        $productData = [];
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productData[] = $row;
        }
        $data['product'] = $productData;
        return $data;
    }
}

function getCustomerStockBalanceByCustId($customerId, $productId)
{
    global $conn;
    $data = [];
    $stockIn = 0;
    $stockOut = 0;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT Type, SUM(Quantity) AS Balance 
        FROM Stock 
        WHERE ProductId = ? AND CustomerId = ? GROUP BY Type";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("ss", $productId, $customerId);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        while ($row1 = $result1->fetch_assoc()) {
            if ($row1['Type'] == 1) {
                $stockIn = $row1['Balance'];
            } else if ($row1['Type'] == 2) {
                $stockOut = $row1['Balance'];
            }
        }
        $data['balance'] = $stockIn - $stockOut;
        if ($conn->autocommit(TRUE)) {
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function getAllProductName()
{
    global $conn;
    $subdata = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query = "SELECT ProductName, Picture FROM Product WHERE Active = true  GROUP BY ProductName";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $subdata = getAllProductSpecs($row["ProductName"]);
            $row['subRows'] = $subdata;
            $data[] = $row;
        }

        if ($conn->autocommit(TRUE)) {
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $data;
}

function getAllProductSpecs($productName)
{
    $subData = [];
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT DISTINCT ProductType FROM Product WHERE ProductName = ?";
        $query2 = "SELECT DISTINCT ProductSize FROM Product WHERE ProductName = ?";
        $query3 = "SELECT DISTINCT ProductThickness FROM Product WHERE ProductName = ?";
        $query4 = "SELECT DISTINCT ProductCondition FROM Product WHERE ProductName = ?";
        $query5 = "SELECT DISTINCT ProductRemark FROM Product WHERE ProductName = ?";
        $query = array($query1, $query2, $query3, $query4, $query5);


        for ($i = 0; $i < 5; $i++) {
            $stmt = $conn->prepare($query[$i]);
            $stmt->bind_param("s", $productName);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $subData[] = $row;
            }
            $data[$i] = $subData;
            $subData = [];
        }

        if ($conn->autocommit(TRUE)) {
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $data;
}

function getProductDetails(
    $productName,
    $productType,
    $productSize,
    $productThkRemark,
    $productThickness,
    $productCondition,
    $productRemark,
    $productColor
) {
    global $conn;
    $result = [];
    $query = "SELECT * FROM Product WHERE ProductName = ? 
    AND (CASE WHEN  ProductType IS NULL  THEN ProductType IS NULL ELSE ProductType = ? END)
    AND (CASE WHEN  ProductSize IS NULL  THEN ProductSize IS NULL ELSE ProductSize = ? END)
    AND (CASE WHEN  ProductThkRemarks IS NULL  THEN ProductThkRemarks IS NULL ELSE ProductThkRemarks = ? END)
    AND (CASE WHEN  ProductThickness IS NULL  THEN ProductThickness IS NULL ELSE ProductThickness = ? END)
    AND (CASE WHEN  ProductCondition IS NULL  THEN ProductCondition IS NULL ELSE ProductCondition = ? END)
    AND (CASE WHEN  ProductRemark IS NULL  THEN ProductRemark IS NULL ELSE ProductRemark = ? END)
    AND (CASE WHEN  ProductColor IS NULL  THEN ProductColor IS NULL ELSE ProductColor = ? END)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssssss",
        $productName,
        $productType,
        $productSize,
        $productThkRemark,
        $productThickness,
        $productCondition,
        $productRemark,
        $productColor
    );
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result;
}

function updateProductBalance($id, $balance)
{
    global $conn;
    $query = "UPDATE Product SET Balance = ? WHERE ProductId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $balance, $id);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function getIsHitMinProduct()
{
    global $conn;
    $data = [];
    $query = "SELECT * FROM Product WHERE isHitMin = TRUE LIMIT 30";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getProductTransaction()
{
    global $conn;
    $data = [];
    $query = "SELECT * , DATE(s.CreatedDate) AS StockCreated
    FROM Stock s
    INNER JOIN Product p
    ON s.ProductId = p.ProductId
    GROUP BY s.CreatedDate DESC LIMIT 30";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getIsHitMinAndProductTrans()
{
    global $conn;
    $data = [];
    $isHitMinData = [];
    $stockData = [];

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $isHitMinData = getIsHitMinProduct();
        $stockData = getProductTransaction();
        if ($conn->autocommit(TRUE)) {
            $data['isHitMin'] = $isHitMinData;
            $data['stockData'] = $stockData;

            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}


/*-----------------
	9. Inbox
-----------------------*/

function getInbox($limit, $userId)
{

    $data = [];
    global $conn;
    $query = "SELECT i.InboxId, i.Title, i.Content,  DATE_FORMAT(i.UpdateDate, '%b %d') AS UpdateDate, 
    YEAR(i.UpdateDate) AS Year, MONTH(i.UpdateDate) AS Month, DAYOFMONTH(i.UpdateDate) AS Date,
    DATE_FORMAT(i.UpdateDate, '%h:%i%p') AS Time,
    role.RoleName AS Role, r.isRead AS IsRead
    FROM Inbox i
    LEFT JOIN InboxIsRead r
    ON r.InboxId = i.InboxId AND r.UserId = ?
    INNER JOIN UserRoles ur
    ON ur.UserId = i.UpdateBy
    INNER JOIN Roles role
    ON role.RoleId = ur.RoleId 
    INNER JOIN User u 
    ON u.UserId = ? AND i.UpdateDate > u.CreateDate
    ORDER BY i.UpdateDate DESC
    LIMIT ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $userId, $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function updateInboxRead($inboxId, $userId)
{
    global $conn;
    $query = "INSERT INTO InboxIsRead(InboxId, UserId, IsRead) VALUES (?,?, true)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $inboxId, $userId);
    $stmt->execute();
    $stmt->close();
}



/*-----------------
	10. Leave
-----------------------*/

function cancelLeave($leaveTransactionId)
{
    global $conn;
    $query = "DELETE FROM LeaveTransactions WHERE LeaveTransactionId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $leaveTransactionId);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        return false;
    } else {
        return true;
    }
}

function getAllLeaveAvailability($userId)
{
    global $conn;
    $data = [];
    $entitlement = [];
    $leaveData = [];
    $leaveUsed = [];

    try {
        $conn->autocommit(FALSE); //turn on transactions

        $query = "SELECT * ,
        (CASE WHEN  LEnt.ExpiryDate IS NOT NULL THEN CURRENT_DATE > LEnt.ExpiryDate ELSE NULL END) AS isExpired,
        MONTH(CURRENT_TIME()) AS CurMonth
        FROM LeaveEntitlement LEnt
        INNER JOIN LeaveType LType
        ON LEnt.LeaveTypeId = LType.LeaveTypeId
        WHERE Year = YEAR(CURDATE()) AND UserId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $entitlement[] = $row;
        }
        $stmt->close();

        $query = "SELECT * , DATEDIFF( LTrans.DateFROM , CURRENT_TIME()) AS DayLeftToLeave,
        (CASE WHEN LType.LeaveTypeCode = 'HD' || LType.LeaveTypeCode = 'TL'
        THEN
        CONCAT(DATE_FORMAT(LTrans.DateFROM, '%b %d, %h:%i%p'),' - ',DATE_FORMAT(LTrans.DateTo, '%b %d, %h:%i%p')) 
        ELSE
        CONCAT(DATE_FORMAT(LTrans.DateFROM, '%b %d'),' - ',DATE_FORMAT(LTrans.DateTo, '%b %d')) 
        END),
        LType.LeaveTypeCode
        AS DateTaken
        FROM LeaveTransactions LTrans 
        INNER JOIN LeaveType LType
        ON LTrans.LeaveTypeId = LType.LeaveTypeId
        WHERE LTrans.UserId = ? 
        AND YEAR(LTrans.UpdateDate) = YEAR(CURRENT_DATE) 
        GROUP BY LTrans.UpdateDate DESC ";
        $stmt1 = $conn->prepare($query);
        $stmt1->bind_param("s", $userId);
        $stmt1->execute();
        $result = $stmt1->get_result();
        while ($row = $result->fetch_assoc()) {
            $leaveData[] = $row;
        }
        $stmt1->close();

        $query = "SELECT SUM(LTrans.TotalDays) AS TotalDays, LTrans.LeaveTypeId,LType.DeductFromLeave,LType.LeaveTypeCode

        FROM LeaveTransactions LTrans
        INNER JOIN LeaveType LType
        ON LTrans.LeaveTypeId = LType.LeaveTypeId
        WHERE YEAR(LTrans.DateFrom) = YEAR(CURRENT_TIME()) AND LTrans.UserId = ? AND Status != 320 
        GROUP BY LTrans.LeaveTypeId";
        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param("s", $userId);
        $stmt2->execute();
        $result = $stmt2->get_result();
        while ($row = $result->fetch_assoc()) {
            $leaveUsed[] = $row;
        }
        $stmt2->close();


        if ($conn->autocommit(TRUE)) {
            $data['entitlement'] = $entitlement;
            $data['leaveData'] = $leaveData;
            $data['leaveTaken'] = $leaveUsed;
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function getAvailableLeave($leaveType, $conn, $userId)
{
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
            return $totalAvailable - $totalLeaveUsed;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function getLeaveUsed($userId, $date, $leaveType)
{
    global $conn;
    $data = [];
    $entitlement = [];
    $leaveData = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions

        $query = "SELECT * ,(CASE WHEN  ExpiryDate IS NOT NULL THEN CURRENT_DATE > ExpiryDate ELSE NULL END) AS isExpired
        FROM LeaveEntitlement WHERE Year = YEAR(?) AND UserId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $date, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $entitlement[] = $row;
        }
        if (count($entitlement) == 0) {
            $data['code'] = 100;
            $data['message'] = 'Your leave entitlement is not set in the system. Please contact admin';
            return $data;
        }

        $query = "SELECT * 
        FROM LeaveTransactions WHERE YEAR(DateFrom) = YEAR(?) AND UserId = ? AND Status != 320 ";
        $stmt1 = $conn->prepare($query);
        $stmt1->bind_param("ss", $date, $userId);
        $stmt1->execute();
        $result = $stmt1->get_result();
        while ($row = $result->fetch_assoc()) {
            $leaveData[] = $row;
        }
        $stmt1->close();

        if ($conn->autocommit(TRUE)) {
            $data['entitlement'] = $entitlement;
            $data['leaveData'] = $leaveData;
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function applyLeave($leaveCode, $userId, $leaveData, $imgUrl)
{
    global $conn;
    $errorData = [];
    $data = [];
    $leaveTypeInfo = getLeaveTypeInfo($leaveCode);

    if ($leaveTypeInfo['code'] == 100) {
        return $leaveTypeInfo;
    } else {
        $leaveValidationInfo = getLeaveValidationInfo($leaveTypeInfo['data']['LeaveTypeId']);
        if ($leaveValidationInfo['code'] == 100) {
            return $leaveValidationInfo;
        } else {
            try {
                $conn->autocommit(FALSE); //turn on transactions

                $approvalsInfo = getApprovalForLeave($userId);
                if ($approvalsInfo['code'] == 100) {
                    return $approvalsInfo;
                } else {

                    if ($leaveValidationInfo['data']['CheckForEL']) {
                        $noticePeriodDay = $leaveValidationInfo['data']['ELNoticeDay'];
                        if (noticePeriodCheck($leaveData['DateFrom'], $noticePeriodDay)) {
                            // do nothing - check pass
                        } else {
                            $data['code'] = 100;
                            $data['message'] = "You notice period is less than " . $noticePeriodDay . ". Please apply for Emergency Leave instead.";
                            return $data;
                        }
                    }
                    if ($leaveValidationInfo['data']['CheckMaxApplyInDept']) {
                        $departmentApplyResult = checkForMaxApplyDept($leaveData['dateDetails'], $userId, $leaveValidationInfo['data']['TotalNumberApplied']);
                        if ($departmentApplyResult['code'] == 200) {
                            // do nothing - check pass
                        } else {
                            $data['code'] = 100;
                            $data['message'] = $departmentApplyResult['message'];
                            $data['data'] = $departmentApplyResult['data'];
                            return $data;
                        }
                    }

                    // if ($leaveValidationInfo['data']['CheckMinDayNotice']) {
                    //     //todo
                    // }

                    $insertResult = insertIntoLeaveTrans($approvalsInfo['data'], $leaveData, $userId, $leaveTypeInfo['data'], $leaveValidationInfo['data'], $imgUrl);
                    if ($insertResult['code'] == 100) {
                        $data['code'] = 100;
                        $data['message'] = $insertResult['message'];
                        $data['data'] = $insertResult['data'];
                        return $data;
                    }
                }
                if ($conn->autocommit(TRUE)) {
                    $data['code'] = 200;
                    $leaveData['leaveTransactionId'] = $insertResult['leaveTransactionId'];
                    $data['data'] = $leaveData;
                    return $data;
                }
            } catch (Exception $e) {
                $conn->rollback(); //remove all queries from queue if error (undo)
                throw $e;
            }
        }
    }
}
function isHoliday($startDate, $endDate)
{
    $isHoliday = false;
    global $conn;
    $total = 0;
    $data = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT DATEDIFF(?, ?) + 1 AS dateDiff";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("ss", $endDate, $startDate);
        $stmt1->execute();
        $dateDiff = $stmt1->get_result()->fetch_assoc();

        for ($x = 0; $x < $dateDiff['dateDiff']; $x++) {
            $query2 = "SELECT DAYNAME(DATE(? + INTERVAL ? DAY)) AS day, DATE(? + INTERVAL ? DAY) AS sDate";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("sisi", $startDate,  $x, $startDate,  $x);
            $stmt2->execute();
            $day = $stmt2->get_result()->fetch_assoc();
            switch ($day['day']) {
                case 'Saturday':
                case 'Sunday':
                    $data[] = $day['sDate'] .  ' WEEKEND';
                    $isHoliday = true;
                    break;
                case 'Monday':
                case 'Tuesday':
                case 'Wednesday':
                case 'Thursday':
                case 'Friday':
                    $query = "SELECT PublicHolidayDate
                    FROM PublicHoliday 
                    WHERE PublicHolidayDate = DATE(? + INTERVAL ? DAY)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("si", $startDate,  $x);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    if ($result == null || count($result) == 0) {
                        $isHoliday = false;
                        $data[] =  date('Y-m-d', strtotime($startDate . ' +' . $x . 'day')) .  ' Leave deduct';
                    } else {
                        $isHoliday = true;
                        $data[] = $result['PublicHolidayDate'] .  ' Public Holiday';
                    }
                    break;
            }
            if (!$isHoliday) {
                $total = $total + 1;
            }
            $isHoliday = false;
        }

        if ($conn->autocommit(TRUE)) {
            $holidayData = [];
            $holidayData['details'] = $data;
            $holidayData['total'] = $total;
            return $holidayData;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}
function insertIntoLeaveTrans($approvalsInfo, $leaveData, $userId, $leaveTypeInfo, $leaveValidationInfo, $imgUrl)
{
    global $LEAVE_PENDING;
    global $conn;
    $leaveTypeId = $leaveTypeInfo['LeaveTypeId'];
    $dateFrom =  $leaveData['DateFrom'];
    $dateTo = $leaveData['DateTo'];
    $dayDiff = $leaveData['dayDiff'] == NULL ? NULL : $leaveData['dayDiff'];
    $timeDiff = $leaveData['timeDiff'] == NULL ? NULL : $leaveData['timeDiff'];

    $isHalfDay = $leaveData['isHalfDay'] == NULL ? NULL : $leaveData['isHalfDay'];
    $AmPm = $leaveData['AmPm'] == NULL ? NULL : $leaveData['AmPm'];
    $replaceDateFrom = $leaveData['ReplaceDateFrom'] == NULL ? NULL : $leaveData['ReplaceDateFrom'];
    $replaceDateTo   = $leaveData['ReplaceDateTo'] == NULL ? NULL : $leaveData['ReplaceDateTo'];
    $reason = $leaveData['Reason'];
    $approval1 = $approvalsInfo['Approval1'];
    $approval2 = $approvalsInfo['Approval2'];
    try {
        $data = [];
        $conn->autocommit(FALSE); //turn on transactions
        if ($dayDiff == null) {
            $result = isHoliday($dateFrom, $dateTo);
            $dayDiff = (int) $result['total'];
        }
        $timeDiffCalc = (int) $result['total'] * 24;
        if ($leaveTypeInfo['DeductLeave']) {
            $leaveLeft = getAvailableLeave($leaveTypeInfo['LeaveTypeCode'], $conn, $userId);
        }

        if ($leaveValidationInfo['CheckMaxDayApply']) {
            if ($dayDiff > $leaveValidationInfo['MaxDayCanApply']) {
                $data['code'] = 100;
                $data['message'] = "Cannot apply exceed than " . $leaveValidationInfo['MaxDayCanApply'] . " days";
                return $data;
            }
        }
        $noticePeriod = 0;
        if ($leaveValidationInfo['CheckMinDayNotice']) {
            if ($dayDiff >= $leaveValidationInfo['MinDayApply']) {
                $queryMinDN = "SELECT DATEDIFF(?, CURRENT_TIME()) AS minDayDiff";
                $smntMinDN =  $conn->prepare($queryMinDN);
                $smntMinDN->bind_param('s', $dateFrom);
                $smntMinDN->execute();
                $noticePeriod = $smntMinDN->get_result()->fetch_assoc()['minDayDiff'];
                if ($noticePeriod < $leaveValidationInfo['MinNoticePeriod']) {
                    $data['code'] = 100;
                    $data['message'] = "Cannot apply more than " . $leaveValidationInfo['MinDayApply'] . " days with notice less than " . $leaveValidationInfo['MinNoticePeriod'];
                    return $data;
                }
            }
        }

        if ($conn->autocommit(TRUE)) {
            if ($leaveTypeInfo['DeductLeave'] && ($dayDiff > $leaveLeft)) {
                $data['code'] = 100;
                $data['message'] = "Leave balance not enough. Please apply unpaid leave instead";
                $subData = [];
                $subData['leaveData'] = $leaveData;
                $subData['deductDays'] = $dayDiff;
                $subData['noticePeriod'] = $noticePeriod;
                $subData['leaveLeft'] = $leaveLeft;
                $data['data'] = $subData;
                return $data;
            } else {
                $query = "INSERT INTO LeaveTransactions(UserId, LeaveTypeId, DateFrom, DateTo,
                        isHalfDay, AmPm, TotalDays, TotalHours, ReplaceDateFrom, ReplaceDateTo, Reason, 
                    Status, CreatedBy, CreatedDate, UpdateDate, UpdateBy, Approver1, Approver2, Attachment)
                    VALUES($userId, $leaveTypeId, ?, ?, 
                    (CASE WHEN ? IS NULL THEN NULL ELSE ? END),
                    (CASE WHEN ? IS NULL THEN NULL ELSE ? END),
                    (CASE WHEN ? IS NULL THEN ? ELSE ? END),
                    (CASE WHEN ? IS NULL THEN ? ELSE ? END),
                    (CASE WHEN ? IS NULL THEN NULL ELSE ? END),
                    (CASE WHEN ? IS NULL THEN NULL ELSE ? END),
                    ?, ?, $userId, CURRENT_TIME(), CURRENT_TIME(), $userId,
                    ?, ?, ?)";
                $stmt1 = $conn->prepare($query);
                $imgUrl == null ? null : $imgUrl;

                $stmt1->bind_param(
                    "ssssssssssssssssdssss",
                    $dateFrom,
                    $dateTo,
                    $isHalfDay,
                    $isHalfDay,
                    $AmPm,
                    $AmPm,
                    $dayDiff,
                    $result['total'],
                    $dayDiff,
                    $timeDiff,
                    $timeDiffCalc,
                    $timeDiff,
                    $replaceDateFrom,
                    $replaceDateFrom,
                    $replaceDateTo,
                    $replaceDateTo,
                    $reason,
                    $LEAVE_PENDING,
                    $approval1,
                    $approval2,
                    $imgUrl
                );
                $stmt1->execute();
                $leaveTransactionId = $stmt1->insert_id;
                $FCMToken = getFCMTokenByUserId($conn, $approval1);
                send($FCMToken, 'LEAVE APPROVE', 'You have new leave pending for your approval.');
                $insertResult['code'] = 200;
                $insertResult['leaveTransactionId'] = $leaveTransactionId;
                return $insertResult;
            }
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return  null;
}

function noticePeriodCheck($startDate, $noticePeriod)
{
    global $conn;
    $query = "SELECT DATEDIFF(?,CURRENT_TIME()) + 1 AS NoticePeriod";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("s", $startDate);
    $stmt1->execute();
    $result = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();
    if ($result['NoticePeriod'] > $noticePeriod) {
        return true;
    } else {
        return false;
    }
}

function checkForMaxApplyDept($dateArray, $userId, $maxDepartmentApply)
{
    global $conn;
    $data = [];
    try {
        $conn->autocommit(FALSE); //turn on transactions

        foreach ($dateArray as &$date) {
            $query = "SELECT COUNT(LT.LeaveTransactionId) AS TotalApplied ,MONTHNAME(?) AS MonthName, DAYOFMONTH(?) AS DayOfMonth, YEAR(?) AS Year
            FROM LeaveTransactions LT
            INNER JOIN User U1
            ON LT.UserId = U1.UserId
            INNER JOIN User U2
            ON LT.UserId = U2.UserId
            WHERE LT.TotalDays >= 1 AND LT.DateFrom <= ?
            AND ? <= LT.DateTo AND LT.UserId != ? AND U1.Department = U2.Department";
            $stmt1 = $conn->prepare($query);
            $stmt1->bind_param("ssssss", $date, $date, $date, $date, $date, $userId);
            $stmt1->execute();
            $result = $stmt1->get_result()->fetch_assoc();
            $stmt1->close();
            if ($result['TotalApplied'] >= $maxDepartmentApply) {
                $data['code'] = 100;
                $data['message'] = "The leave on " . $result['MonthName'] . " " .  $result['DayOfMonth'] .  ", " . $result['Year'] . " already reached max department limit. Please try another date.";
                return $data;
            }
        }
        if ($conn->autocommit(TRUE)) {
            $data['code'] = 200;
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return  null;
}

function getPublicHoliday($dateArray)
{
    global $conn;
    $data = [];
    try {
        foreach ($dateArray as &$date) {
            $query = "SELECT PublicHolidayTitle AS PHDesc, PublicHolidayDate AS PHDate
                FROM PublicHoliday
                WHERE PublicHolidayDate = ?";
            $stmt1 = $conn->prepare($query);
            $stmt1->bind_param("s", $date);
            $stmt1->execute();
            $result = $stmt1->get_result()->fetch_assoc();
            $stmt1->close();
            if ($result == null) {
                $query1 = "SELECT DAYNAME(?) AS PHDesc, ? AS PHDate";
                $stmt2 = $conn->prepare($query1);
                $stmt2->bind_param("ss", $date, $date);
                $stmt2->execute();
                $result2 = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
                $data[] = $result2;
            } else {
                $data[] = $result;
            }
        }
        if ($conn->autocommit(TRUE)) {
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return  null;
}

function getApprovalForLeave($userId)
{
    global $conn;
    global $LEAVE_MODULE_ID;
    $data = [];
    $errorData = [];
    $query = "SELECT * 
    FROM ApprovalLink AL
    INNER JOIN Approval A
    ON A.ApprovalId = AL.ApprovalId
    WHERE AL.ObjectTypeId = $LEAVE_MODULE_ID AND AL.UserId = ?";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("s", $userId);
    $stmt1->execute();
    $data = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();
    if (count($data) == 0) {
        $errorData['code'] = 100;
        $errorData['message'] = 'Leave approval not set';
        return $errorData;
    } else {
        $errorData['code'] = 200;
        $errorData['data'] = $data;
        return $errorData;
    }
}

function getLeaveTypeInfo($leaveCode)
{
    global $conn;
    $data = [];
    $errorData = [];
    $query = "SELECT * FROM  LeaveType WHERE LeaveTypeCode = ?";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("s", $leaveCode);
    $stmt1->execute();
    $data = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();
    if (count($data) == 0) {
        $errorData['code'] = 100;
        $errorData['message'] = 'Leave type not set';
        return $errorData;
    } else {
        if (!$data['Active']) {
            $errorData['code'] = 100;
            $errorData['message'] = 'Leave not active';
            return $errorData;
        } else {
            $errorData['code'] = 200;
            $errorData['data'] = $data;
            return $errorData;
        }
    }
}

function getLeaveValidationInfo($leaveTypeId)
{
    global $conn;
    $data = [];
    $errorData = [];
    $query = "SELECT * FROM LeaveValidationConfig WHERE LeaveTypeId = ?";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("s", $leaveTypeId);
    $stmt1->execute();
    $data = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();
    if (count($data) == 0) {
        $errorData['code'] = 100;
        $errorData['message'] = 'Leave validation not set';
        return $errorData;
    } else {
        $errorData['code'] = 200;
        $errorData['data'] = $data;
        return $errorData;
    }
}


function getLeaveToApprove($approver1Id, $isShowAll)
{
    global $conn;
    $data = [];
    try {
        $conn->autocommit(FALSE);

        if ($isShowAll) {
            $query = "SELECT LT.LeaveTransactionId, DATE(LT.CreatedDate) AS CreatedDate , 
            con.ConfigDescription AS Status, u.EmployeeName AS EmployeeName, LT.Approver1 AS Approver1, LT.Approver2 AS Approver2,
        LT.Approver1Status AS Approver1Status, LT.Approver2Status AS Approver2Status
        FROM LeaveTransactions LT
        INNER JOIN Config con
        ON LT.Status = con.ConfigNumber
        INNER JOIN User u
        ON LT.UserId = u.UserId
        WHERE LT.Approver1 = ? AND LT.Approver1Status != 320 OR (LT.Approver2 = ?  && LT.Approver1Status = 310) 
        ORDER BY LT.CreatedDate DESC
         ";
        } else {
            $query = "SELECT LT.LeaveTransactionId, DATE(LT.CreatedDate) AS CreatedDate ,
            con.ConfigDescription AS Status, u.EmployeeName AS EmployeeName, LT.Approver1 AS Approver1, LT.Approver2 AS Approver2,
            LT.Approver1Status AS Approver1Status, LT.Approver2Status AS Approver2Status, LType.LeaveTypeName
            FROM LeaveTransactions LT
         INNER JOIN Config con
         ON LT.Status = con.ConfigNumber
         INNER JOIN User u
         ON LT.UserId = u.UserId
         INNER JOIN LeaveType LType
         ON LT.LeaveTypeId = LType.LeaveTypeId
         WHERE  LT.Status = 300 AND ((LT.Approver1 = ? && LT.Approver1Status = 300) OR (LT.Approver2 = ? &&  LT.Approver1Status = 310 &&  LT.Approver2Status = 300))
         ORDER BY LT.CreatedDate DESC
         ";
        }
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $approver1Id, $approver1Id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        if ($conn->autocommit(TRUE)) {
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}

function getLeaveByTransactionId($leaveTransactionId)
{
    global $conn;
    $data = [];
    $applicationDetail = [];
    $leaveTypeInfo = [];
    $leaveBalance = [];
    $checkDepartment = [];
    $dateDetails = [];
    $checkMaxDayCanApply = [];

    try {
        $conn->autocommit(FALSE);
        $query = "SELECT u.EmployeeName, c.CompanyName, u.Department, LType.LeaveTypeName,
        (CASE WHEN LTrans.TotalHours < 8 THEN DATE_FORMAT(LTrans.DateFrom, '%b %d, %Y - %h:%i%p') 
        ELSE DATE_FORMAT(LTrans.DateFrom, '%b %d, %Y') END) AS DateFrom,
        (CASE WHEN LTrans.TotalHours < 8 THEN DATE_FORMAT(LTrans.DateTo, '%b %d, %Y - %h:%i%p') 
        ELSE DATE_FORMAT(LTrans.DateTo, '%b %d, %Y') END) AS DateTo,
        (CASE WHEN LTrans.ReplaceDateFrom IS NOT NULL THEN DATE_FORMAT(LTrans.ReplaceDateFrom, '%b %d, %Y - %h:%i%p') 
        ELSE NULL END) AS ReplaceDateFrom,
        (CASE WHEN LTrans.ReplaceDateTo IS NOT NULL THEN DATE_FORMAT(LTrans.ReplaceDateTo, '%b %d, %Y - %h:%i%p') 
        ELSE NULL END) AS ReplaceDateTo,
        LTrans.Reason, LTrans.LeaveTypeId, LTrans.TotalDays, LTrans.UserId, LType.LeaveTypeCode,LTrans.DateFrom,LTrans.DateTo
        FROM LeaveTransactions LTrans
        INNER JOIN User u
        ON u.UserId = LTrans.UserId
        INNER JOIN UserCompany uc
        ON uc.CompanyId = u.UserId
        INNER JOIN Company c
        ON c.CompanyId = uc.CompanyId
        INNER JOIN LeaveType LType
        On LType.LeaveTypeId = LTrans.LeaveTypeId
        WHERE LeaveTransactionId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $leaveTransactionId);
        $stmt->execute();
        $applicationDetail = $stmt->get_result()->fetch_assoc();

        $query2 = "SELECT * FROM LeaveType WHERE LeaveTypeId = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("s", $applicationDetail['LeaveTypeId']);
        $stmt2->execute();
        $leaveTypeInfo = $stmt2->get_result()->fetch_assoc();
        $leaveBalance['deductLeave'] = $leaveTypeInfo['DeductLeave'] == TRUE ? "YES" : "NO";
        $leaveBalance['totalDays'] = $applicationDetail['TotalDays'];
        $availableLeave = getAvailableLeave($applicationDetail['LeaveTypeCode'], $conn, $applicationDetail['UserId']);

        $query3 = "SELECT * FROM LeaveValidationConfig WHERE LeaveTypeId = ?";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("s", $applicationDetail['LeaveTypeId']);
        $stmt3->execute();
        $LeaveValidationConfigInfo = $stmt3->get_result()->fetch_assoc();
        $checkDepartment['checkMaxApply'] = $LeaveValidationConfigInfo['CheckMaxApplyInDept'] == TRUE ? "YES" : "NO";

        $query4 = "SELECT * FROM 
        (SELECT adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) selected_date FROM
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4) v
        WHERE selected_date BETWEEN DATE(?) AND DATE(?)";
        $stmt4 = $conn->prepare($query4);
        $stmt4->bind_param("ss", $applicationDetail['DateFrom'], $applicationDetail['DateTo']);
        $stmt4->execute();
        $dateRes = $stmt4->get_result();
        while ($row = $dateRes->fetch_assoc()) {
            $dateDetails[] = $row['selected_date'];
        }
        if ($LeaveValidationConfigInfo['CheckMaxApplyInDept']) {
            $departmentApplyResult = checkForMaxApplyDept($dateDetails, $applicationDetail['UserId'], $LeaveValidationConfigInfo['TotalNumberApplied']);
            $checkDepartment['totalMaxApplyInDept'] = $LeaveValidationConfigInfo['TotalNumberApplied'];
            if ($departmentApplyResult['code'] == 200) {
                $checkDepartment['maxApplyReached'] = 0;
            } else {
                $checkDepartment['maxApplyReached'] = 1;
                $checkDepartment['maxApplyReachedMessage'] = $departmentApplyResult['message'];
            }
        }
        $checkMaxDayCanApply['CheckMaxDayApply'] = $LeaveValidationConfigInfo['CheckMaxDayApply'] == TRUE ? "YES" : "NO";
        $checkMaxDayCanApply['MaxDayCanApply'] = $LeaveValidationConfigInfo['MaxDayCanApply'];
        $checkMaxDayCanApply['TotalDays'] = $applicationDetail['TotalDays'];
        $checkMaxDayCanApply['PassChecking'] = $LeaveValidationConfigInfo['MaxDayCanApply'] >= $applicationDetail['TotalDays'] ? "YES" : "NO";
        if ($conn->autocommit(TRUE)) {
            $leaveBalance['availableLeave'] = $availableLeave;
            $leaveBalance['newBalance'] = $availableLeave - $applicationDetail['TotalDays'];
            $data['applicationDetail'] = $applicationDetail;
            $data['leaveBalance'] = $leaveBalance;
            $data['department'] = $checkDepartment;
            $data['checkMaxDayCanApply'] = $checkMaxDayCanApply;
            return $data;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}





function approveLeave($leaveTransactionId, $status, $userId)
{
    global $conn;
    try {
        $conn->autocommit(FALSE);
        $query = "SELECT 
        (CASE WHEN Approver1 = ? THEN TRUE ELSE FALSE END) AS Approver1, 
        (CASE WHEN Approver2 = ? THEN TRUE ELSE FALSE END) AS Approver2,
        Approver2 AS Approver2Id
        FROM LeaveTransactions WHERE LeaveTransactionId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $userId, $userId, $leaveTransactionId);
        $stmt->execute();
        $approvalResult = $stmt->get_result()->fetch_assoc();

        if ($approvalResult['Approver1']) {
            // Approver 1 reject leave
            if ($status == '320') {
                $query1 = "UPDATE LeaveTransactions SET Status = ?, Approver1Status = ?, Approver1Date = CURRENT_TIME() 
                WHERE LeaveTransactionId = ?";
                $stmt1 = $conn->prepare($query1);
                $stmt1->bind_param("sss", $status, $status, $leaveTransactionId);
                $stmt1->execute();
                if ($stmt1->affected_rows === 0) exit('No rows updated');
                $stmt1->close();
            } else {
                $query1 = "UPDATE LeaveTransactions SET Approver1Status = ?, Approver1Date = CURRENT_TIME() 
                WHERE LeaveTransactionId = ?";
                $stmt1 = $conn->prepare($query1);
                $stmt1->bind_param("ss", $status, $leaveTransactionId);
                $stmt1->execute();
                if ($stmt1->affected_rows === 0) exit('No rows updated');
                $stmt1->close();
                $FCMToken = getFCMTokenByUserId($conn, $approvalResult['Approver2Id']);
                send($FCMToken, 'LEAVE APPROVE', 'You have new leave pending for your approval.');
            }
        } else if ($approvalResult['Approver2']) {
            $query1 = "UPDATE LeaveTransactions SET Status = ?, Approver2Status = ?, Approver2Date = CURRENT_TIME() 
                WHERE LeaveTransactionId = ?";
            $stmt1 = $conn->prepare($query1);
            $stmt1->bind_param("sss", $status, $status, $leaveTransactionId);
            $stmt1->execute();
            if ($stmt1->affected_rows === 0) exit('No rows updated');
            $stmt1->close();
        } else {
            return null;
        }

        if ($conn->autocommit(TRUE)) {
            return true;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return null;
}
