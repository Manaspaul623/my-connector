<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/after-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/sweetalert.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/bigcommerce-app-management/js/sweetalert.min.js"></script>
        <script>
            var user_token = "<?php echo $_GET['access_id']; ?>";
        </script>
    </head>
    <body>
      <div class="page-wrap">
        <div class="grid-wrap pad">
            <!--<div class="tnaku-msg">
                <h1 class="selected_crm">Thank you for your subscription</h1>
            </div>-->
            <div class="box-wrp">
                <h2>Sync time (UTC)</h2>
                <!--<div id="selectedTimeSlot2" class="nxt-btn position-absolte">
                    <a href="#">Next</a>
                </div>-->
                <div class="synctime-box">
                    <div id="syncDataList" class="sync-list">
                        <div class="loader"></div>
                    </div>
                    <!--<div id="selectedTimeSlot" class="nxt-btn text-center">
                        <a href="#">Next</a>
                    </div>-->
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
                swal("Congratulations", "Your subscription has been created. Please schedule your sync time.", "success");
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
                        window.location="sync-time-action.php?access_id="+user_token+"&selected="+selected;
                    });
                });
                loadAvailableTime();
            });
            function loadAvailableTime() {
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
                            html += '<input disabled type="radio" name="radio" id="radio' + plans.id + '" value="' + plans.id + '" class="radio"/>';
                            html += '<label for="radio' + plans.id + '">' + plans.slot_name + '</label>';
                            html += '</div>';
                        } else {
                            html += '<div class="enableRadioDiv">';
                            html += '<input type="radio" name="radio" id="radio' + plans.id + '" value="' + plans.id + '" class="radio"/>';
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
