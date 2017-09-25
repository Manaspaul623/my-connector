<?php

/* including database related files */
include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
include "/var/www/html/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

/* setting current date and time */
date_default_timezone_set('UTC');
$currentSyncHrs = date("G");
$currentSyncMinLimit = date("i");

// Forced sync settings for 00 hrs.
$currentSyncHrs = 13;
$currentSyncMinLimit = 0;

//$currentSyncMins = date("i");
$currentSyncMins = (int) $currentSyncMinLimit + 30;

//error_log("Test Cron Running", 1, "support@aquaapi.com");



/* getting user with current schedule time */
$cond = " AND sync_hrs='$currentSyncHrs' AND sync_mins<$currentSyncMins AND sync_mins>=$currentSyncMinLimit ORDER BY sync_mins DESC LIMIT 1";
$syncInvDetails = fetch($syncTimeSlot, $cond);

if ($syncInvDetails) {
    $syncInvId = $syncInvDetails[0]['id'];
    $syncTime = $syncInvDetails[0]['sync_time'];

    $cond = " AND sync_time_slot_id=$syncInvId LIMIT 1";
    $resVal = fetch($syncTimeSchedule, $cond);

    if (count($resVal) > 0) {
        foreach ($resVal as $key => $value) {
            $userId = $value['user_id'];

            //$cond = " AND `user_id`='$userId'";
            $cond = " AND `user_id`='$userId' AND current_status='SYNCINTERVAL'";
            $userDetails = fetch($userTable, $cond);
            $syncTimeTable = $syncTime;
           // $nextWeek = time();
            //$max_date_created = date("Y-m-d") . "T" . $syncTimeTable;
            $lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "+1 days"));
            $max_date_created = $lastDay . "T" . $syncTimeTable; // ISO 8601 format
            //print_r("Max Date: ".$max_date_created);
            
            //$min_time = $syncTimeTable - 2; 
            $lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "-8 days"));
            $min_date_created =$lastDay . "T" . $syncTimeTable;
            //print_r("Min Date: ".$min_date_created);

            $typeOfCRM = $userDetails[0]['crm_type'];
            $userID = $userDetails[0]['user_id'];
            $contextValue = $userDetails[0]['context'];
            
            // error_log("Cron Job Running for ".$userId, 1, "support@aquaapi.com");
            // Modified: Debashish
            // $accountType = 'new';
            $accountType = $userDetails[0]['account_type'];

            $userEmail = $userDetails[0]['user_email'];
            $bigCommerceAccessToken = $userDetails[0]['access_token'];

            $bigCommerceCredentials = array(
                'crm_type' => $typeOfCRM,
                'account_type' => $accountType,
                'user_id' => $userID,
                'contextValue' => $contextValue,
                'userEmail' => $userEmail,
                'bigCommerceAccessToken' => $bigCommerceAccessToken,
                'min_date_created' => $min_date_created,
                'max_date_created' => $max_date_created,
                'sfdcCredentialsDetails' => array(),
                'zohoCredentialsdetails' => array()
            );


            /* update user status */
            $updateAccountStatus = array(
                'account_type' => 'new'
            );
            $cond = " AND user_id=$userID";
            $resVal = update($userTable, $updateAccountStatus, $cond);

            if ($typeOfCRM === 'SFDC') {
                $sfdc_user_name = $userDetails[0]['sfdc_user_name'];
                $sfdc_password = $userDetails[0]['sfdc_password'];
                $sfdc_security_password = $userDetails[0]['sfdc_security_password'];
                $sfdcCredentials = array(
                    'sfdc_user_name' => $sfdc_user_name,
                    'sfdc_password' => $sfdc_password,
                    'sfdc_security_password' => $sfdc_security_password
                );
                $bigCommerceCredentials['sfdcCredentialsDetails'] = $sfdcCredentials;
                sfdcCRMInsertDataVer2($bigCommerceCredentials);
            } else if ($typeOfCRM === 'ZOHO') {
                $zoho_auth_id = $userDetails[0]['zoho_auth_id'];
                $zohoCredentials = array(
                    'zoho_auth_id' => $zoho_auth_id
                );
                $bigCommerceCredentials['zohoCredentialsdetails'] = $zohoCredentials;
                //j_index_bigcomm_joho($bigCommerceCredentials);
                zohoCRMInsertDataVer2($bigCommerceCredentials);
			} else if ($typeOfCRM === 'VTIGER') {
                $sfdc_user_name = $userDetails[0]['sfdc_user_name'];
                $sfdc_password = $userDetails[0]['sfdc_password'];
                $sfdc_security_password = $userDetails[0]['sfdc_security_password'];
                $sfdcCredentials = array(
                    'sfdc_user_name' => $sfdc_user_name,
                    'sfdc_password' => $sfdc_password,
                    'sfdc_security_password' => $sfdc_security_password
                );
                $bigCommerceCredentials['sfdcCredentialsDetails'] = $sfdcCredentials;
                vTigerCRMInsertData($bigCommerceCredentials);
            } else if ($typeOfCRM === 'ZOHO_INVENTORY') {
                $zoho_auth_id = $userDetails[0]['zoho_auth_id'];
                $zohoInventoryCredentials = array(
                    'zoho_auth_id' => $userDetails[0]['zoho_auth_id'],
                    'zoho_org_id' => $userDetails[0]['sfdc_security_password']
                );
                $bigCommerceCredentials['zohoCredentialsdetails'] = $zohoInventoryCredentials;
                zohoInventoryInsertData($bigCommerceCredentials);
            }
       }
   }
}

/* sync from bigcommerce to sfdc */

function addErrors($msg, $bigCommerceCredentials) {
    $userName = $bigCommerceCredentials['userEmail'];
    $email = $bigCommerceCredentials['userEmail'];
    $subject = "AquaAPI - BigCommerce Cloud Connector : Sync error";
    $syncDateTime = date("m/d/Y H:i:s");

    $message = <<<EOF
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Untitled Document</title>
    </head>
    <body>
        <div style="padding:0 15px;font-family: Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:13px;">
            <div style="background:#384556;">
                <div style="background:#1c2c3c; text-align:center; padding:10px 5px;">
                    <img src="http://aquaapi.com/images/logo-sml.png">
                </div>
                <div style="background:#384556; padding:15px 25px; color:#fff;">
                    <p style=" margin-bottom:40px;">Hi <a style="color:#fff; font-weight:bold;" href="mailto:$userName">$userName</a></p>
                    <p style="margin-bottom:25px;">Here is the integration report run on $syncDateTime for <a style="color:#fff;">AquaAPI - BigCommerce Cloud Connector</a></p>
                    <h2 style="font-size:18px;">AquaAPI - BigCommerce Cloud Connector : Sync error</h2>
                    <ul>
                        <li style="margin-bottom:10px;">$msg.</li>
                    </ul>
                </div>
                <div style="background:#1c2c3c; text-align:center; padding:25px 10px;">
                    <a target="_blank" style="text-decoration:none; color:#fff; margin-right:15px;" href="http://aquaapi.com/">Home</a>
                    <a target="_blank" style="text-decoration:none; color:#fff;" href="http://aquaapi.com/contact.html">Contact Us</a>
                </div>
            </div>
        </div>
    </body>
</html>
EOF;
    error_log("Message: ".$message, 1, "support@aquaapi.com");

    $from = "BigCommerce CRM connector (support@aquaapi.com)";
    // $to = $userName;
    $to = "info@aquaapi.com";
    $headers = "From: $from\r\n";
    $headers .= "Cc:info@aquaapi.com \r\n";
    $headers .= "Content-type: text/html\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    // $flag = mail($to, $subject, $message, $headers);

    $userid = $bigCommerceCredentials['user_id'];
    $crmType = $bigCommerceCredentials['crm_type'];
    $errMsg = $msg;
    $status = 'YES';
    $userDataArrError = array(
        'user_id' => $userid,
        'crm_type' => $crmType,
        'transaction_head' => NULL,
        'error_message' => $errMsg,
        'status' => $status,
        'added_on' => time()
    );
    include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
    $insertErrorData = insert($zohoSyncErrorDetails, $userDataArrError);
    if ($insertErrorData > 0) {
        $flag = TRUE;
    } else {
        $flag = FALSE;
    }
    return true;
}

function sendSyncReport($bigCommerceCredentials) {
    $userName = $bigCommerceCredentials['userEmail'];
    $email = $bigCommerceCredentials['userEmail'];
    $syncDateTime = date("m/d/Y H:i:s");
    $orderCount = $bigCommerceCredentials['counterOrders'];
    $productCount = $bigCommerceCredentials['counterProducts'];
    $customerCount = $bigCommerceCredentials['counterAccounts'];
    if ($orderCount > 0) {
        $orderText = $orderCount . " order(s) retrieved";
    } else {
        $orderText = "No relevant changes to retrieve.";
    }
    if ($productCount > 0) {
        $productText = $productCount . " product(s) retrieved";
    } else {
        $productText = "No relevant changes to retrieve.";
    }
    if ($customerCount > 0) {
        $customerText = $customerCount . " customer(s) retrieved";
    } else {
        $customerText = "No relevant changes to retrieve.";
    }
    $subject = "AquaAPI - BigCommerce Cloud Connector : Sync report";

    $message = <<<EOF
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Untitled Document</title>
    </head>
    <body>
        <div style="padding:0 15px;font-family: Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:13px;">
            <div style="background:#384556;">
                <div style="background:#1c2c3c; text-align:center; padding:10px 5px;">
                    <img src="http://aquaapi.com/images/logo-sml.png">
                </div>
                <div style="background:#384556; padding:15px 25px; color:#fff;">
                    <p style=" margin-bottom:40px;">Hi <a style="color:#fff; font-weight:bold;" href="mailto:$userName">$userName</a></p>
                    <p style="margin-bottom:25px;">Here is the integration report run on $syncDateTime for <a style="color:#fff;">AquaAPI - BigCommerce Cloud Connector</a></p>
                    <h2 style="font-size:18px;">AquaAPI - BigCommerce Cloud Connector : Sync report</h2>
                    <ul>
                        <li style="margin-bottom:10px;">Orders: $orderText</li>
                        <li style="margin-bottom:10px;">Products: $productText</li>
                        <li style="margin-bottom:10px;">Customers: $customerText</li>
                    </ul>
                </div>
                <div style="background:#1c2c3c; text-align:center; padding:25px 10px;">
                    <a target="_blank" style="text-decoration:none; color:#fff; margin-right:15px;" href="http://aquaapi.com/">Home</a>
                    <a target="_blank" style="text-decoration:none; color:#fff;" href="http://aquaapi.com/contact.html">Contact Us</a>
                </div>
            </div>
        </div>
    </body>
</html>
EOF;

    $from = "BigCommerce Cloud Connector (support@aquaapi.com)";
    // $to = $userName;
    $to = "info@aquaapi.com";
    $headers = "From: $from\r\n";
    $headers .= "Cc:info@aquaapi.com \r\n";
    $headers .= "Content-type: text/html\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    $flag = mail($to, $subject, $message, $headers);
    die();
}

//
//BigCommerce to Zoho DataBase //
//
function j_index_bigcomm_joho($bigCommerceCredentials) {
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $totalTransaction = 0;
    //
    $zohoAuthtoken = '';
    $u_access_token = '';
    //
    $uID = $bigCommerceCredentials['user_id'];
    $crmType = $bigCommerceCredentials['crm_type'];
    $contextVal = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $zohoAuthtoken = $bigCommerceCredentials['zohoCredentialsdetails']['zoho_auth_id'];
    $u_access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $accountType = $bigCommerceCredentials['account_type'];

    try {
        //Bigcommerce base url
        $bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
        //Call account Details form Bigcommerce 
        $flagCustomer = j_countAccounts($zohoAuthtoken, $u_access_token, $bigcommURL, $min_date_created, $max_date_created, $uID, $crmType, $accountType);
        // $flagCustomer = 0;
        //Call Product Information from Bigcommerce.           
      //   $flagProduct = j_countProducts($zohoAuthtoken, $u_access_token, $bigcommURL, $min_date_created, $max_date_created, $uID, $crmType, $accountType);
        // $flagProduct = 0;

        //Call ORDER Information from Bigcommerce.            
        //$flagOrder = j_countOrders($zohoAuthtoken, $u_access_token, $bigcommURL, $min_date_created, $max_date_created, $uID, $crmType);
        $noCustomerData = $flagCustomer;
        $noProductData = $flagProduct;
        $noOrderData = $flagOrder;
        $status = 'YES';
        $totalTransaction = $noCustomerData + $noProductData + $noOrderData;
        //
        //Connect Database and open the connection.
//        $connection_status = Connectdb();
//        $open_status = Opendb();
        //
        $userDataArrTransaction = array(
            'user_id' => $uID,
            'crm_type' => $crmType,
            'no_customer_data' => $noCustomerData,
            'no_product_data' => $noProductData,
            'no_order_data' => $noOrderData,
            'last_sync_time' => time(),
            'total_transaction' => $totalTransaction,
            'status' => $status,
            'added_on' => time()
        );

        $bigCommerceCredentials['counterOrders'] = $noOrderData;
        $bigCommerceCredentials['counterProducts'] = $noProductData;
        $bigCommerceCredentials['counterAccounts'] = $noCustomerData;

        include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
        $insertData = insert($zohoTransactionDetails, $userDataArrTransaction);        
        //
        sendSyncReport($bigCommerceCredentials);        
        //
//        if ($insertData > 0) {
//            //echo 'Zoho Transaction completed successfully ';
//        } else {
//            //echo 'Zoho Transaction completed failed ';
//        }
    } catch (Exception $ex) {
        //echo 'error :' . $ex;
    }
}

