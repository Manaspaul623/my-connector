<?php

include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

session_start();
$crm_type = trim($_SESSION['crmType']);
$userId = trim($_SESSION['userId']);
//
if ($crm_type == 'SFDC') {
    $sfdc_user_name = trim($_REQUEST['sfdc_user_name']);
    $sfdc_password = trim($_REQUEST['sfdc_password']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);
} else if ($crm_type == 'ZOHO_INVENTORY') {
    $sfdc_user_name = trim($_REQUEST['zohoInvenAuthToken']);
    $sfdc_password =trim($_REQUEST['zohoInvenOrgID']);
    $sfdc_security_password = "";
} else if ($crm_type == 'VTIGER') {
    $sfdc_user_name = trim($_REQUEST['vtigerEndpoint']);
    $sfdc_password = trim($_REQUEST['vtigerUsername']);
    $sfdc_security_password = trim($_REQUEST['vtigerAccessKey']);
} else {
    $sfdc_user_name = trim($_REQUEST['zohoAuthToken']);
    $sfdc_password = "";
    $sfdc_security_password = "";
}
$updateArr = array(
    "app2_cred1" => $sfdc_user_name,
    "app2_cred2" => $sfdc_password,
    "app2_cred3" => $sfdc_security_password
);
$cond = " AND user_id=$userId";
//
$re = update($userSubscription, $updateArr, $cond); //Subscription Table Update
$reUpdate = update($userSubscriptionFinal, $updateArr, $cond); //Subscription Final Table Update
if ($re && $reUpdate) {
    header("Location:https://".APP_DOMAIN."/shopify-app/subscription/user-dashboard.php");
}
