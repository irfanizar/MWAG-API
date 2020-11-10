<?php
header("Access-Control-Allow-Origin: *");
// include connection
include './config.php';

$response = array();

// get input data from anywhere
$inputJSON = file_get_contents('php://input');
// Decode convert data to JSON Object
$input = json_decode($inputJSON, TRUE);

$claimDate = $input['claimDate'];

if(isset($claimDate)){
    
    $query1 = "SELECT * FROM Invoice i WHERE i.Date = ?";
    // Prepare Connection to Db
    $stmt1 = $conn->prepare($query1);
    // Replace ? in query with the inputs
    $stmt1->bind_param("s", $claimDate);
    // Run query
    $stmt1->execute();

    $result = $stmt1->get_result();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $response["code"] = 200;

    $response["message"] = "Commission Data success";
    
    $response["data"] = $data;

}
else{

$response["code"] = 100;
$response["message"] = "Missing required parameters";

}


echo json_encode($response);

?>