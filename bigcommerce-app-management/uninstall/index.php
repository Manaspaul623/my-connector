<?php
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

session_start();
if (isset($_REQUEST['signed_payload'])) {
    //Get the signed payload data
    $payLoad = $_REQUEST['signed_payload'];
    $retValSigned = verifySignedRequest($payLoad);

    //Get The User data
    $payLoadUserId = $retValSigned['user']['id'];
    //Get user id on the basis of user id in the signed payload.
    if ($payLoadUserId > 0) {
        try {
            //Connect Database and open the connection.
            $connection_status = Connectdb();
            $open_status = Opendb();
            //Get user information from database.
            //Set the user parameters.
            $userData = $condID . $payLoadUserId;
            $resVal = fetch($userTable, $userData);
            $totCount = count($resVal);
            if ($totCount > 0) {
                $chkUID = $resVal[0]['user_id'];
                $subscriptionId = $resVal[0]['zoho_subscription_id'];
                
            } else {
                echo 'Record not found.';
            }
        } catch (Exception $ex) {
            echo 'errors :' . $ex;
        }
    }
}

//Verify the Signed data
function verifySignedRequest($signedRequest) {
    list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);
    // decode the data
    $signature = base64_decode($encodedSignature);
    $jsonStr = base64_decode($encodedData);
    $data = json_decode($jsonStr, true);
    // confirm the signature
    $expectedSignature = hash_hmac('sha256', $jsonStr, APP_AUTH_CLIENT_SECRET, $raw = false);
    if (!hash_equals($expectedSignature, $signature)) {
        error_log('Bad signed request from Bigcommerce!');
        return null;
    }
    return $data;
} 
