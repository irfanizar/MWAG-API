<?php

// $PUSH_FLAG_CHATROOM = 1;
// $PUSH_FLAG_USER = 2;
// header("Access-Control-Allow-Origin: *");

// $inputJSON = file_get_contents('php://input');
// $input = json_decode($inputJSON, TRUE); //convert JSON into array

// if (isset($input['title'])) {
//     // $token = $input['token'];
//     $title = $input['title'];
//     // $body = $input['body'];
//     // send($token, $title, $body);
//     sendToInbox($title);
//     $response = array();
//     $response['code'] = '200';
//     echo json_encode($response);

//     // return sendToTopic('fcm_inbox', $title);
// } else {
//     $response = array();
//     $response['error'] = 'error1';
//     echo json_encode($response);
// }

// sending push message to single user by gcm registration id
function send($token, $title, $body)
{
    $notification = array(
        'title' => $title,
        'body' => $body
    );

    $data = array(
        'title' => $title
    );

    $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority' => 'high', 'data' => $data);
    return sendPushNotification($arrayToSend);
}



// Sending message to a topic by topic id
function sendToTopic($to, $message)
{
    $fields = array(
        'to' => '/topics/' . $to,
        'data' => $message,
    );
    return sendPushNotification($fields);
}

// sending push message to multiple users by gcm registration ids
function sendMultiple($registration_ids, $message)
{
    $fields = array(
        'registration_ids' => $registration_ids,
        'data' => $message,
    );

    return sendPushNotification($fields);
}

// function makes curl request to fcm servers
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

function sendToInbox($title)
{
    $notification = array(
        'title' => "You received new annoucement!",
        'text' => $title
    );
    $arrayToSend = array('to' => '/topics/fcm_inbox', 'notification' => $notification, 'priority' => 'high');
    return sendPushNotification($arrayToSend);
}
