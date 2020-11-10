<?php

header('Access-Control-Allow-Origin: *');
$uploadPATH = "../../../../uploads/";
$currentYear = date("Y");
$todayDate = date("_dmY_");
$module = "/CLAIM/";
$completeUploadPath = $uploadPATH  . $currentYear . $module;

$tempPath = "/uploads/" . $currentYear . $module;

if ($_FILES) {
    if (!file_exists($completeUploadPath)) {
        mkdir($completeUploadPath, 0777, true);
    }
    $index = 1;
    $filePath = "";
    $actualFilePath = "";
    while ($index < 100) {
        $filePath = $completeUploadPath . basename($_FILES["file"]["name"]) . $todayDate . $index . '.png';
        $actualFilePath = $tempPath . basename($_FILES["file"]["name"]) . $todayDate  . $index . '.png';
        if (!file_exists($filePath)) {
            $success = move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
            break;
        }else{
            $index++;
        }
    }
    echo json_encode($actualFilePath );

}