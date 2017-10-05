<?php
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
//include_once("$_SERVER[DOCUMENT_ROOT]/shopify-app/analyticstracking.php");
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
session_start();
if(isset($_SESSION['userId'])){
    $user_id = $_SESSION['userId'];
}
else {
    $user_id = $_REQUEST['access_id'];
}


$cond =" AND user_id='$user_id'";
$fetchUser = fetch($userLogin,$cond);
//For use Previous Billing id checking
$preBillingId = $fetchUser[0]['billing_id'];
$fetchSubscription = fetch($userSubscription,$cond);

//Define Globally shop and token
$_SESSION['shop_token'] = $fetchSubscription[0]['app1_cred1'];
$_SESSION['shop'] = $fetchSubscription[0]['app1_cred2'];
$_SESSION['userId'] = $fetchSubscription[0]['user_id'];

$charge_id = $fetchSubscription[0]['subscription_id']; // Current Billing Id

$plan = explode(' [ ',$fetchSubscription[0]['plan']);
$plan = $plan[0];
$crmType = $fetchSubscription[0]['app2']; // App2

$currentStatus = $fetchSubscription[0]['current_status']; // Current Status of Subscription
$_SESSION['currentStatus'] = $currentStatus;
//Storing Crm type to Globla
$_SESSION['crmType'] = $crmType;

