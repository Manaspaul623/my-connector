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
        ?>    
        <script>
            var crmType = "<?php echo $crmType; ?>";
        </script>
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
                <!--<h1 style="margin: 40px 0 60px;">Dont worry! just fill your credentials, and we'll help you to update</h1>-->
    
                <div class="box-wrp">
                    <h2>Please provide your new credentials below</h2>
                    <form name="frmSubmit" id="sfdcInput" class="frmInput form-format" method="post" action="#">
                        <?php
                        if ($crmType == 'ZOHO') {
                            $valZoho = 'block';
                            $valZohoInven = 'none';
                            $valsfdc = 'none';
                        } else if($crmType == 'ZOHO_INVENTORY') {
                            $valZoho = 'none';
                            $valZohoInven = 'block';
                            $valsfdc = 'none';
                        } else {
                            $valZoho = 'none';
                            $valZohoInven = 'none';
                            $valsfdc = 'block';
                        }
                        ?>
                        <div class="box">
                            <div id="zohoCredDiv" style="display:<?php echo $valZoho; ?>">
                                <label>Zoho auth token</label>
                                <input type="text" id="zohoAuthToken" name="zohoAuthToken" placeholder="Zoho auth token">
                            </div>
                            <div id="zohoInvenCredDiv" style="display:<?php echo $valZohoInven; ?>">
                                <label>Zoho Inventory auth token</label>
                                <input type="text" id="zohoInvenAuthToken"  name="zohoInvenAuthToken" placeholder="Zoho Inventory auth token">
                                <label>Zoho Inventory Organization ID</label>
                                <input type="text" id="zohoInvenOrgID"  name="zohoInvenOrgID" placeholder="Zoho Inventory Org ID">
                           </div>
                            <div id="sfdcCredDiv" style="display:<?php echo $valsfdc; ?>">
                                <label>Sfdc user name</label>
                                <input type="text" id="sfdc_user_name" name="sfdc_user_name">
                                <label>Sfdc password</label>
                                <input type="password" id="sfdc_password" name="sfdc_password">
                                <label>Sfdc security password</label>
                                <input type="password" id="sfdc_security_password" name="sfdc_security_password">
                            </div>
                            <input class="primary-btn" type="submit" id="credentialCheck" name="submit" value="Submit"> 
                        </div>
                    </form>
                </div>
                
                
            </div>
        </div>
        <div class="footer">   
                <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
                <a target="_blank" href="http://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank" href="http://aquaapi.com/privacy.html">Privacy Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
            </div>
<!--        <div class="grid-wrap pad">
            <div class="grid-wrap">
                <div class="width-60 float-left crmselect">
                    <h1 class="selected_crm">you have chosen</h1>
                    <span class="crm_img">
                        <?php
//                        if ($_SESSION['crmType'] === 'ZOHO') {
//                            ?>
                            <img src="/bigcommerce-app-management/images/zoho.png">
                            //<?php
//                        } else {
//                            ?>
                            <img src="/bigcommerce-app-management/images/salesforce.png">
                            //<?php
//                        }
                        ?></span>
                </div>
                <div class="width-40 float-left">
                    <div id="existingPlan" class="box-wrp selected_plan starter">
                        <div class="pad">
                            <h1>CRM Credential Update</h1>                            
                            <form name="frmSubmit" id="sfdcInput" class="frmInput" method="post" action="#">
                                <?php
