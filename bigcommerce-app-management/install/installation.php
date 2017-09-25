<?php
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
//
//Get the data from zoho signup page.
$userToken = '1';
$zohoSubscriptionId = '';
$zohoCustId = '';
$zohoCustDispName = '';
$zohoCustEmail = '';
//Retrive data from the database using user_token value.
if ($userToken > 0) {
    $connection_status = Connectdb();
    $open_status = Opendb();
    //Get user information from database.
    //Set the user parameters.
    $condTmpId = ' AND id=' . $userToken;
    $resVal = fetch($tempUser, $condTmpId);
    $totCount = count($resVal);
    if ($totCount > 0) {
        $crmType = $resVal[0]['crm_type'];
        if ($crmType === 'SFDC') {
            $sfdcUserName = $resVal[0]['sfdc_user_name'];
            $sfdcPassword = $resVal[0]['sfdc_password'];
            $sfdcSecurityPassword = $resVal[0]['sfdc_security_password'];            
        } elseif ($crmType === 'ZOHO') {
            $zohoAuthId = $resVal[0]['zoho_auth_id'];
        }
        $code = $resVal['code'];
        $scope = $resVal['scope'];
        $context = $resVal['context'];
    }
}
//
$tokenUrl = "https://login.bigcommerce.com/oauth2/token";
$client_ar = array(
    "client_id" => APP_AUTH_CLIENT_ID,
    "client_secret" => APP_AUTH_CLIENT_SECRET,
    "redirect_uri" => "https://".APP_DOMAIN."/bigcommerce-app-management/install/index.php",
    "grant_type" => "authorization_code",
    "code" => $code,
    "scope" => $scope,
    "context" => $context
);
$http_headres = array(
    "Content-Type: application/json",
    "Accept: application/json"
);
$json_value = json_encode($client_ar);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_value);
$return = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
// check if it returns 200, or else return false
if ($http_code === 200) {
    $flag = 1;
    $msg = $return;
} else {
    $error = curl_error($ch);
    $flag = 0;
    $msg = $error;
}
$arr = array(
    'flag' => $flag,
    'msg' => $msg
);
//
//INSERT INTO THE DATABASE
if ($flag === 1) {
    $connection_status = Connectdb();
    $open_status = Opendb();
    //
    try {
        $userInfoJson = json_decode($return, TRUE);
        $u_access_token = $userInfoJson['access_token'];
        $u_scope = $userInfoJson['scope'];
        $u_user_id = $userInfoJson['user']['id'];
        $u_user_username = $userInfoJson['user']['username'];
        $u_user_email = $userInfoJson['user']['email'];
        $u_user_context = $userInfoJson['context'];
        $u_crm_type = $crmType;
        $u_zohoAuthToken_Id = $zohoAuthId;        
        //
        $userDataArr = array(
            'access_token' => $u_access_token,
            'scope' => $u_scope,
            'user_id' => $u_user_id,
            'user_name' => $u_user_username,
            'user_email' => $u_user_email,
            'context' => $u_user_context,
            'crm_type' => $u_crm_type,
            'zoho_auth_id' => $u_zohoAuthToken_Id,
            'user_type' => 'TRUE',
            'sfdc_user_name' => $sfdcUserName,
            'sfdc_password' => $sfdcPassword,
            'sfdc_security_passwor' => $sfdcSecurityPassword,
            'zoho_subscription_id' => $zohoSubscriptionId,
            'zoho_cust_id' => $zohoCustId,
            'zoho_cust_disp_name' => $zohoCustDispName,
            'zoho_cust_email' => $zohoCustEmail,
            'creation_time' => time()
        );
        $insertData = insert($userTable, $userDataArr);
        if ($insertData > 0) {
            header("Location:installation-success.php");
            exit();
        } else {
            header("Location:installation-failed.php");
            exit();
        }
        //
    } catch (Exception $ex) {
        echo 'Error :' . $ex;
    }
}