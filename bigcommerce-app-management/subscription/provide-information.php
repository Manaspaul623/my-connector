<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/before-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/sweetalert.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/bigcommerce-app-management/js/sweetalert.min.js"></script>
        <script>
            var selected_plan_code = "<?php echo $_GET['plan_code']; ?>";
            var user_token = "<?php echo $_GET['user_token']; ?>";
        </script>
    </head>
    <body>
	<?php include_once("$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/analyticstracking.php") ?>
    <div class="page-wrap">
        <div class="grid-wrap">
            <div class="pad">
                <img class="logo" style="margin:auto; display:block;" src="/bigcommerce-app-management/images/logo.png" alt="logo">
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
                getIframeSource();
            });
            function getIframeSource() {
                $.ajax({
                    url: "index.php?type=createSubscription",
                    method: "GET",
                    data: {selected_plan_code: selected_plan_code, user_token: user_token},
                    dataType: "json"
                }).done(function (result) {
                    var url = result.hostedpage.url;
                    $("#subscriptionIframe").attr('src',url);
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
        </script>
    </body>
</html>