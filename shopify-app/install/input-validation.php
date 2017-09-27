<?php

if ($_REQUEST['type']) {
    dispatcher($_REQUEST['type']);
}

function dispatcher($type) {
    switch ($type) {
        case 'validateZohoZuthToken' : validateZohoZuthToken();
            break;
        case 'validateSfdcInfo' : validateSfdcInfo();
            break;
        case 'validateZohoInvenZuthToken' : validateZohoInvenZuthToken();
            break;
		case 'validateVtigerToken' : validateVtigerToken();
			break;
        default : addErrors('no action specified');
    }
}

function addErrors($msg) {
    $arr = array(
        'flag' => 0,
        'msg' => $msg
    );
    echo json_encode($arr);
}

function validateZohoZuthToken() {
    $zohoAuthToken = $_REQUEST['zohoAuthToken'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://crm.zoho.com/crm/private/json/Accounts/getRecords?authtoken=" . $zohoAuthToken . "&scope=crmapi",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
        ),
    ));
    $response = curl_exec($curl);
    $response_arr = json_decode($response, true);
    curl_close($curl);
    if (array_key_exists('error', $response_arr['response'])) {
        echo "false";
    } else {
        echo "true";
    }
}

function validateSfdcInfo() {
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/sfdc-integration/Sfdc.php";
   // require 'Sfdc.php';
    
    $SfdcUsername = trim($_REQUEST['sfdc_user_name']);
    $SfdcPassword = trim($_REQUEST['sfdc_password']);
    $SfdcSecurityToken = trim($_REQUEST['sfdc_security_password']);
    
    $sfdcRes = Sfdc::checkCredentials($SfdcPassword, $SfdcUsername, $SfdcSecurityToken);
    echo json_encode($sfdcRes);
}

function validateZohoInvenZuthToken() {


/*
    $zohoInvenAuthToken = $_REQUEST['zohoInvenAuthToken'];
    $zohoInvenOrgID = $_REQUEST['zohoInvenOrgID'];
   $zohoInvenAuthToken = 'b2ed3c916a2408226772f6a1f52f6a6e';
   $zohoInvenOrgID = '637449486';
   $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://inventory.zoho.com/api/v1/organizations/".$zohoInvenOrgID."?authtoken=" . $zohoInvenAuthToken,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
        ),
    ));
    $response = curl_exec($curl);
    $response_arr = json_decode($response, true);
    curl_close($curl);
    print_r($response_arr);
    if ($response_arr['message'] == 'success') {
        echo "true";
    } else {
        echo "false";
    } */
// $zohoInvenAuthToken = 'b2ed3c916a2408226772f6a1f52f6a6e';
//   $zohoInvenOrgID = '637449486';
    $zohoInvenAuthToken = $_REQUEST['zohoInvenAuthToken'];
    $zohoInvenOrgID = $_REQUEST['zohoInvenOrgID'];
   $curl = curl_init();
    curl_setopt_array($curl, array(
       CURLOPT_URL => "https://inventory.zoho.com/api/v1/organizations/".$zohoInvenOrgID."?authtoken=" . $zohoInvenAuthToken,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
        ),
    ));
    $response = curl_exec($curl);
    $response_arr = json_decode($response, true);
    curl_close($curl);
    if ($response_arr['message'] == 'success') {
        echo "true";
    } else {
        echo "false";
    }

}

function validateVtigerToken() {
    
    $vtigerEndpoint = trim($_REQUEST['vtigerEndpoint']);
    $vtigerUsername = trim($_REQUEST['vtigerUsername']);
    $vtigerAccessKey = trim($_REQUEST['vtigerAccessKey']);
	if (substr($vtigerEndpoint, -1) == '/') {
		// remove '/'
		$vtigerEndpoint = substr($vtigerEndpoint, 0, -1);
	}
    
	
    // Get vTiger access token
	$url = $vtigerEndpoint."/webservice.php?operation=getchallenge&username=". $vtigerUsername;
    $http_headres = array(
			"Content-Type: application/json",
			"Accept: application/json",
	        "cache-control : no-cache"
	);
	$ch = curl_init();
	//error_log("URL ".$url, 1, "support@aquaapi.com");
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
	$return = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	
	if ($http_code === 200)
	{
		//error_log($return, 1, "support@aquaapi.com");
		curl_close($ch);
		$vTigerAuth = json_decode($return, TRUE);
		$vTigerAuthToken = $vTigerAuth['result']['token'];
		$url = $vtigerEndpoint."/webservice.php";
	    $generatedKey = md5($vTigerAuthToken.$vtigerAccessKey);
	    $postfields = array('operation'=>'login', 'username'=>$vtigerUsername,
        'accessKey'=>$generatedKey);
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		$return = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//error_log($url." ".$vTigerAuth." ".$vtigerAccessKey." ".$http_code."Credentials: ".$return, 1, "support@aquaapi.com");
		//curl_close($ch);
		if ($http_code === 200)
		{
		   curl_close($ch);
		   $vTigerLogin = json_decode($return, TRUE);
		   if ($vTigerLogin['success'] == TRUE) {
			   //error_log("Success", 1, "support@aquaapi.com");
			   echo "true";
			   //curl_close($ch);
			   return;
		   }
		   else {
			   //curl_close($ch);
			   echo "false";
			   return;
		   }
		}
		else {
		   curl_close($ch);
		   echo "false";	
		   return;
		}	
	}
	else {
	   curl_close($ch);
	   echo "false";
	   return;
	}
}
