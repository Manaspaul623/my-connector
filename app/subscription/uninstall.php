<?php
/* including database related files */
include "/var/www/html/bigcommerce-app-management/mysql/mysqlconstants.php";
include "/var/www/html/bigcommerce-app-management/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

//
$flag = FALSE;
$flagUser = FALSE;
$flagSchedule = FALSE;
//


if (isset($_REQUEST['signed_payload'])) {
    //Get the signed payload data
    $payLoad = $_REQUEST['signed_payload'];
    $retValSigned = verifySignedRequest($payLoad);
    //
    //Get The User data
    $payLoadUserId = $retValSigned['user']['id'];
    $payLoadUserEmail = $retValSigned['user']['email'];
    $payLoadOwnerId = $retValSigned['owner']['id'];
    $payLoadOwnerEmail = $retValSigned['owner']['email'];
    $payLoadContext = $retValSigned['context'];
    $payLoadStoreHash = $retValSigned['store_hash'];
    $payLoadAddedOn = $retValSigned['timestamp'];
    
   // error_log("Called Uninstall Routine 1. Payload: ". $payLoadUserId. " ".$payLoad. " ", 1, "debashishp@gmail.com");
    //Get user id on the basis of user id in the signed payload.
    if ($payLoadUserId > 0) {
        try {
            //Connect Database and open the connection.
            $connection_status = Connectdb();
            $open_status = Opendb();
            //
            $dataArr = array(
                'user_id' => $payLoadUserId,
                'user_email' => $payLoadUserEmail,
                'owner_id' => $payLoadOwnerId,
                'owner_email' => $payLoadOwnerEmail,
                'context' => $payLoadContext,
                'store_hash' => $payLoadStoreHash,
                'added_on' => $payLoadAddedOn
            );
            
            $res = insert($tblUninstall, $dataArr);
            if ($res > 0) {
                $flag = TRUE;
            } else {
                $flag = FALSE;
            }
            // error_log("Called Uninstall Routine 3. Flag: ". $flag, 1, "debashishp@gmail.com");
            //Set the user parameters.
            //Connect Database and open the connection.
            $connection_status = Connectdb();
            $open_status = Opendb();
            $condData = " AND user_id = $payLoadUserId";
            $resVal = fetch($userTable, $condData);
            //
            $totCount = count($resVal);
            if ($totCount > 0) {
                $chkUID = $resVal[0]['user_id'];
                $zoho_subscription_id = $resVal[0]['zoho_subscription_id'];
                // error_log("Called Uninstall Routine 2", 1, "debashishp@gmail.com");
                //unsubscribe from zoho
                // include 'config.php';
                $url = $subscriptionUrl . '/subscriptions/' . $zoho_subscription_id . '/cancel?cancel_at_end=true';

                $headers = array(
                    "Content-Type: application/json;charset=UTF-8 ",
                    "authorization: Zoho-authtoken " . SUBSCRIPTION_AUTHORIZATION_CODE,
                    "cache-control: no-cache",
                    "x-com-zoho-subscriptions-organizationid: " . ORGANIZATION_ID
                );
                $data_arr = array();
                $data_string = json_encode($data_arr);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                $return = curl_exec($ch);
                $res = json_decode($return, TRUE);

                //Modify the Tables                    
                //Delete Record from User Table. 

                //Connect Database and open the connection.
                $connection_status = Connectdb();
                $open_status = Opendb();
                      
                $condUser = " AND user_id = $payLoadUserId";
                $resUser = delete($userTable, $condUser);
                // error_log("Called Uninstall Routine 3. No. of rows =".$resUser, 1, "debashishp@gmail.com");
                if ($resUser > 0) {
                    $flagUser = TRUE;
                } else {
                    $flagUser = FALSE;
                }
                    
                //Delete Record from Sync time Schedue table. 
                $connection_status = Connectdb();
                $open_status = Opendb();
                $condSchedule = " AND user_id = $payLoadUserId";
                $resSchedule = delete($syncTimeSchedule, $condSchedule);
                // error_log("Called Uninstall Routine 4 No. of rows =".$resSchedule, 1, "debashishp@gmail.com");
                if ($resSchedule > 0) {
                    $flagSchedule = TRUE;
                } else {
                    $flagSchedule = FALSE;
                }

                return true;
            } else {
                error_log("Uninstall didn't work. Record not found.", 1, "debashish@aquaapi.com");
            }
        } catch (Exception $ex) {
            echo 'errors :' . $ex;
        }
    }
}
//Verify the Signed data
function verifySignedRequest($signedRequest) {
    list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);
    // decode the data
    $signature = base64_decode($encodedSignature);
    $jsonStr = base64_decode($encodedData);
    $data = json_decode($jsonStr, true);
    // confirm the signature
    $expectedSignature = hash_hmac('sha256', $jsonStr, APP_AUTH_CLIENT_SECRET, $raw = false);
    if (!hash_equals($expectedSignature, $signature)) {
        error_log('Bad signed request from Bigcommerce!. Uninstall Error for Connector', 1, "debashishp@gmail.com");
        return null;
    }
    return $data;
}
