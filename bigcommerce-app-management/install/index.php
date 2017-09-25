<?php
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/analyticstracking.php");
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");


$code = trim($_REQUEST['code']);
$scope = trim($_REQUEST['scope']);
$context = trim($_REQUEST['context']);
/* installing the app after successful subscription */
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

/* check if response is success i.e response code is 200 */
if ($http_code === 200) {
    $connection_status = Connectdb();
    $open_status = Opendb();
    try {
        $userInfoJson = json_decode($return, TRUE);
        $u_access_token = $userInfoJson['access_token'];
        $u_scope = $userInfoJson['scope'];
        $u_user_id = $userInfoJson['user']['id'];
        $u_user_username = $userInfoJson['user']['username'];
        $u_user_email = $userInfoJson['user']['email'];
        $u_user_context = $userInfoJson['context'];
        $user_store_url = str_replace('s/', '-', $u_user_context);

        $userDataArr = array(
            'access_token' => $u_access_token,
            'scope' => $u_scope,
            'user_id' => $u_user_id,
            'user_name' => $u_user_username,
            'user_email' => $u_user_email,
            'context' => $u_user_context,
            'crm_type' => NULL,
            'zoho_auth_id' => NULL,
            'user_type' => 'FALSE',
            'sfdc_user_name' => NULL,
            'sfdc_password' => NULL,
            'sfdc_security_password' => NULL,
            'zoho_subscription_id' => NULL,
            'zoho_cust_id' => NULL,
            'zoho_cust_disp_name' => NULL,
            'zoho_cust_email' => NULL,
            'current_status' => 'INITIALIZE',
            'account_type' => 'new',
            'creation_time' => time()
        );
        $insertDataId = insert($userTable, $userDataArr);
        header("Location:crm-type.php?access_id=$u_user_id");
    } catch (Exception $ex) {
        echo 'Error :' . $ex;
    }
} else {
    $error = curl_error($ch);
    echo 'Error :' . $error;
}
