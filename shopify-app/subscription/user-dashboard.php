<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Shopify Cloud Connector</title>
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/after-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/sweetalert.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/bigcommerce-app-management/js/sweetalert.min.js"></script>
    </head>
    <body>
        <?php session_start(); ?>
        <script>
            var zoho_subscription_id = "<?php echo $_SESSION['zoho_subscription_id']; ?>";
            var logged_user_id = "<?php echo $_SESSION['userID']; ?>";
        </script>
        <div class="page-wrap">
        <header>
            <div class="grid-wrap pad">
                <div class="logo width-40 float-left">
                    <a href="#"><img src="/bigcommerce-app-management/images/logo.png" alt="aquaApi logo"></a>
                </div>
                <div class="admin-config width-60 float-left text-right">
                    <div class="dropdown-box">
                        <span class="name"><img src="/bigcommerce-app-management/images/header-profile-icon.png"> <span><?php echo $_SESSION['userEmail']; ?></span></span>
                        <ul class="dropdown-menu list text-left">
                            <li><a href="user-dashboard.php">Dashboard</a></li>
                            <li><a href="payment-info.php">Payment card update</a></li>
                            <li><a href="credential.php">App credentials update</a></li>
                            <li><a href="sync-time-update.php">Change sync time</a></li>
                            <li><a href="support.php">Support</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        <div class="grid-wrap pad">
            <!--<div class="loader"></div>-->
            <div class="grid-wrap">
                <div class="width-60 float-left crmselect">
                    <h1 class="selected_crm">You have chosen</h1>
                    <span class="crm_img">
                        <?php
                        if ($_SESSION['crmType'] === 'ZOHO') {
                            ?>
                            <img src="/bigcommerce-app-management/images/zoho.png">
                            <?php
                        } else if ($_SESSION['crmType'] === 'ZOHO_INVENTORY'){
                            ?>
                            <img src="/bigcommerce-app-management/images/zohoInventory.png">
                            <?php
                        } else if ($_SESSION['crmType'] === 'VTIGER'){
                            ?>
                            <img src="/bigcommerce-app-management/images/vtiger.png">
                            <?php
                        }else {
                            ?>
                            <img src="/bigcommerce-app-management/images/salesforce.png">
                            <?php
                        }
                        ?></span>
                </div>
                <div class="width-40 float-left">
                    <div id="existingPlan" class="box-wrp selected_plan starter">
                        <h2>Current Plan</h2>
                        <h3></h3>
                        <p class="pricing"></p>
                        <p class="description"></p>
                        <div class="btn-wrp">
                            <?php if ($_SESSION['userCurrentStatus'] == 'CANCELLED') { ?>
                                <a class="primary-btn" id="reactivateApp" href="#">Reactivate</a>
                            <?php } else { ?>
                                <a class="primary-btn" href="change-plan.php">Change plan</a>
                                <a class="secondary-btn" id="removeSubscription" href="#">Cancel subscription</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="transacTionDetails" class="box-wrp">
                <div class="smallMessage">
                    <p>Your sync will happen between <span id="syncTimeSpan"></span> UTC</p>
                </div>
            </div>
            <div id="syncErrors" class="box-wrp">

            </div>
            

        </div>
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
                        confirmButtonText: "Yes, remove it!"
                    }, function () {
                        cancelSubscription();
                    });
                });
                $("body").on('click', '#reactivateApp', function (e) {
                    e.preventDefault();
                    swal({
                        title: "Reactivating your Subscription?",
                        text: "Thanks for your order!",
                        type: "success",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Activate now!"
                    }, function () {
                        reactivateApp();
                    });
                });
                getCurrentSubscription();
                getTransactionDetails();
                getSyncErrors();
            });

            function getCurrentSubscription() {
                $.ajax({
                    url: "index.php?type=getSubscription",
                    method: "GET",
                    data: {zoho_subscription_id: zoho_subscription_id},
                    dataType: "json"
                }).done(function (result) {
                    if (result.message === 'success') {
                        var subscription = result.subscription;
                        $("#existingPlan h3").text(subscription.name);
                        $("#existingPlan p.pricing").html("<span>Price :</span> $" + subscription.plan.price + '/' + subscription.interval_unit);
                        $("#existingPlan p.description").html("<span>Description :  </span>" + subscription.plan.description);
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }

            function getSyncErrors() {
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
            }

            function getTransactionDetails() {
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
            }

            function cancelSubscription() {
                $.ajax({
                    url: "index.php?type=cancelSubscription",
                    method: "GET",
                    data: {zoho_subscription_id: zoho_subscription_id, logged_user_id: logged_user_id},
                    dataType: "json"
                }).done(function (result) {
                    if (parseInt(result.code) === 0) {
                        swal("Removed!", result.message, "success");
                        location.reload();
                    } else {
                        swal("OOPS...", result.message, "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
            
            function reactivateApp() {
                $.ajax({
                    url: "index.php?type=reactivateApp",
                    method: "GET",
                    data: {zoho_subscription_id: zoho_subscription_id, logged_user_id: logged_user_id},
                    dataType: "json"
                }).done(function (result) {
                    if (parseInt(result.code) === 0) {
                        swal("Activated!", result.message, "success");
                        location.reload();
                    } else {
                        swal("OOPS...", result.message, "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
        </script>
    </body>
</html>