function zohoCRMInsertData($bigCommerceCredentials)
{
	/* including database related files */
	$flagCustomer = 0;
	$flagProduct = 0;
	$flagOrder = 0;
	$totalTransaction = 0;
	$zohoCRMAuthtoken = '';
	$access_token = '';

	//

	$uID = $bigCommerceCredentials['user_id'];
	$crmType = $bigCommerceCredentials['crm_type'];
	$contextVal = $bigCommerceCredentials['contextValue'];
	$min_date_created = $bigCommerceCredentials['min_date_created'];
	$max_date_created = $bigCommerceCredentials['max_date_created'];
	$zohoCRMAuthtoken = $bigCommerceCredentials['zohoCredentialsdetails']['zoho_auth_id'];
	$access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
	$accountType = $bigCommerceCredentials['account_type'];
	try
	{

		// Bigcommerce base url

		$bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
		$retVal = FALSE;
		$cloudApp = 'ZOHO';
		$total_success = 0;
		$url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
		$http_headres = array(
			"Content-Type: application/json",
			"Accept: application/json",
			"X-Auth-Client:APP_AUTH_CLIENT_ID",
			"X-Auth-Token:" . trim($access_token)
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
		$return = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// check if it returns 200, or else return false
		// print_r("HTTP Code ".$http_code);

		if ($http_code === 200)
		{
			curl_close($ch);
			$count_arr = json_decode($return, TRUE);
			$count = $count_arr['count'];
			//print_r("Count: " . $count);
			if ($count > 0)
			{
				$page = ceil($count / MAX_PAGE_SIZE);

				// print_r("Count: " . $count);
				// echo '<br/>';

				for ($i = 1; $i <= $page; $i++)
				{
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

					if ($http_code === 200)
					{
						$orders = json_decode($return, TRUE);

						// Insert everything for the Order including Contact and Products.

						foreach($orders as $key => $value)
						{

							// print_r("Order: " . $orders[$key]['id']);
							// echo '<br/>';

							/* Retrieve Customer Contact for the Order and insert into Zoho Inventory */
							$customer_id = $orders[$key]['customer_id'];
							$customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $customer_url);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
							$return = curl_exec($ch);
							$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

							// print_r($return);
							// echo '<br/>';

							curl_close($ch);
							if ($http_code === 200)
							{
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
								//print_r("HTTP Code: ",$http_code);
								// echo(json_decode($return, TRUE));

								if ($http_code === 200)
								{
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
								}
								else
								{
									$mailingStreet = "NA";
									$otherStreet = "NA";
									$mailingCity = "NA";
									$otherCity = "NA";
									$mailingState = "NA";
									$otherState = "NA";
									$mailingZip = "NA";
									$otherZip = "NA";
									$mailingCountry = "NA";
									$otherCountry = "NA";
								}

								$flag = FALSE;

								try
								{
									$xml = '<?xml version="1.0" encoding="UTF-8"?>
        						<Contacts>
            						<row no="1">
                						<FL val="First Name">' . $firstName . '</FL>
                						<FL val="Last Name">' . $lastName . '</FL>
                						<FL val="Account Name">' . $accountName . '</FL>
                						<FL val="Email">' . $email . '</FL>
                						<FL val="Phone">' . $phone . '</FL>                
                						<FL val="Mailing Street">' . $mailingStreet . '</FL>
                                                                <FL val="Other Street">' . $otherStreet . '</FL>                
                                                                <FL val="Mailing City">' . $mailingCity . '</FL>
                                                                <FL val="Other City">' . $otherCity . '</FL>             
                                                                <FL val="Mailing State">' . $mailingState . '</FL>
                                                                <FL val="Other State">' . $otherState . '</FL>                
                                                                <FL val="Mailing Zip">' . $mailingZip . '</FL>
                                                                <FL val="Other Zip">' . $otherZip . '</FL>                
                                                                <FL val="Mailing Country">' . $mailingCountry . '</FL>
                                                                <FL val="Other Country">' . $otherCountry . '</FL>
            						</row>
       							 </Contacts>';

									// For Insert Records with duplicate checking.

									$url = "https://crm.zoho.com/crm/private/xml/Contacts/insertRecords";
									$query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
									$ch = curl_init();
									/* set url to send post request */
									curl_setopt($ch, CURLOPT_URL, $url);
									/* allow redirects */
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
									/* return a response into a variable */
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									/* times out after 30s */
									curl_setopt($ch, CURLOPT_TIMEOUT, 30);
									/* set POST method */
									curl_setopt($ch, CURLOPT_POST, 1);
									/* add POST fields parameters */
									curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

									// Execute cUrl session

									$response = curl_exec($ch);
									curl_close($ch);
									$res = simplexml_load_string($response);
									$resCode = (string)$res->result->row->success->code;

									// error_log("XML & Response".$error."    ".$response, 1, "support@aquaapi.com");
									// echo $res->axXML();
									// $error['code'] = $resCode;

									if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002'))
									{

										// $flag = TRUE;

										if ($resCode === '2000')
										{
											$flagCustomer++;
										}

										// Contact addition successful. Proceed with addtion of Product and Sales Order
										$subject = 'Order Number ' . $orders[$key]['id'] . ' for ' . $accountName;
										$grandTotal = $orders[$key]['total_inc_tax'];
										$subTotal = $orders[$key]['subtotal_ex_tax'];
										$tax = $orders[$key]['total_tax'];
										$adjustment = $orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'];
										$InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
									<Invoices><row no="1">
                                                                        <FL val="Contact Name">' . $accountName . '</FL> 
                                                                        <FL val="Account Name">' . $accountName . '</FL>
                                                                        <FL val="Subject">' . $subject . '</FL>
									<FL val="Product Details">
									</FL>                     
                                                                        <FL val="Billing Street">' . $mailingStreet . '</FL>
                                                                        <FL val="Shipping Street">' . $otherStreet . '</FL>
                                                                        <FL val="Billing City">' . $mailingCity . '</FL>
                                                                        <FL val="Shipping City">' . $otherCity . '</FL>
                                                                        <FL val="Billing State">' . $mailingState . '</FL>
                                                                        <FL val="Shipping State">' . $otherState . '</FL>
                                                                        <FL val="Billing Zip">' . $mailingZip . '</FL>
                                                                        <FL val="Shipping Zip">' . $otherZip . '</FL>
                                                                        <FL val="Billing Country">' . $mailingCountry . '</FL>
                                                                        <FL val="Shipping Country">' . $otherCountry . '</FL>
									<FL val="Sub Total">'.$subTotal. '</FL>
									<FL val="Tax">'.$tax. '</FL>
									<FL val="Adjustment">'.$adjustment. '</FL>
									<FL val="Grand Total">'.$grandTotal. '</FL>  
                                                                        </row></Invoices>';
										/* Insert Products in Zoho CRM, if not already present */
										$product_url = $orders[$key]['products']['url'];
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $product_url);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
										curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
										$return = curl_exec($ch);
										$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
										curl_close($ch);
										$recordSet = json_decode($return, TRUE);
										$sxeInvoiceXML = new SimpleXMLElement($InvoiceXML);
										/* if multiple sku available */
										if (count($recordSet) > 0)
										{
											$prodNumber = 0;
											foreach($recordSet as $k => $val)
											{
												$m_sku_id = $val['sku'];
												$prodCode = $m_sku_id;
												$m_adjusted_price = $val['price_inc_tax'];
												$prodName = $val['name'];
												$m_quantity = $val['quantity'];
												$prodListPrice = $val['base_price'];
												$prodUnitPrice = $val['price_ex_tax'];
												$total = $val['base_total'];
												$totalAfterDiscount = $val['total_ex_tax'];
												$discount = $total - $totalAfterDiscount;
												$tax = $val['total_tax'];
												$netTotal = $val['total_inc_tax'];
												//print_r("SKU ID: " . $prodCode . " Name: " . $prodName . " Price: " . $prodUnitPrice);
												//echo "\r\n";
												$ProductXML = '<?xml version="1.0" encoding="UTF-8"?>
       											 <Products>
            											<row no="1">
                										<FL val="Product Code">' . $prodCode . '</FL>
                										<FL val="Product Name"><![CDATA[' . urlencode($prodName) . ']]></FL>
                										<FL val="Unit Price">' . $prodUnitPrice . '</FL>
            											</row>
        										</Products>';
												$output = simplexml_load_string($ProductXML);
												//echo $output->asXML();

												// For Insert Records with duplicate checking.

												$url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
												$query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$ProductXML";
												$ch = curl_init();
												/* set url to send post request */
												curl_setopt($ch, CURLOPT_URL, $url);
												/* allow redirects */
												curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
												/* return a response into a variable */
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												/* times out after 30s */
												curl_setopt($ch, CURLOPT_TIMEOUT, 30);
												/* set POST method */
												curl_setopt($ch, CURLOPT_POST, 1);
												/* add POST fields parameters */
												curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

												// Execute cUrl session

												$response = curl_exec($ch);
												curl_close($ch);
												$res = simplexml_load_string($response);
												if ($res->result != FALSE) {
								
												$resCode = (string)$res->result->row->success->code;
												$prodId = $res->result->row->success->details->FL[0];
												}
												else {
												 $resCode = 0;
												}

												// error_log("XML & Response".$error."    ".$response, 1, "support@aquaapi.com");

												//print_r("Product Entry Output:" . $resCode . "Product Code: " . $prodId);
												//echo $res->asXML();
												//echo "</br>";

												// $error['code'] = $resCode;

												if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002'))
												{

													// $flag = TRUE;

													if ($resCode === '2000')
													{
														$flagProduct++;
													}

													$prodNumber++;
													$productDetails = $sxeInvoiceXML->row->FL[3]->addChild("product");
													$productDetails->addAttribute('no', $prodNumber);
													$FLsku = $productDetails->addChild("FL", $prodId);
													$FLsku->addAttribute('val', 'Product Id');
													$FLquantity = $productDetails->addChild("FL", $m_quantity);
													$FLquantity->addAttribute('val', 'Quantity');
													$FLprice = $productDetails->addChild("FL", $prodUnitPrice);
                                                                                                        $FLprice->addAttribute('val', 'Unit Price');
													$FLprice = $productDetails->addChild("FL", $prodListPrice);
													$FLprice->addAttribute('val', 'List Price');
													$FLprice = $productDetails->addChild("FL", $total);
                                                                                                        $FLprice->addAttribute('val', 'Total');
													$FLprice = $productDetails->addChild("FL", $discount);
                                                                                                        $FLprice->addAttribute('val', 'Discount');
													$FLprice = $productDetails->addChild("FL", $totalAfterDiscount);
                                                                                                        $FLprice->addAttribute('val', 'Total after Discount');
													$FLprice = $productDetails->addChild("FL", $tax);
                                                                                                        $FLprice->addAttribute('val', 'Tax');
													$FLprice = $productDetails->addChild("FL", $netTotal);
                                                                                                        $FLprice->addAttribute('val', 'Net Total');
												}
												else
												{
													$resCode = (string)$res->error->code;
													$resMsg = (string)$res->error->message;
													$flag = FALSE;
													$st = 'YES';
													$AccountHead = 'PRODUCT';
													// SaveErrorDetails fails when $prodName has special characters used in MySQL
													SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Product ' . $prodName . ' Error Code: ' . $resCode. ' Cause: '.$resMsg, $st);
												}
											} // for each product

											// echo $sxeInvoiceXML->asXML();

											$InvoiceXML = $sxeInvoiceXML->asXML();

											// error_log($InvoiceXML, 1, "debashishp@gmail.com");

										} // if Product SKUs available.
										/* Insert Invoice in Zoho CRM if not aleady present */
										$url = "https://crm.zoho.com/crm/private/xml/Invoices/insertRecords";
										$query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$InvoiceXML";

										// $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=2&version=4&newFormat=1&xmlData=$InvoiceXML";

										$ch = curl_init();
										/* set url to send post request */
										curl_setopt($ch, CURLOPT_URL, $url);
										/* allow redirects */
										curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
										/* return a response into a variable */
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										/* times out after 30s */
										curl_setopt($ch, CURLOPT_TIMEOUT, 30);
										/* set POST method */
										curl_setopt($ch, CURLOPT_POST, 1);
										/* add POST fields parameters */
										curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

										// Execute cUrl session

										$response = curl_exec($ch);

										// $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

										curl_close($ch);

										// check if it returns 200, or else return false

										$res = simplexml_load_string($response);
										if ($res->result != FALSE){
										$resCode = (string)$res->result->row->success->code;
										}
										else {
										$resCode = 0;
										}
									      //	echo $res->asXML();
										// echo "</br>";
										// $error['code'] = $resCode;

										if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002'))
										{

											// $flag = TRUE;

											if ($resCode === '2000')
											{
												$flagOrder++;
											}
										}
										else
										{
											$resCode = (string)$res->error->code;
											$resMsg = (string)$res->error->message;
											$flag = FALSE;
											$st = 'YES';
											$AccountHead = 'ORDER';
											SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode. ' Reason:'.$resMsg, $st);
										}
									}
									else
									{
										$resCode = (string)$res->error->code;
										$resMsg = (string)$res->error->message;
										$AccountHead = 'CUSTOMER';
										$st = 'YES';
										SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Customer: ' . $accountName . ' Error Code: ' . $resCode. 'Reason:'.$resMsg, $st);
										$flag = FALSE;
									}
								}

								catch(Exception $e)
								{

									// echo '<pre>';
									// print_r($e);

									$msg = $e;
									$flag = FALSE;
								}
							} /// If customer successfully retrieved from BG.
						} // for each order on the page
					}
				} // for each page of orders
			}
		} // if count of orders returned
	} // Try retrieving orders from BigCommerce
	catch(Exception $e)
	{
		$msg = $e;
		$flag = FALSE;
	}

	// Create reports.

	$status = 'YES';
	$totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
	$userDataArrTransaction = array(
		'user_id' => $uID,
		'crm_type' => $crmType,
		'no_customer_data' => $flagCustomer,
		'no_product_data' => $flagProduct,
		'no_order_data' => $flagOrder,
		'last_sync_time' => time() ,
		'total_transaction' => $totalTransaction,
		'status' => $status,
		'added_on' => time()
	);
	$bigCommerceCredentials['counterOrders'] = $flagOrder;
	$bigCommerceCredentials['counterProducts'] = $flagProduct;
	$bigCommerceCredentials['counterAccounts'] = $flagCustomer;
	include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";

	$insertData = insert($zohoTransactionDetails, $userDataArrTransaction);
	sendSyncReport($bigCommerceCredentials);
}

