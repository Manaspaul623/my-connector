<?php

session_start();
//For Logout The Session
if(isset($_GET['logout']))
{   unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    session_destroy();
}
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if (!isset($_SESSION['user_id'])) {
    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}
if(!isset($_REQUEST['subscription'])){
    header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php");
}
//Collecting User Detail From user_login page.

$account_id = base64_decode(urldecode($_REQUEST['subscription']));
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" type="image/png" href="../images/fav.png"/>
        <link rel="canonical" href="www.aquaapi.io/index.html"/>
        <title>AquaAPI eCommerce Cloud Connector</title>
        <link rel="stylesheet" type="text/css" href="/app/style/after-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/app/style/sweetalert.css">
        <!-- Table Css -->
        <link rel="stylesheet" type="text/css" href="/app/style/table.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/app/js/sweetalert.min.js"></script>

    </head>
    <body>

        <script>
            var zoho_subscription_id = "<?php echo $account_id; ?>";
            var logged_user_id = "<?php echo $_SESSION['user_id']; ?>";
        </script>
        <div class="page-wrap">
            <?php include "$_SERVER[DOCUMENT_ROOT]/app/header.php"; ?>
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
                <a target="_blank" href="https://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank" href="https://aquaapi.com/privacy.html">Privacy Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
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