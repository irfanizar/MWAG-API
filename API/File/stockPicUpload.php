<?php

    header('Access-Control-Allow-Origin: *');
    $basePATH = "../../uploads/";
    $currentYear = date("Y");
    $module = "/STOCK/";
    $completePath = $basePATH . $currentYear . $module; 

    if ($_FILES) {
        if (!file_exists($completePath)) {
            mkdir($completePath, 0777, true);
        }
        move_uploaded_file($_FILES["file"]["tmp_name"], $completePath . basename($_FILES["file"]["name"]));
    }
    
?>