function zohoCRMInsertDataVer2($bigCommerceCredentials)
{
	/* including database related files */
	$flagCustomer = 0;
	$flagProduct = 0;
	$flagOrder = 0;
	$totalTransaction = 0;
	$zohoCRMAuthtoken = '';
	$access_token = '';

	//

	$uID = $bigCommerceCredentials['user_id'];
	$crmType = $bigCommerceCredentials['crm_type'];
	$contextVal = $bigCommerceCredentials['contextValue'];
	$min_date_created = $bigCommerceCredentials['min_date_created'];
	$max_date_created = $bigCommerceCredentials['max_date_created'];
	$zohoCRMAuthtoken = $bigCommerceCredentials['zohoCredentialsdetails']['zoho_auth_id'];
	$access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
	$accountType = $bigCommerceCredentials['account_type'];
	try
	{

		// Bigcommerce base url

		$bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
		$retVal = FALSE;
		$cloudApp = 'ZOHO';
		$total_success = 0;
		$url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
		$http_headres = array(
			"Content-Type: application/json",
			"Accept: application/json",
			"X-Auth-Client:APP_AUTH_CLIENT_ID",
			"X-Auth-Token:" . trim($access_token)
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
		$return = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// check if it returns 200, or else return false
		// print_r("HTTP Code ".$http_code);

		if ($http_code === 200)
		{
			curl_close($ch);
			$count_arr = json_decode($return, TRUE);
			$count = $count_arr['count'];
			//print_r("Count: " . $count);
			if ($count > 0)
			{
				$page = ceil($count / MAX_PAGE_SIZE);

				// print_r("Count: " . $count);
				// echo '<br/>';

				for ($i = 1; $i <= $page; $i++)
				{
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

					if ($http_code === 200)
					{
						$orders = json_decode($return, TRUE);

						// Insert everything for the Order including Contact and Products.

						foreach($orders as $key => $value)
						{

							 print_r("Order: " . $orders[$key]['id']);
							 echo '<br/>';
 							// if ($orders[$key]['id'] === 276){

							/* Retrieve Customer Contact for the Order and insert into Zoho Inventory */
							$customer_id = $orders[$key]['customer_id'];
							//echo ("Order ID: ".$orders[$key]['id']);
							//echo ("Status ID: ".$orders[$key]['status']);
							$currentOrderStatus = $orders[$key]['status'];
							if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined'))
							{
								$salesOrder = 'FALSE';
							}
							else 
							{
								$salesOrder = 'TRUE';

							}
						        //echo ("Sales Order: ".$salesOrder);
							//echo "\r\n";
	
							$customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $customer_url);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
							$return = curl_exec($ch);
							$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
							 echo $http_code;
							 echo '<br/>';
							echo $return;
							//print_r($return);
							// echo '<br/>';

							curl_close($ch);
							if ($http_code === 200)
							{
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
								echo("Address: ");
								echo $return;

								if ($http_code === 200)
								{
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
								}
								else
								{
									$mailingStreet = $orders[$key]['billing_address']['street_1']." ". $orders[$key]['billing_address']['street_2'];
									$otherStreet = $orders[$key]['billing_address']['street_1']." ". $orders[$key]['billing_address']['street_2'];
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
								try
								{
								// Insert Account details
                                                                $xml = '<?xml version="1.0" encoding="UTF-8"?>
                                                        <Accounts>
                                                        <row no="1">
                                                                <FL val="Account Name">' . $accountName . '</FL>
                                                                <FL val="Email">' . $email . '</FL>
                                                                <FL val="Phone">' . $phone . '</FL>  
                                                                <FL val="Billing Street">' . $mailingStreet . '</FL>
                                                                <FL val="Shipping Street">' . $otherStreet . '</FL>                
                                                                <FL val="Billing City">' . $mailingCity . '</FL>
                                                                <FL val="Shipping City">' . $otherCity . '</FL>             
                                                                <FL val="Billing State">' . $mailingState . '</FL>
                                                                <FL val="Shipping State">' . $otherState . '</FL>                
                                                                <FL val="Billing Code">' . $mailingZip . '</FL>
                                                                <FL val="Shipping Code">' . $otherZip . '</FL>                
                                                                <FL val="Billing Country">' . $mailingCountry . '</FL>
                                                                <FL val="Shipping Country">' . $otherCountry . '</FL>
                                                        </row>
                                                         </Accounts>';

                                                                        // For Insert Records with duplicate checking.

                                                                        $url = "https://crm.zoho.com/crm/private/xml/Accounts/insertRecords";
                                                                        $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                                                                        $ch = curl_init();
                                                                        /* set url to send post request */
                                                                        curl_setopt($ch, CURLOPT_URL, $url);
                                                                        /* allow redirects */
                                                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                                                        /* return a response into a variable */
                                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                                        /* times out after 30s */
                                                                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                                                        /* set POST method */
                                                                        curl_setopt($ch, CURLOPT_POST, 1);
                                                                        /* add POST fields parameters */
                                                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                                                        // Execute cUrl session

                                                                        $response = curl_exec($ch);
                                                                        curl_close($ch);
                                                                        print_r("Accounts");
                                                                        echo $response;


									$xml = '<?xml version="1.0" encoding="UTF-8"?>
        						<Contacts>
            						<row no="1">
                						<FL val="First Name">' . $firstName . '</FL>
                						<FL val="Last Name">' . $lastName . '</FL>
                						<FL val="Account Name">' . $accountName . '</FL>
                						<FL val="Email">' . $email . '</FL>
                						<FL val="Phone">' . $phone . '</FL>                
                						<FL val="Mailing Street">' . $mailingStreet . '</FL>
                						<FL val="Other Street">' . $otherStreet . '</FL>                
                						<FL val="Mailing City">' . $mailingCity . '</FL>
                						<FL val="Other City">' . $otherCity . '</FL>             
                						<FL val="Mailing State">' . $mailingState . '</FL>
                						<FL val="Other State">' . $otherState . '</FL>                
                						<FL val="Mailing Zip">' . $mailingZip . '</FL>
                						<FL val="Other Zip">' . $otherZip . '</FL>                
                						<FL val="Mailing Country">' . $mailingCountry . '</FL>
                						<FL val="Other Country">' . $otherCountry . '</FL>
            						</row>
       							 </Contacts>';

									// For Insert Records with duplicate checking.

									$url = "https://crm.zoho.com/crm/private/xml/Contacts/insertRecords";
									$query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
									$ch = curl_init();
									/* set url to send post request */
									curl_setopt($ch, CURLOPT_URL, $url);
									/* allow redirects */
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
									/* return a response into a variable */
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									/* times out after 30s */
									curl_setopt($ch, CURLOPT_TIMEOUT, 30);
									/* set POST method */
									curl_setopt($ch, CURLOPT_POST, 1);
									/* add POST fields parameters */
									curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

									// Execute cUrl session

									$response = curl_exec($ch);
									curl_close($ch);
									$res = simplexml_load_string($response);
									$resCode = (string)$res->result->row->success->code;

									// error_log("XML & Response".$error."    ".$response, 1, "support@aquaapi.com");
									// echo $res->axXML();
									// $error['code'] = $resCode;

									if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002'))
									{

										// $flag = TRUE;

										if ($resCode === '2000')
										{
											$flagCustomer++;
										}

										// Contact addition successful. Proceed with addtion of Product and Sales Order
										$subject = 'Order Number ' . $orders[$key]['id'] . ' for ' . $accountName;
										$grandTotal = $orders[$key]['total_inc_tax'];
										$subTotal = $orders[$key]['subtotal_ex_tax'];
										$tax = $orders[$key]['total_tax'];
										$adjustment = $orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'];
										$orderBillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
										$orderBillingCity = $orders[$key]['billing_address']['city'];
										$orderBillingState = $orders[$key]['billing_address']['state'];
										$orderBillingZip = $orders[$key]['billing_address']['zip'];
										$orderBillingCountry = $orders[$key]['billing_address']['country'] ;
										// retrieve shipping address
                                                                                $orderShippingURL = $orders[$key]['shipping_addresses']['url'];
                                                                                $ch = curl_init();
                                                                                curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
                                                                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                                                                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                                                                $return = curl_exec($ch);
                                                                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                                                                curl_close($ch);
                                                                                $orderShippingAddresses = json_decode($return, TRUE);
										$orderShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
                                                                                $orderShippingCity = $orderShippingAddresses[0]['city'];
                                                                                $orderShippingState = $orderShippingAddresses[0]['state'];
                                                                                $orderShippingZip = $orderShippingAddresses[0]['zip'];
                                                                                $orderShippingCountry = $orderShippingAddresses[0]['country'];
	
										if ($salesOrder === 'TRUE')
										{
										$InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
									<SalesOrders><row no="1">
                                                                        <FL val="Contact Name">' . $accountName . '</FL> 
                                                                        <FL val="Account Name">' . $accountName . '</FL>
                                                                        <FL val="Subject">' . $subject . '</FL>
									<FL val="Product Details">
									</FL>                     
                                                                        <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                                                        <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                                                        <FL val="Billing City">' . $orderBillingCity . '</FL>
                                                                        <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                                                        <FL val="Billing State">' . $orderBillingState . '</FL>
                                                                        <FL val="Shipping State">' . $orderShippingState . '</FL>
                                                                        <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                                                        <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                                                        <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                                                        <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
									<FL val="Sub Total">'.$subTotal. '</FL>
									<FL val="Tax">'.$tax. '</FL>
									<FL val="Adjustment">'.$adjustment. '</FL>
									<FL val="Grand Total">'.$grandTotal. '</FL>  
                                                                        </row></SalesOrders>';
										}
										else 
										{
										$InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
                                                                        <Quotes><row no="1">
                                                                        <FL val="Contact Name">' . $accountName . '</FL> 
                                                                        <FL val="Account Name">' . $accountName . '</FL>
                                                                        <FL val="Subject">' . $subject . '</FL>
                                                                        <FL val="Product Details">
                                                                        </FL>                     
                                                                        <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                                                        <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                                                        <FL val="Billing City">' . $orderBillingCity . '</FL>
                                                                        <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                                                        <FL val="Billing State">' . $orderBillingState . '</FL>
                                                                        <FL val="Shipping State">' . $orderShippingState . '</FL>
                                                                        <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                                                        <FL val="Shipping Code">' . $orderShippingrZip . '</FL>
                                                                        <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                                                        <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
                                                                        <FL val="Sub Total">'.$subTotal. '</FL>
                                                                        <FL val="Tax">'.$tax. '</FL>
                                                                        <FL val="Adjustment">'.$adjustment. '</FL>
                                                                        <FL val="Grand Total">'.$grandTotal. '</FL>  
                                                                        </row></Quotes>';

										}
										/* Insert Products in Zoho CRM, if not already present */
										$product_url = $orders[$key]['products']['url'];
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $product_url);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
										curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
										$return = curl_exec($ch);
										$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
										curl_close($ch);
										$recordSet = json_decode($return, TRUE);
										$sxeInvoiceXML = new SimpleXMLElement($InvoiceXML);
										/* if multiple sku available */
										if (count($recordSet) > 0)
										{
											$prodNumber = 0;
											foreach($recordSet as $k => $val)
											{
												$m_sku_id = $val['sku'];
												$prodCode = $m_sku_id;
												$m_adjusted_price = $val['price_inc_tax'];
												$prodName = $val['name'];
												$m_quantity = $val['quantity'];
												$prodListPrice = $val['base_price'];
												$prodUnitPrice = $val['price_ex_tax'];
												$total = $val['base_total'];
												$totalAfterDiscount = $val['total_ex_tax'];
												$discount = $total - $totalAfterDiscount;
												$tax = $val['total_tax'];
												$netTotal = $val['total_inc_tax'];
												//print_r("SKU ID: " . $prodCode . " Name: " . $prodName . " Price: " . $prodUnitPrice);
												//echo "\r\n";
												$ProductXML = '<?xml version="1.0" encoding="UTF-8"?>
       											 <Products>
            											<row no="1">
                										<FL val="Product Code">' . $prodCode . '</FL>
                										<FL val="Product Name"><![CDATA[' . urlencode($prodName) . ']]></FL>
                										<FL val="Unit Price">' . $prodUnitPrice . '</FL>
            											</row>
        										</Products>';
												$output = simplexml_load_string($ProductXML);
												//echo $output->asXML();

												// For Insert Records with duplicate checking.

												$url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
												$query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$ProductXML";
												$ch = curl_init();
												/* set url to send post request */
												curl_setopt($ch, CURLOPT_URL, $url);
												/* allow redirects */
												curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
												/* return a response into a variable */
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												/* times out after 30s */
												curl_setopt($ch, CURLOPT_TIMEOUT, 30);
												/* set POST method */
												curl_setopt($ch, CURLOPT_POST, 1);
												/* add POST fields parameters */
												curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

												// Execute cUrl session

												$response = curl_exec($ch);
												curl_close($ch);
												$res = simplexml_load_string($response);
												if ($res->result != FALSE) {
								
												$resCode = (string)$res->result->row->success->code;
												$prodId = $res->result->row->success->details->FL[0];
												}
												else {
												 $resCode = 0;
												}

												// error_log("XML & Response".$error."    ".$response, 1, "support@aquaapi.com");

												//print_r("Product Entry Output:" . $resCode . "Product Code: " . $prodId);
												//echo $res->asXML();
												//echo "</br>";

												// $error['code'] = $resCode;

												if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002'))
												{

													// $flag = TRUE;

													if ($resCode === '2000')
													{
														$flagProduct++;
													}
													if ($resCode === '2002') // duplicate product
													{
													   // Update Product
													   $url = "https://crm.zoho.com/crm/private/xml/Products/updateRecords";
													   $query = "authtoken=$zohoCRMAuthtoken&newFormat=1&scope=crmapi&id=$prodId&xmlData=$ProductXML";
														$ch = curl_init();
														/* set url to send post request */
														curl_setopt($ch, CURLOPT_URL, $url);
														/* allow redirects */
														curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
														/* return a response into a variable */
														curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
														/* times out after 30s */
														curl_setopt($ch, CURLOPT_TIMEOUT, 30);
														/* set POST method */
														curl_setopt($ch, CURLOPT_POST, 1);
														/* add POST fields parameters */
														curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

														// Execute cUrl session

														$response = curl_exec($ch);
														curl_close($ch);
														//$res = simplexml_load_string($response);
														echo ("Product update");
														echo $response;
													   
													}



													$prodNumber++;
													$productDetails = $sxeInvoiceXML->row->FL[3]->addChild("product");
													$productDetails->addAttribute('no', $prodNumber);
													$FLsku = $productDetails->addChild("FL", $prodId);
													$FLsku->addAttribute('val', 'Product Id');
													$FLquantity = $productDetails->addChild("FL", $m_quantity);
													$FLquantity->addAttribute('val', 'Quantity');
													$FLprice = $productDetails->addChild("FL", $prodUnitPrice);
                                                                                                        $FLprice->addAttribute('val', 'Unit Price');
													$FLprice = $productDetails->addChild("FL", $prodListPrice);
													$FLprice->addAttribute('val', 'List Price');
													$FLprice = $productDetails->addChild("FL", $total);
                                                                                                        $FLprice->addAttribute('val', 'Total');
													$FLprice = $productDetails->addChild("FL", $discount);
                                                                                                        $FLprice->addAttribute('val', 'Discount');
													$FLprice = $productDetails->addChild("FL", $totalAfterDiscount);
                                                                                                        $FLprice->addAttribute('val', 'Total after Discount');
													$FLprice = $productDetails->addChild("FL", $tax);
                                                                                                        $FLprice->addAttribute('val', 'Tax');
													$FLprice = $productDetails->addChild("FL", $netTotal);
                                                                                                        $FLprice->addAttribute('val', 'Net Total');
												}
												else
												{
													$resCode = (string)$res->error->code;
													$resMsg = (string)$res->error->message;
													$flag = FALSE;
													$st = 'YES';
													$AccountHead = 'PRODUCT';
													// SaveErrorDetails fails when $prodName has special characters used in MySQL
													SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Product ' . $prodName . ' Error Code: ' . $resCode. ' Cause: '.$resMsg, $st);
												}
											} // for each product

											// echo $sxeInvoiceXML->asXML();

											$InvoiceXML = $sxeInvoiceXML->asXML();

											// error_log($InvoiceXML, 1, "debashishp@gmail.com");

										} // if Product SKUs available.
										/* Insert Sales Order or Quote in Zoho CRM if not aleady present */
										if ($salesOrder === 'TRUE')
										{
										$url = "https://crm.zoho.com/crm/private/xml/SalesOrders/insertRecords";
										}
										else 
										{
										$url = "https://crm.zoho.com/crm/private/xml/Quotes/insertRecords";
										}
										$query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$InvoiceXML";

										// $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=2&version=4&newFormat=1&xmlData=$InvoiceXML";

										$ch = curl_init();
										/* set url to send post request */
										curl_setopt($ch, CURLOPT_URL, $url);
										/* allow redirects */
										curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
										/* return a response into a variable */
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										/* times out after 30s */
										curl_setopt($ch, CURLOPT_TIMEOUT, 30);
										/* set POST method */
										curl_setopt($ch, CURLOPT_POST, 1);
										/* add POST fields parameters */
										curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

										// Execute cUrl session

										$response = curl_exec($ch);

										// $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

										curl_close($ch);

										// check if it returns 200, or else return false

										$res = simplexml_load_string($response);
										if ($res->result != FALSE){
										$resCode = (string)$res->result->row->success->code;
										}
										else {
										$resCode = 0;
										}
									      	//echo $res->asXML();
										// echo "</br>";
										// $error['code'] = $resCode;

										if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002'))
										{

											// $flag = TRUE;

											if ($resCode === '2000')
											{
												$flagOrder++;
											}
										}
										else
										{
											$resCode = (string)$res->error->code;
											$resMsg = (string)$res->error->message;
											$flag = FALSE;
											$st = 'YES';
											$AccountHead = 'ORDER';
											SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode. ' Reason:'.$resMsg, $st);
										}
									}
									else
									{
										$resCode = (string)$res->error->code;
										$resMsg = (string)$res->error->message;
										$AccountHead = 'CUSTOMER';
										$st = 'YES';
										SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Customer: ' . $accountName . ' Error Code: ' . $resCode. 'Reason:'.$resMsg, $st);
										$flag = FALSE;
									}
								}

								catch(Exception $e)
								{

									// echo '<pre>';
									// print_r($e);

									$msg = $e;
									$flag = FALSE;
								}
							} /// If customer successfully retrieved from BG.
						// } // Temporary code. To be removed.
					        } // for each order on the page
					}
				} // for each page of orders
			}
		} // if count of orders returned
	} // Try retrieving orders from BigCommerce
	catch(Exception $e)
	{
		$msg = $e;
		$flag = FALSE;
	}

	// Create reports.

	$status = 'YES';
	$totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
	$userDataArrTransaction = array(
		'user_id' => $uID,
		'crm_type' => $crmType,
		'no_customer_data' => $flagCustomer,
		'no_product_data' => $flagProduct,
		'no_order_data' => $flagOrder,
		'last_sync_time' => time() ,
		'total_transaction' => $totalTransaction,
		'status' => $status,
		'added_on' => time()
	);
	$bigCommerceCredentials['counterOrders'] = $flagOrder;
	$bigCommerceCredentials['counterProducts'] = $flagProduct;
	$bigCommerceCredentials['counterAccounts'] = $flagCustomer;
	include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";

	$insertData = insert($zohoTransactionDetails, $userDataArrTransaction);
	sendSyncReport($bigCommerceCredentials);
}

