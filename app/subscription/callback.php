<?php

session_start();
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if (!isset($_SESSION['user_id'])) {
    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}

/* Description:     This page is the callback after payment information submission
 * @version:        1.0.0
 * @copyright:      aquaAPI(http://www.aquaapi.com)
 */

$hostedpage_id = trim($_REQUEST['hostedpage_id']);      /* hosted page id to get customer and subscription module */
$userToken = trim($_REQUEST['user_token']);             /* user token which is the link between local db and user */
//$storeHash = trim($_REQUEST['storeHash']);
$planCode = trim($_REQUEST['planCode']);
$user_id=$_SESSION['user_id'];
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
    header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php");
}
if ($ret_values['action'] === 'new_subscription') {

    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";

    $zohoSubscriptionId = $ret_values['data']['subscription']['subscription_id'];
    $planPrice=$ret_values['data']['subscription']['plan']['price']." [ ".$ret_values['data']['subscription']['plan']['name']." ]";
    $zohoCustId = $ret_values['data']['subscription']['customer']['customer_id'];
    $zohoCustDispName = $ret_values['data']['subscription']['customer']['display_name'];
    //$zohoCustEmail = $ret_values['data']['subscription']['customer']['email'];
    $subscriptionDataArr = array(
        'subscription_id' => $zohoSubscriptionId,
        'plan' => $planPrice
    );

    $userDataArr = array(
      'billing_id' => $zohoCustId,
      'account_name' => $zohoCustDispName
    );

    $subscriptionCond = " AND id='$userToken'";
    $sub = update($userSubscription, $subscriptionDataArr, $subscriptionCond); // Subscription TAble Update
    //Fetching Details From Subscription
    $fetchCond = " AND id='$userToken'";
    $fetchSubscription = fetch($userSubscription,$fetchCond);
    foreach($fetchSubscription as $key=>$value)
    {
        $insertArr = array(
            'subscription_id' => $value['subscription_id'],
            'user_id' =>$value['user_id'],
            'app1_cred1' => $value['app1_cred1'],
            'app1_cred2' => $value['app1_cred2'],
            'app1_cred3' => $value['app1_cred3'],
            'app2_cred1' => $value['app2_cred1'],
            'app2_cred2' => $value['app2_cred2'],
            'app2_cred3' => $value['app2_cred3'],
            'hub_order' => $value['hub_order'],
            'function_name' => $value['function_name'],
            'app1' => $value['app1'],
            'app2' =>$value['app2'],
            'plan' => $value['plan']
        );
    }
    $subscriptionFinal=insert($userSubscriptionFinal,$insertArr); //Final Subscription Insert


    $userCond = " AND user_id='$user_id'";
    $user = update($userLogin,$userDataArr,$userCond); //User Login Table Update

    if ($sub && $subscriptionFinal && $user) {
      header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php");
    } else {
       header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php");
    }
}
