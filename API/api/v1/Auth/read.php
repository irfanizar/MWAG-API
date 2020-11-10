<?php

function loginWithUsernameAndPassword($input)
{

    global $conn;
    $responseData = [];
    $username = $input['username'];
    $password = $input['password'];

    $responseData['code'] = "100";
    if (!isset($username)) {
        $responseData['message'] = "Missing parameter username";
        return $responseData;
    } else if (!isset($password)) {
        $responseData['message'] = "Missing parameter password";
        return $responseData;
    }

    $query = "SELECT UserId, Salt, PasswordHash, Active FROM User u
                WHERE u.UserName = ? ";
    $data = [];
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if (!$user['Active']) {
            $data['code'] = 110;
            $data['message'] = "This account is inactive";
        } else {
            if (password_verify(concatPasswordWithSalt($password, $user['Salt']), $user['PasswordHash'])) {
                $responseData['code'] = 200;
                // $data['data'] = loginTransaction($user, $conn, $token);
            } else {
                $responseData['code'] = 100;
                $responseData['message'] = "Wrong username or password";
            }
        }
        $stmt->close();
    }
    return $responseData;
}
