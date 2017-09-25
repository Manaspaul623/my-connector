<?php

session_start();
require 'Sfdc.php';
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

function addErrors($msg) {
    echo $msg;
}

checkIfStandardPriceBook();

function checkIfStandardPriceBook() {
    $priceBookInfo = Sfdc::getPriceBookDetails();
    if ($priceBookInfo->Name === 'Standard Price Book') {
        $_SESSION['stdPriceBookId'] = $priceBookInfo->Id;
        countAccounts();
        //countProducts();
        //countOrders();
    } else {
        addErrors("Standard Price Book need to set");
    }
}

function countAccounts() {
    $u_user_context = $_SESSION['contextValue'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $access_token = $_SESSION['bigCommerceAccessToken'];
    $url = $bigcommurl . "customers/count.json";
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
        createAccount($page, $limit);
    } else {
        $error = curl_error($ch);
        addErrors($error);
    }
}

function createAccount($page, $limit) {
    $u_user_context = $_SESSION['contextValue'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $access_token = $_SESSION['bigCommerceAccessToken'];

    for ($i = 1; $i <= $page; $i++) {
        $url = $bigcommurl . "customers.json?page=" . $i . "&limit=" . $limit;
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
                $accoutRes = Sfdc::createSfdcAccount($accounts);
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }
    countProducts();
}

function countProducts() {
    $u_user_context = $_SESSION['contextValue'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $access_token = $_SESSION['bigCommerceAccessToken'];
    $url = $bigcommurl . "products/count.json";
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
        createProducts($page, $limit);
    } else {
        $error = curl_error($ch);
        addErrors($error);
    }
}

function createProducts($page, $limit) {
    $u_user_context = $_SESSION['contextValue'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $access_token = $_SESSION['bigCommerceAccessToken'];

    for ($i = 1; $i <= $page; $i++) {
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
                $accounts = array();

                $accounts[0] = new stdclass();
                $accounts[0]->Name = $value['name'];
                $accounts[0]->IsActive = TRUE;
                $accounts[0]->ProductCode = $value['sku'];
                $accounts[0]->Description = $value['description'];
                $accoutRes = Sfdc::createSfdcProducts($accounts);
                $insertedProductId = $accoutRes->id;

                $priceEntry = array();

                $priceEntry[0] = new stdclass();
                $priceEntry[0]->Pricebook2Id = $_SESSION['stdPriceBookId'];
                $priceEntry[0]->IsActive = TRUE;
                $priceEntry[0]->Product2Id = $insertedProductId;
                $priceEntry[0]->UnitPrice = $value['price'];
                $priceEntry[0]->UseStandardPrice = FALSE;

                $addedId = Sfdc::addToPriceBook($priceEntry);

                if (count($products[$key]['available_skus']) > 0) {
                    $available_skus = $products[$key]['available_skus'];
                    foreach ($available_skus as $k => $val) {
                        $accounts = array();
                        $accounts[0] = new stdclass();
                        $accounts[0]->Name = $value['name'];
                        $accounts[0]->IsActive = TRUE;
                        $accounts[0]->ProductCode = $val['sku'];
                        $accounts[0]->Description = $value['description'];
                        $accoutRes = Sfdc::createSfdcProducts($accounts);
                        $insertedProductId = $accoutRes->id;

                        $priceEntry = array();
                        $priceEntry[0] = new stdclass();
                        $priceEntry[0]->Pricebook2Id = $_SESSION['stdPriceBookId'];
                        $priceEntry[0]->IsActive = TRUE;
                        $priceEntry[0]->Product2Id = $insertedProductId;
                        $priceEntry[0]->UnitPrice = $val['adjusted_price'];
                        $priceEntry[0]->UseStandardPrice = FALSE;

                        $addedId = Sfdc::addToPriceBook($priceEntry);
                    }
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }
    countOrders();
}

function countOrders() {
    $u_user_context = $_SESSION['contextValue'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $access_token = $_SESSION['bigCommerceAccessToken'];

    $url = $bigcommurl . "orders/count.json";
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
        createOrders($page, $limit);
    } else {
        $error = curl_error($ch);
        addErrors($error);
    }
}

function createOrders($page, $limit) {
    $u_user_context = $_SESSION['contextValue'];
    $bigcommurl = "https://api.bigcommerce.com/$u_user_context/v2/";
    $access_token = $_SESSION['bigCommerceAccessToken'];

    for ($i = 1; $i <= $page; $i++) {
        $url = $bigcommurl . "orders.json?page=" . $i . "&limit=" . $limit . "&is_deleted=false";
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
                $accoutRes = Sfdc::sfdcQuery($sfdcQuery);
                $accountId = $accoutRes->Id;

                $accounts = array();

                $accounts[0] = new stdclass();
                $accounts[0]->AccountId = $accountId;
                $accounts[0]->Amount = $value['total_inc_tax'];
                $accounts[0]->Description = "Bigcommerce CRM order for  $customerEmail with name $customerName and order Id $bigcommerceOrderId";
                $accounts[0]->Name = "Bigcommerce CRM order for  $customerEmail ";
                $accounts[0]->Pricebook2Id = $_SESSION['stdPriceBookId'];
                $accounts[0]->CloseDate = date("Y-m-d", strtotime($value['date_modified']));
                $accounts[0]->StageName = 'Closed Won';

                $oopotunityDetails = Sfdc::createOpportunity($accounts);

                $opportinutyId = $oopotunityDetails->id;
                $orderProducts = $orders[$key]['ordered_products'];

                if (count($orderProducts) > 0) {
                    foreach ($orderProducts as $k => $val) {
                        $productCode = $val['sku'];
                        $priceBook2Id = $_SESSION['stdPriceBookId'];
                        $sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode='$productCode' ";
                        $quryRes = Sfdc::sfdcSelectQuery($sfdcQuery);
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

                            $accoutRes = Sfdc::addProductToOpportunity($priceEntryToOpp);
                        }
                    }
                }
            }
        } else {
            $error = curl_error($ch);
            addErrors($error);
        }
    }
    echo "Sync complete!";
}
