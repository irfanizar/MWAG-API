<?php
include '../config/config.php';

function createNewAttachment($module, $tableId, $path)
{
    global $conn;
    $stmt1 = $conn->prepare("INSERT INTO Attachment (Module, TableId, Path) VALUES (?,?,?)");
    $stmt1->bind_param("sss", $module, $tableId, $path);
    $stmt1->execute();
    if ($stmt1->affected_rows === 0) {
        return false;
    } else {
        return true;
    }
}
