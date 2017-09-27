<?php

session_start();
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/shopify-app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
//user billing id collect from user login
$user_id=$_GET['user_token'];
$cond= " AND user_id='$user_id'";
$billing=fetch($userLogin,$cond);
$billing=$billing[0]['billing_id'];
?>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="../images/fav.png"/>
    <link rel="canonical" href="www.aquaapi.io/index.html"/>
    <title>Aquaapi :: Information Provide</title>
    <link rel="stylesheet" type="text/css" href="/app/style/before-installation/style.css">
    <link rel="stylesheet" type="text/css" href="/app/style/sweetalert.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="/app/js/sweetalert.min.js"></script>
    <script>
        var selected_plan_code = "<?php echo $_GET['plan_code']; ?>";
        var user_token = "<?php echo $_GET['user_token']; ?>";
    </script>
</head>
<body>
<div class="page-wrap">
    <div class="grid-wrap">
        <div class="pad">
            <img class="logo" src="/shopify-app/images/logo.png" alt="logo">
            <div class="iframe">
                <iframe id="subscriptionIframe"></iframe>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
    <a target="_blank" href="http://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank"
                                                                                                   href="http://aquaapi.com/privacy.html">Privacy
        Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
</div>
<script>
    $(document).ready(function () {
        getIframeSource();
    });
    function getIframeSource() {
        var billing = '<?php echo $billing; ?>';
        if(billing === ''){
            billing = '1';
        }

        $.ajax({
            url: "index.php?type=createSubscription",
            method: "GET",
            data: {selected_plan_code: selected_plan_code, user_token: user_token, customer: billing},
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