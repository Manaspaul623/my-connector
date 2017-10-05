<?php
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
//include_once("$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/analyticstracking.php");
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
session_start();


$method = $_SERVER['REQUEST_METHOD'];
if($method == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true); // Collected Response

    //collect from response
    $shop_name = $input['name'];
    $email = $input['email'];

    //search in db
    $cond = " AND user_name='$shop_name' AND user_email='$email'";
    $count = count_row($userLogin,$cond);
    if($count>0) {
        $fetch_user_id = fetch($userLogin, $cond);
        $fetch_user_id = $fetch_user_id[0]['user_id'];
        //Delete from all Table
        $cond = " AND user_id='$fetch_user_id'";
        //From Subscription
        $deleteSubscription = delete($userSubscription, $cond);
        //From Subscription
        $deleteSubscriptionFinal = delete($userSubscriptionFinal, $cond);
        //From Subscription
        $deleteUser = delete($userLogin, $cond);

        unset($_SESSION['shop_token']);
        unset($_SESSION['shop']);
        unset($_SESSION['userId']);
        unset($_SESSION['currentStatus']);
        unset($_SESSION['userName']);
        unset($_SESSION['crmType']);
    }
}
?>