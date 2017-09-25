<?php
$debug = 0;
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
/* database connection */
$db_conn = mysqli_connect(DATABASE_HOST, DATABASE_USER_NAME, DATABASE_PASSWORD, APP_DATABASE_NAME);

/* table name variable */
//$userTable = 'tbl_user';
//$scheduleTable = "tbl_schedule_time";
//$syncDetails = "tbl_sync_details";
//$syncError = "tbl_sync_error";
//$tempUser = "tbl_temp_user";
$userLogin = "tbl_user";
$userSubscription = "tbl_subscription";
$userSubscriptionFinal = "tbl_subscription_final";


/* ZOHO specific tables*/
//$zohoSyncContact = "tbl_sync_details_contact_zoho";
//$zohoSyncProduct = "tbl_sync_details_product_zoho";
//$zohoSyncOrder = "tbl_sync_details_order_zoho";
//$zohoSyncOrderProduct = "tbl_sync_order_product_zoho";
$zohoTransactionDetails = "tbl_transaction_details";
$zohoSyncErrorDetails = "tbl_sync_error";

/* SFDC specific tables */
$sfdcSyncContact = "tbl_sync_details_account_sfdc";
$sfdcSyncProduct = "tbl_sync_details_product_sfdc";
$sfdcSyncOrder = "tbl_sync_order_sfdc";
$sfdcSyncOrderProduct = "tbl_sync_product_of_order_sfdc";

/* sync related table */
$syncTimeSlot = "tbl_sync_time_slot";
$syncTimeSchedule = "tbl_sync_time_schedule";

/* Uninstall Table */
$tblUninstall = 'tbl_uninstall';

/*Create Conditions*/
$condID = " AND user_id=";

?>
