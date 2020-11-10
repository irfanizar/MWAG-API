<?php


function getAllSalesPaymentOutstanding()
{
    $finalResult = [];
    $finalResult['code'] = 100;
    $finalResult['message'] = 'Unexpected error occured.';
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT co.CustomerId, cust.CustomerName, SUM(co.TotalAmount) AS totalCoAmount, c.CurrencyCode
                    FROM CustomerOrder co
                    LEFT JOIN Customer cust
                    ON cust.CustomerId = co.CustomerId
                    INNER JOIN Currency c
                    ON co.currencyId = c.currencyId
                    WHERE co.Status = 200 
                    GROUP BY co.CustomerId";
        $stmt1 = $conn->prepare($query1);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        while ($row = $result1->fetch_assoc()) {
            // $query2 = "SELECT SUM(i.TotalAmount) AS InvoiceAmount
            //         FROM Invoice i
            //         WHERE i.CustomerId = ?
            //         GROUP BY i.CustomerId";
            // $stmt2 = $conn->prepare($query2);
            // $stmt2->bind_param("s", $row['CustomerId']);
            // $stmt2->execute();
            // $result2 = $stmt2->get_result()->fetch_assoc();
            // if ($result2) {
            //     $row['InvoiceAmount'] = $result2['InvoiceAmount'];
            // } else {
            //     $row['InvoiceAmount'] = 0;
            // }
            // $row['totalOutstanding'] = $row['totalCoAmount'] - $result2['InvoiceAmount'];
            // if ($row['totalOutstanding'] > 0) {
            //     $data1[] = $row;
            // }
            $data1[] = $row;
        }

        if ($conn->autocommit(TRUE)) {
            $finalResult['code'] = 200;
            $finalResult['message'] = 'Success get all data';
            $finalOutstanding['outstandingData'] = $data1;
            $finalResult['data'] = $finalOutstanding;
            $finalResult['type'] = 'Payment';

            return $finalResult;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $finalResult;
}

function getAllSalesStockOutstanding()
{
    $finalResult = [];
    $finalResult['code'] = 100;
    $finalResult['message'] = 'Unexpected error occured.';
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT co.CustomerId, cust.CustomerName, coi.ProductId, 
                    SUM(coi.Quantity) AS COQuantity,
                    (CASE WHEN p.ProductType IS NULL THEN p.ProductName ELSE CONCAT(p.ProductType, ' ', p.ProductName
                    ,(CASE WHEN p.ProductSize IS NULL THEN ''ELSE CONCAT(' ', p.ProductSize) END)
                    ,(CASE WHEN coi.ProductThkRemarks IS NULL THEN '' ELSE CONCAT(' - ', coi.ProductThkRemarks) END)
                    ,(CASE WHEN p.ProductThickness IS NULL THEN '' ELSE CONCAT(' ', p.ProductThickness) END)
                    ,(CASE WHEN p.ProductCondition IS NULL THEN '' ELSE CONCAT(' (', p.ProductCondition, ')') END)
                    ,(CASE WHEN p.ProductRemark IS NULL THEN '' ELSE CONCAT(' ', p.ProductRemark) END)
                    ,(CASE WHEN coi.ProductColor IS NULL THEN '' ELSE CONCAT(' ', coi.ProductColor) END)
                    ) 
                    END) AS ProductName,
                    p.Picture
                    FROM CustomerOrder co
                    INNER JOIN CustomerOrderItem coi
                    ON coi.COId = co.COId
                    LEFT JOIN Customer cust
                    ON cust.CustomerId = co.CustomerId
                    INNER JOIN Product p
                    ON p.ProductId = coi.ProductId
                    WHERE co.Category = 2 AND co.Status = 200 
                    GROUP BY co.CustomerId,coi.ProductId";
        $stmt1 = $conn->prepare($query1);
        // $stmt->bind_param("s", $userId);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        while ($row = $result1->fetch_assoc()) {
            $query2 = "SELECT (CASE WHEN SUM(grni.Quantity) IS NULL THEN 0 ELSE SUM(grni.Quantity) END ) AS grniQuantity
                    FROM GoodReturnNote grn
                    LEFT JOIN GoodReturnNoteItem grni
                    ON grni.GoodReturnNoteId = grn.GoodReturnNoteId AND grni.ProductId = ?
                    WHERE grn.CustomerId = ?
                    GROUP BY grni.ProductId";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("ss", $row['ProductId'], $row['CustomerId']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $sumResult2Data = 0;
            while ($row2 = $result2->fetch_assoc()) {
                $sumResult2Data = $sumResult2Data + $row2['grniQuantity'];
            }
            if ($result2) {
                $row['grniQuantity'] = $sumResult2Data;
            } else {
                $row['grniQuantity'] = 0;
            }
            $row['totalOutstanding'] = $row['COQuantity'] - $sumResult2Data;
            if ($row['totalOutstanding'] > 0) {
                $data1[] = $row;
            }
        }

        if ($conn->autocommit(TRUE)) {
            $finalResult['code'] = 200;
            $finalResult['message'] = 'Success get all data';
            if ($data1) {
                $finalOutstandingStock['outstandingData'] = $data1;
            } else {
                $finalOutstandingStock['outstandingData'] = [];
            }
            $finalResult['type'] = 'Stock';
            $finalResult['data'] = $finalOutstandingStock;

            return $finalResult;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $finalResult;
}

function getAllPurchasePaymentOutstanding()
{
    $finalResult = [];
    $finalResult['code'] = 100;
    $finalResult['message'] = 'Unexpected error occured.';
    global $conn;
    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT a.SupplierId, c.CustomerId,c.CustomerName, d.CurrencyCode, SUM(a.TotalPrice) AS totalCoAmount 
        FROM POI a 
        INNER JOIN Customer c ON c.CustomerId = a.SupplierId 
        INNER JOIN Currency d ON d.CurrencyId = a.Currency 
        WHERE a.Status='COMPLETE' GROUP BY a.SupplierId";
        $stmt1 = $conn->prepare($query1);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        while ($row = $result1->fetch_assoc()) {

            $query2 = "SELECT SUM(pv.TotalPrice) AS TotalPrice
            FROM PaymentVoucher pv 
            WHERE pv.SupplierId = ? AND pv.Status='COMPLETE'";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("s", $row['CustomerId']);
            $stmt2->execute();
            $result2 = $stmt2->get_result()->fetch_assoc();

            if ($result2) {
                $row['PV'] =  $row['totalCoAmount'] - $result2['TotalPrice'];
            } else {
                $row['PV'] = 0;
            }

            $row['totalCoAmount'] = $row['PV'];
            if ($row['totalCoAmount'] > 0) {
                $data1[] = $row;
            }
            // $data1[] = $row;

        }

        if ($conn->autocommit(TRUE)) {
            $finalResult['code'] = 200;
            $finalResult['message'] = 'Success get all data';
            $finalOutstanding['outstandingData'] = $data1;
            $finalResult['data'] = $finalOutstanding;
            $finalResult['type'] = 'Payment';

            return $finalResult;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $finalResult;
}

function getAllPurchaseStockOutstanding()
{
    $finalResult = [];
    $finalResult['code'] = 100;
    $finalResult['message'] = 'Unexpected error occured.';
    global $conn;

    try {
        $conn->autocommit(FALSE); //turn on transactions
        $query1 = "SELECT a.POId, a.PONumber, c.CustomerId,c.CustomerName,b.ProductId,
        b.productName AS ProductName, SUM(b.Quantity) AS Quantity ,
        (CASE WHEN p.Picture IS NULL THEN 0 ELSE p.Picture  END ) AS Picture  
        FROM PurchaseOrder a 
        INNER JOIN PurchaseOrderItem b ON b.POId = a.POId 
        INNER JOIN Customer c on c.CustomerId = a.SupplierId 
        INNER JOIN Product p ON p.ProductId = b.ProductId
        WHERE a.Status = 'APPROVED' GROUP BY c.CustomerId,b.ProductId";
        $stmt1 = $conn->prepare($query1);
        // $stmt->bind_param("s", $userId);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        while ($row = $result1->fetch_assoc()) {

            // $query2 = "SELECT b.ProductId,a.Refer_No, b.ProductName, SUM(b.Quantity) AS Quantity
            //            FROM POI a 
            //            INNER JOIN POI_Item b ON b.POI_Id = a.POI_ID 
            //            WHERE a.Status = 'COMPLETE' GROUP BY a.Refer_No, b.ProductId";
            $query2 = "SELECT (CASE WHEN SUM(b.Quantity) IS NULL THEN 0 ELSE SUM(b.Quantity)  END ) AS Quantity, p.Picture
            FROM POI a 
            INNER JOIN POI_Item b ON b.POI_Id = a.POI_ID AND b.ProductId = ?
            LEFT JOIN Product p ON p.ProductId = b.ProductId
            WHERE a.SupplierId = ?
            GROUP BY b.ProductId";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("ss", $row['ProductId'], $row['CustomerId']);
            $stmt2->execute();
            $result2 = $stmt2->get_result()->fetch_assoc();
            if ($result2) {
                $row['POI'] = $row['Quantity'] - $result2['Quantity'];
            } else {
                $row['POI'] = $row['Quantity'];
            }

            $row['totalOutstanding'] =   $row['POI'];
            if ($row['totalOutstanding'] > 0) {
                $data1[] = $row;
            } else {
                $row['totalOutstanding'] =  0;
                $data1[] = $row;
            }
        }

        if ($conn->autocommit(TRUE)) {
            $finalResult['code'] = 200;
            $finalResult['message'] = 'Success get all data';
            if ($data1) {
                $finalOutstandingStock['outstandingData'] = $data1;
            } else {
                $finalOutstandingStock['outstandingData'] = [];
            }
            $finalResult['type'] = 'Stock';
            $finalResult['data'] = $finalOutstandingStock;

            return $finalResult;
        }
    } catch (Exception $e) {
        $conn->rollback(); //remove all queries from queue if error (undo)
        throw $e;
    }
    return $finalResult;
}
