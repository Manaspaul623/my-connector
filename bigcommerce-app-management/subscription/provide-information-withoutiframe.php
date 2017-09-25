<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="style/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>

        <script>
            var selected_plan_code = "<?php echo $_GET['plan_code']; ?>";
            var user_token = '<?php echo $_GET['user_token']; ?>';
        </script>
    </head>
    <body>
        <div class="main_wrapper">
            <div class="logo text-center">
                <img src="images/logo.png" alt="logo">
            </div>
            <div class="text-center">
                <div class="boxWrp">
                    <div  class="cnterBox planwrp">  
                        <div id="planContainer" class="box">
                            <form id="personalInformation" method="get">
                                <input type="text" name="fname" id="fname" placeholder="First name" required>
                                <input type="text" name="lname" id="lname" placeholder="Second name" required>
                                <input type="email" name="email_add" id="email_add" placeholder="Email" required>
                                <input type="submit" name="Submit" id="personalInfoSub" value="Submit">
                            </form>
                            <!--                            <div class="loader">Loading...</div>-->
                       	</div>
                    </div>
                </div>
            </div>
        </div>	
        <script>
            $(document).ready(function () {
                $('body').on('click', '#personalInfoSub', function () {
                    $("#personalInformation").validate({
                        submitHandler: function (form) {
                            var fname = $("#fname").val();
                            var lname = $("#lname").val();
                            var email_add = $("#email_add").val();
                            $.ajax({
                                url: "index.php?type=createSubscription",
                                method: "GET",
                                data: {lname: lname,fname: fname,email_add : email_add,selected_plan_code: selected_plan_code, user_token: user_token},
                                dataType: "json"
                            }).done(function (result) {
                                 console.log(result);
                                var url = result.hostedpage.url;
                               window.location = url;

                            }).fail(function (jqXHR, textStatus) {
                                alert("Request failed: " + textStatus);
                            });
                        }
                    });
                });
            });
            function loadPlans() {
                $.ajax({
                    url: "index.php?type=getPlans",
                    method: "GET",
                    data: {craetedBy: 'aquaAPI'},
                    dataType: "json"
                }).done(function (result) {
                    console.log(result);
                    if (result.message === 'success') {
                        var ratePlans = result.plans;
                        var html = "";
                        for (var i in ratePlans) {
                            var plan = ratePlans[i];
                            html += '<div class="planbox">';
                            html += '<span>' + plan.name + '</span>';
                            html += '<a href="provide-information.php?plan_code=' + plan.plan_code + '">Sign up</a>';
                            html += '<table>';
                            html += '<tr>';
                            html += '<td>Price :</td>';
                            html += '<td>$' + plan.recurring_price + '/' + plan.interval_unit + '</td>';
                            html += ' <tr>';
                            html += '<td>Description :</td>';
                            html += '<td>' + plan.description + '</td>';
                            html += '</tr>';
                            html += '</table>';
                            html += '</div>';
                        }
                        $("#planContainer").html(html);
                    } else {
                        alert("wrong credentials");
                    }
                }).fail(function (jqXHR, textStatus) {
                    alert("Request failed: " + textStatus);
                });
            }
        </script>
    </body>
</html>