<?php
    include '../Model/user.php';

function createNotificationToOneUser($userId, $token, $title, $body)
{
    $notification = array(
        'title' => $title,
        'body' => $body
    );

    if ($token == null) {
        $fcmResult =  getUserToken($userId);
        if ($fcmResult['code'] == 200) {
            $token = $fcmResult['data']['FCMToken'];
            $arrayToSend = array('to' => $token, 'notification' => $notification);
            return sendPushNotification($arrayToSend);
        }
    } else {
        $arrayToSend = array('to' => $token, 'notification' => $notification);
        return sendPushNotification($arrayToSend);
    }
}

function sendPushNotification($fields)
{
    // $GOOGLE_API_KEY = "AIzaSyBpdC0TmdN4RMztskbAMFkxBE3cU1_4S10";

    // Set POST variables
    $URL = "https://fcm.googleapis.com/fcm/send";
    $SERVER_KEY = 'AAAAeFss9Mg:APA91bHm475kZYvJ-nNyHHAZSBboUeMRQCC_FvxGXdpzeqel8nddeHZwJLF8ki593N_caubrNFW7JUjpquIBLf5W0zt647qoTgzbTwl6inSByLUWt79dNTNpnYp8jqvyIvWnIp41hoAo';

    $headers = array(
        'Authorization: key=' .  $SERVER_KEY,
        'Content-Type: application/json',
    );
    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $URL);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 0);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    // Close connection
    curl_close($ch);

    return $result;
}
