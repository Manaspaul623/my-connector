<?php

include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

session_start();
$crm_type = trim($_SESSION['crmType']);
$userId = trim($_SESSION['userID']);
//
if ($crm_type == 'SFDC') {
    $zohoAuthToken = NULL;
    $sfdc_user_name = trim($_REQUEST['sfdc_user_name']);
    $sfdc_password = trim($_REQUEST['sfdc_password']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);
} else if ($crm_type == 'ZOHO_INVENTORY') {
    $zohoAuthToken = trim($_REQUEST['zohoInvenAuthToken']);
    $sfdc_user_name = NULL;
    $sfdc_password = NULL;
    $sfdc_security_password = trim($_REQUEST['zohoInvenOrgID']);;
} else {
    $zohoAuthToken = trim($_REQUEST['zohoAuthToken']);
    $sfdc_user_name = NULL;
    $sfdc_password = NULL;
    $sfdc_security_password = NULL;
}
$updateArr = array(
    'crm_type' => $crm_type,
    'zoho_auth_id' => $zohoAuthToken,
    'sfdc_user_name' => $sfdc_user_name,
    'sfdc_password' => $sfdc_password,
    'sfdc_security_password' => $sfdc_security_password,
);
$cond = " AND user_id=$userId";
//
$re = update($userTable, $updateArr, $cond);
if ($re) {
    header("Location:https://".APP_DOMAIN."/app/subscription/user-dashboard.php");
}
