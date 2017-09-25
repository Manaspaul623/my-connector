<?php

$insertedId = trim($_REQUEST['access_id']);
$selectedId = trim($_REQUEST['selected']);

session_start();
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$updateArr = array(
    'current_status' => 'SYNCINTERVAL'
);
$cond = " AND user_id=$insertedId";
$re = update($userTable, $updateArr, $cond);
$syncArr = array(
    'user_id' => $insertedId,
    'sync_time_slot_id' => $selectedId
);
$addedId = insert($syncTimeSchedule, $syncArr);

$resVal = fetch($userTable, $cond);
$_SESSION['userID'] = $resVal[0]['user_id'];
$_SESSION['crmType'] = $resVal[0]['crm_type'];
$_SESSION['userEmail'] = $resVal[0]['user_email'];
$_SESSION['userCurrentStatus'] = $resVal[0]['current_status'];
$_SESSION['zoho_cust_id'] = $resVal[0]['zoho_cust_id'];
$_SESSION['zoho_subscription_id'] = $resVal[0]['zoho_subscription_id'];
$_SESSION['zoho_cust_email'] = $resVal[0]['zoho_cust_email'];
if ($addedId) {
    header("Location:https://".APP_DOMAIN."/bigcommerce-app-management/subscription/user-dashboard.php?access_id=$insertedId");
}