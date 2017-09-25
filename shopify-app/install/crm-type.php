<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="/shopify-app/style/before-installation/style.css">
        <link rel="stylesheet" type="text/css" href="/shopify-app/style/sweetalert.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="/shopify-app/js/sweetalert.min.js"></script>
    </head>
    <body>
        <?php $insertDataId = $_GET['access_id']; ?>
        <?php // include_once("$_SERVER[DOCUMENT_ROOT]/shopify-app/analyticstracking.php") ?>
       <div class="page-wrap">
        <div class="grid-wrap">
            <img class="logo pad" src="/shopify-app/images/logo.png" alt="logo">
            <div class="row">
                <div class="grid-7">
                    <div class="pad">
                        <h1>Cloud Connector Installation</h1>
                        <h2>It is  simple process requiring less than 5 minutes of your time.</h2>
                        <form name="frmSubmit" id="sfdcInput" class="frmInput" method="post" action="#">
                            <div class="box">
                                <label>Select your Cloud App</label>
                                <select id="crmtype" name="crmtype">
                                    <option value="NOCRM">Select Cloud App</option>
                                    <option value="SFDC">Salesforce CRM</option>
                                    <option value="ZOHO">Zoho CRM</option>
									<option value="VTIGER">Vtiger CRM</option>
                                    <option value="ZOHO_INVENTORY">Zoho Inventory</option>
                                </select> 
                                <div id="zohoCredDiv" class="zohoCredDiv" style="display:none">
                                    <label>Zoho CRM auth token</label>
                                    <input type="text" id="zohoAuthToken"  name="zohoAuthToken" placeholder="Zoho auth token">
                                </div>
                                <div id="sfdcCredDiv" class="sfdcCredDiv" style="display:none">
                                    <label>Salesforce user name</label>
                                    <input type="text" id="sfdc_user_name" name="sfdc_user_name">
                                    <label>Salesforce password</label>
                                    <input type="password" id="sfdc_password" name="sfdc_password">
                                    <label>Salesforce security token</label>
                                    <input type="password" id="sfdc_security_password" name="sfdc_security_password">
                                </div>
								<div id="vtigerCredDiv" class="vtigerCredDiv" style="display:none">
                                    <label>vTiger Service Endpoint</label>
                                    <input type="text" id="vtigerEndpoint" name="vtigerEndpoint" placeholder="https://mycompany.od2.vtiger.com">
                                    <label>vTiger Username</label>
                                    <input type="text" id="vtigerUsername" name="vtigerUsername" placeholder="myname@mycompany.com">
                                    <label>vTiger Access Key </label>
                                    <input type="password" id="vtigerAccessKey" name="vtigerAccessKey" placeholder="aBCdef1GHijKLMnP">
                                </div>
                               <div id="zohoInvenCredDiv" class="zohoInvenCredDiv" style="display:none">
                                    <label>Zoho Inventory auth token</label>
                                    <input type="text" id="zohoInvenAuthToken"  name="zohoInvenAuthToken" placeholder="Zoho auth token">
                                    <label>Zoho Inventory Organization ID</label>
                                    <input type="text" id="zohoInvenOrgID"  name="zohoInvenOrgID" placeholder="Zoho Inventory Org ID">
                                </div>
                                <input class="primary-btn" type="submit" id="credentialCheck" name="submit" value="Submit"> 
                            </div>
                        </form>
                    </div>
                </div>
                <div class="grid-5 help">
                    <div class="pad sfdcCredDiv" style="display: none;">
                        <h2>Instructions</h2>
                        <p>[Your Name] → My Settings → Personal → Reset Security Token</p>
                        <p>You will get an email from Salseforce with Salseforce Security Token.</p>
                    </div>
                    <div class="pad zohoCredDiv" style="display: none;">
                        <h2>Instructions</h2>
                        <p>Login to Zoho CRM -> Setup ->APIs -> Authentication Token Generation</p>
                        <p>Give application name in the TextBox and Press Generate Button.</p>
                    </div>
					<div class="pad vtigerCredDiv" style="display: none;">
                        <h2>Instructions</h2>
						<p>To get your Access Key</p>
                        <p>Login to vtiger CRM -> My Preferences -> User Advanced Options -> Access Key</p>
                        <p>To find Service Endpoint</p>
						<p>Login to vTiger CRM and copy browser address and paste in the format shown here</p>
                    </div>
                    <div class="pad zohoInvenCredDiv" style="display: none;">
                        <h2>Instructions</h2>
                        <p>Login to Zoho Inventory -> Setup ->APIs -> Authentication Token Generation</p>
                        <p>Give application name in the TextBox and Press Generate Button.</p>
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
                var insertedId = "<?php echo $insertDataId; ?>";
                $("body").on('change', '#crmtype', function () {
                    var currentCrmType = $(this).val();
                    if (currentCrmType === 'ZOHO') {
                        $(".error").remove();
                        $(".zohoCredDiv").css('display', 'block');
                        $(".sfdcCredDiv").css('display', 'none');
						$(".vtigerCredDiv").css('display', 'none');
                        $(".zohoInvenCredDiv").css('display', 'none');
                    } else if (currentCrmType === 'SFDC') {
                        $(".zohoCredDiv").css('display', 'none');
                        $(".sfdcCredDiv").css('display', 'block');
                        $(".zohoInvenCredDiv").css('display', 'none');
						$(".vtigerCredDiv").css('display', 'none');
                        $(".error").remove();
					 } else if (currentCrmType === 'VTIGER') {
                        $(".zohoCredDiv").css('display', 'none');
                        $(".sfdcCredDiv").css('display', 'none');
                        $(".zohoInvenCredDiv").css('display', 'none');
						$(".vtigerCredDiv").css('display', 'block');
                        $(".error").remove();
                    } else if (currentCrmType === 'ZOHO_INVENTORY') {
                        $(".zohoCredDiv").css('display', 'none');
                        $(".sfdcCredDiv").css('display', 'none');
                        $(".zohoInvenCredDiv").css('display', 'block');
						$(".vtigerCredDiv").css('display', 'none');
                        $(".error").remove();
                    } else {
                        $(".zohoCredDiv").css('display', 'none');
                        $(".sfdcCredDiv").css('display', 'none');
                        $(".zohoInvenCredDiv").css('display', 'none');
						$(".vtigerCredDiv").css('display', 'none');
                        $(".error").remove();
                    }
                });
                $('body').on('click', '#credentialCheck', function (e) {
					
                    e.preventDefault();
                    var currentCrmType = $('#crmtype').val();
                    if (currentCrmType == 'ZOHO') {
                        var zohoAuthToken = $("#zohoAuthToken").val();
                        $(".error").remove();
                        if (zohoAuthToken.length < 1) {
                            $("#zohoAuthToken").after('<label class="error">This field is required.</label>');
                            return false;
                        } else {
                            $.ajax({
                                url: "input-validation.php?type=validateZohoZuthToken",
                                method: "POST",
                                data: {zohoAuthToken: zohoAuthToken},
                                dataType: "text"
                            }).done(function (result) {
                                if (result == 'true') {
                                    swal({
                                        title: "You've selected ZOHO CRM",
                                        text: "Your provided credential is correct!",
                                        type: "success",
                                        showCancelButton: true,
                                        confirmButtonText: "Yes, proceed next!",
                                        closeOnConfirm: false
                                    }, function () {
                                        window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType + "&zohoAuthToken=" + zohoAuthToken;
                                    });
                                } else {
                                    swal("OOPS...", "Wrong credentials!", "error");
                                }
                            }).fail(function (jqXHR, textStatus) {
                                swal("OOPS...", "Something went wrong!", "error");
                            });
                        }
                    } else if (currentCrmType == 'SFDC') {
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
                                url: "input-validation.php?type=validateSfdcInfo",
                                method: "POST",
                                data: {sfdc_user_name: sfdc_user_name, sfdc_password: sfdc_password, sfdc_security_password: sfdc_security_password},
                                dataType: "json"
                            }).done(function (result) {
                                swal({
                                    title: "You've selected SALSEFORCE CRM",
                                    text: "Your provided credentials are correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed next!",
                                    closeOnConfirm: false
                                }, function () {
                                     window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType + "&sfdc_user_name=" + sfdc_user_name + "&sfdc_password=" + sfdc_password + "&sfdc_security_password=" + sfdc_security_password;
                                });
                            }).fail(function (jqXHR, textStatus) {
                                swal("OOPS...", "Wrong credentials!", "error");
                            });
                        }
					} else if (currentCrmType == 'VTIGER') {
                        var vtigerEndpoint = $("#vtigerEndpoint").val();
                        var vtigerUsername = $("#vtigerUsername").val();
                        var vtigerAccessKey = $("#vtigerAccessKey").val();
                        $(".error").remove();
                        if (vtigerEndpoint.length < 1) {
                            $("#vtigerEndpoint").after('<label class="error">This field is required.</label>');
                            return false;
                        } else if (vtigerUsername.length < 1) {
                            $("#vtigerUsername").after('<label class="error">This field is required.</label>');
                            return false;
                        } else if (vtigerAccessKey.length < 1) {
                            $("#vtigerAccessKey").after('<label class="error">This field is required.</label>');
                            return false;
                        } else {
                            $.ajax({
                                url: "input-validation.php?type=validateVtigerToken",
                                method: "POST",
                                data: {vtigerEndpoint: vtigerEndpoint, vtigerUsername: vtigerUsername, vtigerAccessKey: vtigerAccessKey},
                                dataType: "json"
                            }).done(function (result) {
                                swal({
                                    title: "You've selected VTIGER CRM",
                                    text: "Your provided credentials are correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed next!",
                                    closeOnConfirm: false
                                }, function () {
                                     window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType + "&sfdc_user_name=" + vtigerUsername + "&sfdc_password=" + vtigerEndpoint + "&sfdc_security_password=" + vtigerAccessKey;
                                });
                            }).fail(function (jqXHR, textStatus) {
                                swal("OOPS...", "Wrong credentials!", "error");
                            });
                        }
                    } else if (currentCrmType == 'ZOHO_INVENTORY') {
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
                                url: "input-validation.php?type=validateZohoInvenZuthToken",
                                method: "POST",
                                data: {zohoInvenAuthToken: zohoInvenAuthToken, zohoInvenOrgID: zohoInvenOrgID},
                                dataType: "text"
                            }).done(function (result) {
                                if (result == 'true') {
                                    swal({
                                        title: "You've selected ZOHO INVENTORY",
                                        text: "Your provided credential are correct!",
                                        type: "success",
                                        showCancelButton: true,
                                        confirmButtonText: "Yes, proceed next!",
                                        closeOnConfirm: false
                                    }, function () {
                                        window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType + "&zohoInvenAuthToken=" + zohoInvenAuthToken + "&zohoInvenOrgID=" + zohoInvenOrgID;
                                    });
                                } else {
                                    swal("OOPS...", "Wrong credentials!", "error");
                                }
                            }).fail(function (jqXHR, textStatus) {
                                swal("OOPS...", "Something went wrong!", "error");
                            });
                        }
                      } else {
                        swal("Please select any Cloud App to proceed further!");
                    }
                });
            });
        </script>
        <script type="text/javascript">
         /*   var $zoho = $zoho || {};
            $zoho.salesiq = $zoho.salesiq ||
                {
                    widgetcode: "ef65a35676541685f18b32d11ac194752e5c4d06a654cfd6d2c912381758911d",
                    values: {},
                    ready: function () {
                    }
                };
            var d = document;
            s = d.createElement("script");
            s.type = "text/javascript";
            s.id = "zsiqscript";
            s.defer = true;
            s.src = "https://salesiq.zoho.com/widget";
            t = d.getElementsByTagName("script")[0];
            t.parentNode.insertBefore(s, t);
            d.write("<div id='zsiqwidget'></div>"); */
        </script>
    </body>
</html>

