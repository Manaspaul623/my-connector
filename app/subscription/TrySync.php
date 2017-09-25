<?php
//Session
session_start();
//error_reporting(0);

include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if (!isset($_SESSION['user_id']) || empty($_REQUEST['id'])) {

    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}
//user id collect

$user_id = $_SESSION['user_id']; //for logged in user id collect


include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include "$_SERVER[DOCUMENT_ROOT]/app/common-function.php"; //Common Function Include
//$max_date_created = date("Y-m-d") . "T" . $syncTimeTable;
$maxDay = date('Y-m-d', strtotime(date("Y-m-d") . "+0 days"));
$maxTime = date("H:i");
$max_date_created = $maxDay. "T" . $maxTime;

//$min_time = $syncTimeTable - 2;
$minDay = date('Y-m-d', strtotime(date("Y-m-d") . "+0 days"));
$minTime = date("H:i", time() - 3600);
$min_date_created = $minDay. "T" . $minTime;
//Credential Array Declare

$credentials = array(
    'userId' => $user_id,
    'userEmail' => 'manas.paul@aquaapi.com',
    'userName' => 'manas.paul@aquaapi.com',
	//'userEmail' => 'debashishp@gmail.com',
	//'userName' => 'debashishp@gmail.com',
    'max_date_created' => $max_date_created,
    'min_date_created' => $min_date_created,
    'app1Details' => array(),
    'app2Details' => array()
);


//Condition Crete
$row_id=$_REQUEST['id'];
$cond = " AND id='$row_id'";

$subscriptions = fetch($userSubscription,$cond); //All Subscription Collected



