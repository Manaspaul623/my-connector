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
        <?php
        session_start();
        $crmType = $_SESSION['crmType'];
        $user_email = $_SESSION['userEmail'];
        ?>    
        <script>
            var crmType = "<?php echo $crmType; ?>";
        </script>
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
            <h1 style="margin: 40px 0 60px;">Support:</h1>

            <div class="box-wrp">
                <h2>We will get back to you within 24 hrs</h2>
                <form name="frmSubmit" id="sfdcInput" class="frmInput form-format" method="post" action="send-email.php">                    
                    <div class="box">                        
                        <div id="sfdcCredDiv" style="display:block">
                            <label>Name :</label>
                            <input type="text" id="user_name" name="user_name" required>
                            <label>Email :</label>
                            <input type="email" id="user_email" name="user_email" value="<?php echo $user_email; ?>" readonly="readonly">
                            <label>Subject :</label>
                            <input type="text" id="subject" name="subject" required>
                            <label>Message :</label>
                            <textarea  id="message" name="message" rows="4" cols="10" required></textarea>
                        </div>
                        <input class="primary-btn" type="submit" id="submit" name="submit" value="Submit"> 
                    </div>
                </form>
            </div>            
            <div class="footer">   
                <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
                <a target="_blank" href="http://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank" href="http://aquaapi.com/privacy.html">Privacy Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
            </div>
        </div>  
        <script>
            $(document).ready(function () {
                $('body').on('click', '#submit', function (e) {
                    e.preventDefault();
                    var user_name = $("#user_name").val();
                    var user_email = $("#user_email").val();
                    var subject = $("#subject").val();
                    var message = $("#message").val();
                    $(".error").remove();
                    if (user_name.length < 1) {
                        $("#user_name").after('<label class="error">This field is required.</label>');
                        return false;
                    } else if (subject.length < 1) {
                        $("#subject").after('<label class="error">This field is required.</label>');
                        return false;
                    }else if (message.length < 1) {
                        $("#message").after('<label class="error">This field is required.</label>');
                        return false;
                    }else {
                        $.ajax({
                            url: "index.php?type=supportSubmit",
                            method: "POST",
                            data: {user_name: user_name,user_email: user_email,subject: subject,message: message},
                            dataType: "text"
                        }).done(function (result) {
                            if (result == 'true') {
                                swal("Thank you!", "Your query submitted successfully...", "success");
                            } else {
                                swal("OOPS...", "Something went wrong!", "error");
                            }
                            window.location="user-dashboard.php";
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something went wrong!", "error");
                        });
                    }
                });
            });
        </script> 
    </body>
</html>
