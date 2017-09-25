<?php

include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

if ($_REQUEST['type']) {
    session_start();
    dispatcher($_REQUEST['type']);
}

function dispatcher($type) {
    switch ($type) {
        case 'getPlans' : getPlans();
            break;
        case 'createSubscription' : createSubscription();
            break;
        case 'updateSubscription' : updateSubscription();
            break;
        case 'updateCard' : updateCard();
            break;
        case 'cancelSubscription' : cancelSubscription();
            break;
        case 'getCustomer' : getCustomer();
            break;
        case 'updateCustomer' : updateCustomer();
            break;
        case 'getSubscription' : getSubscription();
            break;
        case 'getTimeSlot' : getTimeSlot();
            break;
        case 'getTransactionDetails' : getTransactionDetails();
            break;
        case 'getSyncErrors' : getSyncErrors();
            break;
        case 'getUserSyncTime' : getUserSyncTime();
            break;
        case 'updateSyncTime' : updateSyncTime();
            break;
        case 'reactivateApp' : reactivateApp();
            break;
        case 'supportSubmit' : supportSubmit();
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

function getPlans() {
    // include 'config.php';
    $url = SUBSCRIPTION_URL . '/plans?product_id='.BG_CONNECTOR_PRODUCT_CODE;

    $headers = array(
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
    echo $return;
}

function createSubscription() {
//    include 'config.php';
    $url = SUBSCRIPTION_URL. '/hostedpages/newsubscription';
    $plan_code = $_REQUEST['selected_plan_code'];
    $user_token = $_REQUEST['user_token'];
    
  //  error_log("Plan Code:".$plan_code."User_Token:".$user_token, 1, "debashishp@gmail.com");

    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
    $cond = " AND user_id=$user_token";
    $usrDetails = fetch($userTable, $cond);
    $userToken = $usrDetails[0]['context'];
    $storeHashArr = explode('/', $userToken);
    $storeHash = $storeHashArr[1];

    $headers = array(
        "Content-Type: application/json;charset=UTF-8 ",
        "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
        "cache-control: no-cache",
        "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
    );
    $data_arr = array(
        'plan' => array('plan_code' => $plan_code),
        'redirect_url' => 'https://'.APP_DOMAIN.'/bigcommerce-app-management/subscription/callback.php?user_token=' . $user_token . '&storeHash=' . $storeHash
    );
    $data_string = json_encode($data_arr);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $return = curl_exec($ch);
//    error_log("Return Value:".$return, 1, "debashishp@gmail.com");

    echo $return;
}

function updateSubscription() {
    // include 'config.php';
    $zoho_subscription_id = $_REQUEST['zoho_subscription_id'];
    $choosen_plan_id = $_REQUEST['choosen_plan_id'];
    $url = SUBSCRIPTION_URL . '/subscriptions/' . $zoho_subscription_id;

    $headers = array(
        "Content-Type: application/json;charset=UTF-8 ",
        "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
        "cache-control: no-cache",
        "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
    );
    $data_arr = array(
        'plan' => array('plan_code' => $choosen_plan_id)
    );
    $data_string = json_encode($data_arr);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $return = curl_exec($ch);
    echo $return;
}

function updateCard() {
    // include 'config.php';
    $zoho_subscription_id = $_REQUEST['zoho_subscription_id'];
    $user_token = $_REQUEST['logged_user_id'];
    $url = SUBSCRIPTION_URL . '/hostedpages/updatecard';

    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
    $cond = " AND user_id=$user_token";
    $usrDetails = fetch($userTable, $cond);
    $userToken = $usrDetails[0]['context'];
    $storeHashArr = explode('/', $userToken);
    $storeHash = $storeHashArr[1];

    $headers = array(
        "Content-Type: application/json;charset=UTF-8 ",
        "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
        "cache-control: no-cache",
        "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
    );
    $data_arr = array(
        'subscription_id' => $zoho_subscription_id,
        'redirect_url' => 'https://'.APP_DOMAIN.'/bigcommerce-app-management/subscription/callback.php?user_token=' . $user_token . '&storeHash=' . $storeHash
    );
    $data_string = json_encode($data_arr);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $return = curl_exec($ch);
    echo $return;
}

function cancelSubscription() {
    // include 'config.php';
    $zoho_subscription_id = trim($_REQUEST['zoho_subscription_id']);
    $url = SUBSCRIPTION_URL. '/subscriptions/' . $zoho_subscription_id . '/cancel?cancel_at_end=true';

    $headers = array(
        "Content-Type: application/json;charset=UTF-8 ",
        "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
        "cache-control: no-cache",
        "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
    );
    $data_arr = array();
    $data_string = json_encode($data_arr);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $return = curl_exec($ch);
    $res = json_decode($return, TRUE);
    if ($res['code'] === 0) {
        include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
        include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

        $user_token = $_REQUEST['logged_user_id'];
        $updateArr = array(
            'current_status' => 'CANCELLED'
        );
        $cond = " AND user_id=$user_token";
        $re = update($userTable, $updateArr, $cond);
        $_SESSION['userCurrentStatus'] = 'CANCELLED';
    }
    echo $return;
}

function reactivateApp() {
    // include 'config.php';
    $zoho_subscription_id = trim($_REQUEST['zoho_subscription_id']);
    $url = SUBSCRIPTION_URL . '/subscriptions/' . $zoho_subscription_id . '/reactivate';

    $headers = array(
        "Content-Type: application/json;charset=UTF-8 ",
        "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
        "cache-control: no-cache",
        "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
    );
    $data_arr = array();
    $data_string = json_encode($data_arr);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $return = curl_exec($ch);
    $res = json_decode($return, TRUE);
    if ($res['code'] === 0) {
        include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
        include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

        $user_token = $_REQUEST['logged_user_id'];
        $updateArr = array(
            'current_status' => 'SYNCINTERVAL'
        );
        $cond = " AND user_id=$user_token";
        $re = update($userTable, $updateArr, $cond);
        $_SESSION['userCurrentStatus'] = 'SYNCINTERVAL';
    }
    echo $return;
}

function getSubscription() {
    $zoho_subscription_id = $_REQUEST['zoho_subscription_id'];
//    include 'config.php';
    $url = SUBSCRIPTION_URL . '/subscriptions/' . $zoho_subscription_id;
//    error_log("Subscription:".$zoho_subscription_id.$url, 1, "debashishp@gmail.com");

    $headers = array(
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
    echo $return;
}

function getCustomer() {
    // include 'config.php';
    $url = SUBSCRIPTION_URL . '/customers/361118000000048067';

    $headers = array(
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
    echo "<pre>";
    print_r($ret_values);
}


function getTimeSlot() {
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
    $timeSlotDetails = fetch($syncTimeSlot);
    foreach ($timeSlotDetails as $key => $value) {
        $user_id = $value['id'];
        $cond = " AND sync_time_slot_id=$user_id";
        $alreadyTaken = count_row($syncTimeSchedule, $cond);
        $timeSlotDetails[$key]['alreadyTaken'] = $alreadyTaken;
    }
    echo json_encode($timeSlotDetails);
}

function getUserSyncTime() {
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

    $user_token = $_REQUEST['logged_user_id'];
    $cond = " AND user_id=$user_token";
    $timeSlotDetails = fetch($syncTimeSchedule, $cond);
    echo json_encode($timeSlotDetails);
}

function updateSyncTime() {
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

    $user_token = $_REQUEST['logged_user_id'];
    $selected_id = $_REQUEST['selected_id'];
    $updateArr = array(
        'sync_time_slot_id' => $selected_id
    );
    $cond = " AND user_id=$user_token";
    $re = update($syncTimeSchedule, $updateArr, $cond);
    echo json_encode($re);
}

function getTransactionDetails() {
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

    $logged_user_id = trim($_REQUEST['logged_user_id']);
    $cond = " AND user_id='$logged_user_id' ORDER BY added_on DESC LIMIT 5";
    $timeSlotDetails = fetch($zohoTransactionDetails, $cond);

    foreach ($timeSlotDetails as $key => $value) {
        $timeSlotDetails[$key]['readableFormatDate'] = date("m-d-Y H:i", $value['last_sync_time']);
    }

    $cond = " AND user_id='$logged_user_id' LIMIT 1";
    $syncTimeTable = fetch($syncTimeSchedule, $cond);
    $slot_id = $syncTimeTable[0]['sync_time_slot_id'];

    $cond = " AND id=$slot_id";
    $slotDetails = fetch($syncTimeSlot, $cond);

    $readDateFormat = $slotDetails[0]['slot_name'];


    $resultArr = array(
        'tracsactionDetails' => $timeSlotDetails,
        'readDateFormat' => $readDateFormat
    );
    echo json_encode($resultArr);
}

function getSyncErrors() {
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";      /* including db related files */
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";

    $logged_user_id = trim($_REQUEST['logged_user_id']);
    $cond = " AND user_id='$logged_user_id' ORDER BY added_on DESC LIMIT 5";
    $syncErrors = fetch($zohoSyncErrorDetails, $cond);
    echo json_encode($syncErrors);
}

function supportSubmit() {
    $userName = $_REQUEST['user_name'];
    $email = $_REQUEST['user_email'];
    $subject = $_REQUEST['subject'];
    $message = $_REQUEST['message'];

    $message_for_admin = <<<EOF
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Jayati Chakraborty</title>
    </head>
    <body>
        <div style="background:#384556; max-width:500px; padding:15px 15px 10px; font-family:'Arial', sans-serif;">
            <div style="border-radius:5px; text-align:center; padding:15px 0; ">
                <a href="#"><img src="http://aquaapi.com/images/logo-sml.png"/></a>
            </div>
            <div style="border-radius:5px; background:#fff; padding:25px 15px; color: rgb(95, 95, 95);">
                <h1 style="font-size:22px; margin:0 0 10px;">Hello !  Admin,</h1>
                <h2 style="font-size:18px; margin:0 0 10px;">User details given below:</h2>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;"><b>Name: </b> $userName</p>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;"><b>Email: </b> $email</p>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;"><b>Message: </b> $message</p>
            </div>
            <div style="color:#fff; text-align:center; font-size:14px; padding:5px 0 0;">© aquaAPI</div> 
        </div>
    </body>
</html>
EOF;

    $from = "$userName" . " ( " . $email . " )";
    $to = 'support@aquaapi.com';
    $headers = "From: $from\r\n";
    $headers .= "Content-type: text/html\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    $flag = mail($to, $subject, $message_for_admin, $headers);

    if ($flag) {
        echo "true";
    } else {
        echo "false";
    }
}
