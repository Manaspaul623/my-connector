<?php
//include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
//include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
//session_start();
/* including database related files */
include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
include "/var/www/html/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
//
//Check all the basic information-userId,CRM Type
$seesionFlag = FALSE;
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    if (isset($_SESSION['crmType']) && !empty($_SESSION['crmType'])) {
        $uID = $_SESSION['userID'];
        $crmType = $_SESSION['crmType'];
        $contextID = $_SESSION['contextValue'];
        $contextVal = str_replace('/', '-', $contextID);
        $seesionFlag = TRUE;
        $syncTimeTable = $_SESSION['syncTimeTable'];
        $max_date_created = date("Y-m-d") . "T" . $syncTimeTable;

        $lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "-1 days"));
        $min_date_created = $lastDay . "T" . $syncTimeTable;

        $_SESSION['min_date_created'] = $min_date_created;
        $_SESSION['max_date_created'] = $max_date_created;
    }
}
//
if ($seesionFlag == TRUE) {    
    $flagAccount = FALSE;
    $flagProduct = FALSE;
    $flagOrder = FALSE;
    $zohoAuthtoken = '';
    $u_access_token = '';
    $crmPlan = '';
    $planType = '';
    try {
        //Connect Database and open the connection.
        $connection_status = Connectdb();
        $open_status = Opendb();
        echo '<br>';
        echo 'USER ID : ' . $uID;
        echo '<br>';
        echo 'CRM TYPE : ' . $crmType;
        echo '<br>';
        //Get user information from database.
        //Set the user parameters.
        $userData = $condID . $uID . ' AND crm_type LIKE ' . '"' . $crmType . '"';
        $resVal = fetch($userTable, $userData);
        $totCount = count($resVal);
        if ($totCount > 0) {
            $u_access_token = $resVal[0]['access_token'];
            $u_scope = $resVal[0]['scope'];
            $u_user_id = $resVal[0]['user_id'];
            $u_user_username = $resVal[0]['user_name'];
            $u_user_email = $resVal[0]['user_email'];
            $u_user_context = $resVal[0]['context'];
            $zohoAuthtoken = $resVal[0]['zoho_auth_id'];
            $crmTypeData = $resVal[0]['crm_type'];
            //$bigCommContext = str_replace('/', '-', $u_user_context);
            //Bigcommerce base url
            $bigcommURL = "https://api.bigcommerce.com/" . "" . $u_user_context . "";
            //Call account Details form Bigcommerce 

            $flagAccount = j_countAccounts($zohoAuthtoken, $u_access_token, $bigcommURL);
            if ($flagAccount === TRUE) {
                echo '<br>';
                echo 'New Record Inserted in the Zoho Customer And Contacts.';
                echo '<br>';
            } else {
                echo '<br>';
                echo 'New Record Insertion Failed in the Zoho Customer And Contacts.';
                echo '<br>';
            }
            echo '<br>';
            echo 'Step 2 :';
            //Call Product Information from Bigcommerce.           
            $flagProduct = j_countProducts($zohoAuthtoken, $u_access_token, $bigcommURL);
            if ($flagProduct === TRUE) {
                echo '<br>';
                echo 'New Record Inserted in the Zoho Product.';
            } else {
                echo '<br>';
                echo 'New Record Insertion Failed in the Zoho Product.';
            }
            //Call Oreder Information from Bigcommerce.            
            $flagOrder = j_countOrders($zohoAuthtoken, $u_access_token, $bigcommURL);
            if ($flagOrder === TRUE) {
                echo '<br>';
                echo 'New Record Inserted in the Zoho Invoice.';
            } else {
                echo '<br>';
                echo 'New Record Insertion Failed in the Invoice.';
            }
        } else {
            echo 'Record not found.';
        }
    } catch (Exception $ex) {
        echo 'error :' . $ex;
    }
} else {
    echo 'User not found..';
}

