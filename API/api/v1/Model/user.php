<?php
include '../config/config.php';
$random_salt_length = 32;

function getUserToken($userId)
{
    global $conn;
    $stmt1 = $conn->prepare("SELECT FCMToken FROM User WHERE UserId = ?");
    $stmt1->bind_param("s", $userId);
    $stmt1->execute();
    $fcmToken = $stmt1->get_result()->fetch_assoc();
    $stmt1->close();
    $responseData['code'] = "100";
    if (!$fcmToken) {
        $responseData['message'] = "User logged out";
        return $responseData;
    } else {
        $responseData['code'] = "200";
        $responseData['message'] = "Success get fcm token";
        $responseData['data'] = $fcmToken;
        return $responseData;
    }
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

function getSalt()
{
    global $random_salt_length;
    return bin2hex(openssl_random_pseudo_bytes($random_salt_length));
}