function sfdcCRMInsertDataVer2($bigCommerceCredentials)
{
	include "/var/www/html/bigcommerce-app-management/sfdc-integration/Sfdc.php";

	$flagCustomer = 0;
	$flagProduct = 0;
	$flagOrder = 0;
	$totalTransaction = 0;
	$zohoCRMAuthtoken = '';
	$access_token = '';


	$uID = $bigCommerceCredentials['user_id'];
	$crmType = $bigCommerceCredentials['crm_type'];
	$contextVal = $bigCommerceCredentials['contextValue'];
	$min_date_created = $bigCommerceCredentials['min_date_created'];
	$max_date_created = $bigCommerceCredentials['max_date_created'];
	$access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
	$accountType = $bigCommerceCredentials['account_type'];
	$sfdcCredentials = $bigCommerceCredentials['sfdcCredentialsDetails'];
	$priceBookInfo = Sfdc::getPriceBookDetails($sfdcCredentials);
	$bigCommerceCredentials['stdPriceBookId'] = $priceBookInfo->Id;
	$cloudApp = 'SFDC';
	
	try
	{

		// Bigcommerce base url

		$bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
		$retVal = FALSE;
		$cloudApp = 'ZOHO';
		$total_success = 0;
		$url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
		$http_headres = array(
			"Content-Type: application/json",
			"Accept: application/json",
			"X-Auth-Client:APP_AUTH_CLIENT_ID",
			"X-Auth-Token:" . trim($access_token)
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
		$return = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// check if it returns 200, or else return false

		if ($http_code === 200)
		{
			curl_close($ch);
			$count_arr = json_decode($return, TRUE);
			$count = $count_arr['count'];
			print_r("Count: " . $count);
			if ($count > 0)
			{
				$page = ceil($count / MAX_PAGE_SIZE);

				// print_r("Count: " . $count);
				// echo '<br/>';

				for ($i = 1; $i <= $page; $i++)
				{
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

					if ($http_code === 200)
					{
						$orders = json_decode($return, TRUE);

						// Insert everything for the Order including Contact and Products.

						foreach($orders as $key => $value)
						{
							print_r("Order: " . $orders[$key]['id']);
							echo '<br/>';
							$currentOrderID = $orders[$key]['id'];

							// Check, if this Order already exists in the system.
							// $sfdcQuery = "Select Id FROM Account WHERE AccountNumber = '$customer_id' ";

							$sfdcQuery = "Select Id FROM Order WHERE OrderReferenceNumber = '$currentOrderID' ";
							$queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
							print_r("Order Query ");
							print_r($queryResult->size. "  ");/*
							if ($currentOrderID == '290') {
							$SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        						$SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        						$SfdcPassword = $sfdcCredentials['sfdc_password'];
        						$SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        
        						$mySforceConnection = new SforceEnterpriseClient();
        						$mySforceConnection->createConnection($SfdcWsdl);
        						$mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        						$response = $mySforceConnection->describeSObject("Account");
							print_r($response);
							} */
							//$queryResult->size = 1;
							if ($queryResult->size === 0) // order doesn't exist
							{
								$customer_id = $orders[$key]['customer_id'];

								 echo ("Order ID: ".$orders[$key]['id']);
								 echo ("Status ID: ".$orders[$key]['status']);

								$currentOrderStatus = $orders[$key]['status'];

								// Search for existing Account

								$sfdcQuery = "Select Id, Name FROM Account WHERE AccountNumber = '$customer_id' ";
								$queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
								print_r("Account Query");
								print_r($queryResult);
								echo '<br>';
								$resCode = 0;
								if ($queryResult->size === 0) // no existing Account
								{
									$customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $customer_url);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
									$return = curl_exec($ch);
									$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

									// echo $http_code;
									// echo '<br/>';
									// echo $return;
									// print_r($return);
									// echo '<br/>';

									curl_close($ch);
									if ($http_code === 200)
									{
										$customerDetails = json_decode($return, TRUE);
										$firstName = $customerDetails['first_name'];
										$lastName = $customerDetails['last_name'];
										$accountName = $firstName . ' ' . $lastName;
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

										// echo("Address: ");
										// echo $return;

										$accounts = array();
										$accounts[0] = new stdclass();
										if ($http_code === 200)
										{
											$addressDetails = json_decode($return, TRUE);
											$accounts[0]->BillingCity = $addressDetails[0]['city'];
											$accounts[0]->BillingCountry = $addressDetails[0]['country'];
											$accounts[0]->BillingPostalCode = $addressDetails[0]['zip'];
											$accounts[0]->BillingState = $addressDetails[0]['state'];
											$accounts[0]->BillingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
											$accounts[0]->ShippingCity = $addressDetails[0]['city'];
											$accounts[0]->ShippingCountry = $addressDetails[0]['country'];
											$accounts[0]->ShippingPostalCode = $addressDetails[0]['zip'];
											$accounts[0]->ShippingState = $addressDetails[0]['state'];
											$accounts[0]->ShippingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
										}
										else
										{
											$accounts[0]->BillingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
											$accounts[0]->ShippingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
											$accounts[0]->BillingCity = $orders[$key]['billing_address']['city'];
											$accounts[0]->ShippingCity = $orders[$key]['billing_address']['city'];
											$accounts[0]->BillingState = $orders[$key]['billing_address']['state'];
											$accounts[0]->ShippingState = $orders[$key]['billing_address']['state'];
											$accounts[0]->BillingPostalCode = $orders[$key]['billing_address']['zip'];
											$accounts[0]->ShippingPostalCode = $orders[$key]['billing_address']['zip'];
											$accounts[0]->BillingCountry = $orders[$key]['billing_address']['country'];
											$accounts[0]->ShippingCountry = $orders[$key]['billing_address']['country'];
										}

										/* Insert new Account in sfdc */
										$accounts[0]->Name = $accountName;
										$accounts[0]->AccountNumber = $customer_id;

										// $accounts[0]->Email = $customerDetails['email'];
										print_r("Creating Account");
										$accounts[0]->Phone = $customerDetails['phone'];
										$oldAccount = new stdclass();
										$accountId = $createAccountRes->id;
										$resCode = $createAccountRes->success;
										
									        //print_r($createAccountRes);	


										if ($resCode == 0) // error in inserting Account
										{       echo '<br>';
											$createAccountError = $createAccountRes->errors[0]->duplicateResult->matchResults[0]->matchRecords[0]->record->Id;
											print_r($createAccountError);
											// check, if duplicate exists.

											if ($createAccountRes->errors[0]->statusCode === 'DUPLICATES_DETECTED') {
											    //update records
											$oldAccount->Id = $createAccountError;
                                                                                	$accounts[0]->Id = $oldAccount->Id;
											$SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
                                                        				$SfdcUsername = $sfdcCredentials['sfdc_user_name'];
                                                        				$SfdcPassword = $sfdcCredentials['sfdc_password'];
                                                        				$SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
                                                        				$mySforceConnection = new SforceEnterpriseClient();
                                                        				$mySforceConnection->createConnection($SfdcWsdl);
                                                        				$mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
                                                        				$response = $mySforceConnection->update(array($oldAccount,$accounts[0]), 'Account');
                                                                                	echo '<br>';
                                                                                	print_r("Update");
                                                                                	print_r($response);
                                                                                	echo '<br>';
											}
											$st = 'YES';
											$AccountHead = 'ACCOUNT';
											// SaveErrorDetails fails when $prodName has special characters used in MySQL
											SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Account ' . $customer_id, $st);
											
										}
										else {
											$resCode = 1;
										}

										// Create Contacts

										$contacts = array();
										$contacts[0] = new stdclass();
										$contacts[0]->LastName = $lastName;
										$contacts[0]->FirstName = $firstName;
										$contacts[0]->AccountId = $accountId;
										$contacts[0]->Email = $customerDetails['email'];
										$contacts[0]->Phone = $customerDetails['phone'];
									        if ($http_code === 200) {	
										$contacts[0]->MailingCity = $addressDetails[0]['city'];
										$contacts[0]->MailingCountry = $addressDetails[0]['country'];
										$contacts[0]->MailingPostalCode = $addressDetails[0]['zip'];
										$contacts[0]->MailingState = $addressDetails[0]['state'];
										$contacts[0]->MailingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
										$contacts[0]->OtherCity = $addressDetails[0]['city'];
										$contacts[0]->OtherCountry = $addressDetails[0]['country'];
										$contacts[0]->OtherPostalCode = $addressDetails[0]['zip'];
										$contacts[0]->OtherState = $addressDetails[0]['state'];
										$contacts[0]->OtherStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
										} else {
										$contacts[0]->MailingCity = $orders[$key]['billing_address']['city'];
                                                                                $contacts[0]->MailingCountry = $orders[$key]['billing_address']['country'];
                                                                                $contacts[0]->MailingPostalCode = $orders[$key]['billing_address']['zip'];
                                                                                $contacts[0]->MailingState = $orders[$key]['billing_address']['state'];
                                                                                $contacts[0]->MailingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                                                                $contacts[0]->OtherCity = $orders[$key]['billing_address']['city'];
                                                                                $contacts[0]->OtherCountry = $orders[$key]['billing_address']['country'];
                                                                                $contacts[0]->OtherPostalCode = $orders[$key]['billing_address']['zip'];
                                                                                $contacts[0]->OtherState = $orders[$key]['billing_address']['state'];
                                                                                $contacts[0]->OtherStreet =  $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2']; 
										}
										//$createAccountRes = Sfdc::createSfdcContact($sfdcCredentials, $contacts);
									        echo '<br>';
										//print_r($createAccountRes);
									}
								}
								else

								// Account already exists

								{
									$accountId = $queryResult->records[0]->Id;
									$accountName = $queryResult->records[0]->Name;
									$resCode = 2; // account exists
								}
								if ($resCode > 0)
								{

									// $flag = TRUE;

									if ($resCode === 1)
									{
										$flagCustomer++;
									}

									// Contact addition successful. Proceed with addtion of Product and Sales Order

									/* Insert Sales Order  */
									$sfdcOrders = array();
									$sfdcOrders[0] = new stdclass();
									$subject = 'Order Number ' . $orders[$key]['id'] . ' for ' . $accountName;
									$grandTotal = $orders[$key]['total_inc_tax'];
									$subTotal = $orders[$key]['subtotal_ex_tax'];
									$tax = $orders[$key]['total_tax'];
									$adjustment = $orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'];
									$sfdcOrders[0]->BillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
									$sfdcOrders[0]->BillingCity = $orders[$key]['billing_address']['city'];
									$sfdcOrders[0]->BillingState = $orders[$key]['billing_address']['state'];
									$sfdcOrders[0]->BillingPostalCode = $orders[$key]['billing_address']['zip'];
									$sfdcOrders[0]->BillingCountry = $orders[$key]['billing_address']['country'];

									// retrieve shipping address

									$orderShippingURL = $orders[$key]['shipping_addresses']['url'];
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
									$return = curl_exec($ch);
									$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
									curl_close($ch);
									$orderShippingAddresses = json_decode($return, TRUE);
									$sfdcOrders[0]->ShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
									$sfdcOrders[0]->ShippingCity = $orderShippingAddresses[0]['city'];
									$sfdcOrders[0]->ShippingState = $orderShippingAddresses[0]['state'];
									$sfdcOrders[0]->ShippingPostalCode = $orderShippingAddresses[0]['zip'];
									$sfdcOrders[0]->ShippingCountry = $orderShippingAddresses[0]['country'];
									$accounts[0]->AccountId = $accountId;
									$sfdcOrders[0]->AccountId = $accountId;
									$sfdcOrders[0]->Description = $subject;
									$sfdcOrders[0]->Name = $subject;
									$salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders[$key]['date_created']);
									$sfdcOrders[0]->EffectiveDate = $salesOrderDate->format('Y-m-d');
									$sfdcOrders[0]->OrderReferenceNumber = $orders[$key]['id'];
									$sfdcOrders[0]->Pricebook2Id = $bigCommerceCredentials['stdPriceBookId'];
									$currentOrderStatus = $orders[$key]['status'];
									if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined'))
									{

										// $sfdcOrders[0]->StatusCode = 'Draft';

										$sfdcOrders[0]->Status = 'Draft';
									}
									else
									{

										// $sfdcOrders[0]->Status = 'Activated';
										// $sfdcOrders[0]->StatusCode = 'Draft';

										$sfdcOrders[0]->Status = 'Draft';
									}

									// $Orders[0]->Pricebook2Id = $s_priceBookId;

									print_r($sfdcOrders[0]);
									$orderDetails = Sfdc::createOrder($sfdcCredentials, $sfdcOrders);
									if ($orderDetails->success === TRUE)
									{
										$flagOrder++;
									}
									else // error in inserting Order
									{
										$st = 'YES';
										$AccountHead = 'ORDER';
										// SaveErrorDetails fails when $prodName has special characters used in MySQL
										SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Order ' . $orders[$key]['id'], $st);	
									}
									//print_r("Order Inserted");
									//print_r($orderDetails);
									/* Insert Products in Zoho CRM, if not already present */
									$product_url = $orders[$key]['products']['url'];
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $product_url);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
									$return = curl_exec($ch);
									$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
									curl_close($ch);
									$recordSet = json_decode($return, TRUE);
									/* if multiple sku available */
									if (count($recordSet) > 0)
									{
										$prodNumber = 0;
										foreach($recordSet as $k => $val)
										{
											$prodCode = $val['sku'];
											$sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode = '$prodCode' ";
											$queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
											//print_r($queryResult);
											//print_r("K: " . $k);
											if ($queryResult->size != 0) // Product exists
											{
												$productPricebookEntryId = $queryResult->records[0]->Id;
											}
											else
											{
												/* insert into products */
												$products = array();
												$products[0] = new stdclass();
												$products[0]->Name = $val['name'];;
												$products[0]->IsActive = TRUE;
												$products[0]->ProductCode = $val['sku'];
												$products[0]->Description = $val['name']; // Check description
												$productRes = Sfdc::createSfdcProducts($sfdcCredentials, $products);
												if ($productRes->success === TRUE)
												{
													$flagProduct++;
												}
												else // error in inserting Product
												{
													$st = 'YES';
													$AccountHead = 'PRODUCT';
													// SaveErrorDetails fails when $prodName has special characters used in MySQL
													SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Product ' . $val['sku'], $st);	
												}

												$insertedProductId = $productRes->id;
												$priceEntry = array();
												$priceEntry[0] = new stdclass();
												$priceEntry[0]->Pricebook2Id = $bigCommerceCredentials['stdPriceBookId'];
												$priceEntry[0]->IsActive = TRUE;
												$priceEntry[0]->Product2Id = $insertedProductId;
												$priceEntry[0]->UnitPrice = $val['base_total'];
												$priceEntry[0]->UseStandardPrice = FALSE;
												//print_r($priceEntry[0]);
												$addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);
												$productPricebookEntryId = $addedId->id;
												//print_r($addedId);
												//print_r("Added to Price book");
											}

											// Add Order Item

											$orderItem = array();
											$orderItem[0] = new stdclass();
											$orderItem[0]->PricebookEntryId = $productPricebookEntryId;
											$orderItem[0]->OrderId = $orderDetails->id;
											$orderItem[0]->Quantity = $val['quantity'];
											$orderItem[0]->UnitPrice = $val['base_total'];
											//print_r($orderItem[0]);
											$orderItemRes = Sfdc::createOrderItem($sfdcCredentials, $orderItem);
											//print_r($orderItemRes);
										} // for each product
									} // if Product SKUs available.
								}
								else
								{
									$AccountHead = 'CUSTOMER';
									$st = 'YES';
									SaveErrorDetails($uID, $cloudApp, $AccountHead, 'Error adding Customer: ' . $customer_id . ' Error Code: ' , $st);
									$flag = FALSE;
								}
							} /// If customer successfully retrieved from BG.
						}
					}
				} // for each page of orders
			}
		} // if count of orders returned
	} // Try retrieving orders from BigCommerce
	catch(Exception $e)
	{
		$msg = $e;
		$flag = FALSE;
	}

	// Create reports.

	$status = 'YES';
	$totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
	$userDataArrTransaction = array(
		'user_id' => $uID,
		'crm_type' => $crmType,
		'no_customer_data' => $flagCustomer,
		'no_product_data' => $flagProduct,
		'no_order_data' => $flagOrder,
		'last_sync_time' => time() ,
		'total_transaction' => $totalTransaction,
		'status' => $status,
		'added_on' => time()
	);
	$bigCommerceCredentials['counterOrders'] = $flagOrder;
	$bigCommerceCredentials['counterProducts'] = $flagProduct;
	$bigCommerceCredentials['counterAccounts'] = $flagCustomer;
	include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";

	$insertData = insert($zohoTransactionDetails, $userDataArrTransaction);
	//sendSyncReport($bigCommerceCredentials);
}