//                                if ($crmType == 'ZOHO') {
//                                    $valZoho = 'block';
//                                    $valsfdc = 'none';
//                                } else {
//                                    $valZoho = 'none';
//                                    $valsfdc = 'block';
//                                }
                                ?>
                                <div class="box">
                                    <div id="zohoCredDiv" style="display:<?php echo $valZoho; ?>">
                                        <label>Zoho auth token</label>
                                        <input type="text" id="zohoAuthToken" name="zohoAuthToken" placeholder="Zoho auth token">
                                    </div>
                                    <div id="sfdcCredDiv" style="display:<?php echo $valsfdc; ?>">
                                        <label>Sfdc user name</label>
                                        <input type="text" id="sfdc_user_name" name="sfdc_user_name">
                                        <label>Sfdc password</label>
                                        <input type="password" id="sfdc_password" name="sfdc_password">
                                        <label>Sfdc security password</label>
                                        <input type="password" id="sfdc_security_password" name="sfdc_security_password">
                                    </div>
                                    <input class="primary-btn" type="submit" id="credentialCheck" name="submit" value="Submit"> 
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>            
        </div>      -->
        <script>
            $(document).ready(function () {
                $('body').on('click', '#credentialCheck', function (e) {
                    e.preventDefault();

                    if (crmType == 'ZOHO') {
                        var zohoAuthToken = $("#zohoAuthToken").val();
                        $(".error").remove();
                        if (zohoAuthToken.length < 1) {
                            $("#zohoAuthToken").after('<label class="error">This field is required.</label>');
                            return false;
                        } else {
                            $.ajax({
                                url: "/bigcommerce-app-management/install/input-validation.php?type=validateZohoZuthToken",
                                method: "POST",
                                data: {zohoAuthToken: zohoAuthToken},
                                dataType: "json"
                            }).done(function (result) {
                                if (result == true) {
                                    swal("Correct!", "Your credentials are correct,We are updating...", "success");
                                    window.location = "credential-update.php?zohoAuthToken=" + zohoAuthToken;
                                } else {
                                    swal("OOPS...", "Something went wrong!", "error");
                                }
                            }).fail(function (jqXHR, textStatus) {
                                swal("OOPS...", "Something went wrong!", "error");
                            });
                        }
                    } else if (crmType == 'ZOHO_INVENTORY') {
                        var zohoInvenAuthToken = $("#zohoInvenAuthToken").val();
                        var zohoInvenOrgID = $("#zohoInvenOrgID").val();
                        $(".error").remove();
                        if (zohoInvenAuthToken.length < 1) {
                            $("#zohoInvenAuthToken").after('<label class="error">This field is required.</label>');
                            return false;
                        } else if (zohoInvenOrgID.length < 1) { 
                            $("#zohoInvenOrgID").after('<label class="error">This field is required.</label>');
                            return false;             
                        } else {
                            $.ajax({
                                url: "/bigcommerce-app-management/install/input-validation.php?type=validateZohoInvenZuthToken",
                                method: "POST",
                                data: {zohoInvenAuthToken: zohoInvenAuthToken, zohoInvenOrgID: zohoInvenOrgID},
                                dataType: "json"
                            }).done(function (result) {
                                if (result == true) {
                                    swal("Correct!", "Your credentials are correct,We are updating...", "success");
                                    window.location = "credential-update.php?zohoInvenAuthToken=" + zohoInvenAuthToken;
                                } else {
                                    swal("Oops...", "Something went wrong!", "error");
                                }
                            }).fail(function (jqXHR, textStatus) {
                                swal("OOPS...", "Something went wrong!", "error");
                            });
                        }
                    } else {
                        var sfdc_user_name = $("#sfdc_user_name").val();
                        var sfdc_password = $("#sfdc_password").val();
                        var sfdc_security_password = $("#sfdc_security_password").val();
                        $(".error").remove();
                        if (sfdc_user_name.length < 1) {
                            $("#sfdc_user_name").after('<label class="error">This field is required.</label>');
                            return false;
                        } else if (sfdc_password.length < 1) {
                            $("#sfdc_password").after('<label class="error">This field is required.</label>');
                            return false;
                        } else if (sfdc_security_password.length < 1) {
                            $("#sfdc_security_password").after('<label class="error">This field is required.</label>');
                            return false;
                        } else {
                            $.ajax({
                                url: "/bigcommerce-app-management/install/input-validation.php?type=validateSfdcInfo",
                                method: "POST",
                                data: {sfdc_user_name: sfdc_user_name, sfdc_password: sfdc_password, sfdc_security_password: sfdc_security_password},
                                dataType: "json"
                            }).done(function (result) {
                                swal("Correct!", "Your credentials are correct,We are updating...", "success");
                                window.location = "credential-update.php?sfdc_user_name=" + sfdc_user_name + "&sfdc_password=" + sfdc_password + "&sfdc_security_password=" + sfdc_security_password;
                            }).fail(function (jqXHR, textStatus) {
                                aswal("OOPS...", "Something went wrong!", "error");
                            });
                        }
                    }
                });
            });
        </script> 
    </body>
</html>
