<?php
/*
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
session_start();
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
        echo 'USER NAME : '.$uID;
        echo '<br>';
        echo 'CRM TYPE : '.$crmType;
        //Get user information from database.
        //Set the user parameters.
        $userData = $condID . $uID . ' AND crm_type=' . $crmType;
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
            } 
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
    $url = "https://api.bigcommerce.com/stores/t0w5eu/v2/customers";
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "X-Auth-Client:$appAuthClientID",
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
      } /
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
      } /
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
            /* set url to send post request 
            curl_setopt($ch, CURLOPT_URL, $url);
            /* allow redirects 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            /* return a response into a variable 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            /* times out after 30s 
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            /* set POST method 
            curl_setopt($ch, CURLOPT_POST, 1);
            /* add POST fields parameters 
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
    }*/
    ?>
<!DOCTYPE html>
<html>
        <style>
            body {font-family: "Lato", sans-serif; width:60%}

            ul.tab {
                list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                border: 1px solid #ccc;
                background-color: #f1f1f1;
            }

            /* Float the list items side by side */
            ul.tab li {float: left;}

            /* Style the links inside the list items */
            ul.tab li a {
                display: inline-block;
                color: black;
                text-align: center;
                padding: 14px 16px;
                text-decoration: none;
                transition: 0.3s;
                font-size: 17px;
            }

            /* Change background color of links on hover */
            ul.tab li a:hover {
                background-color: #ddd;
            }

            /* Create an active/current tablink class */
            ul.tab li a:focus, .active {
                background-color: #ccc;
            }

            /* Style the tab content */
            .tabcontent {
                display: none;
                padding: 6px 12px;
                border: 1px solid #ccc;
                border-top: none;
            }
        </style>
        <body>

            <p>Welcome to Zoho Connector : </p>
            <ul class="tab">
                <li><a href="zoho-accounts.php" class="tablinks" >Customers</a></li>
                <li><a href="zoho-payment-methods.php" class="tablinks" >Payment Methods</a></li>
                <li><a href="zoho-orders.php" class="tablinks" >Orders</a></li>
                <li><a href="zoho-products.php" class="tablinks" >Products</a></li>
                <li><a href="zoho-invoices.php" class="tablinks" >Invoices</a></li>
            </ul>

           <!-- <ul class="tab">
                <li><a href="#" class="tablinks" onclick="openCity(event, 'Overview')">Overview</a></li>
                <li><a href="#" class="tablinks" onclick="openCity(event, 'Sync_Time')">Sync Time</a></li>
                <li><a href="#" class="tablinks" onclick="openCity(event, 'Sync_Details')">Sync Details</a></li>
                <li><a href="#" class="tablinks" onclick="openCity(event, 'Sync_Errors')">Sync Errors</a></li>
            </ul> -->
            <div id="Overview" class="tabcontent">
                <h3><b>Quick and Easy 3 Steps to Set up you Connector</b></h3>
                <p>1.Connect your Zoho CRM &amp; BigCommerce</p>
                <p>2.Choose Sync Option</p>
                <p>3.Choose Sync Timing</p>

            </div>

            <div id="Sync_Time" class="tabcontent">
                <h3>Sync Time</h3>
                <p>Your Last on demand sync happened at 18:49(24hours format)Pacific Time on 02-Aug-2016</p>
            </div>

            <div id="Sync_Details" class="tabcontent">
                <h3>Sync Details</h3>
                <p>For the sync happened at 18:49(24hours format)Pacific Time on 02-Aug-2016</p>
                <table style="border: 1">
                    <th>Sl.No</th>
                    <th>Record</th>
                    <th>Status</th>
                    <tr>
                        <td>1.</td>
                        <td>Jhon Doe</td>
                        <td>Record added successfully</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Joe Do</td>
                        <td>Record added successfully</td>
                    </tr>
                </table>
            </div>

            <div id="Sync_Errors" class="tabcontent">
                <h3>Sync Errors</h3>
                <p>For the sync happened at 18:49(24hours format)Pacific Time on 02-Aug-2016</p>
                <table style="border: 1">
                    <th>Sl.No</th>
                    <th>Error</th>
                    <tr>
                        <td>1</td>
                        <td>KKKKKK</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>JJJJJ</td>
                    </tr>
                </table>
            </div>
            <script>
                function openCity(evt, syncName) {
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }
                    document.getElementById(syncName).style.display = "block";
                    evt.currentTarget.className += " active";
                }
            </script>
    </body>
</html>