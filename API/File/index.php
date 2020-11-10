<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include '../function.php';
include '../config.php';

//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
$basePATH = "../../uploads/";

if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_CLAIM_FILE_NUMBER) {
        $response = m1($response, $input, $basePATH);
    } else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}


function m1($response, $input, $basePATH)
{
    global $conn;
    $userName = $input['userName'];
    $userId = $input['userId'];

    $query = "SELECT ci.Attachment, ci.ClaimId
    FROM ClaimItem ci
    INNER JOIN Claim c
    ON c.ClaimId = ci.ClaimId
    WHERE c.UserId = ? AND DATE(c.CreatedDate) = CURRENT_DATE() 
    ORDER BY c.CreatedDate DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if($result){
        $p1 = strrpos($result['Attachment'], "_");
        $number = (int)substr($result['Attachment'], $p1+1);

        $response['code'] = 200;
        $response['data'] = ++$number;
    }else{
        $response['code'] = 200;
        $response['data'] = 0;
    }
    
    // $currentYear = date("Y");
    // $module = "/CLAIM/";
    // $completePath = $basePATH . $currentYear . $module; 
    // if (!file_exists($completePath)) {
    //     $response['code'] = 200;
    //     $response['data'] = 0;
    // }else{
    //     $index = 1;
    //     $date = new DateTime();
    //     $today = $date->format('Ymd');
    //     $dirname = $completePath . $userName . '_' . $today . '_0' . $index;
    //     while(file_exists($dirname)){
    //         $index = $index + 1;
    //         $dirname = $completePath . $userName . '_' . $today . '_0' . $index;
    //         if(index == 1000){
    //             $response['code'] = 100;
    //             $response['data'] = 'fail';
    //             return $response;
    //         }
    //     }
     
    //     $response['code'] = 200;
    //     $response['data'] = $index;
    // }
    return $response;
}






echo json_encode($response);