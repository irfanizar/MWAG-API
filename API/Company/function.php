<?php

function getAllCompanyList()
{
    global $conn;
    $data = [];
    $query1 = "SELECT CompanyId, CompanyName FROM Company WHERE Active = TRUE";
    $stmt1 = $conn->prepare($query1);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    while ($row1 = $result1->fetch_assoc()) {
        $data[] = $row1;
    }
    return $data;
}
