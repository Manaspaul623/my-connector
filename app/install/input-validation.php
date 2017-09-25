<?php
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if ($_REQUEST['type']) {
    dispatcher($_REQUEST['type']);
}

function dispatcher($type)
{
    switch ($type) {
        case 'validateZohoZuthToken' :
            validateZohoZuthToken();
            break;
        case 'validateSfdcInfo' :
            validateSfdcInfo();
            break;
        case 'validateZohoInvenZuthToken' :
            validateZohoInvenZuthToken();
            break;
        case 'validateVtigerToken' :
            validateVtigerToken();
            break;
        case 'validateMagentoToken' :
            validateMagentoToken();
            break;
        case 'validateBigcommerceToken' :
            validateBigcommerceToken();
            break;
        case 'validateShopifyToken' :
            validateShopifyToken();
            break;
        case 'validatePrestashopToken' :
            validatePrestashopToken();
            break;
        default :
            addErrors('no action specified');
    }
}

function addErrors($msg)
{
    $arr = array(
        'flag' => 0,
        'msg' => $msg
    );
    echo json_encode($arr);
}

function validateZohoZuthToken()
{
    $authType = $_REQUEST['authType'];
    if ($authType == 'api') {
        $zohoAuthToken = $_REQUEST['zohoAuthToken'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://crm.zoho.com/crm/private/json/Accounts/getRecords?authtoken=" . $zohoAuthToken . "&scope=crmapi",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $response_arr = json_decode($response, true);
        curl_close($curl);
        if (array_key_exists('error', $response_arr['response'])) {
            echo "false";
            echo '<pre>';
            print_r($response_arr);
        } else {
            echo "true";
        }
    } else {
        $zohoUserName = $_REQUEST['zohoUserName'];
        $zohoPassword = $_REQUEST['zohoPassword'];
        //$zohoInvenOrgID = $_REQUEST['zohoInvenOrgID'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://accounts.zoho.com/apiauthtoken/nb/create?SCOPE=ZohoCRM/crmapi&EMAIL_ID=" . $zohoUserName . "&PASSWORD=" . $zohoPassword,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST"
        ));
        $response = curl_exec($curl);
        if (strpos($response, 'AUTHTOKEN=') !== false) {
            $resultToken = explode('AUTHTOKEN=', $response);
            $final = explode("RESULT=TRUE", $resultToken[1]);
            $authToken = trim($final[0]);

            echo $authToken;

        } else {
            echo 'false';
        }
    }
}

function validateSfdcInfo()
{
    include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/sfdc-integration/Sfdc.php";
    // require 'Sfdc.php';

    $SfdcUsername = trim($_REQUEST['sfdc_user_name']);
    $SfdcPassword = trim($_REQUEST['sfdc_password']);
    $SfdcSecurityToken = trim($_REQUEST['sfdc_security_password']);

    $sfdcRes = Sfdc::checkCredentials($SfdcPassword, $SfdcUsername, $SfdcSecurityToken);
    echo json_encode($sfdcRes);
}

