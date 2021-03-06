<?php

session_start();
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if (!isset($_SESSION['user_id'])) {

    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="../images/fav.png"/>
    <link rel="canonical" href="www.aquaapi.io/index.html"/>
    <title>Aquaapi :: Select Plan</title>
    <link rel="stylesheet" type="text/css" href="/app/style/before-installation/style.css">
    <link rel="stylesheet" type="text/css" href="/app/style/sweetalert.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="/app/js/sweetalert.min.js"></script>
    <script>
        var user_token = "<?php echo $_GET['access_id']; ?>";
    </script>
</head>
<body>
<div class="page-wrap">
    <?php include "$_SERVER[DOCUMENT_ROOT]/app/header.php"; ?>
    <div class="grid-wrap">
        <div class="pad">
            <h1>Select Your Subscription Plan :</h1>
            <h2>14 days FREE TRIAL on All Plans. Cancel any time. You will not be billed for first 14 days</h2>
            <hr>

            <div id="planContainer" class="plan-wrp">
                <div class="loader"></div>
            </div>


            <div class="info-big-size">
                <p>For higher volume pricing please email us at <a href="support@aquaapi.com" target="_top">support@aquaapi.com</a>
                </p>

                <p>For bulk historical data transfer pricing please email us at <a href="support@aquapi.com"
                                                                                   target="_top">support@aquapi.com</a>
                </p>
            </div>

            <div class="info">
                <p>* One way from BigCommrce to CRM only.</p>
                <p>** Connector does not sync older data earlier than the signup date.</p>
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
        loadPlans();
    });
    function loadPlans() {
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
                    html += '<div class="pln-bx">';
                    html += '<span class="span-name">' + plan.name + '</span>';
                    html += '<div class="pricewrp">';
                    html += '<span class="price"><span>$</span>' + plan.recurring_price + '<span>/' + plan.interval_unit + '</span></span>';
                    html += '</div>';
                    html += '<ul>';
                    html += '<li>Daily sync at your set time.</li>';
                    html += '<li>Sync all your products.</li>';
                    html += '<li>' + plan.description + '</li>';
                    html += '<li>Sync related accounts and contacts.</li>';
                    html += '<li>Email support included.</li>';
                    html += '</ul>';
                    html += '<a class="signup-btn" href="provide-information.php?plan_code=' + plan.plan_code + '&user_token=' + user_token + '">Sign up</a>';
                    html += '</div>';
                    html += '</div>';
                }
                $("#planContainer").html(html);
            } else {
                swal("Oops...", "Something gone wrong!", "error");
            }
        }).fail(function (jqXHR, textStatus) {
            swal("Oops...", "Something wrong here!", "error");
        });
    }
</script>
</body>
</html>
