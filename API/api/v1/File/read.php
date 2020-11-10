<?php
include '../config/config.php';

function getAllAttachment($module, $tableId)
{
    global $conn;
    $data = [];
    $stmt1 = $conn->prepare("SELECT Path FROM Attachment WHERE module = ? AND tableId = ?");
    $stmt1->bind_param("ss", $module, $tableId);
    $stmt1->execute();
    $result = $stmt1->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}
