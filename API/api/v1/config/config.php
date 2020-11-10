<?php
error_reporting(E_ALL ^ E_NOTICE);

$servername = "localhost";
$username = "eticketi";
$password = "LCPBuildsoft@123";
$dbname = "eticketi_MWAG";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if(mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");
?>