function vTigerCRMInsertData($bigCommerceCredentials)
{
	$flagCustomer = 0;
	$flagProduct = 0;
	$flagOrder = 0;
	$totalTransaction = 0;
	$vtigerCRMAuthtoken = '';
	$access_token = '';
	$uID = $bigCommerceCredentials['user_id'];
	$crmType = $bigCommerceCredentials['crm_type'];
	$contextVal = $bigCommerceCredentials['contextValue'];
	$min_date_created = $bigCommerceCredentials['min_date_created'];
	$max_date_created = $bigCommerceCredentials['max_date_created'];
	$access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
	$accountType = $bigCommerceCredentials['account_type'];
	$vTigerUserName = $bigCommerceCredentials['sfdcCredentialsDetails']['sfdc_user_name'];
	$vTigerAccessKey = $bigCommerceCredentials['sfdcCredentialsDetails']['sfdc_security_password'];
	$vTigerEndpoint = $bigCommerceCredentials['sfdcCredentialsDetails']['sfdc_password'];

	// Get vTiger access token

	$url = $vTigerEndpoint . "/webservice.php?operation=getchallenge&username=" . $vTigerUserName;
	$http_headres = array(
		"Content-Type: application/json",
		"Accept: application/json",
		"cache-control : no-cache"
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
	$return = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code === 200)
	{
		curl_close($ch);
		$vTigerAuth = json_decode($return, TRUE);
		$vTigerAuthToken = $vTigerAuth['result']['token'];
	}

	$url = $vTigerEndpoint . "/webservice.php";
	$generatedKey = md5($vTigerAuthToken . $vTigerAccessKey);
	$postfields = array(
		'operation' => 'login',
		'username' => $vTigerUserName,
		'accessKey' => $generatedKey
	);

	// $postfieldsstring = json_encode($postfields);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	$return = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($http_code === 200)
	{
		curl_close($ch);
		$vTigerLogin = json_decode($return, TRUE);
		$vTigerSessionID = $vTigerLogin['result']['sessionName'];
		$vTigerUserID = $vTigerLogin['result']['userId'];
	}

	try
	{

		// Bigcommerce base url

		$bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
		$retVal = FALSE;

		// $cloudApp = 'vTigerCRM';

		$total_success = 0;
		$url = $bigcommURL . "/v2/customers/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
		$http_headres = array(
			"Content-Type: application/json",
			"Accept: application/json",
			"X-Auth-Client:APP_AUTH_CLIENT_ID",
			"X-Auth-Token:" . trim($access_token)
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
		$return = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// check if it returns 200, or else return false
		// print_r("HTTP Code ".$http_code);

		if ($http_code === 200)
		{
			curl_close($ch);
			$count_arr = json_decode($return, TRUE);
			$count = $count_arr['count'];
			print_r("Count: " . $count);
			if ($count > 0)
			{
				$page = ceil($count / MAX_PAGE_SIZE);
				for ($i = 1; $i <= $page; $i++)
				{
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

					if ($http_code === 200)
					{
						$orders = json_decode($return, TRUE);

						// Insert everything for the Order including Contact and Products.

						foreach($orders as $key => $value)
						{
							$customer_id = $orders[$key]['customer_id'];

							// echo ("Order ID: ".$orders[$key]['id']);
							// echo ("Status ID: ".$orders[$key]['status']);

							$currentOrderStatus = $orders[$key]['status'];
							if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined'))
							{
								$salesOrder = 'FALSE';
							}
							else
							{
								$salesOrder = 'TRUE';
							}



							$customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $customer_url);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
							$return = curl_exec($ch);
							$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
							
							// print_r($return);
							// echo '<br/>';

							curl_close($ch);
							if ($http_code === 200)
							{
								$customerDetails = json_decode($return, TRUE);
								$firstName = $customerDetails['first_name'];
								$lastName = $customerDetails['last_name'];
								$accountName = $firstName . ' ' . $lastName;
								$email = $customerDetails['email'];
								$phone = $customerDetails['phone'];

								// print_r($firstName);

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
								
								if ($http_code === 200)
								{
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
								}
								else
								{
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
								try
								{

									// Insert Account details..................

									$url = $vTigerEndpoint . "/webservice.php";
									$accountFields = array(
										'accountname' => $accountName,
										'assigned_user_id' => $vTigerUserID,
										'phone' => $phone,
										'email1' => $email,
										'bill_street' => $mailingStreet,
										'bill_city' => $mailingCity,
										'bill_state' => $mailingState,
										'bill_country' => $mailingCountry,
										'bill_code' => $mailingZip,
										'ship_street' => $otherStreet,
										'ship_city' => $otherCity,
										'ship_state' => $otherState,
										'ship_country' => $otherCountry,
										'ship_code' => $otherZip
									);
									$accountFieldsJSON = json_encode($accountFields);

									// var_dump($accountFieldsJSON);

									$moduleName = 'Accounts';
									$postfields = array(
										"sessionName" => $vTigerSessionID,
										"operation" => 'create',
										"element" => $accountFieldsJSON,
										"elementType" => $moduleName
									);
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $url);
									curl_setopt($ch, CURLOPT_POST, 1);
									curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

									// $return = curl_exec($ch);
									// Execute cUrl session

									$response = curl_exec($ch);
									curl_close($ch);

									

									// End of  Insert Account details..................
									// Collection Account Id From Accounts..............

									$accountIdQuery = "select id from Accounts where accountname='$accountName';";

									// urlencode to as its sent over http.

									$queryParam = urlencode($accountIdQuery);
									$accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";
									$getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;

									// var_dump($getAccountUrl);

									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
									curl_setopt($ch, CURLOPT_POST, 0);
									curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
									$response = curl_exec($ch);
									curl_close($ch);
									$collectedResponseId = json_decode($response, true);

									// Account Id

									$collectedAccountId = $collectedResponseId['result'][0]['id'];

									// End ofCollection Account Id From Accounts..............
									// Create Contact for this account..................

									$contactData = array(
										'firstname' => $firstName,
										'lastname' => $lastName,
										'assigned_user_id' => $vTigerUserID,
										'contacttype' => 'Primary',
										'account_id' => $collectedAccountId,
										'email' => $email,
										'mobile' => $phone,
										'website' => 'www.aquaapi.com',
										'mailingstreet' => $mailingStreet,
										'otherstreet' => $otherStreet,
										'mailingcity' => $mailingCity,
										'othercity' => $otherCity,
										'mailingstate' => $mailingState,
										'otherstate' => $otherState,
										'mailingzip' => $mailingZip,
										'otherzip' => $otherZip,
										'mailingcountry' => $mailingCountry,
										'othercountry' => $otherCountry
									); //lastName,userId And contacttype Mandetory
									$contactFieldsJSON = json_encode($contactData); //Encoding to json formate for communicating with server

									// var_dump($contactFieldsJSON);

									$moduleName = 'Contacts';
									$contactParams = array(
										"sessionName" => $vTigerSessionID,
										"operation" => 'create',
										"element" => $contactFieldsJSON,
										"elementType" => $moduleName
									);
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $url);
									curl_setopt($ch, CURLOPT_POST, 1);
									curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $contactParams);

									// curl_setopt($ch, CURLOPT_HEADER, 0);

									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

									// $return = curl_exec($ch);

									$response = curl_exec($ch);
									$collectedResponseContactId = json_decode($response, true);

									// Account Id

									$collectedContactId = $collectedResponseContactId['result']['id'];
									curl_close($ch);

									// End of Create Contact for this account..................
									// Contact addition successful. Proceed with addtion of Product and Sales Order

									$subject = 'Order Number ' . $orders[$key]['id'] . ' for ' . $accountName;
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
									$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
									curl_close($ch);
									$orderShippingAddresses = json_decode($return, TRUE);
									$orderShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
									$orderShippingCity = $orderShippingAddresses[0]['city'];
									$orderShippingState = $orderShippingAddresses[0]['state'];
									$orderShippingZip = $orderShippingAddresses[0]['zip'];
									$orderShippingCountry = $orderShippingAddresses[0]['country'];
									/* Insert Products in vTiger CRM, if not already present */
									$product_url = $orders[$key]['products']['url'];
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $product_url);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
									$return = curl_exec($ch);
									$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
									curl_close($ch);
									$recordSet = json_decode($return, TRUE);
									$lineitem = [];
									$items = [];
									/* if multiple sku available */
									if (count($recordSet) > 0)
									{
										$prodNumber = 0;
										foreach($recordSet as $k => $val)
										{
											$m_sku_id = $val['sku'];
											$prodCode = $m_sku_id;
											$m_adjusted_price = $val['price_inc_tax'];
											$prodName = $val['name'];
											$m_quantity = $val['quantity'];
											$prodListPrice = $val['base_price'];
											$prodUnitPrice = $val['price_ex_tax'];
											$total = $val['base_total'];
											$totalAfterDiscount = $val['total_ex_tax'];
											$discount = $total - $totalAfterDiscount;
											$tax = $val['total_tax'];
											$netTotal = $val['total_inc_tax'];

											// Product exist or Not checking

											$productExistQuery = "select count(*) from Products where productcode='$prodCode';";

											// urlencode to as its sent over http.

											$queryProductParam = urlencode($productExistQuery);
											$accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";
											$getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
											curl_setopt($ch, CURLOPT_POST, 0);
											curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
											curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
											curl_setopt($ch, CURLOPT_HEADER, 0);
											curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
											$response = curl_exec($ch);
											curl_close($ch);
											$collectedCount = json_decode($response, true);

											// Account Id

											$collectedCountNo = $collectedCount['result'][0]['count'];

											// For Insert Records with duplicate checking.

											if ($collectedCountNo == 0)
											{

												// INSERT VTIGER PRODUCT

												$url = $vTigerEndpoint . "/webservice.php";
												$productData = array(
													'productcode' => $prodCode,
													'productname' => $prodName,
													"productid" => $m_sku_id,
													"comment" => 'comment',
													"qty_per_unit" => $m_quantity,
													"list_price" => $prodListPrice,
													"unit_price" => $prodUnitPrice,
													"isclosed" => 0,
													"currency1" => 0,
													"currency_id" => "21x1",
													"hdnTaxType" => "individual",
													"taxtype" => "individual",
													"hdnProductId" => "53",
													"description" => "Product Description 1",
													"currency_id" => "21x1",
													"assigned_user_id" => "19x1",
													"totalProductCount" => 2
												);
												$productFieldsJSON = json_encode($productData);
												$moduleName = 'Products';
												print_r($productFieldsJSON);
												$productfields = array(
													"operation" => 'create',
													"sessionName" => $vTigerSessionID,
													"element" => $productFieldsJSON,
													"elementType" => $moduleName
												);

												// Execute cUrl session

												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $url);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
												curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $productfields);
												curl_setopt($ch, CURLOPT_HEADER, 0);
												curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
												$response = curl_exec($ch);
												curl_close($ch);

												// Collected Quote Id

												$collectedResponseId = json_decode($response, true);
												$collectedProductId = $collectedResponseId['result']['id'];
												$collectedProductBasePrice = $collectedResponseId['result']['unit_price'];
												$collectedProductQuantity = $collectedResponseId['result']['qty_per_unit'];

												// var_dump($response);
												// echo "<br />QUOTES ID : ".$collectedQuoteId."<br />";
												// var_dump($response);
											}
											else
											{

												// Collection Product Id From Products..............

												$productIdQuery = "select * from Products where productcode='$prodCode';";

												// urlencode to as its sent over http.

												$queryProductParam = urlencode($productIdQuery);
												$accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";
												$getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;

												// var_dump($getAccountUrl);

												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
												curl_setopt($ch, CURLOPT_POST, 0);
												curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
												curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
												curl_setopt($ch, CURLOPT_HEADER, 0);
												curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
												$response = curl_exec($ch);
												curl_close($ch);
												$collectedResponseId = json_decode($response, true);

												// Account Id

												$collectedProductId = $collectedResponseId['result'][0]['id'];
												$collectedProductBasePrice = $collectedResponseId['result'][0]['unit_price'];
												$collectedProductQuantity = $collectedResponseId['result'][0]['qty_per_unit'];

												// End of Collection Product Id From Products..............

												/*Product Detials Update
												$productUpdateData = array(
												'productid'=>$collectedProductId,
												'productname'=>$prodName,
												"unit_price"=>$prodUnitPrice
												);
												$productFieldsJSON = json_encode($productUpdateData);

												// print_r($productFieldsJSON);

												$productfields = array("operation"=>'update', "sessionName"=>$vTigerSessionID,
												"element"=>$productFieldsJSON);

												// Execute cUrl session

												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $url);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
												curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $productfields);
												curl_setopt($ch, CURLOPT_HEADER, 0);
												curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
												$response = curl_exec($ch);
												curl_close($ch);

												// Collected Quote Id

												$collectedResponseId = json_decode($response, true);
												$collectedProductId = $collectedResponseId['result']['id'];
												$collectedProductBasePrice = $collectedResponseId['result']['unit_price'];
												$collectedProductQuantity=$collectedResponseId['result']['qty_per_unit'];
												var_dump($response);

												// echo "<br />QUOTES ID : ".$collectedQuoteId."<br />";
												// ............End of Product Detials Update 			 */

											}

											$lineitem[$prodNumber] = ['productid' => $collectedProductId, 'listprice' => $collectedProductBasePrice, 'quantity' => $collectedProductQuantity, 'discount_amount' => $discount, 'tax1' => $tax, 'netprice' => $netTotal];
											$prodNumber++;
										} // for each product
									} // if Product SKUs available.
									/*Retrieve Product Detials  From Products..............
									$accountParams = "sessionName=$vTigerSessionID&operation=retrieve&id=$collectedProductId";
									$getAccountUrl = $vTigerEndpoint."/webservice.php?".$accountParams;

									// var_dump($getAccountUrl);

									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
									curl_setopt($ch, CURLOPT_POST, 0);
									curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
									$response = curl_exec($ch);
									curl_close($ch);
									print_r("<br />PRODUCT RETRIEVE : ");
									echo $response.'<br />';

									// End of Retrieve Product Detials  From Products..............  */
									// Retrieve All List Available From vTiger..............

									$listTypeParams = "sessionName=$vTigerSessionID&operation=listtypes";
									$getListTypeUrl = $vTigerEndpoint . "/webservice.php?" . $listTypeParams;

									// var_dump($getAccountUrl);

									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $getListTypeUrl);
									curl_setopt($ch, CURLOPT_POST, 0);
									curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
									$response = curl_exec($ch);
									curl_close($ch);
									$encode = json_decode($response, TRUE);
									$a = $encode['result']['types'];

									// Sales Order Exist or not in This Module Finding

									$salesOrderExist = array_search("SalesOrder", $a);

									// echo count($encode['result']['types']);
									// End of Retrieve All List Available From vTiger..............

									if ($salesOrder === 'TRUE')
									{

										// Start of Sales Order ..............
										// $lineitem = array('productid'=>$collectedProductId,'listprice'=>$collectedProductBasePrice,'quantity'=>1);

										if ($salesOrderExist >= 0)
										{
											$salesOrderData = array(
												'subject' => $subject,
												'sostatus' => 'Created',
												'account_id' => $collectedAccountId,

												// 'quote_id'=>$collectedQuoteId,

												'bill_street' => $orderBillingStreet,
												'bill_city' => $orderBillingCity,
												'bill_code' => $orderBillingZip,
												'bill_country' => $orderBillingCountry,
												'bill_state' => $orderBillingState,
												'assigned_user_id' => $vTigerUserID,
												'contact_id' => $collectedContactId,
												'invoicestatus' => 'Created',
												'adjustment' => $adjustment,
												'exciseduty' => $tax,
												'subtotal' => $subTotal,
												'total' => $grandTotal,
												'ship_city' => $orderShippingCity,
												'ship_street' => $orderShippingStreet,
												'ship_code' => $orderShippingZip,
												'ship_country' => $orderShippingCountry,
												'ship_state' => $orderShippingState,
												'invoicestatus' => 'AutoCreated',
												'status' => 'Created',
												'LineItems' => $lineitem,
												'currency_id' => '21x1',
												'hdntaxtype' => 'group',
												'conversion_rate' => '1',
												'hdndiscountamount' => '2',
												'hdnGrandTotal' => '123',
												'hdnSubTotal' => '123',
											);

											// Sales Order Fields View
											// var_dump($salesOrderData);

											$salesOrderFieldsJSON = json_encode($salesOrderData);
											$moduleName = 'SalesOrder';

											// print_r("<br />SALES ORDER FIELDS: ");
											// print_r($salesOrderFieldsJSON);

											$salesOrderfields = array(
												"operation" => 'create',
												"sessionName" => $vTigerSessionID,
												"element" => $salesOrderFieldsJSON,
												"elementType" => $moduleName
											);

											// Execute cUrl session

											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, $url);
											curl_setopt($ch, CURLOPT_POST, 1);
											curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
											curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
											curl_setopt($ch, CURLOPT_POSTFIELDS, $salesOrderfields);
											curl_setopt($ch, CURLOPT_HEADER, 0);
											curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
											$response = curl_exec($ch);
											curl_close($ch);

											// var_dump($response);

											// End of Sales Orders.................

										}
									}
									else
									{
										$productQuote = array(
											"subject" => $subject,
											'account_id' => $collectedAccountId,
											"quotestage" => 'created',
											"contact_id" => $collectedContactId,
											'bill_street' => $orderBillingStreet,
											'bill_city' => $orderBillingCity,
											'bill_code' => $orderBillingZip,
											'bill_country' => $orderBillingCountry,
											'bill_state' => $orderBillingState,
											'ship_city' => $orderShippingCity,
											'ship_street' => $orderShippingStreet,
											'ship_code' => $orderShippingZip,
											'ship_country' => $orderShippingCountry,
											'ship_state' => $orderShippingState,
											'assigned_user_id' => $vTigerUserID,
											"productid" => $collectedProductId,
											"LineItems" => $lineitem
										);
										$productQuoteFieldsJSON = json_encode($productQuote);
										$moduleName = 'Quotes';
										$productQuotefields = array(
											"operation" => 'create',
											"sessionName" => $vTigerSessionID,
											"element" => $productQuoteFieldsJSON,
											"elementType" => $moduleName
										);

										// Execute cUrl session

										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, $url);
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $productQuotefields);
										curl_setopt($ch, CURLOPT_HEADER, 0);
										curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
										$response = curl_exec($ch);
										curl_close($ch);

										// var_dump($response);



										// Collected Quote Id

										$collectedResponseId = json_decode($response, true);
										$collectedQuoteId = $collectedResponseId['result']['id'];

										// End of Quotes Insert .................

									}

									exit(1);
								}

								catch(Exception $e)
								{

									// echo '<pre>';
									// print_r($e);

									$msg = $e;
									$flag = FALSE;
								}
							} /// If customer successfully retrieved from BG.

							// } // Temporary code. To be removed.

						} // for each order on the page
					}
				} // for each page of orders
			}
		} // if count of orders returned
	} // Try retrieving orders from BigCommerce
	catch(Exception $e)
	{
		$msg = $e;
		$flag = FALSE;
	}

	// Create reports.

	$status = 'YES';
	$totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
	$userDataArrTransaction = array(
		'user_id' => $uID,
		'crm_type' => $crmType,
		'no_customer_data' => $flagCustomer,
		'no_product_data' => $flagProduct,
		'no_order_data' => $flagOrder,
		'last_sync_time' => time() ,
		'total_transaction' => $totalTransaction,
		'status' => $status,
		'added_on' => time()
	);
	$bigCommerceCredentials['counterOrders'] = $flagOrder;
	$bigCommerceCredentials['counterProducts'] = $flagProduct;
	$bigCommerceCredentials['counterAccounts'] = $flagCustomer;
	include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";

	$insertData = insert($zohoTransactionDetails, $userDataArrTransaction);
	sendSyncReport($bigCommerceCredentials);
}