function validateZohoInvenZuthToken()
{

    $authType = $_REQUEST['authType'];
    if ($authType == 'api') {
        $zohoInvenAuthToken = $_REQUEST['zohoInvenAuthToken'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://inventory.zoho.com/api/v1/organizations?authtoken=" . $zohoInvenAuthToken,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $res = curl_exec($curl);
        $response_arr = json_decode($res, true);
        curl_close($curl);
        if ($response_arr['message'] == 'success') {
            $count_org = count($response_arr['organizations']);
            echo '<label>Select Organization Id</label>
                        <input type="hidden" id="zohoInventoryAuthToken" value="' . $zohoInvenAuthToken . '">
                        <select id="zohoInventoryOrganizationId" required>
                        <option value="">Select Here</option>';
            for ($i = 0; $i < $count_org; $i++) {
                echo '<option value="' . $response_arr['organizations'][$i]['organization_id'] . '">' . $response_arr['organizations'][$i]['organization_id'] . '</option>';
            }
            echo '</select>';

        } else {
            echo 'false';
        }
    } else {
        $zohoInvenUserName = $_REQUEST['zohoInvenUserName'];
        $zohoInvenPassword = $_REQUEST['zohoInvenPassword'];
        //$zohoInvenOrgID = $_REQUEST['zohoInvenOrgID'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://accounts.zoho.com/apiauthtoken/nb/create?SCOPE=ZohoInventory/inventoryapi&EMAIL_ID=" . $zohoInvenUserName . "&PASSWORD=" . $zohoInvenPassword,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST"
        ));
        $response = curl_exec($curl);
        if (strpos($response, 'AUTHTOKEN=') !== false) {
            $resultToken = explode('AUTHTOKEN=', $response);
            $final = explode("RESULT=TRUE", $resultToken[1]);
            $authToken = trim($final[0]);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://inventory.zoho.com/api/v1/organizations?authtoken=" . $authToken,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache"
                ),
            ));
            $res = curl_exec($curl);
            $response_arr = json_decode($res, true);
            curl_close($curl);
            if ($response_arr['message'] == 'success') {
                $count_org = count($response_arr['organizations']);
                echo '<label>Select Organization Id</label>
                        <input type="hidden" id="zohoInventoryAuthToken" value="' . $authToken . '">
                        <select id="zohoInventoryOrganizationId" required>
                        <option value="">Select Here</option>';
                for ($i = 0; $i < $count_org; $i++) {
                    echo '<option value="' . $response_arr['organizations'][$i]['organization_id'] . '">' . $response_arr['organizations'][$i]['organization_id'] . '</option>';
                }
                echo '</select>';

            } else {
                echo 'false';
            }
        } else {
            echo 'false';
        }


        //print_r($response_arr);
        /* if ($response_arr['message'] == 'success') {
             echo "true";
         } else {
             echo "false";
         } */
    }

}

function validateVtigerToken()
{

    $vtigerEndpoint = trim($_REQUEST['vtigerEndpoint']);
    $vtigerUsername = trim($_REQUEST['vtigerUsername']);
    $vtigerAccessKey = trim($_REQUEST['vtigerAccessKey']);
    if (substr($vtigerEndpoint, -1) == '/') {
        // remove '/'
        $vtigerEndpoint = substr($vtigerEndpoint, 0, -1);
    }


    // Get vTiger access token
    $url = $vtigerEndpoint . "/webservice.php?operation=getchallenge&username=" . $vtigerUsername;
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "cache-control : no-cache"
    );
    $ch = curl_init();
    //error_log("URL ".$url, 1, "support@aquaapi.com");

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    if ($http_code === 200) {
        //error_log($return, 1, "support@aquaapi.com");
        curl_close($ch);
        $vTigerAuth = json_decode($return, TRUE);
        $vTigerAuthToken = $vTigerAuth['result']['token'];
        $url = $vtigerEndpoint . "/webservice.php";
        $generatedKey = md5($vTigerAuthToken . $vtigerAccessKey);
        $postfields = array('operation' => 'login', 'username' => $vtigerUsername,
            'accessKey' => $generatedKey);
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
        //error_log($url." ".$vTigerAuth." ".$vtigerAccessKey." ".$http_code."Credentials: ".$return, 1, "support@aquaapi.com");
        //curl_close($ch);
        if ($http_code === 200) {
            curl_close($ch);
            $vTigerLogin = json_decode($return, TRUE);
            if ($vTigerLogin['success'] == TRUE) {
                //error_log("Success", 1, "support@aquaapi.com");
                echo "true";
                //curl_close($ch);
                return;
            } else {
                //curl_close($ch);
                echo "false";
                return;
            }
        } else {
            curl_close($ch);
            echo "false";
            return;
        }
    } else {
        curl_close($ch);
        echo "false";
        return;
    }
}