//Bigcommerce Customer address details
//ADDED ON 22/08/2016 //Bigcommerce Customer  details
function j_countAccounts($zohoAuthtoken, $access_token1, $bigcommURL) {
    //
    $min_date_created = $_SESSION['min_date_created'];
    $max_date_created = $_SESSION['max_date_created'];
    //    
    $bigcommurl = $bigcommURL . "/v2/";
    $access_token = $access_token1;
    $url = $bigcommurl . "customers/count.json";
    echo '<br>';
    echo 'Inside Count.Account..';
    echo '<br>';
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
            j_createAccount($page, $limit, $bigcommURL, $zohoAuthtoken, $access_token);
        } else {
            //countProducts();
        }
    } else {
        $error = curl_error($ch);
        addErrors($error);
    }
}

function j_createAccount($page, $limit, $bigcommURL1, $zohoAuthtoken1, $access_token1) {
    //
    $min_date_created = $_SESSION['min_date_created'];
    $max_date_created = $_SESSION['max_date_created'];
    //    
    $bigcommurl = $bigcommURL1 . "/v2/";
    $access_token = $access_token1;
    //
    for ($i = 1; $i <= $page; $i++) {
        //$url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit;
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

                /* inserting accounts in sfdc */
                $retVal = FALSE;
                $retVal = j_InsertIntoZohoContact($customers[$key], $zohoAuthtoken1);
                if ($retVal == TRUE) {
                    $retVal == TRUE;
                } else {
                    $retVal == FALSE;
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }
    
}

//Insert Into Bigcommerce to Zoho Contacts
function j_InsertIntoZohoContact($contactDetailsCon, $auth) {
    //Constarct The Data to Insert Zoho Contact Details.
    $firstName = $contactDetailsCon['first_name'];
    $lastName = $contactDetailsCon['last_name'];
    $accountName = $contactDetailsCon['company'];
    $email = $contactDetailsCon['email'];
    $phone = $contactDetailsCon['phone'];
    if ($contactDetailsCon['ret_address']) {
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
        $query = "authtoken=" . $auth . "&scope=crmapi&duplicateCheck=1&version=4&xmlData=" . $xml;

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
            echo 'Record Added Successfully';
            echo '<br>';
            echo '=========================================';
            //Update counter
            $flag = TRUE;
        } elseif (strpos($response, '2001') !== false) {
            echo 'Record Updated Successfully';
            echo '<br>';
            echo '=========================================';
            $flag = FALSE;
        } elseif (strpos($response, '2002') !== false) {
            echo 'Record Already Exists';
            echo '<br>';
            echo '=========================================';
            $flag = FALSE;
        }
        //$flag = TRUE;
    } catch (Exception $e) {
        echo '<pre>';
        print_r($e);
        $flag = FALSE;
    }
    return $flag;
}

//ADDED on 22/08/2016
function j_countProducts($zohoAuthtoken, $access_token1, $bigcommURL) {
    $u_user_context = $_SESSION['contextValue'];
    $min_date_created = $_SESSION['min_date_created'];
    $max_date_created = $_SESSION['max_date_created'];
    //
    $bigcommurl = $bigcommURL . "/v2/";
    $access_token = $access_token1;
    //$url = $bigcommurl . "products/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
    $url = $bigcommurl . "products/count.json";
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
            j_createProducts($page, $limit, $zohoAuthtoken, $access_token, $bigcommURL);
        } else {
            //countOrders();
        }
    } else {
        $error = curl_error($ch);
        addErrors($error);
    }
}

