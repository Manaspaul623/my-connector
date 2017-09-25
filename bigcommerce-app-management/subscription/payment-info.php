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
            <div class="grid-wrap pad">
                <div class="grid-wrap">
                    <div class="iframe">
                        <iframe id="subscriptionIframe"></iframe>
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
                getExistingIframeSrc();
            });
            function getExistingIframeSrc() {
                $.ajax({
                    url: "index.php?type=updateCard",
                    method: "GET",
                    data: {zoho_subscription_id: zoho_subscription_id, logged_user_id: logged_user_id},
                    dataType: "json"
                }).done(function (result) {
                    var url = result.hostedpage.url;
                    $("#subscriptionIframe").attr('src', url);
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
        </script>
    </body>
</html>