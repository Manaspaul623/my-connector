<?php

/* Description:     This page is the callback after payment information submission
 * @version:        1.0.0
 * @copyright:      aquaAPI(http://www.aquaapi.com)
 */
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$hostedpage_id = trim($_REQUEST['hostedpage_id']);      /* hosted page id to get customer and subscription module */
$userToken = trim($_REQUEST['user_token']);             /* user token which is the link between local db and user */
$storeHash = trim($_REQUEST['storeHash']); 

//
$url = 'https://subscriptions.zoho.com/api/v1/hostedpages/' . $hostedpage_id;

// include 'config.php';       /* including configuration file */
/* start: retrieving user subscription information from hostedpage */
$headers = array(
    "Content-Type: application/json;charset=UTF-8 ",
    "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
    "cache-control: no-cache",
    "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$return = curl_exec($ch);
$ret_values = json_decode($return, TRUE);

/* end: retrieving user subscription information from hostedpage */

/* checking if new subscription or payment card updated */
if ($ret_values['action'] === 'update_card') {
    echo "<script> alert('Subscrption is successful,We are redirecting you shortly.'); </script>";
    echo "Credit card updated";
    header("Location:https://store-$storeHash.mybigcommerce.com/manage/app/".APP_ID);
}
if ($ret_values['action'] === 'new_subscription') {

    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

    $zohoSubscriptionId = $ret_values['data']['subscription']['subscription_id'];
    $zohoCustId = $ret_values['data']['subscription']['customer']['customer_id'];
    $zohoCustDispName = $ret_values['data']['subscription']['customer']['display_name'];
    $zohoCustEmail = $ret_values['data']['subscription']['customer']['email'];
    $userDataArr = array(
        'user_type' => 'TRUE',
        'zoho_subscription_id' => $zohoSubscriptionId,
        'zoho_cust_id' => $zohoCustId,
        'zoho_cust_disp_name' => $zohoCustDispName,
        'zoho_cust_email' => $zohoCustEmail,
        'current_status' =>'SUBSCRIBED'
    );
    $cond = " AND user_id=$userToken";
    $re = update($userTable, $userDataArr, $cond);
    if($re){
        header("Location:https://store-$storeHash.mybigcommerce.com/manage/app/".APP_ID);
    }else{
        header("Location:https://store-$storeHash.mybigcommerce.com/manage/app/".APP_ID);
    }
}
