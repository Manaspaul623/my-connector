<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>BigCommerce CRM connector</title>
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
                                <li><a href="credential.php">CRM credentials update</a></li>
                                <li><a href="sync-time-update.php">Change sync time</a></li>
                                <li><a href="support.php">Support</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="grid-wrap">
                <div class="pad">
                    <h1>Change Your Subscription Plan :</h1>
                    <h2>14 days FREE TRIAL on All Plans. Cancel any time. You will not be billed for first 14 days</h2>
                    <hr>
                    <div id="planContainer" class="plan-wrp">
                        <div class="loader"></div>
                    </div>
                    <div class="info-big-size">
                        <p>For higher volume or custom workflows, pricing please email us at <a href="support@aquaapi.com" target="_top">support@aquaapi.com</a></p>
    
                        <p>For bulk historical data transfer pricing please email us at <a href="support@aquapi.com" target="_top">support@aquapi.com</a></p>
                    </div>
                    <div class="info">
                        <p>* One way from BigCommerce to your target app only.</p>
                        <p>** Connector does not sync orders earlier than the signup date.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">   
                <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
                <a target="_blank" href="http://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank" href="http://aquaapi.com/privacy.html">Privacy Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
            </div>
        <script>
            $(document).ready(function () {
                getCurrentSubscription();
                $('body').on('click', '.planChange', function (e) {
                    e.preventDefault();
                    var plan_id = $(this).data('plan_id');
                    swal({
                        title: "Are you sure?",
                        text: "Your current plan will be changed!",
                        type: "success",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Change it!",
                        cancelButtonText: "No, Not now!",
                        closeOnConfirm: false
                    }, function () {
                        changeSubscription(plan_id);
                    });
                });
            });

            function changeSubscription(plan_id) {
                $.ajax({
                    url: "index.php?type=updateSubscription",
                    method: "GET",
                    data: {zoho_subscription_id: zoho_subscription_id, choosen_plan_id: plan_id},
                    dataType: "json"
                }).done(function (result) {
                    if (parseInt(result.code) === 0) {
                        swal({   
                            title: "Congratulations",   
                            text: result.message,   
                            type: "success",   
                            showCancelButton: false,     
                            confirmButtonText: "Take me to home!",  
                            closeOnConfirm: false 
                        }, function(){   
                            window.location = "user-dashboard.php";
                        });
                        
                    } else {
                        swal("OOPS...", result.message, "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                   swal("OOPS...", "Something went wrong!", "error");
                });
            }

            function getCurrentSubscription() {
                $.ajax({
                    url: "index.php?type=getSubscription",
                    method: "GET",
                    data: {zoho_subscription_id: zoho_subscription_id},
                    dataType: "json"
                }).done(function (result) {
                    if (result.message === 'success') {
                        var subscription = result.subscription;
                        loadPlans(subscription.plan.plan_code);
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }

            function loadPlans(selected_plan_code) {
                $.ajax({
                    url: "index.php?type=getPlans",
                    method: "GET",
                    data: {craetedBy: 'aquaAPI'},
                    dataType: "json"
                }).done(function (result) {
                    if (result.message === 'success') {
                        var ratePlans = result.plans;
                        var html = "";
                        for (var i in ratePlans) {
                            var plan = ratePlans[i];
                            html += '<div class="plan">';
                            if (plan.plan_code == selected_plan_code) {
                                html += '<div class="pln-bx selected">';
                            } else {
                                html += '<div class="pln-bx">';
                            }
                            
                            html += '<span class="span-name">' + plan.name + '</span>';
                            html += '<div class="pricewrp">';
                            html += '<span class="price"><span>$</span>' + plan.recurring_price + '<span>/' + plan.interval_unit + '</span></span>';
                            html += '</div>';
                            html += '<ul>';
                            html += '<li>Daily sync at your set time.</li>';
                            html += '<li>' + plan.description + '</li>';
                            html += '<li>Sync related Accounts, Contacts & Products</li>';
                            html += '<li>Email support included.</li>';
                            html += '</ul>';
                            if (plan.plan_code == selected_plan_code) {
                                html += '<a class="signup-btn disabled" disabled>Selected</a>';
                            } else {
                                html += '<a class="signup-btn planChange" data-plan_id="' + plan.plan_code + '">Update Now</a>';
                            }
                            html += '</div>';
                            html += '</div>';
                        }
                        $("#planContainer").html(html);
                    } else {
                        swal("OOPS...", "Something went wrong!", "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
        </script>
    </body>
</html>