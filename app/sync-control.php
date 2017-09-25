<?php

/*
 * Description:         Sync data from Bigcommerce to CRM using cron job
 * @author:             Bidyabrata & Tarak Kayal
 * @version:            1.0.0
 * @copyright:          aquaAPI (http://www.aquaapi.com)
 * @contact:            info@aquaapi.com
 */
/* including database related files */
include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
include "/var/www/html/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

/* Connect Database and open the connection. */
$from = "BigCommerce to CRM connector (support@aquaapi.com)";
$to = 'crmconnector@aquaapi.com'; //$userName;
$headers = "From: $from\r\n";
$headers .= "Cc:info@aquaapi.com \r\n";
$headers .= "Content-type: text/html\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$subject = 'First Email..';
$message = 'First Email.';
$flag = mail($to, $subject, $message, $headers);

/* setting current date and time */
$currentSyncHrs = date("G");
$currentSyncMins = date("i");
$currentSyncMinLimit = (int) $currentSyncMins - 30;
/* getting user with current schedule time */
$cond = " AND sync_hrs='$currentSyncHrs' AND sync_mins<=$currentSyncMins AND sync_mins>=$currentSyncMinLimit ORDER BY sync_mins DESC LIMIT 1";
$syncInvDetails = fetch($syncTimeSlot, $cond);
//echo "<pre>";
//print_r($syncInvDetails);
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
            $max_date_created = date("Y-m-d") . "T" . $syncTimeTable;
            $lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "-1 days"));
            $min_date_created = $lastDay . "T" . $syncTimeTable;

            $typeOfCRM = $userDetails[0]['crm_type'];
            $userID = $userDetails[0]['user_id'];
            $contextValue = $userDetails[0]['context'];
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
                'account_type' => 'old'
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
                s_checkIfStandardPriceBook($bigCommerceCredentials);
            } else if ($typeOfCRM === 'ZOHO') {
                $zoho_auth_id = $userDetails[0]['zoho_auth_id'];
                $zohoCredentials = array(
                    'zoho_auth_id' => $zoho_auth_id
                );
                $bigCommerceCredentials['zohoCredentialsdetails'] = $zohoCredentials;
                j_index_bigcomm_joho($bigCommerceCredentials);
            }
        }
    }
}

/* sync from bigcommerce to sfdc */