function zohoInventoryInsertData($bigCommerceCredentials) {
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $totalTransaction = 0;
    //
    $zohoAuthtoken = '';
    $u_access_token = '';
    //
    $uID = $bigCommerceCredentials['user_id'];
    $crmType = $bigCommerceCredentials['crm_type'];
    $contextVal = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $zohoInventoryAuthtoken = $bigCommerceCredentials['zohoCredentialsdetails']['zoho_auth_id'];
    $zohoInventoryOrgID = $bigCommerceCredentials['zohoCredentialsdetails']['zoho_org_id'];
    $u_access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $accountType = $bigCommerceCredentials['account_type'];
    error_log("Inserting Data", 1, "support@aquaapi.com");

    try {
        //Bigcommerce base url
        $bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
        //Call account Details form Bigcommerce 
        $createOrderReport = zohoInventory_CreateSalesOrder($uID, $u_access_token, $contextVal, $zohoInventoryAuthtoken, $zohoInventoryOrgID, $min_date_created, $max_date_created);
        //
        $noCustomerData = $createOrderReport['Accounts'];
        $noProductData = $createOrderReport['Products'];
        $noOrderData = $createOrderReport['Orders'];
        $status = 'YES';
        $totalTransaction = $noCustomerData + $noProductData + $noOrderData;
         
        $userDataArrTransaction = array(
            'user_id' => $uID,
            'crm_type' => $crmType,
            'no_customer_data' => $noCustomerData,
            'no_product_data' => $noProductData,
            'no_order_data' => $noOrderData,
            'last_sync_time' => time(),
            'total_transaction' => $totalTransaction,
            'status' => $status,
            'added_on' => time()
        );

        $bigCommerceCredentials['counterOrders'] = $noOrderData;
        $bigCommerceCredentials['counterProducts'] = $noProductData;
        $bigCommerceCredentials['counterAccounts'] = $noCustomerData;

        include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
        $insertData = insert($zohoTransactionDetails, $userDataArrTransaction);
        //
        sendSyncReport($bigCommerceCredentials);
        //
//        if ($insertData > 0) {
//            //echo 'Zoho Transaction completed successfully ';
//        } else {
//            //echo 'Zoho Transaction completed failed ';
//        }
    } catch (Exception $ex) {
        //echo 'error :' . $ex;
    }
}

function zohoInventory_CreateSalesOrder($userID, $bigCommerceAccessToken, $bigCommerceStoreHash, $zohoInventoryAuthToken, $zohoInventoryOrgID, $min_date_created_a, $max_date_created_a)
{
	$retVal = FALSE;
	$countAccount = 0;
	$countProduct = 0;
	$countOrder = 0;
	$cloudApp = 'ZOHO_INVENTORY';
	$min_date_created = $min_date_created_a;
	$max_date_created = $max_date_created_a;


	$bigcommurl = "https://api.bigcommerce.com/" . "" . $bigCommerceStoreHash . "" . "/v2/";
	$access_token = $bigCommerceAccessToken;
	$total_success = 0;
	$url = $bigcommurl . "orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
	$http_headres = array(
		"Content-Type: application/json",
		"Accept: application/json",
		"X-Auth-Client:APP_AUTH_CLIENT_ID",
		"X-Auth-Token:" . trim($access_token)
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
	$return = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// check if it returns 200, or else return false

	if ($http_code === 200)
	{
		curl_close($ch);
		$count_arr = json_decode($return, TRUE);
		$count = $count_arr['count'];
		error_log("Number of Orders: ".$count, 1, "support@aquaapi.com");
		if ($count > 0)
		{
			$page = ceil($count / MAX_PAGE_SIZE);
			//print_r("Count: " . $count);
			// echo '<br/>';
			for ($i = 1; $i <= $page; $i++)
			{
				$url = $bigcommurl . "orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
				$return = curl_exec($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				// check if any orders exist for the page

				if ($http_code === 200)
				{
					$orders = json_decode($return, TRUE);

					// Insert Order in Zoho Inventory as Sales Order
					foreach($orders as $key => $value)
					{
						 print_r("Order: " . $orders[$key]['id']);
						 echo '<br/>';
						/* Retrieve Customer Contact for the Order and insert into Zoho Inventory */
						$customer_id = $orders[$key]['customer_id'];
						$customer_url = $bigcommurl . "customers/$customer_id.json";
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $customer_url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
						$return = curl_exec($ch);
						$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						 print_r($return);
						 echo '<br/>';
						if ($http_code === 200)
						{
							$customerDetails = json_decode($return, TRUE);
							error_log("Customer retrieved", 1, "support@aquaapi.com");
							curl_close($ch);
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

						// check if it returns 200, or else return false

						// _r("Addresses:");
						// print_r($return);
							$addressDetails = json_decode($return, TRUE);
						//else
                                                //{      
						//	error_log("Error retrieving customer ", 1, "support@aquaapi.com"); 
                                                //        $error = curl_error($ch);
						//	curl_close($ch);
                                                //}

						// print_r($addressDetails[0]);
						/* Create Customer as Contact in Zoho Inventory */
						$contactDetails = json_encode(array(
							"contact_name" => $customerDetails['first_name'] . " " . $customerDetails['last_name'],
							"company_name" => $customerDetails['company'],
							"contact_type" => "customer",
							"billing_address" => array(
								"address" => $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'],
								"city" => $addressDetails[0]['city'],
								"state" => $addressDetails[0]['state'],
								"zip" => $addressDetails[0]['zip'],
								"country" => $addressDetails[0]['country'],
								"fax" => ""
							) ,
							"shipping_address" => array(
								"address" => $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'],
								"city" => $addressDetails[0]['city'],
								"state" => $addressDetails[0]['state'],
								"zip" => $addressDetails[0]['zip'],
								"country" => $addressDetails[0]['country'],
								"fax" => ""
							) ,
							"contact_persons" => array(
								array(
									"first_name" => $customerDetails['first_name'],
									"last_name" => $customerDetails['last_name'],
									"email" => $customerDetails['email'],
									"phone" => $customerDetails['phone'],
								)
							)
						));
						$customerName = $customerDetails['first_name'] . " " . $customerDetails['last_name'];
						$url = "https://inventory.zoho.com/api/v1/contacts";
						$query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $contactDetails;
						$ch = curl_init();
						/* set url to send post request */
						curl_setopt($ch, CURLOPT_URL, $url);
						/* allow redirects */
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
						/* return a response into a variable */
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						/* times out after 30s */
						curl_setopt($ch, CURLOPT_TIMEOUT, 30);
						/* set POST method */
						curl_setopt($ch, CURLOPT_POST, 1);
						/* add POST fields parameters */
						curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

						// Execute cUrl session

						$response = curl_exec($ch);
						$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
						// print_r("Response item Entry: " . $response);
						// print_r("HTTP Code: " . $http_code);
						// echo '<br/>';

						// If Contact addition is successful
						if ($http_code === 201)
						{
						    curl_close($ch);
							$flag = 1;
							$msg = json_decode($response, TRUE);
							$retVal = $msg['code'];
							$customerID = $msg['contact']['contact_id'];
							$countAccount++;

							// Contact addition successful. Proceed with addtion of Product and Sales Order

							/* Insert Products in Zoho Inventory, if not already present */
							$product_url = $orders[$key]['products']['url'];
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $product_url);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
							$return = curl_exec($ch);
							$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

							// check if it returns 200, or else return false

							// print_r("Products: ");
							// print_r(json_decode($return));
							// echo '<br/>';
							curl_close($ch);
							if ($http_code === 200)
							{
								$orderProductDetails = json_decode($return, TRUE);
								for ($i = 0; $i < $orders[$key]['items_total']; $i++)
								{
									/* Insert each product into zoho Inventory */
									$itemDetails = json_encode(array(
										"item_type" => "sales",
									    "name" => $orderProductDetails[$i]['name'],
										"rate" => $orderProductDetails[$i]['base_total'],
										"sku" => $orderProductDetails[$i]['sku'],
									));
									/* Copy Product details ordered for inserting with Sales Order */
									$orderItems[$i]['name'] = $orderProductDetails[$i]['name'];
									$orderItems[$i]['description'] = $orderProductDetails[$i]['name'];
									$orderItems[$i]['rate'] = $orderProductDetails[$i]['price_ex_tax'];
									$orderItems[$i]['quantity'] = $orderProductDetails[$i]['quantity'];
									$url = "https://inventory.zoho.com/api/v1/items";
									$query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $itemDetails;
									$ch = curl_init();
									/* set url to send post request */
									curl_setopt($ch, CURLOPT_URL, $url);
									/* allow redirects */
									curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
									/* return a response into a variable */
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									/* times out after 30s */
									curl_setopt($ch, CURLOPT_TIMEOUT, 30);
									/* set POST method */
									curl_setopt($ch, CURLOPT_POST, 1);
									/* add POST fields parameters */
									curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

									// Execute cUrl session

									$response = curl_exec($ch);
									$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

									// curl_close($ch);

									// print_r("Response item Entry 2: " . $response);
									// print_r("HTTP Code: " . $http_code);
									// echo '<br/>';

									// check if it returns 200, or else return false
									if ($http_code === 201)
									{
										curl_close($ch);
										$flag = 1;
										$countProduct++;
									}
									else // Product couldn't be added
									{
                                        					$error = json_decode($response, TRUE);                                       
										// print_r("Product addition Error: " . $error['message']);
										// echo '<br/>';
										// Error is not because of duplicate record
										if ($error['code'] != 1001)
										{
											// Insert Error Data in Database
											$AccountHead = 'PRODUCT';
											$st = 'YES';							
											$flagErr = SaveErrorDetails($userID, $cloudApp, $AccountHead, 'Error adding Product for Order: ' . $orders[$key]['id'] . ':' . $error['message'], $st);
										}
										curl_close($ch);
									}
								}
							}

							/* Insert Sales Order into Zoho Inventory */
							$salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders[$key]['date_created']);
							$salesOrderDetails = json_encode(array(
								"salesorder_number" => $orders[$key]['id'],
								"date" => $salesOrderDate->format('Y-m-d') ,
								"shipment_date" => "",
								"delivery_method" => "None",
								"discount" => 0,
								"discount_type" => "entity_level",
								"customer_id" => $customerID,
								"status" => "draft",
								"line_items" => $orderItems
							));
							$url = "https://inventory.zoho.com/api/v1/salesorders?ignore_auto_number_generation=TRUE";
							$query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $salesOrderDetails;
							$ch = curl_init();
							/* set url to send post request */
							curl_setopt($ch, CURLOPT_URL, $url);
							/* allow redirects */
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
							/* return a response into a variable */
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							/* times out after 30s */
							curl_setopt($ch, CURLOPT_TIMEOUT, 30);
							/* set POST method */
							curl_setopt($ch, CURLOPT_POST, 1);
							/* add POST fields parameters */
							curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

							$response = curl_exec($ch);
							$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
							// print_r("Order Created: " . $response);
							// print_r("HTTP Code: " . $http_code);
							$zohoSalesOrderDetails = json_decode($response, TRUE);
							curl_close($ch);
							// Check, if Order has been suceessfully created
							if ($http_code === 201)
							{	
								$countOrder++;
								// print_r("Created Order: " . $zohoSalesOrderDetails['message']);
								// print_r("Created Order: " . $zohoSalesOrderDetails['salesorder']['salesorder_id']);

                                // Change the order status to Confirmed from Draft
								$url = "https://inventory.zoho.com/api/v1/salesorders/" . $zohoSalesOrderDetails['salesorder']['salesorder_id'] . "/status/confirmed";
								$query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken;
								$ch = curl_init();
								/* set url to send post request */
								curl_setopt($ch, CURLOPT_URL, $url);
								/* allow redirects */
								curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
								/* return a response into a variable */
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								/* times out after 30s */
								curl_setopt($ch, CURLOPT_TIMEOUT, 30);
								/* set POST method */
								curl_setopt($ch, CURLOPT_POST, 1);
								/* add POST fields parameters */
								curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

								// Execute cUrl session

							    $response = curl_exec($ch);
								$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
								// print_r("Response: " . $response);
								// print_r("HTTP Code: " . $http_code);
								curl_close($ch);
							}
							else /* Order addition unsuccessful */
							{
								$error = json_decode($response, TRUE);                                       
								// print_r("Order addition Error: " . $error['message']. " ".$error['code']);
							    // echo '<br/>';
								// Insert Error Data in Database
								$AccountHead = 'ORDER';
								$st = 'YES';							
								$flagErr = SaveErrorDetails($userID, $cloudApp, $AccountHead, 'Error adding order: ' . $orders[$key]['id'] . ':' . $error['message'], $st);
							}						
						}
						else /* If Contact addition is not successful */
						{
							$error = json_decode($response, TRUE);                                       
							// Error is not because of duplicate record
							if ($error['code'] != 3062)
							{
								// Insert Error Data in Database
								$AccountHead = 'CUSTOMER';
								$st = 'YES';							
								$flagErr = SaveErrorDetails($userID, $cloudApp, $AccountHead, 'Error adding Customer for Order: ' . $orders[$key]['id'] . ':' . $error['message'], $st);
							}
							curl_close($ch);
						}
						}
						else
                                                {
							// No contact for order retrieved.
                                                        error_log("Error retrieving customer ", 1, "support@aquaapi.com");
                                                        $error = curl_error($ch);
                                                        curl_close($ch);
                                                }
					}
				} // if order returned
			} // for each order page
		} // if new orders exist
		else
		{
			$error = curl_error($ch);
			curl_close($ch);
		}
        /* Send Report and insert into Database */
        $createOrderReport['Accounts'] = $countAccount;
        $createOrderReport['Products'] = $countProduct;
        $createOrderReport['Orders'] = $countOrder;
	return $createOrderReport;
	}
}


