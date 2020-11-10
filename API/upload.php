<?php

    header('Access-Control-Allow-Origin: *');
    $basePATH = "http://eticketing2u.com.my/JSMS/uploads/";
    $currentYear = date("Y");
    $module = "/CLAIM/";
    $completePath = $basePATH . $currentYear . $module; 
    $dirname = '../uploads/' . $currentYear . $module;

    if ($_FILES) {
        if (!file_exists($completePath)) {
            mkdir($dirname, 0777, true);
        }
        move_uploaded_file($_FILES["file"]["tmp_name"], $dirname . basename($_FILES["file"]["name"]));
    }
    
?>