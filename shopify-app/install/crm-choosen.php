<?php

include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$crm_type = trim($_REQUEST['crmType']);
$insertedId = trim($_REQUEST['insertedId']);
$function_name = "shopifyappTo".strtolower($crm_type);
if ($crm_type == 'SFDC') {
    $sfdc_user_name = trim($_REQUEST['sfdc_user_name']);
    $sfdc_password = trim($_REQUEST['sfdc_password']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);
} else if ($crm_type == 'ZOHO'){
    $sfdc_user_name = trim($_REQUEST['zohoAuthToken']);
    $sfdc_password = "";
    $sfdc_security_password = "";
} else if ($crm_type == 'VTIGER'){
    $sfdc_user_name = trim($_REQUEST['sfdc_password']);
    $sfdc_password = trim($_REQUEST['sfdc_user_name']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);
} else {
    $sfdc_user_name = trim($_REQUEST['zohoInvenAuthToken']);
    $sfdc_password = trim($_REQUEST['zohoInvenOrgID']);
    $sfdc_security_password = "";
}

$updateArr = array(
    'app2' => $crm_type,
    'app2_cred1' => $sfdc_user_name,
    'app2_cred2' => $sfdc_password,
    'app2_cred3' => $sfdc_security_password,
    'function_name' => $function_name
);
$cond = " AND user_id=$insertedId";
//error_log (implode($updateArr), 1, "support@aquaapi.com");

$re = update($userSubscription, $updateArr, $cond);
if ($re) {
    header("Location:https://".APP_DOMAIN."/shopify-app/subscription/select-plan.php?access_id=$insertedId");
}
