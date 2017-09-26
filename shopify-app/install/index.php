<?php
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
//include_once("$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/analyticstracking.php");
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
session_start();
if(!isset($_REQUEST['code'])) {
    $hmac = trim($_REQUEST['hmac']);
    $shop = trim($_REQUEST['shop']);
    //A random  Key is Generated For Security Check
    $randomNum = md5(substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 11));

    $_SESSION['state'] = $randomNum;
    /*Send The User For install the app */
    header("Location: https://" . $shop . "/admin/oauth/authorize?client_id=" . SHOPIFY_CLIENT_ID . "&scope=read_products,write_products,read_product_listings,read_collection_listings,read_customers,write_customers,read_orders,write_orders&redirect_uri=https://www.showmeademo.com/shopify-app/install/index.php&state=" . $randomNum . "&grant_options[]=");
}
else{
     $state = trim($_REQUEST['state']);
    if($_SESSION['state'] === $state) {
       unset($_SESSION['state']);


       $code = trim($_REQUEST['code']);
       $shop = trim($_REQUEST['shop']);
       $state = trim($_REQUEST['state']);
       $timestamp = trim($_REQUEST['timestamp']);

        /* Collecting Access Token After Successfully Install*/
        $tokenUrl = "https://".$shop."/admin/oauth/access_token";
        $client_ar = array(
            "client_id" => SHOPIFY_CLIENT_ID,
            "client_secret" => SHOPIFY_CLIENT_SECRET,
            "code" => $code
        );

        $http_headres = array(
            "Content-Type: application/json"
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
        print_r(json_decode($return,TRUE));
        if($http_code == 200){
           try {
                $userInfoJson = json_decode($return, TRUE);
                $u_access_token = $userInfoJson['access_token'];
                $u_scope = $userInfoJson['scope'];

               $insertArr = array(
                   'user_id' => "",
                   'app1' => "SHOPIFY",
                   'app1_cred1' => $u_access_token,
                   'app1_cred2' => $shop
               );

               $cond=" AND app1_cred1='$u_access_token'";
                $count_row = count_row($userSubscription,$cond);
                if($count_row == 0) {
                    $insertSubscription = insert($userSubscription, $insertArr);
                    $insertSubscriptionFinal = insert($userSubscriptionFinal, $insertArr);
                }
               /*
               $insertDataId = insert($userTable, $userDataArr); */
               //header("Location:crm-type.php?access_id=$u_user_id");
           } catch (Exception $ex) {
               echo 'Error :' . $ex;
           }
        }
    }
}