//Bigcommerce Customer address details
function j_countAccounts($zohoAuthtoken, $access_token1, $bigcommURL, $min_date_created1, $max_date_created1, $userid, $crmTypeAcc, $accountTypeC) {
    //  
    $total_success = 0;
    $min_date_created = $min_date_created1;
    $max_date_created = $max_date_created1;
    $bigcommurl = $bigcommURL . "/v2/";
    $access_token = $access_token1;
    error_log("Account pull for Account Type ".$accountTypeC, 1, "support@aquaapi.com");
 
    if ($accountTypeC === 'new') {
        $url = $bigcommurl . "customers/count.json";
    } else {
        $url = $bigcommurl . "customers/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    }
    //$url = $bigcommurl . "customers/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    //$url = $bigcommurl . "customers/count.json";
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "X-Auth-Client:APP_AUTH_CLIENT_ID",
        "X-Auth-Token:" . trim($access_token)
    );
    //
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //check if it returns 200, or else return false        
    if ($http_code === 200) {
        curl_close($ch);
        $count_arr = json_decode($return, TRUE);
        $count = $count_arr['count'];
        error_log("Count ".$count, 1, "support@aquaapi.com");
        if ($count > 0) {
            $limit = 50;
            $page = ceil($count / $limit);
            $total_success = j_createAccount($page, $limit, $bigcommURL, $zohoAuthtoken, $access_token, $min_date_created1, $max_date_created1, $userid, $crmTypeAcc, $accountTypeC);
        }
    } else {
        $error = curl_error($ch);
        //addErrors($error, $bigCommerceCredentials);
    }
    return $total_success;
}