function addErrors($msg, $bigCommerceCredentials) {
    $userName = $bigCommerceCredentials['userEmail'];
    $email = $bigCommerceCredentials['userEmail'];
    $subject = "AquaAPI - BigCommerce to CRM connector : Sync error";
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
                    <p style="margin-bottom:25px;">Here is the integration report run on $syncDateTime for <a style="color:#fff;" href="#">AquaAPI - BigCommerce to CRM connector</a></p>
                    <h2 style="font-size:18px;">AquaAPI - BigCommerce to CRM connector : Sync error</h2>
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

    $from = "BigCommerce to CRM connector (support@aquaapi.com)";
    $to = $userName;
    $headers = "From: $from\r\n";
    $headers .= "Cc:info@aquaapi.com \r\n";
    $headers .= "Content-type: text/html\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    $flag = mail($to, $subject, $message, $headers);

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

function s_checkIfStandardPriceBook($bigCommerceCredentials) {
    include "/var/www/html/bigcommerce-app-management/sfdc-integration/Sfdc.php";
    $sfdcCredentials = $bigCommerceCredentials['sfdcCredentialsDetails'];
    $priceBookInfo = Sfdc::getPriceBookDetails($sfdcCredentials);

    if ($priceBookInfo->Name === 'Standard Price Book') {
        $bigCommerceCredentials['stdPriceBookId'] = $priceBookInfo->Id;
        s_countAccounts($bigCommerceCredentials);
    } else {
        $userid = $bigCommerceCredentials['user_id'];
        addErrors("Standard Price Book need to set", $bigCommerceCredentials);
        //SaveErrorDetails($userid, 'SFDC', "PRODUCT", "Standard Price Book need to set", "YES");
    }
}

function s_countAccounts($bigCommerceCredentials) {
    $u_user_context = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $accountType = $bigCommerceCredentials['account_type'];
    $access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    if ($accountType === 'new') {
        $url = $bigcommurl . "customers/count.json";
    } else {
        $url = $bigcommurl . "customers/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    }
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
        $bigCommerceCredentials['counterAccounts'] = $count;
        if ($count > 0) {
            $limit = 20;
            $page = ceil($count / $limit);
            s_createAccount($bigCommerceCredentials, $page, $limit);
        } else {
            s_countProducts($bigCommerceCredentials);
        }
    } else {
        $error = curl_error($ch);
        //addErrors($error);
        addErrors($error, $bigCommerceCredentials);
    }
}

function s_createAccount($bigCommerceCredentials, $page, $limit) {
    $u_user_context = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $accountType = $bigCommerceCredentials['account_type'];
    $access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $sfdcCredentials = $bigCommerceCredentials['sfdcCredentialsDetails'];

    for ($i = 1; $i <= $page; $i++) {
        if ($accountType === 'new') {
            $url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit;
        } else {
            $url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        }


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

                /* inserting accounts in sfdc */
                $accounts = array();

                $accounts[0] = new stdclass();
                $accounts[0]->Name = $value['email'];
                $address_count = count($customers[$key]['ret_address']);
                $ret_address = $customers[$key]['ret_address'];
                if ($address_count > 0) {
                    if ($address_count === 1) {
                        $accounts[0]->BillingCity = $ret_address[0]['city'];
                        $accounts[0]->BillingCountry = $ret_address[0]['country'];
                        $accounts[0]->BillingPostalCode = $ret_address[0]['zip'];
                        $accounts[0]->BillingState = $ret_address[0]['state'];
                        $accounts[0]->BillingStreet = $ret_address[0]['street_1'];
                        $accounts[0]->Phone = $value['phone'];
                        $accounts[0]->ShippingCity = $ret_address[0]['city'];
                        $accounts[0]->ShippingCountry = $ret_address[0]['country'];
                        $accounts[0]->ShippingPostalCode = $ret_address[0]['zip'];
                        $accounts[0]->ShippingState = $ret_address[0]['state'];
                        $accounts[0]->ShippingStreet = $ret_address[0]['street_1'];
                    } else {
                        $accounts[0]->BillingCity = $ret_address[0]['city'];
                        $accounts[0]->BillingCountry = $ret_address[0]['country'];
                        $accounts[0]->BillingPostalCode = $ret_address[0]['zip'];
                        $accounts[0]->BillingState = $ret_address[0]['state'];
                        $accounts[0]->BillingStreet = $ret_address[0]['street_1'];
                        $accounts[0]->Phone = $value['phone'];
                        $accounts[0]->ShippingCity = $ret_address[1]['city'];
                        $accounts[0]->ShippingCountry = $ret_address[1]['country'];
                        $accounts[0]->ShippingPostalCode = $ret_address[1]['zip'];
                        $accounts[0]->ShippingState = $ret_address[1]['state'];
                        $accounts[0]->ShippingStreet = $ret_address[1]['street_1'];
                    }
                }
                $accoutRes = Sfdc::createSfdcAccount($sfdcCredentials, $accounts);
            }
        } else {
            $error = curl_error($ch);
            addErrors($error, $bigCommerceCredentials);
        }
    }
    s_countProducts($bigCommerceCredentials);
}

function s_countProducts($bigCommerceCredentials) {
    $u_user_context = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $accountType = $bigCommerceCredentials['account_type'];

    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";

    if ($accountType === 'new') {
        $url = $bigcommurl . "products/count.json";
    } else {
        $url = $bigcommurl . "products/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    }

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
        $bigCommerceCredentials['counterProducts'] = $count;
        if ($count > 0) {
            $limit = 20;
            $page = ceil($count / $limit);
            s_createProducts($bigCommerceCredentials, $page, $limit);
        } else {
            s_countOrders($bigCommerceCredentials);
        }
    } else {
        $error = curl_error($ch);
        addErrors($error, $bigCommerceCredentials);
    }
}

