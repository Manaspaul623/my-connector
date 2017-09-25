<?php

include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");


include "$_SERVER[DOCUMENT_ROOT]/app/common-function-demo.php"; //Common Function Include
//$max_date_created = date("Y-m-d") . "T" . $syncTimeTable;
$lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "+0 days"));
$maxTime = date("H:i");
$max_date_created = $lastDay. "T" . $maxTime;
//print_r("Max Date: ".$max_date_created);

//$min_time = $syncTimeTable - 2;
$lastDay = date('Y-m-d', strtotime(date("Y-m-d") . "+0 days"));
$minTime = date("H:i", time() - 3600);
$min_date_created = $lastDay. "T" . $minTime;
//print_r("<br>Min Date: ".$min_date_created);
//exit(1);
$credentials = array(
    'userEmail' => 'manas.paul@aquaapi.com',
    'userName' => 'manas.paul@aquaapi.com',
    'max_date_created' => $max_date_created,
    'min_date_created' => $min_date_created,
    'app1Details' => array(),
    'app2Details' => array()
);
$cond = " AND subscription_id = '446888000000285201'";
$subscriptions = fetch($userSubscriptionFinal,$cond); //All Subscription Collected

foreach ($subscriptions as $key=>$value)
{
  if($value['function_name'] != '')
  {
      $function = $value['function_name'];
      $credentials['userId'] = $value['user_id'];
      $credentials['subscription_id'] = $value['subscription_id'];
      //exit(1);



      switch ($function)
      {
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
               magentoToZohoCRM($credentials);
          }break;
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

              magentoToSfdcCRM($credentials);

          }break;
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

              magentoToVTigerCRM($credentials);

          }break;
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


              bigcommerceToSfdcCRM($credentials);

          }break;
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


              bigcommerceToZohoCRM($credentials);

          }break;
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


              bigcommerceToVTigerCRM($credentials);

          }break;
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


