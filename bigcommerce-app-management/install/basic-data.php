<?php

include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$crmType = $_POST['crm_type'];
$code = $_POST['code'];
$scope = $_POST['scope'];
$context = $_POST['context'];

if ($crmType === 'SFDC') {
    /* salse force data */
    $sfdcUserName = $_POST['sfdc_user_name'];
    $sfdcPassword = $_POST['sfdc_password'];
    $sfdcSecurityPassword = $_POST['sfdc_security_password'];

    $userDataArr = array(
        'crm_type' => $crmType,
        'sfdc_user_name' => $sfdcUserName,
        'sfdc_password' => $sfdcPassword,
        'sfdc_security_password' => $sfdcSecurityPassword,
        'code' => $code,
        'scope' => $scope,
        'context' => $context
    );
} else if ($crmType === 'ZOHO') {
    /* Zoho data */
    $zohoAuthTokenId = $_POST['zohoAuthToken'];
    $userDataArr = array(
        'crm_type' => $crmType,
        'zoho_auth_id' => $zohoAuthTokenId,
        'code' => $code,
        'scope' => $scope,
        'context' => $context
    );
}

/* INSERT in the database */
if (isset($_POST["crm_type"]) && !empty($_POST["crm_type"])) {
    $connection_status = Connectdb();
    $open_status = Opendb();
    try {
        $insertData = insert($tempUser, $userDataArr);
        if ($insertData > 0) {
            header("Location:https://".APP_DOMAIN."/bigcommerce-app-management/subscription/select-plan.php?user_token=$insertData");
            exit();
        }
    } catch (Exception $ex) {
        echo 'Error :' . $ex;
    }
}