function s_createProducts($bigCommerceCredentials, $page, $limit) {
    $u_user_context = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $accountType = $bigCommerceCredentials['account_type'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $sfdcCredentials = $bigCommerceCredentials['sfdcCredentialsDetails'];
    $s_priceBookId = $bigCommerceCredentials['stdPriceBookId'];

    for ($i = 1; $i <= $page; $i++) {
        if ($accountType === 'new') {
            $url = $bigcommurl . "products.json?page=" . $i . "&limit=" . $limit;
        } else {
            $url = $bigcommurl . "products.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        }

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
                $accounts = array();

                $accounts[0] = new stdclass();
                $accounts[0]->Name = $value['name'];
                $accounts[0]->IsActive = TRUE;
                $accounts[0]->ProductCode = $value['sku'];
                $accounts[0]->Description = $value['description'];
                $accoutRes = Sfdc::createSfdcProducts($sfdcCredentials, $accounts);
                $insertedProductId = $accoutRes->id;

                $priceEntry = array();

                $priceEntry[0] = new stdclass();
                $priceEntry[0]->Pricebook2Id = $s_priceBookId;
                $priceEntry[0]->IsActive = TRUE;
                $priceEntry[0]->Product2Id = $insertedProductId;
                $priceEntry[0]->UnitPrice = $value['price'];
                $priceEntry[0]->UseStandardPrice = FALSE;

                $addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);

                if (count($products[$key]['available_skus']) > 0) {
                    $available_skus = $products[$key]['available_skus'];
                    foreach ($available_skus as $k => $val) {
                        $accounts = array();
                        $accounts[0] = new stdclass();
                        $accounts[0]->Name = $value['name'];
                        $accounts[0]->IsActive = TRUE;
                        $accounts[0]->ProductCode = $val['sku'];
                        $accounts[0]->Description = $value['description'];
                        $accoutRes = Sfdc::createSfdcProducts($sfdcCredentials, $accounts);
                        $insertedProductId = $accoutRes->id;

                        $priceEntry = array();
                        $priceEntry[0] = new stdclass();
                        $priceEntry[0]->Pricebook2Id = $s_priceBookId;
                        $priceEntry[0]->IsActive = TRUE;
                        $priceEntry[0]->Product2Id = $insertedProductId;
                        $priceEntry[0]->UnitPrice = $val['adjusted_price'];
                        $priceEntry[0]->UseStandardPrice = FALSE;

                        $addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);
                    }
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error, $bigCommerceCredentials);
        }
    }
    s_countOrders($bigCommerceCredentials);
}

function s_countOrders($bigCommerceCredentials) {

    $u_user_context = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";

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
    //check if it returns 200, or else return false
    if ($http_code === 200) {
        curl_close($ch);
        $count_arr = json_decode($return, TRUE);
        $count = $count_arr['count'];
        $bigCommerceCredentials['counterOrders'] = $count;


        /* inserting count */
        $sUID = $bigCommerceCredentials['user_id'];
        $sCrmType = $bigCommerceCredentials['crm_type'];
        $sNoCustomerData = $bigCommerceCredentials['counterAccounts'];
        $sNoProductData = $bigCommerceCredentials['counterProducts'];
        $sNoOrderData = $bigCommerceCredentials['counterOrders'];
        $sStatus = 'YES';
        $sTotalTransaction = $sNoCustomerData + $sNoProductData + $sNoOrderData;

        $sUserDataArrTrans = array(
            'user_id' => $sUID,
            'crm_type' => $sCrmType,
            'no_customer_data' => $sNoCustomerData,
            'no_product_data' => $sNoProductData,
            'no_order_data' => $sNoOrderData,
            'last_sync_time' => time(),
            'total_transaction' => $sTotalTransaction,
            'status' => $sStatus,
            'added_on' => time()
        );
        /* including database related files */
        include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
        $insertData = insert($zohoTransactionDetails, $sUserDataArrTrans);

        $limit = 20;
        $page = ceil($count / $limit);
        s_createOrders($bigCommerceCredentials, $page, $limit);
    } else {
        $error = curl_error($ch);
        addErrors($error, $bigCommerceCredentials);
    }
}

