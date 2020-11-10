<?php


function createNewPasswordHash($input)
{
    $password = $input['password'];
    $responseData['code'] = "100";
    if (!isset($password)) {
        $responseData['message'] = "Missing parameter password";
        return $responseData;
    }

    $salt = getSalt();
    $passwordHash = password_hash(concatPasswordWithSalt($password, $salt),PASSWORD_DEFAULT);
    $responseData['data']['salt'] = $salt;
    $responseData['data']['passwordHash'] = $passwordHash;

    return $responseData;
}