function j_createProducts($page, $limit, $zohoAuthtoken, $access_token1, $bigcommURL1) {

    $min_date_created = $_SESSION['min_date_created'];
    $max_date_created = $_SESSION['max_date_created'];
    //    
    $bigcommurl = $bigcommURL1 . "/v2/";
    $access_token = $access_token1;
    //
    for ($i = 1; $i <= $page; $i++) {
        //$url = $bigcommurl . "products.json?page=" . $i . "&limit=" . $limit . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $url = $bigcommurl . "products.json?page=" . $i . "&limit=" . $limit;
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
                $resultVal = j_InsertProductToZohoProduct($zohoAuthtoken, $products[$key]);
                if ($resultVal == TRUE) {
                    echo 'Product Inserted successfully....';
                } else {
                    echo 'Product Insertion failed....';
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }   
}

//Insert Product Information From Bigcommerce to Zoho Product
function j_InsertProductToZohoProduct($auth, $recordSet) {
    $flag = FALSE;
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
        $query = "authtoken=" . $auth . "&scope=crmapi&duplicateCheck=1&version=4&xmlData=" . $xml;

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
            echo 'Record Added Successfully';
            echo '<br>';
            echo '=========================================';
            //Update counter
            $flag = TRUE;
        } elseif (strpos($response, '2001') !== false) {
            //echo 'Record Updated Successfully';
            //echo '<br>';
            //echo '=========================================';
            $flag = FALSE;
        } elseif (strpos($response, '2002') !== false) {
            //echo 'Record Already Exists';
            //echo '<br>';
            //echo '=========================================';
            $flag = FALSE;
        }

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
                $query = "authtoken=" . $auth . "&scope=crmapi&duplicateCheck=1&version=4&xmlData=" . $xml;

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
                    echo 'Record Added Successfully';
                    echo '<br>';
                    echo '=========================================';
                    //Update counter
                    $flag = TRUE;
                } elseif (strpos($response, '2001') !== false) {
                    //echo 'Record Updated Successfully';
                    //echo '<br>';
                    //echo '=========================================';
                    $flag = FALSE;
                } elseif (strpos($response, '2002') !== false) {
                    //echo 'Record Already Exists';
                    //echo '<br>';
                    //echo '=========================================';
                    $flag = FALSE;
                }
            }
        }
    } catch (Exception $e) {
        echo '<pre>';
        print_r($e);
        $flag = FALSE;
    }
    return $flag;
}

function j_InsertOrderToZohoInvoice($accountDetails, $auth) {
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
    //
    //
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
        $query = "authtoken=" . $auth . "&scope=crmapi&duplicateCheck=1&version=4&xmlData=" . $xml;

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
            echo 'Record Added Successfully';
            echo '<br>';
            echo '=========================================';
            //Update counter
            $flag = TRUE;
        } elseif (strpos($response, '2001') !== false) {
            echo 'Record Updated Successfully';
            echo '<br>';
            echo '=========================================';
            $flag = FALSE;
        } elseif (strpos($response, '2002') !== false) {
            echo 'Record Already Exists';
            echo '<br>';
            echo '=========================================';
            $flag = FALSE;
        }
    } catch (Exception $e) {
        echo '<pre>';
        print_r($e);
        $flag = FALSE;
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
        CURLOPT_HTTPHEADER => array(
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        //echo $response;
        $res = json_decode($response, TRUE);
//        echo '<pre>';
//        print_r($res);
        $productId = $res['response']['result']['Products']['row']['FL'][0]['content'];
    }
    return $productId;
}

//
function j_countOrders($zohoAuthtoken, $access_token, $bigcommurl) {
    $url = $bigcommurl . "/v2/orders/count.json";
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
        j_createOrders($page, $limit, $zohoAuthtoken, $access_token, $bigcommurl);
    } else {
        $error = curl_error($ch);
        addErrors($error);
    }
}

function j_createOrders($page, $limit, $zohoAuthtoken, $access_token, $bigcommurl) {
    for ($i = 1; $i <= $page; $i++) {
        $url = $bigcommurl . "/v2/orders.json?page=" . $i . "&limit=" . $limit . "&is_deleted=false";
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
                $flagVal = j_InsertOrderToZohoInvoice($orders[$key], $zohoAuthtoken);
            }
            //echo '<pre>';
            //print_r($orders);
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }
}
function addErrors($msg) {
    echo $msg;
    die();
}
