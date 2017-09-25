<?php

session_start();
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
//For Logout The Session
if(isset($_GET['logout']))
{   unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['app']);
    session_destroy();
    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}

if (!isset($_SESSION['user_id'])) {
    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}


//Fetching Subscription History for this user

$user_id = $_SESSION['user_id'];
$cond = " AND user_id = '$user_id' AND subscription_id != ''";
$subscriptions = fetch($userSubscription,$cond);
?>

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
    var logged_user_id = "<?php echo $_SESSION['user_id']; ?>";
</script>
<div class="page-wrap">
    <?php include "$_SERVER[DOCUMENT_ROOT]/app/header.php"; ?>
    <div class="grid-wrap pad">
        <!--<div class="loader"></div>-->
        <div class="grid-wrap">
            <div class="crmselect">
                <h1 class="selected_crm">Your Subscription List</h1>
                <table class="container">
                    <thead>
                    <tr>
                        <th><h1>App 1</h1></th>
                        <th><h1>App 2</h1></th>
                        <th><h1>Active Plan</h1></th>
                        <th><h1>Action</h1></th>
                        <th><h1>Card</h1></th>
                        <th><h1>Sync</h1></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($subscriptions as $key=>$value)
                    {   //for jquery Subscription Id
                        $sub_id="'".$value['subscription_id']."'";
                        //for change plan
                        $subscription=urlencode(base64_encode($value['subscription_id']));
                        $id=urlencode(base64_encode($value['id']));
                        echo '<tr>';
                            echo '<td>'.$value['app1'].'</td>';
                            echo '<td>'.$value['app2'].'</td>';
                            echo '<td> $'.$value['plan'].'</td>';
                            echo '<td><a class="change" href="change-plan.php?subscription='.$subscription.'&id='.$id.'">Change Plan</a><a class="cancel" href="#" id="cancel-subscription'.$value["id"].'" onclick="cancel_subscription('.$value["id"].','.$sub_id.');">Cancel Subscription</a></td>';
                            echo '<td><a class="change" href="payment-info.php?subscription='.$subscription.'">Update Payment Method</a></td>';
                            echo '<td><a class="sync" href="#" id="synchronize'.$value["id"].'" onclick="sync_subscription('.$value["id"].');">Synchronize</a></td>';

                        echo '</tr>';
                    }
                    ?>


                    </tbody>
                </table>
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
    //For Delete Subscription  from Database
 function cancel_subscription(id,sub_id){

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
         $('#cancel-subscription'+id).text('Canceling.....');
         $.ajax({
             url: "index.php?type=cancelSubscription",
             method: "GET",
             data: {zoho_subscription_id: sub_id, row_id: id},
             dataType: "json"
         }).done(function (result) {
             if (parseInt(result.code) === 0) {
                 swal("Removed!", result.message, "success");
                 setTimeout(function() {
                     location.reload();
                 }, 3000);

             } else {
                 swal("OOPS...", result.message, "error");
             }
         }).fail(function (jqXHR, textStatus) {
             swal("OOPS...", "Something went wrong!", "error");
         });

     });
 }

    //Sync function
    function sync_subscription(id){
        var now = new Date(),
            time_to = now.getHours()+':'+now.getMinutes();
            time_from =now.getHours() - 1+':'+now.getMinutes();


        swal({
            title: "Are you sure?",
            text: "From "+time_from+" To "+time_to+" Data will Synchronize !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Do it!",
            showLoaderOnConfirm: true,
            closeOnConfirm: false
        }, function () {

             $('#synchronize'+id).text('Loading....');
            $.ajax({
                url: "TrySync.php",
                type: "get", //send it through get method
                data: {
                    id: id
                },
                success: function (response) {
                    $('#synchronize'+id).text('Synchronize');
                    if ($.isNumeric(response)) {
                        $total="Total No of Order is " + response;
                        swal("Success", $total, "success");

                    }
                    else {
                        swal("OOPS...", "Unable  To Process Credential", "error");
                    }
                },
                error: function (xhr) {
                    swal("OOPS...", "Unable  To Process", "error");
                }
            });
            //window.location = "TrySync.php?id=" + id;
        });
    }
    $(document).ready(function () {

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


</script>
</body>
</html>