function s_createOrders($bigCommerceCredentials, $page, $limit) {
    $u_user_context = $bigCommerceCredentials['contextValue'];
    $min_date_created = $bigCommerceCredentials['min_date_created'];
    $max_date_created = $bigCommerceCredentials['max_date_created'];
    $access_token = $bigCommerceCredentials['bigCommerceAccessToken'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $sfdcCredentials = $bigCommerceCredentials['sfdcCredentialsDetails'];
    $s_priceBookId = $bigCommerceCredentials['stdPriceBookId'];

    for ($i = 1; $i <= $page; $i++) {
        $url = $bigcommurl . "orders.json?page=" . $i . "&limit=" . $limit . "&is_deleted=false&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
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

            foreach ($orders as $key => $value) {
                $product_url = $value['products']['url'];
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
                } else {
                    $error = curl_error($ch);
                    $orders[$key]['ordered_products'] = array();
                }
                $customer_id = $value['customer_id'];
                $customer_url = $bigcommurl . "customers/$customer_id.json";
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
                $customerEmail = $orders[$key]['customer_info']['email'];
                $customerName = $orders[$key]['customer_info']['first_name'] . " " . $orders[$key]['customer_info']['last_name'];
                $bigcommerceOrderId = $value['id'];
                $sfdcQuery = "Select Id FROM Account WHERE Name = '$customerEmail' ";
                $accoutRes = Sfdc::sfdcQuery($sfdcCredentials, $sfdcQuery);
                $accountId = $accoutRes->Id;

                $accounts = array();

                $accounts[0] = new stdclass();
                $accounts[0]->AccountId = $accountId;
                $accounts[0]->Amount = $value['total_inc_tax'];
                $accounts[0]->Description = "Bigcommerce CRM order for  $customerEmail with name $customerName and order Id $bigcommerceOrderId";
                $accounts[0]->Name = "Bigcommerce CRM order for  $customerEmail ";
                $accounts[0]->Pricebook2Id = $s_priceBookId;
                $accounts[0]->CloseDate = date("Y-m-d", strtotime($value['date_modified']));
                $accounts[0]->StageName = 'Closed Won';

                $oopotunityDetails = Sfdc::createOpportunity($sfdcCredentials, $accounts);

                $opportinutyId = $oopotunityDetails->id;
                $orderProducts = $orders[$key]['ordered_products'];

                if (count($orderProducts) > 0) {
                    foreach ($orderProducts as $k => $val) {
                        $productCode = $val['sku'];
                        $priceBook2Id = $s_priceBookId;
                        $sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode='$productCode' ";
                        $quryRes = Sfdc::sfdcSelectQuery($sfdcCredentials, $sfdcQuery);
                        if ($quryRes) {
                            $priceBookId = $quryRes[0]->Id;
                            $priceEntryToOpp = array();

                            $priceEntryToOpp[0] = new stdclass();
                            $priceEntryToOpp[0]->OpportunityId = $opportinutyId;
                            $priceEntryToOpp[0]->Description = $val['name'];
                            $priceEntryToOpp[0]->PricebookEntryId = $priceBookId;
                            $priceEntryToOpp[0]->Quantity = $val['quantity'];
                            $priceEntryToOpp[0]->ServiceDate = date("Y-m-d", strtotime($value['date_modified']));
                            $priceEntryToOpp[0]->UnitPrice = $val['total_inc_tax'];

                            $accoutRes = Sfdc::addProductToOpportunity($sfdcCredentials, $priceEntryToOpp);
                        }
                    }
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error, $bigCommerceCredentials);
        }
    }
    sendSyncReport($bigCommerceCredentials);
    return true;
}

