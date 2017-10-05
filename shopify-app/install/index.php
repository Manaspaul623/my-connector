

<?php
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
//include_once("$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/analyticstracking.php");
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
session_start();
if(isset($_REQUEST['code'])){  //For First time Access_token Collection if Code Returning
    $state = trim($_REQUEST['state']);
    if($_SESSION['state'] === $state) {
        unset($_SESSION['state']);


        $code = trim($_REQUEST['code']);
        $shop = trim($_REQUEST['shop']);
        //Globally Shop Stored for Using in all page where required shop
        $_SESSION['shop'] = $shop;

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
        if($http_code == 200){
            try {
                $userInfoJson = json_decode($return, TRUE);

                $u_access_token = $userInfoJson['access_token'];

                //Store Globally
                $_SESSION['access_token'] = $u_access_token;
                $_SESSION['shop'] = $shop;


                $u_scope = $userInfoJson['scope'];

                $cond=" AND app1_cred1='$u_access_token'";
                $count_row = count_row($userSubscription,$cond);
                if($count_row == 0) {
                    //$insertSubscriptionFinal = insert($userSubscriptionFinal, $insertArr);

                    /* Collecting Shop Details After Successfully Install*/
                    $tokenUrl = "https://" . $shop . "/admin/shop.json";
                    $http_headers = array(
                        "X-Shopify-Access-Token: " . trim($u_access_token)
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    $shop_data = json_decode($return, TRUE); //Shop Data Collect
                    $shop_email = $shop_data['shop']['email'];
                    $shop_name = $shop_data['shop']['name'];
                    $insertUser = "";

                    $user_cond = " AND user_email='$shop_email' AND user_name='$shop_name'";
                    $count_user = count_row($userLogin,$user_cond);
                    if($count_user == 0) { //Email Already Exist or Not in our record checking
                        //For User Table Insert
                        $user_arr = array(
                            'user_name' => $shop_data['shop']['name'],
                            'user_email' => $shop_data['shop']['email'],
                            'account_name' => $shop_data['shop']['name']
                        );
                        $insertUser = insert($userLogin, $user_arr);

                        //For Subscription Table
                        $insertArr = array(
                            'user_id' => $insertUser,
                            'app1' => "SHOPIFY",
                            'app1_cred1' => $u_access_token,
                            'app1_cred2' => $shop
                        );
                        $insertSubscription = insert($userSubscription, $insertArr);

                        //After all Create and insert into database Now we will create  a uninstall Webhook For Future uninstall  of The App
                        /***** And it is Very Important ************/

                        /* Creating A uninstall Webhook*/
                        $tokenUrl = "https://" . $shop . "/admin/webhooks.json";
                        $http_headers = array(
                            "X-Shopify-Access-Token: " . trim($u_access_token),
                            "Content-Type: application/json"
                        );
                        $client_ar = array (
                            "webhook" => array (
                            "topic" => "app/uninstalled",
                            "address" => "https://" . APP_DOMAIN . "/shopify-app/install/uninstall.php",
                            "format" => "json"
                            )
                        );

                        $json_uninstall = json_encode($client_ar);


                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_uninstall);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        curl_close($ch);
                        if($http_code == 201) {
                            header("Location:https://" . $shop . "/admin/apps"); // For Linking First Time App install
                        }
                        else{
                            echo 'UnInstall Webhook not Created';
                            print_r($return);
                        }
                    }
                    else{
                        $fetch_user = fetch($userLogin,$user_cond);
                        $insertUser = $fetch_user[0]['user_id'];
                        header("Location:crm-type.php?access_id=$insertUser"); // After linking Every time when user click on app link
                    }



                    /*
                    $insertDataId = insert($userTable, $userDataArr); */
                    //
                }
                else
                    {
                    $fetch_subscription = fetch($userSubscription,$cond);
                    $subscription=$fetch_subscription[0]['subscription_id'];
                    $app2 = $fetch_subscription[0]['app2'];
                    if(empty($subscription) && empty($app2)){    //  only User and Shopify Details exist but no subscription or Second app Not Exist then will send to chose Second App
                        $user = $fetch_subscription[0]['user_id'];
                        header("Location:crm-type.php?access_id=$user");
                    }
                    else{ //Subscription Previously Created with second app but in InActive State
                        $user = $fetch_subscription[0]['user_id'];
                        header("Location:../subscription/user-dashboard?access_id=".$user);
                    }
                }
            } catch (Exception $ex) {
                echo 'Error :' . $ex;
            }
        }
    }
}
else{
    $hmac = trim($_REQUEST['hmac']);
    $shop = trim($_REQUEST['shop']);
    //A random  Key is Generated For Security Check
    $randomNum = md5(substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 11));

    $_SESSION['state'] = $randomNum;
    /*Send The User For install the app */
    header("Location: https://" . $shop . "/admin/oauth/authorize?client_id=" . SHOPIFY_CLIENT_ID . "&scope=read_products,read_product_listings,read_collection_listings,read_customers,read_orders&redirect_uri=https://" . APP_DOMAIN . "/shopify-app/install/index.php&state=" . $randomNum . "&grant_options[]=");
} ?>