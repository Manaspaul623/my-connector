<?php

include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$crm_type = trim($_REQUEST['crmType']);
$insertedId = trim($_REQUEST['insertedId']);
$credential = trim($_REQUEST['credential']);
$function_name = trim($_REQUEST['function_name']);
if ($crm_type == 'SFDC') {    

    $sfdc_user_name = trim($_REQUEST['sfdc_user_name']);
    $sfdc_password = trim($_REQUEST['sfdc_password']);
    $sfdc_security_password = trim($_REQUEST['sfdc_security_password']);

    $updateArr = array(
        'app2' => $crm_type,
        'app2_cred1' => $sfdc_user_name,
        'app2_cred2' => $sfdc_password,
        'app2_cred3' => $sfdc_security_password,
        'function_name' => $function_name
    );
}
else if ($crm_type == 'ZOHO'){
    $zohoAuthToken = trim($_REQUEST['zohoAuthToken']);


    $updateArr = array(
        'app2' => $crm_type,
        'app2_cred1' => $zohoAuthToken,
        'function_name' => $function_name
    );

}
else if ($crm_type == 'HUBSPOT'){
    $hubspotRefreshToken = trim($_REQUEST['refresh_token']);

    $updateArr = array(
        'app2' => $crm_type,
        'app2_cred1' => $hubspotRefreshToken,
        'function_name' => $function_name
    );

}
else if ($crm_type == 'VTIGER'){
    $vtiger_user_name = trim($_REQUEST['vtiger_user_name']);
    $vtiger_password = trim($_REQUEST['vtiger_password']);
    $vtiger_security_password = trim($_REQUEST['vtiger_security_password']);

    $updateArr = array(
        'app2' => $crm_type,
        'app2_cred1' => $vtiger_password,
        'app2_cred2' => $vtiger_user_name,
        'app2_cred3' => $vtiger_security_password,
        'function_name' => $function_name
    );

}
else if ($crm_type == 'SHOPIFY'){
    $shopifyUrl = trim($_REQUEST['shopifyUrl']);
    $shopifyApikey = trim($_REQUEST['shopifyApikey']);
    $shopifyPassword = trim($_REQUEST['shopifyPassword']);

    //Url Creating
    if (substr($shopifyUrl, -1) == '/') {
        // remove '/'
        $shopifyUrl = substr($shopifyUrl, 0, -1);
    }


    //Customer Details collect from BigCommerce order
    $shopifyUrl = "https://".$shopifyApikey.":".$shopifyPassword."@".$shopifyUrl;

    $insertArr = array(
        'user_id' => $insertedId,
        'app1' => $crm_type,
        'app1_cred1' => $shopifyUrl,
        'app1_cred2' => $shopifyApikey,
        'app1_cred3' => $shopifyPassword
    );

}
else if($crm_type == 'PRESTASHOP'){
    $prestashopUrl = trim($_REQUEST['prestashopUrl']);
    $prestashopApikey = trim($_REQUEST['prestashopApikey']);
    if (substr($prestashopUrl, -1) == '/') {
        // remove '/'
        $prestashopUrl = substr($prestashopUrl, 0, -1);
    }

    if (strpos($prestashopUrl, 'http://') !== false) {
        $url = explode("http://",$prestashopUrl);
        $url = 'http://'.$prestashopApikey."@".$url[1]."/api";
    }
    else if(strpos($prestashopUrl, 'https://') !== false){
        $url = explode("https://",$prestashopUrl);
        $url = 'https://'.$prestashopApikey."@".$url[1]."/api";
    }
    else{
        $url = 'http://'.$prestashopApikey."@".$prestashopUrl."/api";
    }

    $insertArr = array(
        'user_id' => $insertedId,
        'app1' => $crm_type,
        'app1_cred1' => $url,
        'app1_cred2' => $prestashopApikey
    );

}
else if ($crm_type == 'MAGENTO'){
     $user_name = trim($_REQUEST['magento_name']);
     $user_password = trim($_REQUEST['magento_password']);
     $endpoint = trim($_REQUEST['magento_endpoint']);
    $insertArr = array(
        'user_id' => $insertedId,
        'app1' => $crm_type,
        'app1_cred1' => $user_name,
        'app1_cred2' => $user_password,
        'app1_cred3' => $endpoint
    );
}
else if ($crm_type == 'BIGCOMMERCE'){
    $user_name = trim($_REQUEST['bigcommerce_name']);
    $user_password = trim($_REQUEST['bigcommerce_password']);
    $endpoint = trim($_REQUEST['bigcommerce_endpoint']);
	if (substr($endpoint, -1) == '/') {
		// remove '/'
		$endpoint = substr($endpoint, 0, -1);
	}
    $insertArr = array(
		'user_id' => $insertedId,
        'app1' => $crm_type,
        'app1_cred1' => $user_name,
        'app1_cred2' => $user_password,
        'app1_cred3' => $endpoint
    );
}
else {
    $zohoAuthToken = trim($_REQUEST['zohoInvenAuthToken']);
    $zohoOrganisationId = trim($_REQUEST['zohoInvenOrgID']);

    $updateArr = array(
        'app2' => $crm_type,
        'app2_cred1' => $zohoAuthToken,
        'app2_cred2' => $zohoOrganisationId,
        'function_name' =>$function_name
    );
}

$cond = " AND id=$credential";
//error_log (implode($credential), 1, "support@aquaapi.com");
if(!is_numeric($credential))
{
    $re = insert($userSubscription, $insertArr);
	//error_log($re. "Testing", 1, "support@aquaapi.com");
    if(is_numeric($re))
    {
        echo $re;
    }
    else
    {
        echo 'Not Inserted';
    }
}
else
{
    $re = update($userSubscription, $updateArr, $cond);
    if ($re)
    {
        echo '1';
        //header("Location:https://" . APP_DOMAIN . "/app/subscription/select-plan.php?access_id=$credential");
    }
    else
    {
        echo "Not Updated";
    }
}


