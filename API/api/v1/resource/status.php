<?php

/*-----------------
	1. STATUS
-----------------------*/
$STATUS_PENDING = "PENDING FOR APPROVAL";
$STATUS_REJECTED = "REJECTED";
$STATUS_APPROVED = "APPROVED";
$STATUS_IN_PREPARATION = "IN PREPARATION";
$STATUS_REDEMPTION = "READY FOR REDEMPTION";
$STATUS_COMPLETED = "COMPLETED";

/*-----------------
	2. CATEGORY
-----------------------*/
$CLAIM_GNRL = "GENERAL CLAIM";
$CLAIM_COMM = "COMMISSION CLAIM";



function getStatusDescription($status)
{
    global $STATUS_PENDING;
    global $STATUS_REJECTED;
    global $STATUS_APPROVED;
    global $STATUS_IN_PREPARATION;
    global $STATUS_REDEMPTION;
    global $STATUS_COMPLETED;

    switch ($status) {
        case $STATUS_PENDING:
            return "Pending for approval";
        case $STATUS_REJECTED:
            return "Rejected";
        case $STATUS_APPROVED:
            return "Approved";
        case $STATUS_IN_PREPARATION:
            return "In-preparation";
        case $STATUS_REDEMPTION:
            return "Ready for redemption";
        case $STATUS_COMPLETED:
            return "Completed";
    }
}
