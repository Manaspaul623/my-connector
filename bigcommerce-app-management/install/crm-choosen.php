<?php

include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$crm_type = trim($_REQUEST['crmType']);
$insertedId = trim($_REQUEST['insertedId']);

if ($crm_type == 'SFDC') {    
    $zohoAuthToken = NULL;
    $sfdc_user_name = trim($_REQUEST['sfdc_user_name']);
    $sfdc_password = trim($_REQUEST['sfdc_password']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);
} else if ($crm_type == 'ZOHO'){    
    $zohoAuthToken = trim($_REQUEST['zohoAuthToken']);
    $sfdc_user_name = NULL;
    $sfdc_password = NULL;
    $sfdc_security_password = NULL;
} else if ($crm_type == 'VTIGER'){    
    $zohoAuthToken = NULL;
    $sfdc_user_name = trim($_REQUEST['sfdc_user_name']);
    $sfdc_password = trim($_REQUEST['sfdc_password']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);
} else {
    $zohoAuthToken = trim($_REQUEST['zohoInvenAuthToken']);
    $sfdc_user_name = NULL;
    $sfdc_password = NULL;
    $sfdc_security_password = trim($_REQUEST['zohoInvenOrgID']);
}

$updateArr = array(
    'crm_type' => $crm_type,
    'zoho_auth_id' => $zohoAuthToken,
    'sfdc_user_name' => $sfdc_user_name,
    'sfdc_password' => $sfdc_password,
    'sfdc_security_password' => $sfdc_security_password,
    'current_status' => 'CRMCHOOSEN'
);
$cond = " AND user_id=$insertedId";
//error_log (implode($updateArr), 1, "support@aquaapi.com");

$re = update($userTable, $updateArr, $cond);
if ($re) {
    header("Location:https://".APP_DOMAIN."/bigcommerce-app-management/subscription/select-plan.php?access_id=$insertedId");
}
