<?php

header('Access-Control-Allow-Origin: *');
$basePATH = "../../uploads/";
$currentYear = date("Y");
$module = "/LEAVE/";
$completePath = $basePATH . $currentYear . $module;
$tempPath = "/uploads/" . $currentYear . $module;

if ($_FILES) {
    if (!file_exists($completePath)) {
        mkdir($completePath, 0777, true);
    }
    // $ext = mimeToExt($_FILES["file"]["type"]);
    $index = 1;
    $filePath = "";
    $actualFilePath = "";
    while ($index < 100) {
        $filePath = $completePath . basename($_FILES["file"]["name"]) . $index . '.png';
        $actualFilePath = $tempPath . basename($_FILES["file"]["name"]) . $index . '.png';
        if (!file_exists($filePath)) {
            $success = move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
            break;
        }else{
            $index++;
        }
    }
    echo json_encode($actualFilePath );

}