$_SESSION['userName'] = $fetchUser[0]['user_name'];;
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Shopify Cloud Connector</title>
        <link rel="stylesheet" type="text/css" href="/shopify-app/style/after-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/shopify-app/style/sweetalert.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/shopify-app/js/sweetalert.min.js"></script>
    </head>
    <body>

    <script>
        var subscription_id = "<?php echo $charge_id; ?>";
        var user_id = "<?php echo $_SESSION['userId']; ?>";
        var pre_subscription_id = "<?php echo $preBillingId; ?>";
    </script>
      <div class="page-wrap">
        <header>
            <div class="grid-wrap pad">
                <div class="logo width-40 float-left">
                    <a href="#"><img src="/shopify-app/images/logo.png" alt="aquaApi logo"></a>
                </div>
                <div class="admin-config width-60 float-left text-right">
                    <div class="dropdown-box">
                        <span class="name"><img src="/shopify-app/images/header-profile-icon.png"> <span><?php echo $_SESSION['userName']; ?></span></span>
                        <ul class="dropdown-menu list text-left">
                            <li><a href="user-dashboard.php">Dashboard</a></li>
                            <?php
                            if($_SESSION['currentStatus'] == 1) {
                                echo '<li><a href="credential.php">App credentials update</a></li>';
                            }
                            ?>
                            <li><a href="support.php">Support</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
         <?php
         if($_SESSION['currentStatus'] == "1")
         {  //For Active Status
         ?>
        <div class="grid-wrap pad">
            <!--<div class="loader"></div>-->
            <div class="grid-wrap">
                <div class="width-60 float-left crmselect">
                    <h1 class="selected_crm">You have chosen</h1>
                    <span class="crm_img">
                        <?php
                        if ($fetchSubscription[0]['app2'] === 'ZOHO') {
                            ?>
                            <img src="/shopify-app/images/zoho.png">
                            <?php
                        } else if ($fetchSubscription[0]['app2'] === 'ZOHO_INVENTORY'){
                            ?>
                            <img src="/shopify-app/images/zohoInventory.png">
                            <?php
                        } else if ($fetchSubscription[0]['app2'] === 'VTIGER'){
                            ?>
                            <img src="/shopify-app/images/vtiger.png">
                            <?php
                        }else {
                            ?>
                            <img src="/shopify-app/images/salesforce.png">
                            <?php
                        }
                        ?></span>
                </div>
                <div class="width-40 float-left">
                    <div id="existingPlan" class="box-wrp selected_plan starter">
                        <h2>Current Plan</h2>
                        <h3></h3>
                        <p class="pricing"></p>
                        <p class="description"><?php
                            if ($plan == 10.00) {
                                ?>
                                Up to 500 orders processing per month.
                                <?php
                            } else if ($plan == 15.00){
                                ?>
                                Up to 1000 orders processing per month.
                                <?php
                            } else if ($plan == 20.00){
                                ?>
                                Up to 2000 orders processing per month.
                                <?php
                            }else {
                                ?>
                                Up to 3000 orders processing per month.
                                <?php
                            }
                            ?></p>
                        <div class="btn-wrp">
                                <a class="primary-btn" href="change-plan.php?access_id=<?php echo $user_id; ?>&active_plan=<?php echo $plan; ?>">Change plan</a>
                                <a class="secondary-btn" id="removeSubscription" href="#">Cancel subscription</a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="transacTionDetails" class="box-wrp">
                <div class="smallMessage">
                    <p>Your sync will happen in Every 1 Hour</p>
                </div>
            </div>
            <div id="syncErrors" class="box-wrp">

            </div>
        </div>
        <?php } else { //For non Active Status. ?>
        <div class="grid-wrap pad">
                 <!--<div class="loader"></div>-->
                 <div class="grid-wrap">
                     <div class="width-60 float-left crmselect">
                         <h1 class="non_selected_crm">Currently You have not chosen Any CRM</h1><br>
                         <h2>Your Previous Subscription was</h2>
                         <span class="crm_img">
                        <?php
                        if ($fetchSubscription[0]['app2'] === 'ZOHO') {
                            ?>
                            <img src="/shopify-app/images/zoho.png">
                            <?php
                        } else if ($fetchSubscription[0]['app2'] === 'ZOHO_INVENTORY'){
                            ?>
                            <img src="/shopify-app/images/zohoInventory.png">
                            <?php
                        } else if ($fetchSubscription[0]['app2'] === 'VTIGER'){
                            ?>
                            <img src="/shopify-app/images/vtiger.png">
                            <?php
                        }else {
                            ?>
                            <img src="/shopify-app/images/salesforce.png">
                            <?php
                        }
                        ?></span>
                     </div>
                     <div class="width-40 float-left">
                         <div id="existingPlan" class="box-wrp selected_plan starter">
                             <h2>Previous Plan</h2>
                             <h3></h3>
                             <p class="pricing"></p>
                             <p class="description" style="display: none;"><?php
                                 if ($plan == 10.00) {
                                     ?>
                                     Up to 500 orders processing per month.
                                     <?php
                                 } else if ($plan == 15.00){
                                     ?>
                                     Up to 1000 orders processing per month.
                                     <?php
                                 } else if ($plan == 20.00){
                                     ?>
                                     Up to 2000 orders processing per month.
                                     <?php
                                 }else {
                                     ?>
                                     Up to 3000 orders processing per month.
                                     <?php
                                 }
                                 ?></p>
                             <div class="btn-wrp">
                                 <a class="primary-btn" href="change-plan.php?access_id=<?php echo $user_id; ?>">Reactive plan</a>
                                 <a class="secondary-btn" id="cancelSubscription" href="#">Cancel Permanently</a>
                             </div>
                         </div>
                     </div>
                 </div>


             </div>
          <?php } ?>
      </div>
        <div class="footer">   
                <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
                <a target="_blank" href="http://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank" href="http://aquaapi.com/privacy.html">Privacy Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
            </div>
        <script>
            $(document).ready(function () {
                $("body").on('click', '#removeSubscription', function (e) {
                    e.preventDefault();
                    swal({
                        title: "Are you sure?",
                        text: "You want to remove your subscription!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, remove it!",
                        showLoaderOnConfirm: true,
                        closeOnConfirm: false
                    }, function () {
                        cancelSubscription();
                    });
                });
                $("body").on('click', '#cancelSubscription', function (e) {
                    e.preventDefault();
                    swal({
                        title: "Remove Permanently !",
                        text: "Are you Sure to Delete Permanently and Proceed to New Credential ?",
                        type: "info",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Delete Now !",
                        showLoaderOnConfirm: true,
                        closeOnConfirm: false
                    }, function () {
                        cancelSubscriptionPermanently();
                    });
                });
                getCurrentSubscription();
                //getTransactionDetails();
                //getSyncErrors();
            });

            function getCurrentSubscription() {
                $.ajax({
                    url: "index.php?type=getSubscription",
                    method: "GET",
                    data: {subscription_id: pre_subscription_id},
                    dataType: "json"
                }).done(function (result) {

                        var subscription = result.recurring_application_charge;
                        $("#existingPlan h3").text(subscription.name);
                        $("#existingPlan p.pricing").html("<span>Price :</span> $" + subscription.price + "/months");

                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }

           /* function getSyncErrors() {
                $.ajax({
                    url: "index.php?type=getSyncErrors",
                    method: "GET",
                    data: {logged_user_id: logged_user_id},
                    dataType: "json"
                }).done(function (result) {
                    var html = "<h2>Sync Status</h2>";
                    html += '<div class="sync-error">';
                    if (result.length > 0) {
                        for (var i in result) {
                            var error = result[i];
                            html += '<p>' + error.error_message + ' (' + error.transaction_head + ')</p>';
                        }
                    } else {
                        html += '<p class="noerror">No sync error found</p>';
                    }
                    html += '</div>';
                    $("#syncErrors").html(html);
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            } */

           /* function getTransactionDetails() {
                $.ajax({
                    url: "index.php?type=getTransactionDetails",
                    method: "GET",
                    data: {logged_user_id: logged_user_id},
                    dataType: "json"
                }).done(function (result) {
                    var readDateFormat = result.readDateFormat;
                    $("#syncTimeSpan").text(readDateFormat);
                    var tracsactionDetails = result.tracsactionDetails;
                    var html = "<h2>Sync Details</h2>";
                    html += '<div class="resp-tabs-container">';
                    html += '<table class="tab-table">';
                    html += '<thead>';
                    html += '<th>Sync Time</th>';
                    html += '<th>Customer(s)</th>';
                    html += '<th>Product(s)</th>';
                    html += '<th>Order(s)</th>';
                    html += '</thead>';
                    html += '<tbody>';
                    if (tracsactionDetails.length > 0) {
                        for (var i in tracsactionDetails) {
                            var transaction = tracsactionDetails[i];
                            html += '<tr>';
                            html += '<td>' + transaction.readableFormatDate + '</td>';
                            html += '<td>' + transaction.no_customer_data + '</td>';
                            html += '<td>' + transaction.no_product_data + '</td>';
                            html += '<td>' + transaction.no_order_data + '</td>';
                            html += '</tr>';
                        }
                    } else {
                        html += '<tr>';
                        html += '<td>--</td>';
                        html += '<td>--</td>';
                        html += '<td>--</td>';
                        html += '<td>--</td>';
                        html += '</tr>';
                    }
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    $("#transacTionDetails").append(html);
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            } */

            function cancelSubscription() {
                $.ajax({
                    url: "index.php?type=cancelSubscription",
                    method: "GET",
                    data: {subscription_id: subscription_id}
                }).done(function (result) {
                    if(result == 1)
                    {  swal("Canceled!", "Successfully Canceled", "success");
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                    else{
                        swal("OOPS...", "Something went wrong!", "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
            
            function cancelSubscriptionPermanently() {
                $.ajax({
                    url: "index.php?type=cancelPermanently",
                    method: "GET",
                    data: {user_id: user_id}
                }).done(function (result) {
                    if (result == 1 ) {
                        swal("Canceled Permanently !", "Successfully Delete", "success");
                        setTimeout(function() {
                            window.location = "/shopify-app/install/crm-type.php?access_id=" + user_id;
                        }, 2000);

                    } else {
                        swal("OOPS...", " Not Deleted.  Something went wrong!", "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
        </script>
    </body>
</html>