function validateMagentoToken()
{

    $magentoEndpoint = trim($_REQUEST['magentoEndpoint']);
    $magentoUsername = trim($_REQUEST['magentoUsername']);
    $magentoAccessKey = trim($_REQUEST['magentoAccessKey']);
    if (substr($magentoEndpoint, -1) == '/') {
        // remove '/'
        $magentoEndpoint = substr($magentoEndpoint, 0, -1);
    }

    $url = $magentoEndpoint . "/rest/V1/integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postFields = array('username' => $magentoUsername,
        'password' => $magentoAccessKey);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //var_dump($return);
    curl_close($ch);

    $magentoToken = json_decode($return, TRUE);
    if ($http_code === 200) {


        if (!empty($magentoToken)) {
            echo 'true';
            return;
        } else {
            echo 'false';
            return;
        }

    } else {
        echo $url . '<br>';
        echo $magentoUsername . '<br>';
        echo $magentoAccessKey . '<br>';
        echo $http_code . '<br>';
        var_dump($magentoToken);
    }
}

function validateShopifyToken()
{

    $shopifyUrl = trim($_REQUEST['shopifyUrl']);
    $shopifyApikey = trim($_REQUEST['shopifyApikey']);
    $shopifyPassword = trim($_REQUEST['shopifyPassword']);
    if (substr($shopifyUrl, -1) == '/') {
        // remove '/'
        $shopifyUrl = substr($shopifyUrl, 0, -1);
    }


    //Customer Details collect from BigCommerce order
    $customer_url = "https://" . $shopifyApikey . ":" . $shopifyPassword . "@" . $shopifyUrl . "/admin/orders.json";
    //echo $customer_url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $customer_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code === 200) {
        echo 'true';
        return;
    } else {
        echo 'false';
        return;
    }
}

function validatePrestashopToken()
{

    $prestashopUrl = trim($_REQUEST['prestashopUrl']);
    $prestashopApikey = trim($_REQUEST['prestashopApikey']);
    if (substr($prestashopUrl, -1) == '/') {
        // remove '/'
        $prestashopUrl = substr($prestashopUrl, 0, -1);
    }

    if (strpos($prestashopUrl, 'http://') !== false) {
        $url = explode("http://",$prestashopUrl);
        $url = 'http://'.$prestashopApikey."@".$url[1]."/api";
    } else if(strpos($prestashopUrl, 'https://') !== false){
        $url = explode("https://",$prestashopUrl);
        $url = 'https://'.$prestashopApikey."@".$url[1]."/api";
    } else{
        $url = 'http://'.$prestashopApikey."@".$prestashopUrl."/api";
    }


    //Customer Details collect from BigCommerce order
    $order_url = $url . "/customers";
    //echo $customer_url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $order_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code === 200) {
        echo 'true';
        return;
    } else {
        echo 'false';
        return;
    }
}

function validateBigcommerceToken()
{

    $bigcommerceEndpoint = trim($_REQUEST['bigcommerceEndpoint']);
    $bigcommerceUsername = trim($_REQUEST['bigcommerceUsername']);
    $bigcommerceAccessKey = trim($_REQUEST['bigcommerceAccessKey']);
    if (substr($bigcommerceEndpoint, -1) == '/') {
        // remove '/'
        $bigcommerceEndpoint = substr($bigcommerceEndpoint, 0, -1);
    }

    $auth_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
    $url = $bigcommerceEndpoint . "/api/v2/";
    //error_log("Testing".$url." ".$bigcommerceUsername.$bigcommerceAccessKey, 1, "support@aquaapi.com");
    $http_headres = array(
        "Authorization: Basic " . $auth_token,
        "Accept: application/json",
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //var_dump($return);
    curl_close($ch);
    $bigcommerceToken = json_decode($return, TRUE);
    if ($http_code === 200) {
        if (!empty($bigcommerceToken)) {
            echo 'true';
            return;
        } else {
            echo 'false';
            return;
        }

    } else {
        echo $url . '<br>';
        echo $bigcommerceUsername . '<br>';
        echo $bigcommerceAccessKey . '<br>';
        echo $http_code . '<br>';
        //	var_dump($bigcommerceToken);
    }
}