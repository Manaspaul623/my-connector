<?php
session_start();
$active_plan ="";
if(isset($_REQUEST['active_plan'])) //For Changing plan if previously active any plan on this user then active_plan request will come along with access_id;
{
    $active_plan = $_REQUEST['active_plan'];
}

?>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" type="text/css" href="/shopify-app/style/after-installation/style.css">
    <link rel="stylesheet" type="text/css" href="/shopify-app/style/sweetalert.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="/shopify-app/js/sweetalert.min.js"></script>
    <script>
        var user_token = "<?php echo $_GET['access_id']; ?>";
    </script>
</head>
<body>
<?php // include_once("$_SERVER[DOCUMENT_ROOT]/shopify-app/analyticstracking.php") ?>
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
    <div class="grid-wrap">
        <div class="pad">
            <h1>Select Your Subscription Plan :</h1>
            <h2>14 days FREE TRIAL on All Plans. Cancel any time. You will not be billed for first 14 days</h2>
            <hr>

            <div id="planContainer" class="plan-wrp">
                <div class="plan">
                    <div class="pln-bx">
                        <span class="span-name">Lite</span>
                        <div class="pricewrp">
                            <span class="price"><span>$</span>10<span>/months</span></span>
                        </div>
                        <ul>
                            <li>Daily sync at your set time.</li>
                            <li>Sync all your products.</li>
                            <li>Up to 500 orders processing per month.</li>
                            <li>Sync related Accounts, Contacts & Products</li>
                            <li>Email support included.</li>
                        </ul>
                        <?php
                        if($active_plan == 10.00) {
                            echo '<a class="signup-btn" disabled> Selected </a>';
                        }
                        else {
                            echo '<a class="signup-btn" id="plan10" href="#">Update</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="plan">
                    <div class="pln-bx">
                        <span class="span-name">Plus</span>
                        <div class="pricewrp">
                            <span class="price"><span>$</span>15<span>/months</span></span>
                        </div>
                        <ul>
                            <li>Daily sync at your set time.</li>
                            <li>Sync all your products.</li>
                            <li>Up to 1000 orders processing per month.</li>
                            <li>Sync related Accounts, Contacts & Products</li>
                            <li>Email support included.</li>
                        </ul>
                        <?php
                        if($active_plan == 15.00) {
                            echo '<a class="signup-btn"  disabled> Selected </a>';
                        }
                        else {
                            echo '<a class="signup-btn" id="plan15" href="#">Update</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="plan">
                    <div class="pln-bx">
                        <span class="span-name">Advanced</span>
                        <div class="pricewrp">
                            <span class="price"><span>$</span>20<span>/months</span></span>
                        </div>
                        <ul>
                            <li>Daily sync at your set time.</li>
                            <li>Sync all your products.</li>
                            <li>Up to 2000 orders processing per month.</li>
                            <li>Sync related Accounts, Contacts & Products</li>
                            <li>Email support included.</li>
                        </ul>
                        <?php
                        if($active_plan == 20.00) {
                            echo '<a class="signup-btn"  disabled> Selected </a>';
                        }
                        else {
                            echo '<a class="signup-btn" id="plan20" href="#">Update</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="plan">
                    <div class="pln-bx">
                        <span class="span-name">Pro</span>
                        <div class="pricewrp">
                            <span class="price"><span>$</span>25<span>/months</span></span>
                        </div>
                        <ul>
                            <li>Daily sync at your set time.</li>
                            <li>Sync all your products.</li>
                            <li>Up to 3000 orders processing per month.</li>
                            <li>Sync related Accounts, Contacts & Products</li>
                            <li>Email support included.</li>
                        </ul>
                        <?php
                        if($active_plan == 25.00) {
                            echo '<a class="signup-btn disabled"  disabled> Selected </a>';
                        }
                        else {
                            echo '<a class="signup-btn" id="plan25" href="#">Update</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>


            <div class="info-big-size">
                <p>For higher volume and/or custom workflows please email us at <a href="support@aquaapi.com" target="_top">support@aquaapi.com</a></p>

                <p>For bulk historical data transfer pricing please email us at <a href="support@aquapi.com" target="_top">support@aquapi.com</a></p>
            </div>

            <div class="info">
                <p>* One way from BigCommerce to your choosen Cloud App only.</p>
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
    var access_id = "<?php echo $_GET['access_id']; ?>";
    $('body').on('click', '#plan10', function (e) {
        window.open('change-plan-information.php?plan_code=10&user_token='+access_id,'Plan Confirmation');
    });

    $('#plan15').click(function () {
        window.open('change-plan-information.php?plan_code=15&user_token='+access_id,'Plan Confirmation');

    });

    $('#plan20').click(function () {
        window.open('change-plan-information.php?plan_code=20&user_token='+access_id,'Plan Confirmation');

    });

    $('#plan25').click(function () {
        window.open('change-plan-information.php?plan_code=25&user_token='+access_id,'Plan Confirmation');

    });


    function GetCharge(charge, status) {  //After Collecting The Charge Operation from change-plan-information.php page, charge id and status will come here
        if(status === 'accepted') {
            swal({
                title: "Successful",
                text: "Your have successfully confirmed your subscription",
                type: "success",
                showCancelButton: false,
                confirmButtonText: "Yes, proceed next!",
                showLoaderOnConfirm: true,
                closeOnConfirm: false
            }, function () {
                window.location = "user-dashboard?access_id="+access_id;
            });
        }
        else
        {
            swal("Oops...", "You have Canceled the Order", "error");
        }

    }

    /*
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
     html += '<li>' + plan.description + '</li>';
     html += '<li>Sync related Accounts, Contacts & Products</li>';
     html += '<li>Email support included.</li>';
     html += '</ul>';
     html += '<a class="signup-btn" href="change-plan-information.php?plan_code=' + plan.plan_code + '&user_token=' + user_token + '">Sign up</a>';
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
     } */
</script>
</body>
</html>
