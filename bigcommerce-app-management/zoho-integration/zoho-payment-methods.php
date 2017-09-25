<?php
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

session_start();
//
echo 'Payment Methods...Page...';
echo '<br>';
//die();
//
$seesionFlag = FALSE;
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    if (isset($_SESSION['crmType']) && !empty($_SESSION['crmType'])) {
        $uID = $_SESSION['userID'];
        $crmType = $_SESSION['crmType'];
        $seesionFlag = TRUE;
    }
}
if ($seesionFlag == TRUE) {
    $flag = 0;
    $crmPlan = '';
    $planType = '';
    try {
        //Connect Database and open the connection.
        $connection_status = Connectdb();
        $open_status = Opendb();
        echo '<br>';
        echo 'USER ID : '.$uID;
        echo '<br>';
        echo 'CRM TYPE : '.$crmType; 
        echo '<br>';
        //Get user information from database.
        //Set the user parameters.
        $userData = $condID . $uID . ' AND crm_type LIKE '.'"'.$crmType.'"';
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
            //$planTypeData = $resVal[0]['category'];
            //
            if ($crmTypeData === 'SFDC') {
                $crmPlan = 'Salesforce';
            } else if ($crmTypeData === 'ZOHO') {
                $crmPlan = 'Zoho';
            } else {
                $crmPlan = 'NIL';
            }
            //
           /* if ($planTypeData === '1') {
                $planType = 'Starter';
            } else if ($planTypeData === '2') {
                $planType = 'Plus';
            } else if ($planTypeData === '3') {
                $planType = 'Pro';
            } else {
                $planType = 'NIL';
            } */
            //Call Index function 
            $flag = index($zohoAuthtoken, $u_access_token);
        } else {
            echo 'Record not found.';
        }
    } catch (Exception $ex) {
        echo 'error :' . $ex;
    }
} else {
    echo 'User not found..';
}

function index($zoho_auth_id, $access_token) {
    //
    $count = 0;
    $zoho_account_name = '';
    $zoho_phone = '';
    //
    $newElement = array();
    //
    $url = "https://api.bigcommerce.com/stores/t0w5eu/v2/payments/methods";
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
        $flag = 1;
        $msg = $return;
    } else {
        $error = curl_error($ch);
        $flag = 0;
        $msg = $error;
    }
    $arr = array(
        'flag' => $flag,
        'msg' => $msg
    );
    //Decode the result as array
    $arrResult = json_decode($return, TRUE);
    echo '<pre>';
    print_r($arrResult);
    die(0);

    //SEND the data to Zoho Account
    echo 'Record Inserted in the Zoho Account are :';
    echo '<br>';
    $flag_zoho =FALSE;
    foreach ($arrResult as $value):
        $zoho_account_name = $value['first_name'] . ' ' . $value['last_name'];
        $zoho_phone = $value['phone'];
        //$flag_zoho = insertDataToZohoAccount($zoho_account_name, $zoho_phone, $zoho_auth_id);
        if ($flag_zoho == TRUE) {
            //array_push($arr, $flag_zoho);
            $newElement[$count] = array(
                'account_name' => $zoho_account_name,
                'phone_no' => $zoho_phone
            );
            $count++;
        }
    endforeach;
    /* $insertData = insert($userTable, $userDataArr);
      if ($insertData > 0) {
      header("Location:installation-success.php");
      exit();
      } else {
      header("Location:installation-failed.php");
      exit();
      } */
    echo 'NEW ELEMENTS';
    echo '<br>';
    echo '<pre>';
    print_r($newElement);
    echo 'REVERSE NEW ELEMENTS';
    $revElement = array_reverse($newElement, TRUE);
    echo '<br>';
    echo '<pre>';
    print_r($revElement);
    echo '<br>';
    $cntElement = count($revElement);
    if ($cntElement >= 5) {
        $output = array_slice($revElement, 0, 4, TRUE);
    } else {
        $output = $revElement;
    }


    echo '<pre>';
    print_r($output);
    echo '<br>';
    //insert the data in the details table.
    foreach ($output as $val):
        $zoho_account_name = $value['first_name'] . ' ' . $value['last_name'];
        $zoho_phone = $value['phone'];
        //$flag_zoho = insertDataToZohoAccount($zoho_account_name, $zoho_phone, $zoho_auth_id);

    endforeach;
    /* $insertData = insert($userTable, $userDataArr);


      echo 'No. of New Record(s) :' . $count;
      return $flag;
      }

      /* function verifySignedRequest($signedRequest) {
      list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);
      // decode the data
      $signature = base64_decode($encodedSignature);
      $jsonStr = base64_decode($encodedData);
      $data = json_decode($jsonStr, true);
      // confirm the signature
      $expectedSignature = hash_hmac('sha256', $jsonStr, 'dibiccyowthdxx6hycffbg5ykv39yjc', $raw = false);
      if (!hash_equals($expectedSignature, $signature)) {
      error_log('Bad signed request from Bigcommerce!');
      return null;
      }
      return $data;
      } */
}
    function insertDataToZohoAccount($account_name, $phone, $auth) {
        //
        $flag = FALSE;
        try {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <Leads>
    <row no="1">
    <FL val="Account Name">' . $account_name . '</FL>
    <FL val="Phone">' . $phone . '</FL>
    </row>
    </Leads>';
            //For Insert Records with duplicate checking.
            $url = "https://crm.zoho.com/crm/private/xml/Accounts/insertRecords";
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
            //echo '<br>';
            //echo 'Account Name :' . $account_name;
            //echo '<br>';
            //echo 'Phone :' . $phone;
            //echo '<br>';

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
    ?>