foreach ($subscriptions as $key=>$value)
{
    if($value['function_name'] != '')
    {
        $function = $value['function_name'];
        $credentials['subscription_id'] = $value['subscription_id'];

        switch ($function)
        {
            //Magento Core Operation
            case 'magentoTozoho' : {
                $magento_user_name = $value['app1_cred1'];
                $magento_password = $value['app1_cred2'];
                $magento_context = $value['app1_cred3'];
                $zoho_auth_id = $value['app2_cred1'];
                $app1Credentials = array(
                    'magento_user_name' => $magento_user_name,
                    'magento_password' => $magento_password,
                    'magento_context' => $magento_context
                );
                $app2Credentials = array(
                    'zoho_auth_id' => $zoho_auth_id
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                //j_index_bigcomm_joho($credentials);
                $return=magentoToZohoCRM($credentials);
                echo $return;
            }break; //done
            case 'magentoTosfdc' : {

                $magento_user_name = $value['app1_cred1'];
                $magento_password = $value['app1_cred2'];
                $magento_context = $value['app1_cred3'];
                $sfdc_user_name = $value['app2_cred1'];
                $sfdc_password = $value['app2_cred2'];
                $sfdc_security_password = $value['app2_cred3'];
                $app1Credentials = array(
                    'magento_user_name' => $magento_user_name,
                    'magento_password' => $magento_password,
                    'magento_context' => $magento_context
                );
                $app2Credentials = array(
                    'sfdc_user_name' => $sfdc_user_name,
                    'sfdc_password' => $sfdc_password,
                    'sfdc_security_password' => $sfdc_security_password
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


               $return=magentoToSfdcCRM($credentials);
                echo $return;
            }break; //done
            case 'magentoTovtiger' : {

                $magento_user_name = $value['app1_cred1'];
                $magento_password = $value['app1_cred2'];
                $magento_context = $value['app1_cred3'];
                $vtiger_endpoint = $value['app2_cred1'];
                $vtiger_username = $value['app2_cred2'];
                $vtiger_key = $value['app2_cred3'];
                $app1Credentials = array(
                    'magento_user_name' => $magento_user_name,
                    'magento_password' => $magento_password,
                    'magento_context' => $magento_context
                );

                $app2Credentials = array(
                    'vtiger_endpoint' => $vtiger_endpoint,
                    'vtiger_username' => $vtiger_username,
                    'vtiger_key' => $vtiger_key
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;

                $return = magentoToVTigerCRM($credentials);
                echo $return;
            }break; //done
            case 'magentoTohubspot' : {
                $magento_user_name = $value['app1_cred1'];
                $magento_password = $value['app1_cred2'];
                $magento_context = $value['app1_cred3'];
                $hubspot_refresh_token = $value['app2_cred1'];
                $hubspot_portal = $value['app2_cred2'];
                $hubspot_form = $value['app2_cred3'];
                $hubspot_last_order = $value['hub_order'];
                $app1Credentials = array(
                    'magento_user_name' => $magento_user_name,
                    'magento_password' => $magento_password,
                    'magento_context' => $magento_context
                );
                $app2Credentials = array(
                    'hubspot_refresh_token' => $hubspot_refresh_token,
                    'hubspot_portal_id' => $hubspot_portal,
                    'hubspot_form_id' => $hubspot_form,
                    'hubspot_last_order' => $hubspot_last_order
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                //j_index_bigcomm_joho($credentials);
                $return=magentoToHubspotCRM($credentials);
                echo $return;
            }break; //Done
            case 'magentoTozohoinventory' : {
                $magento_user_name = $value['app1_cred1'];
                $magento_password = $value['app1_cred2'];
                $magento_context = $value['app1_cred3'];
                $zoho_auth_id = $value['app2_cred1'];
                $zoho_organisation_id = $value['app2_cred2'];
                $app1Credentials = array(
                    'magento_user_name' => $magento_user_name,
                    'magento_password' => $magento_password,
                    'magento_context' => $magento_context
                );
                $app2Credentials = array(
                    'zoho_auth_id' => $zoho_auth_id,
                    'zoho_organisation_id' => $zoho_organisation_id
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                $return=magentoToZohoInventoryCRM($credentials);
                echo $return;
            }break; //done
            //Bigcommerce Core Operation
            case 'bigcommerceTosfdc' : {
                $bigcommerce_user_name = $value['app1_cred1'];
                $bigcommerce_password = $value['app1_cred2'];
                $bigcommerce_context = $value['app1_cred3'];
                $sfdc_user_name = $value['app2_cred1'];
                $sfdc_password = $value['app2_cred2'];
                $sfdc_security_password = $value['app2_cred3'];
                $app1Credentials = array(
                    'bigcommerce_user_name' => $bigcommerce_user_name,
                    'bigcommerce_password' => $bigcommerce_password,
                    'bigcommerce_context' => $bigcommerce_context
                );
                $app2Credentials = array(
                    'sfdc_user_name' => $sfdc_user_name,
                    'sfdc_password' => $sfdc_password,
                    'sfdc_security_password' => $sfdc_security_password
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


               $return=bigcommerceToSfdcCRM($credentials);
                echo $return;
            }break; //done
            case 'bigcommerceTozoho' : {
                $bigcommerce_user_name = $value['app1_cred1'];
                $bigcommerce_password = $value['app1_cred2'];
                $bigcommerce_context = $value['app1_cred3'];
                $zoho_auth_id = $value['app2_cred1'];
                $app1Credentials = array(
                    'bigcommerce_user_name' => $bigcommerce_user_name,
                    'bigcommerce_password' => $bigcommerce_password,
                    'bigcommerce_context' => $bigcommerce_context
                );
                $app2Credentials = array(
                    'zoho_auth_id' => $zoho_auth_id
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                $return=bigcommerceToZohoCRM($credentials);
                echo $return;
            }break; //done
            case 'bigcommerceTovtiger' : {
                $bigcommerce_user_name = $value['app1_cred1'];
                $bigcommerce_password = $value['app1_cred2'];
                $bigcommerce_context = $value['app1_cred3'];
                $vtiger_endpoint = $value['app2_cred1'];
                $vtiger_username = $value['app2_cred2'];
                $vtiger_key = $value['app2_cred3'];
                $app1Credentials = array(
                    'bigcommerce_user_name' => $bigcommerce_user_name,
                    'bigcommerce_password' => $bigcommerce_password,
                    'bigcommerce_context' => $bigcommerce_context
                );
                $app2Credentials = array(
                    'vtiger_endpoint' => $vtiger_endpoint,
                    'vtiger_username' => $vtiger_username,
                    'vtiger_key' => $vtiger_key
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                $return=bigcommerceToVTigerCRM($credentials);
                echo $return;
            }break; //done
            case 'bigcommerceTohubspot' : {
                $bigcommerce_user_name = $value['app1_cred1'];
                $bigcommerce_password = $value['app1_cred2'];
                $bigcommerce_context = $value['app1_cred3'];
                $hubspot_refresh_token = $value['app2_cred1'];
                $hubspot_portal = $value['app2_cred2'];
                $hubspot_form = $value['app2_cred3'];
                $hubspot_last_order = $value['hub_order'];
                $app1Credentials = array(
                    'bigcommerce_user_name' => $bigcommerce_user_name,
                    'bigcommerce_password' => $bigcommerce_password,
                    'bigcommerce_context' => $bigcommerce_context
                );
                $app2Credentials = array(
                    'hubspot_refresh_token' => $hubspot_refresh_token,
                    'hubspot_portal_id' => $hubspot_portal,
                    'hubspot_form_id' => $hubspot_form,
                    'hubspot_last_order' => $hubspot_last_order
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                $return=bigcommerceToHubspotCRM($credentials);
                echo $return;
            }break;//done
            case 'bigcommerceTozohoinventory' : {
                $bigcommerce_user_name = $value['app1_cred1'];
                $bigcommerce_password = $value['app1_cred2'];
                $bigcommerce_context = $value['app1_cred3'];
                $zoho_auth_id = $value['app2_cred1'];
                $zoho_organisation_id = $value['app2_cred2'];
                $app1Credentials = array(
                    'bigcommerce_user_name' => $bigcommerce_user_name,
                    'bigcommerce_password' => $bigcommerce_password,
                    'bigcommerce_context' => $bigcommerce_context
                );
                $app2Credentials = array(
                    'zoho_auth_id' => $zoho_auth_id,
                    'zoho_organisation_id' => $zoho_organisation_id
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                $return=bigcommerceToZohoInventoryCRM($credentials);
                echo $return;
            }break; //done
            //Shopify Core Operation
            case 'shopifyTohubspot' : {
                $shopify_url = $value['app1_cred1'];
                $shopify_api= $value['app1_cred2'];
                $shopify_password = $value['app1_cred3'];

                $hubspot_refresh_token = $value['app2_cred1'];
                $hubspot_portal = $value['app2_cred2'];
                $hubspot_form = $value['app2_cred3'];
                $hubspot_last_order = $value['hub_order'];
                $app1Credentials = array(
                    'shopify_url' => $shopify_url,
                    'shopify_api' => $shopify_api,
                    'shopify_password' => $shopify_password
                );
                $app2Credentials = array(
                    'hubspot_refresh_token' => $hubspot_refresh_token,
                    'hubspot_portal_id' => $hubspot_portal,
                    'hubspot_form_id' => $hubspot_form,
                    'hubspot_last_order' => $hubspot_last_order
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                //j_index_bigcomm_joho($credentials);
                $return=shopifyToHubspotCRM($credentials);
                echo $return;
            }break; //Done
            case 'shopifyTozoho' : {
                $shopify_url = $value['app1_cred1'];
                $shopify_api= $value['app1_cred2'];
                $shopify_password = $value['app1_cred3'];

                $zoho_auth_id = $value['app2_cred1'];
                $app1Credentials = array(
                    'shopify_url' => $shopify_url,
                    'shopify_api' => $shopify_api,
                    'shopify_password' => $shopify_password
                );
                $app2Credentials = array(
                    'zoho_auth_id' => $zoho_auth_id
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                //j_index_bigcomm_joho($credentials);
                $return=shopifyToZohoCRM($credentials);
                echo $return;
            }break; //Done
            case 'shopifyTozohoinventory' : {
                $shopify_url = $value['app1_cred1'];
                $shopify_api= $value['app1_cred2'];
                $shopify_password = $value['app1_cred3'];
                $zoho_auth_id = $value['app2_cred1'];
                $zoho_organisation_id = $value['app2_cred2'];
                $app1Credentials = array(
                    'shopify_url' => $shopify_url,
                    'shopify_api' => $shopify_api,
                    'shopify_password' => $shopify_password
                );
                $app2Credentials = array(
                    'zoho_auth_id' => $zoho_auth_id,
                    'zoho_organisation_id' => $zoho_organisation_id
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;


                //j_index_bigcomm_joho($credentials);
                $return=shopifyToZohoInventoryCRM($credentials);
                echo $return;
            }break; //Done
            case 'shopifyTovtiger' : {

                $shopify_url = $value['app1_cred1'];
                $shopify_api= $value['app1_cred2'];
                $shopify_password = $value['app1_cred3'];

                $vtiger_endpoint = $value['app2_cred1'];
                $vtiger_username = $value['app2_cred2'];
                $vtiger_key = $value['app2_cred3'];
                $app1Credentials = array(
                    'shopify_url' => $shopify_url,
                    'shopify_api' => $shopify_api,
                    'shopify_password' => $shopify_password
                );

                $app2Credentials = array(
                    'vtiger_endpoint' => $vtiger_endpoint,
                    'vtiger_username' => $vtiger_username,
                    'vtiger_key' => $vtiger_key
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;

                $return = shopifyToVTigerCRM($credentials);
                echo $return;
            }break; //Done
            case 'shopifyTosfdc' : {

                $shopify_url = $value['app1_cred1'];
                $shopify_api= $value['app1_cred2'];
                $shopify_password = $value['app1_cred3'];

                $sfdc_user_name = $value['app2_cred1'];
                $sfdc_password = $value['app2_cred2'];
                $sfdc_security_password = $value['app2_cred3'];

                $app1Credentials = array(
                    'shopify_url' => $shopify_url,
                    'shopify_api' => $shopify_api,
                    'shopify_password' => $shopify_password
                );

                $app2Credentials = array(
                    'sfdc_user_name' => $sfdc_user_name,
                    'sfdc_password' => $sfdc_password,
                    'sfdc_security_password' => $sfdc_security_password
                );
                $credentials['app1Details'] = $app1Credentials;
                $credentials['app2Details'] = $app2Credentials;

                $return = shopifyToSfdcCRM($credentials);
                echo $return;
            }break; //Done
            default :
                addErrors('no action specified');
        }
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