function sendSyncReport($bigCommerceCredentials) {
    $from = "BigCommerce to CRM connector (support@aquaapi.com)";
    $to = 'crmconnector@aquaapi.com'; //$userName;
    $headers = "From: $from\r\n";
    $headers .= "Cc:info@aquaapi.com \r\n";
    $headers .= "Content-type: text/html\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $subject = 'Inside Calling Email..';
    $message = 'Inside Calling Email.' . json_encode($bigCommerceCredentials);
    $flag = mail($to, $subject, $message, $headers);

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
    $subject = "AquaAPI - BigCommerce to CRM connector : Sync report";

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
                    <p style="margin-bottom:25px;">Here is the integration report run on $syncDateTime for <a style="color:#fff;" href="#">AquaAPI - BigCommerce to CRM connector</a></p>
                    <h2 style="font-size:18px;">AquaAPI - BigCommerce to CRM connector : Sync report</h2>
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

    $from = "BigCommerce to CRM connector (support@aquaapi.com)";
    $to = $userName;
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
    //
    try {
        //Bigcommerce base url
        $bigcommURL = "https://api.bigcommerce.com/" . "" . $contextVal . "";
        //Call account Details form Bigcommerce 
        $flagCustomer = j_countAccounts($zohoAuthtoken, $u_access_token, $bigcommURL, $min_date_created, $max_date_created, $uID, $crmType, $accountType);
        //
        //Call Product Information from Bigcommerce.           
        $flagProduct = j_countProducts($zohoAuthtoken, $u_access_token, $bigcommURL, $min_date_created, $max_date_created, $uID, $crmType, $accountType);
        //
        //Call Oreder Information from Bigcommerce.            
        $flagOrder = j_countOrders($zohoAuthtoken, $u_access_token, $bigcommURL, $min_date_created, $max_date_created, $uID, $crmType);
        //
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
        $from = "BigCommerce to CRM connector (support@aquaapi.com)";
        $to = 'crmconnector@aquaapi.com'; //$userName;
        $headers = "From: $from\r\n";
        $headers .= "Cc:info@aquaapi.com \r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $subject = 'Before Calling Email..';
        $message = 'Before Calling Email.';
        $flag = mail($to, $subject, $message, $headers);
        //
        sendSyncReport($bigCommerceCredentials);

        $from = "BigCommerce to CRM connector (support@aquaapi.com)";
        $to = 'crmconnector@aquaapi.com'; //$userName;
        $headers = "From: $from\r\n";
        $headers .= "Cc:info@aquaapi.com \r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $subject = 'After Calling Email..';
        $message = 'After Calling Email.';
        $flag = mail($to, $subject, $message, $headers);
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

//Bigcommerce Customer address details
function j_countAccounts($zohoAuthtoken, $access_token1, $bigcommURL, $min_date_created1, $max_date_created1, $userid, $crmTypeAcc, $accountTypeC) {
    //  
    $total_success = 0;
    $min_date_created = $min_date_created1;
    $max_date_created = $max_date_created1;
    $bigcommurl = $bigcommURL . "/v2/";
    $access_token = $access_token1;
    //
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
        if ($count > 0) {
            $limit = 20;
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
    //
    for ($i = 1; $i <= $page; $i++) {
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
    $accountName = $contactDetailsCon['company'];
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
    } catch (Exception $e) {
        //echo '<pre>';
        //print_r($e);
        $msg = $e;
        $flag = FALSE;
    }
    //Insert Error details in the Database.
    if ($flag == FALSE) {
        $flagErr = FALSE;
        $AccountHead = 'CUSTOMER';
        $st = 'YES';
        $flagErr = SaveErrorDetails($userid, $crmTypeIAcc, $AccountHead, 'Error while creating contact: ' . $email, $st);
        /* if ($flagErr > 0) {
          //echo 'Error Record Insersetion successfull.';
          } else {
          //echo 'Error Record Insersetion failed.';
          } */
    }
    //
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
        $url = $bigcommurl . "products/count.json";
    } else {
        $url = $bigcommurl . "products/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    }
    //$url = $bigcommurl . "products/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    //$url = $bigcommurl . "products/count.json";
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
        $count_arr = json_decode($return, TRUE);
        $count = $count_arr['count'];
        if ($count > 0) {
            $limit = 20;
            $page = ceil($count / $limit);
            $total_success_product = j_createProducts($page, $limit, $zohoAuthtoken, $access_token, $bigcommURL, $min_date_created_p, $max_date_created_p, $userid, $crmTypeProdC, $accountType2);
        } else {
            //countOrders();
        }
    } else {
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
    //
    for ($i = 1; $i <= $page; $i++) {
        if ($accountType1 === 'new') {
            $url = $bigcommurl . "products.json?page=" . $i . "&limit=" . $limit;
        } else {
            $url = $bigcommurl . "products.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        }
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
            $flag = FALSE;
        } elseif (strpos($response, '2002') !== false) {
            $msg1 = 'Duplicate Record..';
            $flag = FALSE;
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
    $product_id = $accountDetails['ordered_products'][0]['sku'];
    $zoho_product_id = j_GetProductIDFromOrder($auth, $product_id);
    //
    $contactName = $accountDetails['customer_info']['first_name'] . ' ' . $accountDetails['customer_info']['last_name'];
    $accountName = $accountDetails['customer_info']['company'];
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
    $subject = 'big_' . $accountDetails['id'] . 'order for ' . $contactName;
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
                        <FL val="Product Id">' . $zoho_product_id . '</FL>
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
        $flagErr = SaveErrorDetails($userid, $crmTypeOrdI, $AccountHead, 'Error while adding order: ' . $subject . ' : ' . $msg, $st);
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
    $url = $bigcommurl . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
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
        $limit = 20;
        $page = ceil($count / $limit);
        //createOrders($page, $limit);
        $total_success_order = j_createOrders($page, $limit, $zohoAuthtoken, $access_token, $bigcommurl, $min_date_created_ord, $max_date_created_ord, $userid, $crmTypeOrdC);
    } else {
        $error = curl_error($ch);
        addErrors($error);
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

            foreach ($orders as $key => $value) {
                $product_url = $value['products']['url'];
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
                } else {
                    $error = curl_error($ch);
                    $orders[$key]['ordered_products'] = array();
                }
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

                //Insert 20 record to Zoho Invoice                 
                $retVal = j_InsertOrderToZohoInvoice($orders[$key], $zohoAuthtoken, $userid, $crmTypeOrdCr);
                if ($retVal == TRUE) {
                    $countOrder++;
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
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