//Create Contact
function j_createAccount($page, $limit, $bigcommURL1, $zohoAuthtoken1, $access_token1, $min_date_created_a, $max_date_created_a, $userid, $crmTypeCAcc, $accountTypeCC) {
    //    
    $retVal = FALSE;
    $countAccount = 0;
    $min_date_created = $min_date_created_a;
    $max_date_created = $max_date_created_a;
    //
    $bigcommurl = $bigcommURL1 . "/v2/";
    
    $access_token = $access_token1;
    error_log("Number of Pages: ".$page, 1, "support@aquaapi.com");

    if ($page > 100) {
      $page = 100;
     } 
    for ($i = 100; $i <= $page; $i++) {
        if ($accountTypeCC === 'new') {
            $url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit;
        } else {
            $url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        }
        //$url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        //$url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit;
        //
        $http_headres = array(
            "Content-Type: application/json",
            "Accept: application/json",
            "X-Auth-Client:APP_AUTH_CLIENT_ID",
            "X-Auth-Token:" . trim($access_token)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //check if it returns 200, or else return false
        if ($http_code === 200) {
            curl_close($ch);
            $customers = json_decode($return, TRUE);
            foreach ($customers as $key => $value) {
                $address_url = $value['addresses']['url'];
                $http_headres = array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "X-Auth-Client:APP_AUTH_CLIENT_ID",
                    "X-Auth-Token:" . trim($access_token)
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $address_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                $return = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //check if it returns 200, or else return false
                if ($http_code === 200) {
                    $address = json_decode($return, TRUE);
                    $customers[$key]['ret_address'] = $address;
                } else {
                    $error = curl_error($ch);
                    $customers[$key]['ret_address'] = array();
                }
                /* inserting accounts in zoho */
                $retVal = j_InsertIntoZohoContact($customers[$key], $zohoAuthtoken1, $userid, $crmTypeCAcc);
                if ($retVal == TRUE) {
                    $countAccount++;
                }
               
            }
        } else {
            $error = curl_error($ch);
            //addErrors($error, $bigCommerceCredentials);
        }
    }
    return $countAccount;
}

//Insert Into Bigcommerce to Zoho Contacts
function j_InsertIntoZohoContact($contactDetailsCon, $auth, $userid, $crmTypeIAcc) {
    //Constarct The Data to Insert Zoho Contact Details.
    $msg = '';
    $firstName = $contactDetailsCon['first_name'];
    $lastName = $contactDetailsCon['last_name'];
    $accountName = $firstName. ' ' . $lastName;
    $email = $contactDetailsCon['email'];
    $phone = $contactDetailsCon['phone'];
    if (count($contactDetailsCon['ret_address']) > 0) {
        $mailingStreet = $contactDetailsCon['ret_address'][0]['street_1'] . ' ' . $contactDetailsCon['ret_address'][0]['street_2'];
        $otherStreet = $contactDetailsCon['ret_address'][0]['street_1'] . ' ' . $contactDetailsCon['ret_address'][0]['street_2'];
        $mailingCity = $contactDetailsCon['ret_address'][0]['city'];
        $otherCity = $contactDetailsCon['ret_address'][0]['city'];
        $mailingState = $contactDetailsCon['ret_address'][0]['state'];
        $otherState = $contactDetailsCon['ret_address'][0]['state'];
        $mailingZip = $contactDetailsCon['ret_address'][0]['zip'];
        $otherZip = $contactDetailsCon['ret_address'][0]['zip'];
        $mailingCountry = $contactDetailsCon['ret_address'][0]['country'];
        $otherCountry = $contactDetailsCon['ret_address'][0]['country'];
    } else {
        $mailingStreet = "NA";
        $otherStreet = "NA";
        $mailingCity = "NA";
        $otherCity = "NA";
        $mailingState = "NA";
        $otherState = "NA";
        $mailingZip = "NA";
        $otherZip = "NA";
        $mailingCountry = "NA";
        $otherCountry = "NA";
    }
    //
    $flag = FALSE;
    try {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Contacts>
            <row no="1">
                <FL val="First Name">' . $firstName . '</FL>
                <FL val="Last Name">' . $lastName . '</FL>
                <FL val="Account Name">' . $accountName . '</FL>
                <FL val="Email">' . $email . '</FL>
                <FL val="Phone">' . $phone . '</FL>                
                <FL val="Mailing Street">' . $mailingStreet . '</FL>
                <FL val="Other Street">' . $otherStreet . '</FL>                
                <FL val="Mailing City">' . $mailingCity . '</FL>
                <FL val="Other City">' . $otherCity . '</FL>             
                <FL val="Mailing State">' . $mailingState . '</FL>
                <FL val="Other State">' . $otherState . '</FL>                
                <FL val="Mailing Zip">' . $mailingZip . '</FL>
                <FL val="Other Zip">' . $otherZip . '</FL>                
                <FL val="Mailing Country">' . $mailingCountry . '</FL>
                <FL val="Other Country">' . $otherCountry . '</FL>
            </row>
        </Contacts>';
        //For Insert Records with duplicate checking.
        $url = "https://crm.zoho.com/crm/private/xml/Contacts/insertRecords";
        $query = "authtoken=$auth&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";

        $ch = curl_init();
        /* set url to send post request */
        curl_setopt($ch, CURLOPT_URL, $url);
        /* allow redirects */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        /* return a response into a variable */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        /* times out after 30s */
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /* set POST method */
        curl_setopt($ch, CURLOPT_POST, 1);
        /* add POST fields parameters */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        //Execute cUrl session
        $response = curl_exec($ch);
        curl_close($ch);
        // error_log("XML & Response".$xml." ".$response, 1, "support@aquaapi.com");

        if (strpos($response, '2000') !== false) {
            $flag = TRUE;
        } elseif (strpos($response, '2001') !== false) {
            $msg = 'Duplicate Record..';
            $flag = TRUE;
        } elseif (strpos($response, '2002') !== false) {
            $msg = 'Duplicate Record..';
            $flag = TRUE;
        } elseif (strpos($response, 'error') !== false) {
//           $res = json_decode(json_encode(simplexml_load_string($response)),TRUE);
//           $msg = $res['error']['code'];
//           $msg1 = $res['error']['message'];
           $error = json_decode($response, TRUE);
           $AccountHead = 'CUSTOMER';
           $st = 'YES';
           $flagErr = SaveErrorDetails($userid, $crmTypeIAcc, $AccountHead, 'Error adding Customer: ' . $accountName. ':' . $error['message'], $st);
 
            // $res = json_decode($response, TRUE);
            // $msg = $res['response']['error']['message'];
            $flag = FALSE;
        }
    } catch (Exception $e) {
        //echo '<pre>';
        //print_r($e);
        $msg = $e;
        $flag = FALSE;
    }
    return $flag;
}

//Count Products
function j_countProducts($zohoAuthtoken, $access_token1, $bigcommURL, $min_date_created_p, $max_date_created_p, $userid, $crmTypeProdC, $accountType2) {
    $total_success_product = 0;
    $min_date_created = $min_date_created_p;
    $max_date_created = $max_date_created_p;
    //
    $bigcommurl = $bigcommURL . "/v2/";
    $access_token = $access_token1;
    
    //
    if ($accountType2 === 'new') {
        $url = $bigcommurl . "products/count.json?is_visible=TRUE";
    } else {
        $url = $bigcommurl . "products/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created ."&is_visible=TRUE";
    }
    //$url = $bigcommurl . "products/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    //$url = $bigcommurl . "products/count.json";
        
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "X-Auth-Client:APP_AUTH_CLIENT_ID",
        "X-Auth-Token:" . trim($access_token)
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // Added: Debashish
      // error_log($url . " URL, Account Type " .$accountType2. "HTTP Code: " .$http_code. "Access Token: ".$access_token, 1, "support@aquaapi.com");

    //check if it returns 200, or else return false
    if ($http_code === 200) {
        curl_close($ch);
        $count_arr = json_decode($return, TRUE);
        $count = $count_arr['count'];
        // Added: Debashish
         error_log("Number of Products: ".$count, 1, "support@aquaapi.com");
         // Debashish : Override
        if ($count > 0) {
            $limit = 20;
            $page = ceil($count / $limit);
            $total_success_product = j_createProducts($page, $limit, $zohoAuthtoken, $access_token, $bigcommURL, $min_date_created_p, $max_date_created_p, $userid, $crmTypeProdC, $accountType2);
       
        } else {
            //countOrders();
        }
    } else {
        // Added: Debashish
       //  error_log("Error adding Product", 1, "support@aquaapi.com");
        $error = curl_error($ch);
        // addErrors($error, $bigCommerceCredentials);
    }
    return $total_success_product;
}

//Create Product
function j_createProducts($page, $limit, $zohoAuthtoken, $access_token1, $bigcommURL1, $min_date_created_cp, $max_date_created_cp, $userid, $crmTypeProdCr, $accountType1) {
    $retVal = FALSE;
    $countProduct = 0;
    $min_date_created = $min_date_created_cp;
    $max_date_created = $max_date_created_cp;
    $lastPage = $page;
    //    
    $bigcommurl = $bigcommURL1 . "/v2/";
    $access_token = $access_token1;
    /*
    if ($page > 10) {
      $page = 20;
    } */
    for ($i = 1; $i <= $page; $i++) {
        if ($accountType1 === 'new') {
            $url = $bigcommurl . "products.json?is_visible=TRUE&page=" . $i . "&limit=" . $limit;
        } else {
            $url = $bigcommurl . "products.json?is_visible=TRUE&page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        }
        // Addedd: Debashish
        // error_log("Pushing Product page: ".$i, 1, "support@aquaapi.com");

        $http_headres = array(
            "Content-Type: application/json",
            "Accept: application/json",
            "X-Auth-Client:APP_AUTH_CLIENT_ID",
            "X-Auth-Token:" . trim($access_token)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //check if it returns 200, or else return false
        if ($http_code === 200) {
            curl_close($ch);
            $products = json_decode($return, TRUE);
            foreach ($products as $key => $value) {
                $sku_url = $value['skus']['url'];
                $http_headres = array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "X-Auth-Client:APP_AUTH_CLIENT_ID",
                    "X-Auth-Token:" . trim($access_token)
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $sku_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                $return = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //check if it returns 200, or else return false
                if ($http_code === 200) {
                    $skues = json_decode($return, TRUE);
                    $products[$key]['available_skus'] = $skues;
                } else {
                    $error = curl_error($ch);
                    $products[$key]['available_skus'] = array();
                }

                /* insert into products */
                $retVal = j_InsertProductToZohoProduct($zohoAuthtoken, $products[$key], $userid, $crmTypeProdCr);
                if ($retVal == TRUE) {
                    $countProduct++;
                } else {
                   // To be removed.
                   //$countProduct++; 
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }
    return $countProduct;
}

//Insert Product Information From Bigcommerce to Zoho Product
function j_InsertProductToZohoProduct($auth, $recordSet, $userid, $crmTypeProdI) {
    $msg = '';
    $flag = FALSE;
    $iflag = FALSE;
    $prodCode = $recordSet['sku'];
    $prodName = $recordSet['name'];
    $prodUnitPrice = $recordSet['price'];
    try {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Products>
            <row no="1">
                <FL val="Product Code">' . $prodCode . '</FL>
                <FL val="Product Name">' . $prodName . '</FL>
                <FL val="Unit Price">' . $prodUnitPrice . '</FL>
            </row>
        </Products>';
        //For Insert Records with duplicate checking.
        $url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
        $query = "authtoken=$auth&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";

        $ch = curl_init();
        /* set url to send post request */
        curl_setopt($ch, CURLOPT_URL, $url);
        /* allow redirects */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        /* return a response into a variable */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        /* times out after 30s */
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /* set POST method */
        curl_setopt($ch, CURLOPT_POST, 1);
        /* add POST fields parameters */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        //Execute cUrl session
        $response = curl_exec($ch);
        curl_close($ch);
        //
        if (strpos($response, '2000') !== false) {
            $flag = TRUE;
        } elseif (strpos($response, '2001') !== false) {
            $msg1 = 'Duplicate Record..';
            $flag = TRUE;
        } elseif (strpos($response, '2002') !== false) {
            $msg1 = 'Duplicate Record..';
            $flag = TRUE;
        } elseif (strpos($response, 'error') !== false) {
            $res = json_decode($response, TRUE);
            $msg = $res['response']['error']['message'];
            $flag = FALSE;
        }
        //Insert Error details in the Database.
        if ($flag == FALSE) {
            $flagErr = FALSE;
            $flagErr1 = FALSE;
            $AccountHead = 'PRODUCT';
            $st = 'YES';
            $flagErr = SaveErrorDetails($userid, $crmTypeProdI, $AccountHead, 'Error while adding product: ' . $prodName, $st);
//            if ($flagErr > 0) {
//                echo 'Error Record Insersetion successfull.';
//            } else {
//                echo 'Error Record Insersetion failed.';
//            }
        }
        //
        /* if multiple sku available */
        if (count($recordSet['available_skus']) > 0) {
            foreach ($recordSet['available_skus'] as $k => $val) {
                $m_sku_id = $val['sku'];
                $m_adjusted_price = $val['adjusted_price'];
                $prodName1 = $recordSet['name'] . '-' . $m_sku_id;
                //
                $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <Products>
                    <row no="1">
                        <FL val="Product Code">' . $m_sku_id . '</FL>
                        <FL val="Product Name">' . $prodName1 . '</FL>
                        <FL val="Unit Price">' . $m_adjusted_price . '</FL>
                    </row>
                </Products>';
                //For Insert Records with duplicate checking.
                $url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
                $query = "authtoken=$auth&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";

                $ch = curl_init();
                /* set url to send post request */
                curl_setopt($ch, CURLOPT_URL, $url);
                /* allow redirects */
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                /* return a response into a variable */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                /* times out after 30s */
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                /* set POST method */
                curl_setopt($ch, CURLOPT_POST, 1);
                /* add POST fields parameters */
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                //Execute cUrl session
                $response = curl_exec($ch);
                curl_close($ch);
                //
                if (strpos($response, '2000') !== false) {
                    $flag = TRUE;
                } elseif (strpos($response, '2001') !== false) {
                    $msg = 'Duplicate Record..';
                    $flag = FALSE;
                } elseif (strpos($response, '2002') !== false) {
                    $msg = 'Duplicate Record..';
                    $flag = FALSE;
                } elseif (strpos($response, 'error') !== false) {
                    $res = json_decode($response, TRUE);
                    $msg = $res['response']['error']['message'];
                    $flag = FALSE;
                }
                //Insert Error details in the Database. 
                if ($flag == FALSE) {
                    $flagErr = SaveErrorDetails($userid, $crmTypeProdI, $AccountHead, 'Error while adding product: ' . $prodName, $st);
                }
                //
            }
        }
    } catch (Exception $e) {
//        echo '<pre>';
//        print_r($e);
        $msg1 = $e;
        $msg = $e;
        $flag = FALSE;
    }
    //
    return $flag;
}

//Insert Product Information From Bigcommerce to Zoho Invoice
function j_InsertOrderToZohoInvoice($accountDetails, $auth, $userid, $crmTypeOrdI) {
    //
    $flag = FALSE;
    //Get Product id from Zoho id using Product Code(sku)
    //    $product_id = $accountDetails['ordered_products'][0]['sku'];
    // error_log ("Product ID: ".$product_id, 1, "support@aquaapi.com");

    // $zoho_product_id = j_GetProductIDFromOrder($auth, $product_id);
   
    $contactName = $accountDetails['customer_info']['first_name'] . ' ' . $accountDetails['customer_info']['last_name'];
    $accountName = $accountName;
    $productDetail_name = $accountDetails['ordered_products'][0]['name'];
    $quantity = $accountDetails['ordered_products'][0]['quantity'];
    $amountList = $accountDetails['ordered_products'][0]['base_price'];
    $totalIncTax = $accountDetails['ordered_products'][0]['total_inc_tax'];
    $discountAmt = $totalIncTax - $amountList;
    $totalAfterDiscount = $totalIncTax - $discountAmt;
    $totalExTax = $accountDetails['ordered_products'][0]['total_ex_tax'];
    $tax = $totalIncTax - $totalExTax;
    $netTotal = $totalIncTax * $quantity;
    //
    $subject = 'Order Number ' . $accountDetails['id'] . ' Order for ' . $contactName;
    if (count($accountDetails['billing_address']) > 0) {
        $billingStreet = $accountDetails['billing_address']['street_1'] . ' ' . $accountDetails['billing_address']['street_2'];
        $billingCity = $accountDetails['billing_address']['city'];
        $billingState = $accountDetails['billing_address']['state'];
        $billingCountry = $accountDetails['billing_address']['country'];
        $billingZip = $accountDetails['billing_address']['zip'];
        //
        $shippingStreet = $accountDetails['billing_address']['street_1'] . ' ' . $accountDetails['billing_address']['street_2'];
        $shippingCity = $accountDetails['billing_address']['city'];
        $shippingState = $accountDetails['billing_address']['state'];
        $shippingCountry = $accountDetails['billing_address']['country'];
        $shippingZip = $accountDetails['billing_address']['zip'];
    } else {
        $billingStreet = 'NA';
        $billingCity = 'NA';
        $billingState = 'NA';
        $billingCountry = 'NA';
        $billingZip = 'NA';
        //
        $shippingStreet = 'NA';
        $shippingCity = 'NA';
        $shippingState = 'NA';
        $shippingCountry = 'NA';
        $shippingZip = 'NA';
    }

    try {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
          <Invoices>
            <row no="1">
                <FL val="Contact Name">' . $contactName . '</FL> 
                <FL val="Account Name">' . $accountName . '</FL>
                <FL val="Product Details">
                    <product no="1">   
                        <FL val="Product Name">' . $productDetail_name . '</FL>
                        <FL val="Quantity">' . $quantity . '</FL>
                        <FL val="Unit Price">' . $totalIncTax . '</FL>
                        <FL val="List Price">' . $amountList . '</FL>
                        <FL val="Discount">' . $discountAmt . '</FL>
                        <FL val="Total">' . $totalIncTax . '</FL>
                        <FL val="Total After Discount">' . $totalAfterDiscount . '</FL>
                        <FL val="Tax">' . $tax . '</FL>
                        <FL val="Net Total">' . $netTotal . '</FL>
                    </product>
                </FL>                     
                <FL val="Subject">' . $subject . '</FL>
                <FL val="Billing Street">' . $billingStreet . '</FL>
                <FL val="Shipping Street">' . $shippingStreet . '</FL>
                <FL val="Billing City">' . $billingCity . '</FL>
                <FL val="Shipping City">' . $shippingCity . '</FL>
                <FL val="Billing State">' . $billingState . '</FL>
                <FL val="Shipping State">' . $shippingState . '</FL>
                <FL val="Billing Zip">' . $billingZip . '</FL>
                <FL val="Shipping Zip">' . $shippingZip . '</FL>
                <FL val="Billing Country">' . $billingCountry . '</FL>
                <FL val="Shipping Country">' . $shippingCountry . '</FL>  
            </row>
          </Invoices>';

        //For Insert Records with duplicate checking.
        $url = "https://crm.zoho.com/crm/private/xml/Invoices/insertRecords";
        $query = "authtoken=$auth&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml"; 
        error_log ("Insert One Order". $xml, 1, "support@aquaapi.com");

        $ch = curl_init();
        /* set url to send post request */
        curl_setopt($ch, CURLOPT_URL, $url);
        /* allow redirects */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        /* return a response into a variable */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        /* times out after 30s */
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /* set POST method */
        curl_setopt($ch, CURLOPT_POST, 1);
        /* add POST fields parameters */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        //Execute cUrl session
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //curl_close($ch);
        //check if it returns 200, or else return false
        if ($http_code === 200) {
            curl_close($ch);
            $flag = 1;
            $msg = $response;
        } else {
            $error = curl_error($ch);
            $flag = 0;
            $msg = $error;
        }
        $arr = array(
            'flag' => $flag,
            'msg' => $msg
        );
        // Added : Debashish
        error_log("Order entry response".$response, 1, "support@aquaapi.com");
        
      if (strpos($response, '2000') !== false) {
            $flag = TRUE;
      } elseif (strpos($response, '2001') !== false) {
           $msg = 'Existing record updated';
           $flag = TRUE;
      } elseif (strpos($response, '2002') !== false) {
            $msg = 'Record already exists';
            $flag = TRUE;
      } elseif (strpos($response, 'error') !== false) {
           $res = json_decode(json_encode(simplexml_load_string($response)),TRUE);
           $msg = $res['error']['code'];
           $msg1 = $res['error']['message'];

            //$res = json_decode($response, TRUE);
            //$msg1 = $res['response']['error']['message'];
            //$msg = $res['response']['error']['code'];
            $flag = FALSE;
       }
    } catch (Exception $e) {
        //echo '<pre>';
        //print_r($e);
        $msg1 = $e;
        $flag = FALSE;
    }

    //Insert Error details in the Database.
    if ($flag == FALSE) {
        $flagErr = FALSE;
        $AccountHead = 'ORDER';
        $st = 'YES';
        $flagErr = SaveErrorDetails($userid, $crmTypeOrdI, $AccountHead, 'Error adding order: ' . $subject . ':' . $msg1 . ', Code: ' . $msg, $st);
    }

    return $flag;
}

//Bigcommerce Customer Details for Order.
function j_GetProductIDFromOrder($auth_id, $product_code) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://crm.zoho.com/crm/private/json/Products/searchRecords?authtoken=$auth_id&scope=crmapi&criteria=(Product%20Code:$product_code)",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        //echo "cURL Error #:" . $err;
    } else {
        $res = json_decode($response, TRUE);
        $productId = $res['response']['result']['Products']['row']['FL'][0]['content'];
    }
    return $productId;
}

//Bigcommerce Order count
function j_countOrders($zohoAuthtoken, $access_token, $bigcommurl, $min_date_created_ord, $max_date_created_ord, $userid, $crmTypeOrdC) {
    //
    $total_success_order = 0;
    $min_date_created = $min_date_created_ord;
    $max_date_created = $max_date_created_ord;
    //
    $url = $bigcommurl . "/v2/orders/count.json?is_deleted=false&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    //$url = $bigcommurl . "/v2/orders/count.json";
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "X-Auth-Client:APP_AUTH_CLIENT_ID",
        "X-Auth-Token:" . trim($access_token)
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //check if it returns 200, or else return false
    if ($http_code === 200) {
        curl_close($ch);
        $count_arr = json_decode($return, TRUE);
        $count = $count_arr['count'];
        error_log("Count Order ".$count, 1, "support@aquaapi.com");
        $limit = 20;
        $page = ceil($count / $limit);
        //createOrders($page, $limit);
        $total_success_order = j_createOrders($page, $limit, $zohoAuthtoken, $access_token, $bigcommurl, $min_date_created_ord, $max_date_created_ord, $userid, $crmTypeOrdC);
    } else {
        $error = curl_error($ch);
       // addErrors($error);
    }
    //
    return $total_success_order;
}

//Create Orders
function j_createOrders($page, $limit, $zohoAuthtoken, $access_token, $bigcommurl, $min_date_created_crord, $max_date_created_crord, $userid, $crmTypeOrdCr) {
    //
    $retVal = FALSE;
    $countOrder = 0;
    $min_date_created = $min_date_created_crord;
    $max_date_created = $max_date_created_crord;
    //
    for ($i = 1; $i <= $page; $i++) {
         $url = $bigcommurl . "/v2/orders.json?page=" . $i . "&limit=" . $limit . "&is_deleted=false&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        // $url = $bigcommurl . "/v2/orders.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        //$url = $bigcommurl . "/v2/orders.json?page=" . $i . "&limit=" . $limit . "&is_deleted=false";
        $http_headres = array(
            "Content-Type: application/json",
            "Accept: application/json",
            "X-Auth-Client:APP_AUTH_CLIENT_ID",
            "X-Auth-Token:" . trim($access_token)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //check if it returns 200, or else return false
        if ($http_code === 200) {
            curl_close($ch);
            $orders = json_decode($return, TRUE);
            error_log("Retrieved up to 20 orders", 1, "support@aquaapi.com");

            foreach ($orders as $key => $value) {
                $product_url = $value['products']['url'];
                //error_log("Product URL: ".$product_url, 1, "support@aquaapi.com");
                $http_headres = array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "X-Auth-Client:APP_AUTH_CLIENT_ID",
                    "X-Auth-Token:" . trim($access_token)
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $product_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                $return = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //check if it returns 200, or else return false
                if ($http_code === 200) {
                    $ordered_products = json_decode($return, TRUE);
                    $orders[$key]['ordered_products'] = $ordered_products;
                    // error_log("Product URL: ".$product_url, 1, "support@aquaapi.com");
                	$customer_id = $value['customer_id'];
                	$customer_url = $bigcommurl . "/v2/customers/$customer_id.json";
                	$ch = curl_init();
                	curl_setopt($ch, CURLOPT_URL, $customer_url);
                	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                	curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                	$return = curl_exec($ch);
                	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                	//check if it returns 200, or else return false
                	if ($http_code === 200) {
                    	$customer_info = json_decode($return, TRUE);
                    	$orders[$key]['customer_info'] = $customer_info;
                	$retVal = j_InsertOrderToZohoInvoice($orders[$key], $zohoAuthtoken, $userid, $crmTypeOrdCr);
                         if ($retVal == TRUE) {
                            $countOrder++;
                	  }
                        } else {
                    	$error = curl_error($ch);
                    	$orders[$key]['customer_info'] = array();
                	}


                } else {
//                    error_log("Error retrieving products: ".$product_url, 1, "support@aquaapi.com");
                 
                    $error = curl_error($ch);
                    $orders[$key]['ordered_products'] = array();
                } /*
                $customer_id = $value['customer_id'];
                $customer_url = $bigcommurl . "/v2/customers/$customer_id.json";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $customer_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                $return = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //check if it returns 200, or else return false
                if ($http_code === 200) {
                    $customer_info = json_decode($return, TRUE);
                    $orders[$key]['customer_info'] = $customer_info;
                } else {
                    $error = curl_error($ch);
                    $orders[$key]['customer_info'] = array();
                }
                */
                //Insert 20 record to Zoho Invoice                 
               // $retVal = j_InsertOrderToZohoInvoice($orders[$key], $zohoAuthtoken, $userid, $crmTypeOrdCr);
               // if ($retVal == TRUE) {
               //     $countOrder++;
               // }
            } 
        } else {
            $error = curl_error($ch);
            // addErrors($error);
        }
    }
    return $countOrder;
}

//Save error message to database
function SaveErrorDetails($userid, $crmType, $transHead, $errMsg, $status) {
    $flag = FALSE;
    try {
        //Connect Database and open the connection.
//        $connection_status = Connectdb();
//        $open_status = Opendb();
        //add data in the database
        $userDataArrError = array(
            'user_id' => $userid,
            'crm_type' => $crmType,
            'transaction_head' => $transHead,
            'error_message' => $errMsg,
            'status' => $status,
            'added_on' => time()
        );
        include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
        $insertErrorData = insert($zohoSyncErrorDetails, $userDataArrError);
        if ($insertErrorData > 0) {
            $flag = TRUE;
        } else {
            $flag = FALSE;
        }
    } catch (Exception $ex) {
        //echo 'error : ' . $ex;
    }
    return $flag;
}
