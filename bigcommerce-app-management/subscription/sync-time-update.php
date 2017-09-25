<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/after-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/sweetalert.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/bigcommerce-app-management/js/sweetalert.min.js"></script>
        <?php session_start(); ?>
        <script>
            var zoho_subscription_id = "<?php echo $_SESSION['zoho_subscription_id']; ?>";
            var logged_user_id = "<?php echo $_SESSION['userID']; ?>";
        </script>
    </head>
    <body>
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
            <div class="box-wrp">
                <h2>Update your sync time (UTC)</h2>
                <div class="synctime-box">
                    <div id="syncDataList" class="sync-list">
                        <div class="loader"></div>
                    </div>
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
                $("body").on('click', '.radio', function (e) {
                    e.preventDefault();
                    var selected = $("input:checked").val();
                    var selectedText = $(this).next().text();
                    swal({
                        title: selectedText,
                        text: "You've selected this time slot!",
                        type: "success",
                        showCancelButton: true,
                        confirmButtonText: "Yes, proceed next!",
                        cancelButtonText: "No,I want to change!",
                        closeOnConfirm: false
                    }, function () {
                        updateSyncTime(selected);
                    });
                });
                loadUserSyncInterval();
            });

            function updateSyncTime(selected) {
                $.ajax({
                    url: "index.php?type=updateSyncTime",
                    method: "GET",
                    data: {logged_user_id: logged_user_id, selected_id: selected},
                    dataType: "json"
                }).done(function (result) {
                    if (result === true) {
                        swal("Congrats!", "Your sync time has been updated", "success");
                        window.location = "user-dashboard.php";
                    } else {
                        swal("OOPS...", "Something went wrong!", "error");
                    }
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }

            function loadUserSyncInterval() {
                $.ajax({
                    url: "index.php?type=getUserSyncTime",
                    method: "GET",
                    data: {logged_user_id: logged_user_id},
                    dataType: "json"
                }).done(function (result) {
                    loadAvailableTime(result[0].sync_time_slot_id);
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }

            function loadAvailableTime(selectedSlotId) {
                $.ajax({
                    url: "index.php?type=getTimeSlot",
                    method: "GET",
                    data: {craetedBy: 'aquaAPI'},
                    dataType: "json"
                }).done(function (result) {
                    var html = "";
                    for (var i in result) {
                        var plans = result[i];
                        if (parseInt(plans.max_allow) === parseInt(plans.alreadyTaken)) {
                            html += '<div class="diasble">';
                            if (selectedSlotId === plans.id) {
                                html += '<input disabled type="radio" checked="1" name="radio" id="radio' + plans.id + '" value="' + plans.id + '" class="radio"/>';
                            } else {
                                html += '<input disabled type="radio" name="radio" id="radio' + plans.id + '" value="' + plans.id + '" class="radio"/>';
                            }
                            html += '<label for="radio' + plans.id + '">' + plans.slot_name + '</label>';
                            html += '</div>';
                        } else {
                            html += '<div>';
                            if (selectedSlotId === plans.id) {
                                html += '<input type="radio" checked="1" name="radio" id="radio' + plans.id + '" value="' + plans.id + '" class="radio"/>';
                            } else {
                                html += '<input type="radio" name="radio" id="radio' + plans.id + '" value="' + plans.id + '" class="radio"/>';
                            }
                            html += '<label for="radio' + plans.id + '">' + plans.slot_name + '</label>';
                            html += '</div>';
                        }
                    }
                    $("#syncDataList").html(html);
                }).fail(function (jqXHR, textStatus) {
                    swal("OOPS...", "Something went wrong!", "error");
                });
            }
        </script>
    </body>
</html>