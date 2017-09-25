<?php
/* including database related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

//$max_date_created = date("Y-m-d") . "T" . $syncTimeTable;
$lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "+0 days"));
$maxTime = date("H:i");
$max_date_created = $lastDay. "T" . $maxTime;
//print_r("Max Date: ".$max_date_created);

//$min_time = $syncTimeTable - 2;
$lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "+0 days"));
$minTime = date("H:i", time() - 7200);
$min_date_created = $lastDay. "T" . $minTime;
//print_r("<br>Min Date: ".$min_date_created);

$credentials = array(
    'userEmail' => 'manas.paul@aquaapi.com',
    'userName' => 'manas.paul@aquaapi.com',
    'max_date_created' => $max_date_created,
    'min_date_created' => $min_date_created,
    'app1Details' => array(),
    'app2Details' => array()
);

//bigcommerceToHubspotCRM($credentials);
//magentoToHubspotCRM($credentials);
prestashopToZohoCRM($credentials);

function prestashopToZohoCRM($credentials)
{
    //Prestashop credentionals
    $credentials['app1Details'] = array(
        'prestashop_shop_url' => 'http://sample-env.8pxyunxime.us-west-2.elasticbeanstalk.com',
        'prestashop_api_key' => 'XZGBFCN9AR79MJQ3JSRD38J72BU2G4DB'
    );
    //Zoho Credential
    $credentials['app2Details'] = array(
        'app_key' => '68bc025757c71ed64caf1df80ad2ffd7',
        'app_password' => '1d0fc0a8bdaa0e432e0139b69f5171bf',
        'store_url' => 'https://68bc025757c71ed64caf1df80ad2ffd7:1d0fc0a8bdaa0e432e0139b69f5171bf@aquaapi-llc.myshopify.com/admin/',
    );


    $url = explode("http://", $credentials['app1Details']['prestashop_shop_url']);
    echo '<pre>';
    $url = 'http://' . $credentials['app1Details']['prestashop_api_key'] . "@" . $url['1'] . "/api";

    $customer_url = $url . "/orders/3/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $customer_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);


    $xml = simplexml_load_string($return, "SimpleXMLElement", LIBXML_NOCDATA); //For Converting XML file to Json
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    print_r($array);


}

function bigcommerceToHubspotCRM($credentials)
{
    //bigcommerce credentionals
      $credentials['app1Details']=array(
          'bigcommerce_user_name' => 'debashishpr',
          'bigcommerce_password' => 'aa3ca2573b560f3bda5982b429ef657b24dffa75',
          'bigcommerce_context' => 'https://store-t0w5eu.mybigcommerce.com'
      );
    //hubspot Credentials
   /* $credentials['app2Details']=array(
        'app' => 'My Test App',
        'client_id' => '11dad90b-0204-4bab-8170-8b50c5154046',
        'client_secret' => '3745a9a9-1594-4990-b427-2d404b3ac80c',
        'hubapi_url' => 'https://api.hubapi.com'
    ); */

   $credentials['app2Details'] = array(
       'app_key' => '68bc025757c71ed64caf1df80ad2ffd7',
       'app_password' => '1d0fc0a8bdaa0e432e0139b69f5171bf',
       'store_url' => 'https://68bc025757c71ed64caf1df80ad2ffd7:1d0fc0a8bdaa0e432e0139b69f5171bf@aquaapi-llc.myshopify.com/admin/',
   );

   /* $subscriptionId = $credentials['subscription_id'];
    $crmType = 'bigcommerceTovtiger'; */
    $bigcommURL = $credentials['app1Details']['bigcommerce_context'] . "/api";
    $bigcommerceUsername = $credentials['app1Details']['bigcommerce_user_name'];
    $bigcommerceAccessKey = $credentials['app1Details']['bigcommerce_password'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];

     //$hubspotUrl = $credentials['app2Details']['hubapi_url'];
   // $hubspotKey = $credentials['app2Details']['hub_hapikey'];
     $app_key = $credentials['app2Details']['app_key'];
      $app_password = $credentials['app2Details']['app_password'];
      $app_url = $credentials['app2Details']['store_url'];


    //Customer Details collect from BigCommerce order
    $customer_url = $app_url."/orders.json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $customer_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = json_decode($return, TRUE);
    echo '<pre>';
    print_r($http_code);

    exit(1);
    try {

        $retVal = FALSE;
        $total_success = 0;
        $access_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
        $url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $http_headres = array(
            "Authorization: Basic " . trim($access_token),
            "Content-Type: application/json",
            "Accept: application/json",

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //print_r($return);

        // check if it returns 200, or else return false
        // print_r("HTTP Code ".$http_code);


        if ($http_code === 200) {
            curl_close($ch);
            $count_arr = json_decode($return, TRUE);
            $count = $count_arr['count'];

            if ($count > 0) {
                $page = ceil($count / MAX_PAGE_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $url = $bigcommURL . "/v2/orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // check if any orders exist for the page

                    if ($http_code === 200) {
                        $orders = json_decode($return, TRUE);
                        // Insert everything for the Order including Contact and Products.

                        foreach ($orders as $key => $value) {

                            $customer_id = $orders[$key]['customer_id'];

                            //Customer Details collect from BigCommerce order
                            $customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $customer_url);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);

                            if ($http_code === 200) {
                                $customerDetails = json_decode($return, TRUE);
                                $firstName = $customerDetails['first_name'];
                                $lastName = $customerDetails['last_name'];
                                $accountName = $firstName . ' ' . $lastName;
                                $email = $customerDetails['email'];
                                $phone = $customerDetails['phone'];


                                /* retrieve address for the customer */
                                $address_url = $customerDetails['addresses']['url'];
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $address_url);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                $return = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                curl_close($ch);
                                if ($http_code === 200) {
                                    $addressDetails = json_decode($return, TRUE);
                                    $mailingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                    $otherStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                    $mailingCity = $addressDetails[0]['city'];
                                    $otherCity = $addressDetails[0]['city'];
                                    $mailingState = $addressDetails[0]['state'];
                                    $otherState = $addressDetails[0]['state'];
                                    $mailingZip = $addressDetails[0]['zip'];
                                    $otherZip = $addressDetails[0]['zip'];
                                    $mailingCountry = $addressDetails[0]['country'];
                                    $otherCountry = $addressDetails[0]['country'];
                                } else {
                                    $mailingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                    $otherStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                    $mailingCity = $orders[$key]['billing_address']['city'];
                                    $otherCity = $orders[$key]['billing_address']['city'];
                                    $mailingState = $orders[$key]['billing_address']['state'];
                                    $otherState = $orders[$key]['billing_address']['state'];
                                    $mailingZip = $orders[$key]['billing_address']['zip'];
                                    $otherZip = $orders[$key]['billing_address']['zip'];
                                    $mailingCountry = $orders[$key]['billing_address']['country'];
                                    $otherCountry = $orders[$key]['billing_address']['country'];
                                }

                                $flag = FALSE;
                                try {
                                    //Create Contact for this Order ..................

                                    $contactInsertUrl = $hubspotUrl . '/contacts/v1/contact/createOrUpdate/email/' . $email;
                                    $http_headres = array(
                                        "Authorization: Bearer $hubspot_access_token"
                                        //"Content-Type: application/json"
                                    );

                                    $contactData = array(
                                        'properties' => array(
                                            array(
                                                'property' => 'email',
                                                'value' => $email
                                            ),
                                            array(
                                                'property' => 'firstname',
                                                'value' => $firstName
                                            ),
                                            array(
                                                'property' => 'lastname',
                                                'value' => $lastName
                                            ),
                                            array(
                                                'property' => 'phone',
                                                'value' => $phone
                                            ),
                                            array(
                                                'property' => 'address',
                                                'value' => $mailingStreet
                                            ),
                                            array(
                                                'property' => 'city',
                                                'value' => $mailingCity
                                            ),
                                            array(
                                                'property' => 'state',
                                                'value' => $mailingState
                                            ),
                                            array(
                                                'property' => 'zip',
                                                'value' => $mailingZip
                                            ),
                                            array(
                                                'property' => 'country',
                                                'value' => $mailingCountry
                                            ),
                                            array(
                                                'property' => 'jobtitle',
                                                'value' => 'New Contact'
                                            ),
                                            array(
                                                'property' => 'lifecyclestage',
                                                'value' => 'customer'
                                            )
                                        )
                                    );
                                    $contactFieldsJSON = json_encode($contactData);

                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $contactInsertUrl);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $response = curl_exec($ch);
                                    $responseDecode = json_decode($response, true);
                                    echo '<pre>';
                                    print_r($responseDecode);
                                    //End of Contacts
                                    $order_id = $orders[$key]['id'];
                                    $subject = 'Order Number ' . $orders[$key]['id'];
                                    $grandTotal = $orders[$key]['total_inc_tax'];
                                    $subTotal = $orders[$key]['subtotal_ex_tax'];
                                    $tax = $orders[$key]['total_tax'];
                                    $adjustment = $orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'];

                                    $orderBillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
                                    $orderBillingCity = $orders[$key]['billing_address']['city'];
                                    $orderBillingState = $orders[$key]['billing_address']['state'];
                                    $orderBillingZip = $orders[$key]['billing_address']['zip'];
                                    $orderBillingCountry = $orders[$key]['billing_address']['country'];

                                    // retrieve shipping address

                                    $orderShippingURL = $orders[$key]['shipping_addresses']['url'];
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    curl_close($ch);
                                    $orderShippingAddresses = json_decode($return, TRUE);
                                    $orderShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
                                    $orderShippingCity = $orderShippingAddresses[0]['city'];
                                    $orderShippingState = $orderShippingAddresses[0]['state'];
                                    $orderShippingZip = $orderShippingAddresses[0]['zip'];
                                    $orderShippingCountry = $orderShippingAddresses[0]['country'];

                                    //Form Data Submit
                                    $formInsertUrl = "https://forms.hubspot.com/uploads/form/v2/3847747/2b0889e6-aa7e-420b-ab5d-a5b3b116110a";

                                    $headers = array(
                                        "Content-Type: application/x-www-form-urlencoded"
                                    );
                                    $hubspotutk      = $_COOKIE['hubspotutk']; //grab the cookie from the visitors browser.
                                    $ip_addr         = $_SERVER['REMOTE_ADDR']; //IP address too.
                                    $hs_context      = array(
                                        'hutk' => $hubspotutk,
                                        'ipAddress' => $ip_addr
                                    );
                                    $hs_context_json = json_encode($hs_context);

                                    $str_post = "subject=" . urlencode($subject)
                                        . "&order_id=" . urlencode($order_id)
                                        . "&email=" . urlencode($email)
                                        . "&status=" . urlencode("Created")
                                        . "&bill_street=" . urlencode($orderBillingStreet)
                                        . "&bill_city=" . urlencode($orderBillingCity)
                                        . "&bill_code=" . urlencode($orderBillingZip)
                                        . "&bill_country=" . urlencode($orderBillingCountry)
                                        . "&bill_state=" . urlencode($orderBillingState)
                                        . "&ship_street=" . urlencode($orderShippingStreet)
                                        . "&ship_city=" . urlencode($orderShippingCity)
                                        . "&ship_code=" . urlencode($orderShippingZip)
                                        . "&ship_country=" . urlencode($orderShippingCountry)
                                        . "&ship_state=" . urlencode($orderShippingState)
                                        . "&tax=" . urlencode($tax)
                                        . "&subtotal=" . urlencode($subTotal)
                                        . "&total=" . urlencode($grandTotal)
                                        . "&hs_context=" . urlencode($hs_context_json); //Leave this one be
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $formInsertUrl);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $response = curl_exec($ch);
                                    $responseDecode = json_decode($response, true);
                                    curl_close($ch);

                                    //Form submited
                                    echo 'Email Detail<br>';
                                    //form list collect

                                    $formUrl = $hubspotUrl . '/contacts/v1/contact/email/'.$email.'/profile';
                                    $http_headres = array(
                                        "Authorization: Bearer $hubspot_access_token"
                                         //"Content-Type: application/json"
                                    );


                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $formUrl);
                                    curl_setopt($ch, CURLOPT_POST, 0);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $response = curl_exec($ch);
                                    $responseDecode = json_decode($response, true);

                                    print_r($responseDecode);



                                } catch (Exception $e) {
                                    $msg = $e;
                                    $flag = FALSE;
                                }// for each order on the page
                            }
                        } // for each page of orders
                    }


                } // if count of orders returned
            }
        }
    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

}
function magentoToHubspotCRM($credentials){
    //Magento Credentials
    $credentials['app1Details'] = array(
        'magento_user_name' => 'admin',
        'magento_password' => 'Estuate!23',
        'magento_context' => 'http://sample-env-1.d33pahahbj.us-west-2.elasticbeanstalk.com/'
    );
    //hubspot Credentials
    $credentials['app2Details']=array(
        'app' => 'My Test App',
        'client_id' => '11dad90b-0204-4bab-8170-8b50c5154046',
        'client_secret' => '3745a9a9-1594-4990-b427-2d404b3ac80c',
        'hubapi_url' => 'https://api.hubapi.com'
    );
    $magentoURL = $credentials['app1Details']['magento_context'];
    $magentoID = $credentials['app1Details']['magento_user_name'];
    $magentoPassword = $credentials['app1Details']['magento_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];

    $hubspotUrl = $credentials['app2Details']['hubapi_url'];
    // $hubspotKey = $credentials['app2Details']['hub_hapikey'];
    $client_id = $credentials['app2Details']['client_id'];
    $client_secret = $credentials['app2Details']['client_secret'];
    if(!isset($_REQUEST['code']))
    {
        header('location:https://app.hubspot.com/oauth/authorize?client_id='.$client_id.'&scope=contacts%20automation&redirect_uri=https://www.showmeademo.com/DemoHubspot.php/');
    }
    else {
        $auth_code = $_REQUEST['code'];

        // Get HubSpot access token for the First time Access
        $url = 'https://api.hubapi.com/oauth/v1/token'; //$hubspotUrl.'/contacts/v1/lists/all/contacts/all?hapikey='.$hubspotKey;
        $http_headres = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );

        $postfields = array(
            'grant_type' => 'authorization_code',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => 'https://www.showmeademo.com/DemoHubspot.php/',
            'code' => $auth_code
        );
        $contactFieldsJSON = http_build_query($postfields);
        //print_r($contactFieldsJSON);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = curl_exec($ch);
        $responseDecode = json_decode($response, true);

        //Access Token Collected For a User
        $hubspot_access_token = $responseDecode['access_token'];
        $refresh_token = $responseDecode['refresh_token'];
        curl_close($ch);

        //........... Get HubSpot access token if already Access Permission Given.......

        /*  $url = 'https://api.hubapi.com/oauth/v1/token';
          $http_headres = array(
              'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
          );

          $postfields = array(
              'grant_type' => 'refresh_token',
              'client_id' => $client_id,
              'client_secret' => $client_secret,
              'redirect_uri' => 'https://www.showmeademo.com/DemoHubspot.php/',
              'refresh_token' => $refresh_token
          );
          $contactFieldsJSON = http_build_query($postfields);
          //print_r($contactFieldsJSON);
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
          $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $response = curl_exec($ch);
          $responseDecode = json_decode($response, true);
          echo $http_code;
          print_r($responseDecode);
          //Access Token Collected For a User
          $hubspot_access_token = $responseDecode['access_token'];
          $refresh_token = $responseDecode['refresh_token'];
          curl_close($ch);  */

    }

    // Get Magento Token
    $magentoURL = $magentoURL . "rest/V1/";

    $total_success = 0;
    $url = $magentoURL . "integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postfields = array('username' => $magentoID,
        'password' => $magentoPassword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {
        curl_close($ch);
        $magentoToken = json_decode($return, TRUE);

    }

    try {

        //............ORDER LIST COLLECTIONG.................


        $orderUrl = $magentoURL . "orders?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=" . $min_date_created . "&searchCriteria[filter_groups][0][filters][0][condition_type]=from&searchCriteria[filter_groups][1][filters][1][field]=created_at&searchCriteria[filter_groups][1][filters][1][value]=" . $max_date_created . "&searchCriteria[filter_groups][1][filters][1][condition_type]=to";
        $http_headres = array(
            "content-Type: application/json",
            "acccept: application/json",
            "authorization: Bearer " . $magentoToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = $orders['total_count'];
            //echo '<pre>';
            //print_r($orders);
            //exit(1);


            if ($count > 0) {

                for ($orderCount = 1; $orderCount <= $count; $orderCount++) {
                    $key = $orderCount - 1;

                    //Customer Detials Collect
                    $customerId = $orders['items'][$key]['customer_id'];
                    $currencyExchangeRate = $orders['items'][$key]['base_to_global_rate'];
                    $discountAmount = $orders['items'][$key]['discount_amount'];
                    //Order id collect
                    $orderId = $orders['items'][$key]['items'][0]['order_id'];
                    $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];

                    $totalProduct = $orders['items'][$key]['total_item_count'] . "<br>";

                    //Status of Order./................
                    $currentOrderStatus = $orders['items'][$key]['status'];



                        //Customer  Detail collect..........

                        $customerDetailUrl = $magentoURL . "customers/" . $customerId;
                        $http_headres = array(
                            "content-Type: application/json",
                            "acccept: application/json",
                            "authorization: Bearer " . $magentoToken
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $customerDetailUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);


                        //End of Collecting of Customer Detailas
                        if ($http_code === 200) {

                            $customerDetails = json_decode($return, TRUE);
                            $firstName = $customerDetails['firstname'];
                            $lastName = $customerDetails['lastname'];
                            $accountName = $firstName . ' ' . $lastName;
                            $email = $customerDetails['email'];
                            $phone = $customerDetails['addresses'][0]['telephone'];
                            //print_r($firstName);

                            //Customer Billing Address Collecting
                            $orderUrl = $magentoURL . "customers/" . $customerId . "/billingAddress";
                            $http_headres = array(
                                "content-Type: application/json",
                                "acccept: application/json",
                                "authorization: Bearer " . $magentoToken
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $orderUrl);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);


                            $customerBillingAddress = json_decode($return, TRUE);


                            //End of Billing Address Collecting


                            if (!empty($customerBillingAddress)) {
                                $addressDetails = json_decode($return, TRUE);


                                $mailingStreet = $addressDetails['street'][0];
                                $otherStreet = $addressDetails['street'][0];
                                $mailingCity = $addressDetails['city'];
                                $otherCity = $addressDetails['city'];
                                $mailingState = $addressDetails['region']['region'];
                                $otherState = $addressDetails['region']['region'];
                                $mailingZip = $addressDetails['postcode'];
                                $otherZip = $addressDetails['postcode'];
                                $mailingCountry = $addressDetails['country_id'];
                                $otherCountry = $addressDetails['country_id'];
                            } else {
                                $mailingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $otherStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $mailingCity = $orders['items'][$key]['billing_address']['city'];
                                $otherCity = $orders['items'][$key]['billing_address']['city'];
                                $mailingState = $orders['items'][$key]['billing_address']['region'];
                                $otherState = $orders['items'][$key]['billing_address']['region'];
                                $mailingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $otherZip = $orders['items'][$key]['billing_address']['postcode'];
                                $mailingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $otherCountry = $orders['items'][$key]['billing_address']['country_id'];
                            }

                            $flag = FALSE;
                            try {

                                // Create Contact for this Order ..................

                                $contactInsertUrl = $hubspotUrl . '/contacts/v1/contact/createOrUpdate/email/' . $email;
                                $http_headres = array(
                                    "Authorization: Bearer $hubspot_access_token"
                                    //"Content-Type: application/json"
                                );

                                $contactData = array(
                                    'properties' => array(
                                        array(
                                            'property' => 'email',
                                            'value' => $email
                                        ),
                                        array(
                                            'property' => 'firstname',
                                            'value' => $firstName
                                        ),
                                        array(
                                            'property' => 'lastname',
                                            'value' => $lastName
                                        ),
                                        array(
                                            'property' => 'phone',
                                            'value' => $phone
                                        ),
                                        array(
                                            'property' => 'address',
                                            'value' => $mailingStreet
                                        ),
                                        array(
                                            'property' => 'city',
                                            'value' => $mailingCity
                                        ),
                                        array(
                                            'property' => 'state',
                                            'value' => $mailingState
                                        ),
                                        array(
                                            'property' => 'zip',
                                            'value' => $mailingZip
                                        ),
                                        array(
                                            'property' => 'country',
                                            'value' => $mailingCountry
                                        ),
                                        array(
                                            'property' => 'jobtitle',
                                            'value' => 'New Contact'
                                        ),
                                        array(
                                            'property' => 'lifecyclestage',
                                            'value' => 'customer'
                                        )
                                    )
                                );
                                $contactFieldsJSON = json_encode($contactData);

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $contactInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $response = curl_exec($ch);
                                $responseDecode = json_decode($response, true);
                                echo '<pre>';
                                print_r($responseDecode);
                                curl_close($ch);


                            } catch (Exception $e) {
                                $msg = $e;
                                $flag = FALSE;
                            }


                        }

                }
            }


        }

    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

}