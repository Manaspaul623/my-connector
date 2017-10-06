<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>  <!-- Using For a Little use of javascript code inside in php code on line no 281 -->
<?php
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
//include_once("$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/analyticstracking.php");
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
session_start();

// After Clicking on Plans , this page will come on another tab (because of the result from this section will another Confirmation link which is not supporting in iFrame.).
if (isset($_REQUEST['plan_code']) && isset($_REQUEST['user_token']))
{
    $plan = $_REQUEST['plan_code'];
    $user = $_REQUEST['user_token'];
    //$user Details Collect
    $cond = " AND user_id='$user'";
    $u_details = fetch($userSubscription,$cond);


    $shop_token = $u_details[0]['app1_cred1'];
    $shop = $u_details[0]['app1_cred2'];

    $_SESSION['shop_token'] = $shop_token;  //Token Accessing set For Global
    $_SESSION['shop'] = $shop;


    if ($plan == 10) {
        /* Collecting Access Token After Successfully Install*/
        $tokenUrl = "https://" . $shop . "/admin/recurring_application_charges.json";
        $http_headers = array(
            "X-Shopify-Access-Token: " . trim($shop_token),
            "Content-Type: application/json"
        );
        $post_fields = array(
            'recurring_application_charge' => array(
                "name" => "Lite",
                "price" => 10,
                "return_url" => "https://" . APP_DOMAIN . "/shopify-app/subscription/provide-information.php",
                "terms" => "$10 for 500 orders",
                "capped_amount" => 10,
                "trial_days" => 14
            )
        );
        $fields = json_encode($post_fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return_data = json_decode($return, TRUE); //return data

        if($http_code == 201)
        {
            $charge_url = $return_data['recurring_application_charge']['confirmation_url'];
            header("location: ".$charge_url);
        }
        else {
            echo 'Some Thing Went Wrong on Creating Recurring charge';
            print_r($return_data);
        }

    }
    else if ($plan == 15) {
        /* Collecting Access Token After Successfully Install*/
        $tokenUrl = "https://" . $shop . "/admin/recurring_application_charges.json";
        $http_headers = array(
            "X-Shopify-Access-Token: " . trim($shop_token),
            "Content-Type: application/json"
        );
        $post_fields = array(
            'recurring_application_charge' => array(
                "name" => "Plus",
                "price" => 15,
                "return_url" => "https://" . APP_DOMAIN . "/shopify-app/subscription/provide-information.php",
                "terms" => "$15 for 1000 orders",
                "capped_amount" => 15,
                "trial_days" => 14
            )
        );
        $fields = json_encode($post_fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return_data = json_decode($return, TRUE); //return data

        if($http_code == 201)
        {
            $charge_url = $return_data['recurring_application_charge']['confirmation_url'];
            header("location: ".$charge_url);
        }
        else {
            echo 'Some Thing Went Wrong on Creating Recurring charge';
            print_r($return_data);
        }

    }
    else if ($plan == 20) {
        /* Collecting Access Token After Successfully Install*/
        $tokenUrl = "https://" . $shop . "/admin/recurring_application_charges.json";
        $http_headers = array(
            "X-Shopify-Access-Token: " . trim($shop_token),
            "Content-Type: application/json"
        );
        $post_fields = array(
            'recurring_application_charge' => array(
                "name" => "Advanced",
                "price" => 20,
                "return_url" => "https://" . APP_DOMAIN . "/shopify-app/subscription/provide-information.php",
                "terms" => "$20 for 2000 orders",
                "capped_amount" => 20,
                "trial_days" => 14
            )
        );
        $fields = json_encode($post_fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return_data = json_decode($return, TRUE); //return data

        if($http_code == 201)
        {
            $charge_url = $return_data['recurring_application_charge']['confirmation_url'];
            header("location: ".$charge_url);
        }
        else {
            echo 'Some Thing Went Wrong on Creating Recurring charge';
            print_r($return_data);
        }

    }
    else if ($plan == 25) {
        /* Collecting Access Token After Successfully Install*/
        $tokenUrl = "https://" . $shop . "/admin/recurring_application_charges.json";
        $http_headers = array(
            "X-Shopify-Access-Token: " . trim($shop_token),
            "Content-Type: application/json"
        );
        $post_fields = array(
            'recurring_application_charge' => array(
                "name" => "Pro",
                "price" => 25,
                "return_url" => "https://" . APP_DOMAIN . "/shopify-app/subscription/provide-information.php",
                "terms" => "$25 for 3000 orders",
                "capped_amount" => 25,
                "trial_days" => 14
            )
        );
        $fields = json_encode($post_fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return_data = json_decode($return, TRUE); //return data

        if($http_code == 201)
        {
            $charge_url = $return_data['recurring_application_charge']['confirmation_url'];
            header("location: ".$charge_url);
        }
        else {
            echo 'Some Thing Went Wrong on Creating Recurring charge';
            print_r($return_data);
        }

    }
    else{
        echo "invalid Plan Selection";
    }
}
// After Subscription Complete A charge id Will Return Then we will Send the result of this section to plan-select page.
else if(isset($_REQUEST['charge_id']))
{
    $charge_id=$_REQUEST['charge_id'];

    $shop = $_SESSION['shop'];
    $shop_token = $_SESSION['shop_token'];
    /* Collecting Access Token After Successfully Install*/
    $tokenUrl = "https://" . $shop . "/admin/recurring_application_charges/".$charge_id.".json";
    $http_headers = array(
        "X-Shopify-Access-Token: " . trim($shop_token),
        "Content-Type: application/json"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);

    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $return_data = json_decode($return, TRUE); //return data

    $paymentStatus = $return_data['recurring_application_charge']['status'];

    $statusChecking = 0;

     //Here we will use our Database to Insert all the subscription details of Customer
     if($paymentStatus == "accepted")
     {
         //For Payment Status is Accepted then we will activate the Subscription
         //
         $tokenUrl = "https://" . $shop . "/admin/recurring_application_charges/".$charge_id."/activate.json";
         $http_headers = array(
             "X-Shopify-Access-Token: " . trim($shop_token),
             "Content-Type: application/json"
         );
         $post_fileds = array(
             'recurring_application_charge' => array(
                 "name" => $return_data['recurring_application_charge']['name'],
                 "price" => $return_data['recurring_application_charge']['price'],
                 "api_client_id" => $return_data['recurring_application_charge']['api_client_id'],
                 "return_url" => "https://" . APP_DOMAIN . "/shopify-app/subscription/provide-information.php",
                 "capped_amount" => $return_data['recurring_application_charge']['price'],
                 "trial_days" => 14
             )
         );
         $fields = json_encode($post_fileds);
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $tokenUrl);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
         curl_setopt($ch, CURLOPT_POST,1);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

         $return = curl_exec($ch);
         $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         curl_close($ch);
         if($http_code == 200)
         { //Subscription Activated now will insert to out DB

             //Update to our Subscription and Subscription Final Table
             $cond = " AND app1_cred1='$shop_token' AND app1_cred2='$shop'";
             $updateArr = array(
                 'subscription_id' => $charge_id,
                 'plan' => $return_data['recurring_application_charge']['price'] ." [ ". $return_data['recurring_application_charge']['name']. " ] ",
                 'current_status' => 1
             );
            $upSubscription = update($userSubscription,$updateArr,$cond);

            if($upSubscription){
                $fetchSubscription = fetch($userSubscription,$cond);
                //insert into Subscription Final
                $insertArr = array (
                  'subscription_id' => $fetchSubscription[0]['subscription_id'],
                   'user_id' => $fetchSubscription[0]['user_id'],
                    'app1_cred1' => $fetchSubscription[0]['app1_cred1'],
                    'app1_cred2' => $fetchSubscription[0]['app1_cred2'],
                    'app1_cred3' => $fetchSubscription[0]['app1_cred3'],
                    'app2_cred1' => $fetchSubscription[0]['app2_cred1'],
                    'app2_cred2' => $fetchSubscription[0]['app2_cred2'],
                    'app2_cred3' => $fetchSubscription[0]['app2_cred3'],
                    'hub_order' => $fetchSubscription[0]['hub_order'],
                    'function_name' => $fetchSubscription[0]['function_name'],
                    'app1' => $fetchSubscription[0]['app1'],
                    'app2' => $fetchSubscription[0]['app2'],
                    'plan' => $fetchSubscription[0]['plan'],
                    'current_status' => $fetchSubscription[0]['current_status']
                );
                $insertFinalSubscription = insert($userSubscriptionFinal,$insertArr);

                //update to user Login Table
                $user_id = $fetchSubscription[0]['user_id'];
                $con = " AND user_id ='$user_id'";
                $updateAr = array (
                  'billing_id' => $fetchSubscription[0]['subscription_id']
                );
                $upUser = update($userLogin,$updateAr,$con);

                if(is_numeric($insertFinalSubscription)){
                    $statusChecking = 1;
                }
                else{
                    echo "not insert to Final Subscription table";
                }

            }
            else {
                echo 'Not updated to Subscription Table';
            }

         }
         else {
             echo 'Subscription Not Activated';
             print_r($return);
         }
     }
     else {
         $statusChecking = 1;  // For Payment Status Declined
     }

    if($statusChecking ==1 ) {
        echo '<script type="text/javascript">
                    var charge_id = "' . $charge_id . '";
                    var status = "' . $paymentStatus . '";
                        SetCharge(charge_id,status);
                        
                        function SetCharge(charge,status) {
                                try {
                                    window.opener.GetCharge(charge,status);
                                }
                                catch (err) {}
                                window.close();
                                return false;
                            }

                            
                </script>';
    }

}
//Except Other wrong link typing or clicking the error will show
else{
    echo "invalid Url Selection";
}

?>