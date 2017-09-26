<?php

session_start();
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if (isset($_SESSION['user_id'])) {
    //$insertDataId = $_GET['access_id'];
    $insertDataId = $_SESSION['user_id'];
} else {
    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}


?>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="../images/fav.png"/>
    <link rel="canonical" href="www.aquaapi.io/index.html"/>
    <title>New Subscription</title>

    <link rel="stylesheet" type="text/css" href="/app/style/before-installation/style.css">
    <link rel="stylesheet" type="text/css" href="/app/style/sweetalert.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="/app/js/sweetalert.min.js"></script>

    <style type="text/css">
        label.error2 {
            color: #ff0000;
            font-size: 12px;
            margin-top: -8px;
        }

        /*Checkboxes styles*/
        input[type="checkbox"] {
            display: none;
        }

        input[type="checkbox"] + label:before {
            content: '';
            display: block;
            width: 20px;
            height: 20px;
            border: 1px solid #6cc0e5;
            left: 0;
            top: 0;
            opacity: .6;
            -webkit-transition: all .12s, border-color .08s;
            transition: all .12s, border-color .08s;
        }

        input[type="checkbox"]:checked + label:before {
            width: 10px;
            top: -5px;
            left: 5px;
            border-radius: 0;
            opacity: 1;
            border-top-color: transparent;
            border-left-color: transparent;
            -webkit-transform: rotate(45deg);
            transform: rotate(45deg);
        }
    </style>

</head>
<body>

<?php include_once("$_SERVER[DOCUMENT_ROOT]/app/analyticstracking.php") ?>
<div class="page-wrap">
    <?php include "$_SERVER[DOCUMENT_ROOT]/app/header.php"; ?>
    <div class="grid-wrap">
        <div class="row">
            <h1>Cloud Connector Installation</h1>
            <h2>It is simple process requiring less than 5 minutes of your time.</h2>
            <div class="grid-5">
                <div class="pad">

                    <form name="frmSubmit" id="sfdcInput" class="frmInput" method="post" action="#">
                        <div class="box">

                            <h3>APP 1 Credentials</h3>
                            <label>Select your Cloud App</label>
                            <select id="crmtype" name="crmtype">
                                <option value="NOCRM">Select Cloud App</option>

                                <option value="MAGENTO">Magento</option>
                                <option value="BIGCOMMERCE">BigCommerce</option>
                                <option value="SHOPIFY">Shopify</option>
                                <option value="PRESTASHOP">Prestashop</option>
                            </select>


                            <div id="magentoCredDiv" class="magentoCredDiv" style="display:none">
                                <label>Magento Service Endpoint</label>
                                <input type="text" id="magentoEndpoint" name="magentoEndpoint"
                                       placeholder="https://www.mydomain.com">
                                <label>Magento Username</label>
                                <input type="text" id="magentoUsername" name="magentoUsername" placeholder="admin">
                                <label>Magento Password </label>
                                <input type="password" id="magentoAccessKey" name="magentoAccessKey"
                                       placeholder="aBCdef1GHijKLMnP">
                            </div>
                            <div id="bigcommerceCredDiv" class="bigcommerceCredDiv" style="display:none">
                                <label>API Path</label>
                                <input type="text" id="bigcommerceEndpoint" name="bigcommerceEndpoint"
                                       placeholder="https://store-abc123.mybigcommerce.com">
                                <label>API User</label>
                                <input type="text" id="bigcommerceUsername" name="bigcommerceUsername"
                                       placeholder="new_user">
                                <label>API Token</label>
                                <input type="text" id="bigcommerceAccessKey" name="bigcommerceAccessKey"
                                       placeholder="e59d76e03deeb85e0960d982f3d043750d89ecc8">
                            </div>
                            <div id="shopifyCredDiv" class="shopifyCredDiv2" style="display:none">
                                <label>Shopify Shop Address</label>
                                <input type="text" id="shopify_url" name="shopify_url"
                                       placeholder="myshopstore.myshopify.com">
                                <label>Shopify Api Key</label>
                                <input type="password" id="shopify_key" name="shopify_key"
                                       placeholder="aBCdef1GHijKLMnPSjkSf">
                                <label>Shopify App Password</label>
                                <input type="password" id="shopify_password" name="shopify_password"
                                       placeholder="e23s3f194eg3s6c3tgajd24j1c11a0sdf@">
                            </div>
                            <div id="prestashopCredDiv" class="prestashopCredDiv2" style="display:none">
                                <label>Prestashop Shop Address</label>
                                <input type="text" id="prestashop_url" name="prestashop_url"
                                       placeholder="myshopstore.prestashop.com">
                                <label>Prestashop Api Key</label>
                                <input type="password" id="prestashop_key" name="prestashop_key"
                                       placeholder="aBCdef1GHijKLMnPSjkSf">
                            </div>

                        </div>
                    </form>
                </div>
                <button class="primary-btn" type="button" id="credentialCheck" name="submit">Credential 1 Submit
                </button>
            </div>

            <!-- For App 2 -->
            <div class="grid-5">
                <div class="pad">

                    <h3>APP 2 Credentials</h3>
                    <form name="frmSubmit" id="sfdcInput2" class="frmInput" method="post" action="#">
                        <div class="box">
                            <label>Select your Cloud App</label>
                            <select id="crmtype2" name="crmtype">
                                <option value="NOCRM">Select Cloud App</option>
                                <option value="SFDC">Salesforce CRM</option>
                                <option value="ZOHO">Zoho CRM</option>
                                <option value="VTIGER">Vtiger CRM</option>
                                <option value="HUBSPOT">Hubspot Marketing</option>
                                <option value="ZOHO_INVENTORY">Zoho Inventory</option>
                            </select>
                            <div id="zohoCredDiv" class="zohoCredDiv2" style="display:none">
                                <div id="token_type_crm" style="display:none">
                                    <label>Zoho CRM auth token</label>
                                    <input type="text" id="zohoAuthToken2" name="zohoAuthToken"
                                           placeholder="Zoho auth token">
                                </div>
                                <div id="login_type_crm">
                                    <label>Zoho CRM User Name</label>
                                    <input type="text" id="zohoUserName" name="zohoUserName"
                                           placeholder="example@email.com">
                                    <label>Zoho CRM Password</label>
                                    <input type="password" id="zohoPassword" name="zohoPassword"
                                           placeholder="Zoho CRM Password">
                                </div>
                                <div class="boxes">
                                    <input type="checkbox" id="box-2">
                                    <label for="box-2" style="display: -webkit-inline-box;"> &nbsp Use AuthToken
                                        Instead</label>
                                </div>
                            </div>
                            <div id="sfdcCredDiv" class="sfdcCredDiv2" style="display:none">
                                <label>Salesforce user name</label>
                                <input type="text" id="sfdc_user_name2" name="sfdc_user_name"
                                       placeholder="myname@mycompany.com">
                                <label>Salesforce password</label>
                                <input type="password" id="sfdc_password2" name="sfdc_password"
                                       placeholder="aBCdef1GHijKLMnPSjkSf">
                                <label>Salesforce security token</label>
                                <input type="password" id="sfdc_security_password2" name="sfdc_security_password"
                                       placeholder="e23s3f194eg3s6c3tgajd24j1c11a0sdf@">
                            </div>
                            <div id="vtigerCredDiv" class="vtigerCredDiv2" style="display:none">
                                <label>vTiger Service Endpoint</label>
                                <input type="text" id="vtigerEndpoint2" name="vtigerEndpoint"
                                       placeholder="https://mycompany.od2.vtiger.com">
                                <label>vTiger Username</label>
                                <input type="text" id="vtigerUsername2" name="vtigerUsername"
                                       placeholder="myname@mycompany.com">
                                <label>vTiger Access Key </label>
                                <input type="password" id="vtigerAccessKey2" name="vtigerAccessKey"
                                       placeholder="aBCdef1GHijKLMnP">
                            </div>
                            <div id="zohoInvenCredDiv" class="zohoInvenCredDiv2" style="display:none">
                                <div id="token_type_inventory" style="display:none">
                                    <label>Zoho Inventory auth token</label>
                                    <input type="text" id="zohoInvenAuthToken2" name="zohoInvenAuthToken"
                                           placeholder="Zoho auth token">
                                    <div class="zohoInventoryOrgDivApi">
                                        <!-- After Login Checking The Fileds will Populate Here -->

                                    </div>
                                </div>
                                <div id="login_type_inventory">
                                    <label>Zoho Inventory User Name</label>
                                    <input type="text" id="zohoInvenUserName" name="zohoInvenUserName"
                                           placeholder="example@email.com">
                                    <label>Zoho Inventory Password</label>
                                    <input type="password" id="zohoInvenPassword" name="zohoInvenPassword"
                                           placeholder="Zoho Inventory Password">
                                    <div class="zohoInventoryOrgDivLogin">
                                        <!-- After Login Checking The Fileds will Populate Here -->

                                    </div>
                                </div>
                                <div class="boxes">
                                    <input type="checkbox" id="box-1">
                                    <label for="box-1" style="display: -webkit-inline-box;"> &nbsp Use AuthToken
                                        Instead</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <button class="primary-btn" type="button" id="credential2Check" name="submit" style="display: none;">
                    Credential 2 Submit
                </button>
            </div>

            <div class="grid-5 help">

                <!-- For App 1 -->
                <div class="app1help" style="display: none;">

                    <div class="pad magentoCredDiv" style="display: none;">
                        <h2>Instructions</h2>
                        <p>Login to Magento CRM → Look For URL bar and Copy the url (ex. sample.exmple.....com/)</p>
                        <p>Give Your login Email and Password.</p>
                    </div>
                    <div class="pad bigcommerceCredDiv" style="display: none;">
                        <h2>Instructions</h2>
                        <p>From BigCommerce Admin Panel, go to <b>"Advanced Settings"</b> > <b>"Legacy API Accounts"</b>
                            to setup your credentials.</p>
                        <p>Your credentials will be in the following format</p>
                        <p><b>API Path:</b> https://store-abc123.mybigcommerce.com/api/v2/</p>
                        <p><b>API User:</b> new_user</p>
                        <p><b>API Token:</b> e59d76e03deeb85e0960d982f3d043750d89ecc8</p>
                    </div>
                    <div class="pad shopifyCredDiv2" style="display: none;">
                        <h2>Instructions</h2>
                        <p>Enter Your Shopify </p>
                        <p>Login to Shopify -> Look Url Bar of the browser and Copy it without https:// -></p>
                        <p>To get your Api Key and Password</p>
                        <p>Login to Shopify -> Apps -> Manage private apps -> Generate API Credentials</p>
                    </div>
                    <div class="pad prestashopCredDiv2" style="display: none;">
                        <h2>Instructions</h2>
                        <p>Enter Your Prestashop </p>
                        <p>Login to Prestashop -> Look Url Bar of the browser and Copy it  -></p>
                        <p>To get your Api Key </p>
                        <p>Login to Prestashop Admin -> Advanced Parameters -> Webservices -> Add New</p>
                    </div>
                </div>
                <div class="app2help" style="display: none;">
                    <!-- For App 2 -->
                    <div class="pad sfdcCredDiv2" style="display: none;">
                        <h2>Instructions</h2>
                        <p>[Your Name] → My Settings → Personal → Reset Security Token</p>
                        <p>You will get an email from Salseforce with Salseforce Security Token.</p>
                    </div>
                    <div class="pad zohoCredDiv2" style="display: none;">
                        <div class="token-type-crm" style="display: none;">
                            <h2>Instructions</h2>
                            <p>Login to Zoho CRM -> Setup ->APIs -> Authentication Token Generation</p>
                            <p>Give application name in the TextBox and Press Generate Button.</p>
                        </div>
                        <div class="login-type-crm">
                            <h2>Instructions</h2>
                            <p>Enter Zoho CRM User Email -> </p>
                            <p>Enter Zoho CRM Password -></p>
                        </div>
                    </div>
                    <div class="pad vtigerCredDiv2" style="display: none;">
                        <h2>Instructions</h2>
                        <p>To get your Access Key</p>
                        <p>Login to vtiger CRM -> My Preferences -> User Advanced Options -> Access Key</p>
                        <p>To find Service Endpoint</p>
                        <p>Login to vTiger CRM and copy browser address and paste in the format shown here</p>
                    </div>
                    <div class="pad zohoInvenCredDiv2" style="display: none;">
                        <div class="token-type-help" style="display: none;">
                            <h2>Instructions</h2>
                            <p>Login to Zoho Inventory -> Setup ->APIs -> Authentication Token Generation</p>
                            <p>Still need Help. Click <a
                                        href="https://accounts.zoho.com/apiauthtoken/create?SCOPE=ZohoInventory/inventoryapi"
                                        target="_blank">Here</a></p>
                            <!--p>Give application name in the TextBox and Press Generate Button.</p-->
                            <p>For Organization Id. Click <a href="https://inventory.zoho.com/app#/organizations"
                                                             target="_blank">Here</a></p>
                        </div>
                        <div class="login-type-help">
                            <h2>Instructions</h2>
                            <p>Enter Zoho Inventory User Email -> </p>
                            <p>Enter Zoho Inventory Password -></p>
                            <p>Enter Zoho Inventory Organisation Id ( Click on Profile Name -> My Organisation -> Manage
                                -> Organisation Id)</p>
                        </div>
                    </div>

                </div>
                <div class="app3help" style="display: none;">
                    <!-- For App 2 -->
                    <div class="pad">
                        <h2>All Set</h2>
                        <p>You can'Try Now' to sync last one hour of data to test.</p>
                        <p>From App 1 to App 2</p>
                    </div>
                    <button class="primary-btn" type="button" id="try_sync" name="try_sync"
                            data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Synchronizing....">Try Now
                    </button> &nbsp;Or
                    <input class="warning-btn" type="button" id="skip_next" name="skip_next" value="Skip to Next">
                </div>
            </div>


        </div>
        <!-- Inserted Id value Stored in  -->
        <input type="hidden" id="subscription_id">

    </div>
</div>
<div class="footer">
    <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
    <a target="_blank" href="http://aquaapi.io/termsofservice.html">Terms And Conditions</a> | <a target="_blank">Privacy
        Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
</div>
<script>
    $(document).ready(function () {
        var insertedId = "<?php echo $insertDataId; ?>";

        //For App1
        $("body").on('change', '#crmtype', function () {
            $(".app2help").css('display', 'none');
            $(".app1help").css('display', 'block');
            var currentCrmType = $(this).val();
            if (currentCrmType === 'MAGENTO') {
                $(".error").remove();
                $(".magentoCredDiv").css('display', 'block');
                $(".bigcommerceCredDiv").css('display', 'none');
                $(".shopifyCredDiv2").css('display', 'none');
                $(".prestashopCredDiv2").css('display', 'none');
            } else if (currentCrmType === 'BIGCOMMERCE') {
                $(".error").remove();
                $(".magentoCredDiv").css('display', 'none');
                $(".bigcommerceCredDiv").css('display', 'block');
                $(".shopifyCredDiv2").css('display', 'none');
                $(".prestashopCredDiv2").css('display', 'none');
            } else if (currentCrmType === 'SHOPIFY') {
                $(".magentoCredDiv").css('display', 'none');
                $(".bigcommerceCredDiv").css('display', 'none');
                $(".shopifyCredDiv2").css('display', 'block');
                $(".prestashopCredDiv2").css('display', 'none');
                $(".error2").remove();
            } else if (currentCrmType === 'PRESTASHOP') {
                $(".magentoCredDiv").css('display', 'none');
                $(".bigcommerceCredDiv").css('display', 'none');
                $(".shopifyCredDiv2").css('display', 'none');
                $(".prestashopCredDiv2").css('display', 'block');
                $(".error2").remove();
            } else {
                $(".error").remove();
                $(".magentoCredDiv").css('display', 'none');
                $(".bigcommerceCredDiv").css('display', 'none');
                $(".shopifyCredDiv2").css('display', 'none');
                $(".prestashopCredDiv2").css('display', 'none');
            }
        });

        //For App 2
        $("body").on('change', '#crmtype2', function () {
            $(".app1help").css('display', 'none');

            $(".app2help").css('display', 'block');
            var currentCrmType = $(this).val();
            if (currentCrmType === 'ZOHO') {
                $(".error2").remove();
                $(".zohoCredDiv2").css('display', 'block');
                $(".sfdcCredDiv2").css('display', 'none');
                $(".vtigerCredDiv2").css('display', 'none');
                $(".zohoInvenCredDiv2").css('display', 'none');
            }
            else if (currentCrmType === 'SFDC') {
                $(".zohoCredDiv2").css('display', 'none');
                $(".sfdcCredDiv2").css('display', 'block');
                $(".zohoInvenCredDiv2").css('display', 'none');
                $(".vtigerCredDiv2").css('display', 'none');
                $(".error2").remove();
            }
            else if (currentCrmType === 'VTIGER') {
                $(".zohoCredDiv2").css('display', 'none');
                $(".sfdcCredDiv2").css('display', 'none');
                $(".zohoInvenCredDiv2").css('display', 'none');
                $(".vtigerCredDiv2").css('display', 'block');
                $(".error2").remove();
            }
            else if (currentCrmType === 'ZOHO_INVENTORY') {
                $(".zohoCredDiv2").css('display', 'none');
                $(".sfdcCredDiv2").css('display', 'none');
                $(".zohoInvenCredDiv2").css('display', 'block');
                $(".vtigerCredDiv2").css('display', 'none');
                $(".error2").remove();
                $('#credential2Check').html('Check Credential 2');
            }
            else if (currentCrmType === 'HUBSPOT') {
                $(".zohoCredDiv2").css('display', 'none');
                $(".sfdcCredDiv2").css('display', 'none');
                $(".zohoInvenCredDiv2").css('display', 'none');
                $(".vtigerCredDiv2").css('display', 'none');
                $(".error2").remove();
            }
            else {
                $(".zohoCredDiv2").css('display', 'none');
                $(".sfdcCredDiv2").css('display', 'none');
                $(".zohoInvenCredDiv2").css('display', 'none');
                $(".vtigerCredDiv2").css('display', 'none');
                $(".error2").remove();
            }
        });

        $('body').on('click', '#credentialCheck', function (e) {

            e.preventDefault();

            var currentCrmType = $('#crmtype').val();
            if (currentCrmType === "NOCRM") {
                swal("Please Select  App 1 Credential");
            }
            else {
                // For App 1
                if (currentCrmType === 'MAGENTO') {
                    var magentoEndpoint = $("#magentoEndpoint").val();
                    var magentoUsername = $("#magentoUsername").val();
                    var magentoAccessKey = $("#magentoAccessKey").val();
                    $(".error").remove();
                    if (magentoEndpoint.length < 1) {
                        $("#magentoEndpoint").after('<label class="error">This field is required.</label>');
                        return false;
                    } else if (magentoUsername.length < 1) {
                        $("#magentoUsername").after('<label class="error">This field is required.</label>');
                        return false;
                    } else if (magentoAccessKey.length < 1) {
                        $("#magentoAccessKey").after('<label class="error">This field is required.</label>');
                        return false;
                    } else {
                        $('#credentialCheck').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        $.ajax({
                            url: "input-validation.php?type=validateMagentoToken",
                            method: "POST",
                            data: {
                                magentoEndpoint: magentoEndpoint,
                                magentoUsername: magentoUsername,
                                magentoAccessKey: magentoAccessKey
                            }
                        }).done(function (result) {
                            $('#credentialCheck').html('Credential 1 Submit');
                            if (result === 'true') {
                                swal({
                                    title: "You've selected MAGENTO as App 1",
                                    text: "Your provided credentials are correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed To next Credential!",
                                    showLoaderOnConfirm: true,
                                    closeOnConfirm: true
                                }, function () {


                                    $.ajax({
                                        url: "crm-choosen.php",
                                        type: "get", //send it through get method
                                        data: {
                                            insertedId: insertedId,
                                            crmType: currentCrmType,
                                            magento_name: magentoUsername,
                                            magento_password: magentoAccessKey,
                                            magento_endpoint: magentoEndpoint,
                                            credential: 'credential',
                                            function_name: 'No'
                                        },
                                        success: function (response) {
                                            if ($.isNumeric(response)) {
                                                //inserted id stored in hidden field
                                                $('#subscription_id').val(response);

                                                $('#credentialCheck').css('display', 'none');
                                                $('#credential2Check').css('display', 'block');
                                                $('#sfdcInput :input').prop("disabled", true);
                                            }
                                            else {
                                                swal("OOPS...", "Unable  To Process Credential", "error");
                                            }
                                        },
                                        error: function (xhr) {
                                            swal("OOPS...", "Unable  To Insert Credential", "error");
                                        }
                                    });

                                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType + "&magento_name=" + magentoUsername + "&magento_password=" + magentoAccessKey + "&magento_endpoint=" + magentoEndpoint;
                                });
                            }
                            else {
                                swal("OOPS...", "Wrong Credential in App 1", "error");
                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something Went Wrong in App 1", "error");
                        });
                    }
                }
                else if (currentCrmType === 'BIGCOMMERCE') {
                    var bigcommerceEndpoint = $("#bigcommerceEndpoint").val();
                    var bigcommerceUsername = $("#bigcommerceUsername").val();
                    var bigcommerceAccessKey = $("#bigcommerceAccessKey").val();
                    $(".error").remove();
                    if (bigcommerceEndpoint.length < 1) {
                        $("#bigcommerceEndpoint").after('<label class="error">This field is required.</label>');
                        return false;
                    } else if (bigcommerceUsername.length < 1) {
                        $("#bigcommerceUsername").after('<label class="error">This field is required.</label>');
                        return false;
                    } else if (bigcommerceAccessKey.length < 1) {
                        $("#bigcommerceAccessKey").after('<label class="error">This field is required.</label>');
                        return false;
                    } else {
                        $('#credentialCheck').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        $.ajax({
                            url: "input-validation.php?type=validateBigcommerceToken",
                            method: "POST",
                            data: {
                                bigcommerceEndpoint: bigcommerceEndpoint,
                                bigcommerceUsername: bigcommerceUsername,
                                bigcommerceAccessKey: bigcommerceAccessKey
                            }
                        }).done(function (result) {
                            $('#credentialCheck').html('Credential 1 Submit');
                            if (result === 'true') {
                                swal({
                                    title: "You've selected BigCommerce as App 1",
                                    text: "Your provided credentials are correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed To next Credential!",
                                    showLoaderOnConfirm: true,
                                    closeOnConfirm: true
                                }, function () {

                                    $.ajax({
                                        url: "crm-choosen.php",
                                        type: "get", //send it through get method
                                        data: {
                                            insertedId: insertedId,
                                            crmType: currentCrmType,
                                            bigcommerce_name: bigcommerceUsername,
                                            bigcommerce_password: bigcommerceAccessKey,
                                            bigcommerce_endpoint: bigcommerceEndpoint,
                                            credential: 'credential',
                                            function_name: 'No'
                                        },
                                        success: function (response) {
                                            if ($.isNumeric(response)) {
                                                //inserted id stored in hidden field
                                                $('#subscription_id').val(response);

                                                $('#credentialCheck').css('display', 'none');
                                                $('#credential2Check').css('display', 'block');
                                                $('#sfdcInput :input').prop("disabled", true);
                                            }
                                            else {
                                                swal("OOPS...", "Unable  To Process Credential", "error");
                                            }
                                        },
                                        error: function (xhr) {
                                            swal("OOPS...", "Unable  To Insert Credential", "error");
                                        }
                                    });

                                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType + "&magento_name=" + magentoUsername + "&magento_password=" + magentoAccessKey + "&magento_endpoint=" + magentoEndpoint;
                                });
                            }
                            else {
                                swal("OOPS...", "Wrong Credential in App 1", "error");
                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something Went Wrong in App 1", "error");
                        });
                    }
                }
                else if (currentCrmType === 'SHOPIFY') {
                    var credential = $('#subscription_id').val();
                    var shopifyUrl = $("#shopify_url").val();
                    var shopifyApikey = $("#shopify_key").val();
                    var shopifyPassword = $("#shopify_password").val();
                    $(".error2").remove();
                    if (shopifyUrl.length < 1) {
                        $("#shopify_url").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (shopifyApikey.length < 1) {
                        $("#shopify_key").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (shopifyPassword.length < 1) {
                        $("#shopify_password").after('<label class="error">This field is required.</label>');
                        return false;
                    } else {
                        $('#credentialCheck').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        $.ajax({
                            url: "input-validation.php?type=validateShopifyToken",
                            method: "POST",
                            data: {
                                shopifyUrl: shopifyUrl,
                                shopifyApikey: shopifyApikey,
                                shopifyPassword: shopifyPassword
                            }
                        }).done(function (result) {
                            if (result === 'true') {
                                $('#credentialCheck').html('Credential 1 Submit');
                                swal({
                                    title: "You've selected SHOPIFY In App 1",
                                    text: "Your provided credentials are correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed To next Credential!",
                                    showLoaderOnConfirm: true,
                                    closeOnConfirm: true
                                }, function () {

                                    $.ajax({
                                        url: "crm-choosen.php",
                                        type: "get", //send it through get method
                                        data: {
                                            insertedId: insertedId,
                                            crmType: currentCrmType,
                                            shopifyUrl: shopifyUrl,
                                            shopifyApikey: shopifyApikey,
                                            shopifyPassword: shopifyPassword,
                                            credential: 'credential',
                                            function_name: 'No'
                                        },
                                        success: function (response) {
                                            if ($.isNumeric(response)) {
                                                //inserted id stored in hidden field
                                                $('#subscription_id').val(response);

                                                $('#credentialCheck').css('display', 'none');
                                                $('#credential2Check').css('display', 'block');
                                                $('#sfdcInput :input').prop("disabled", true);
                                            }
                                            else {
                                                swal("OOPS...", "Unable  To Process Credential", "error");
                                            }
                                        },
                                        error: function (xhr) {
                                            swal("OOPS...", "Unable  To Insert Credential", "error");
                                        }
                                    });
                                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&vtiger_user_name=" + vtigerUsername + "&vtiger_password=" + vtigerEndpoint + "&vtiger_security_password=" + vtigerAccessKey + "&credential=" + credential + "&function_name=" + function_name;
                                });
                            }
                            else {
                                swal("OOPS...", "Wrong credentials In App 2!", "error");
                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Wrong credentials In App 2!", "error");
                        });
                    }
                }
                else if (currentCrmType === 'PRESTASHOP') {
                    var credential = $('#subscription_id').val();
                    var prestashopUrl = $("#prestashop_url").val();
                    var prestashopApikey = $("#prestashop_key").val();
                    $(".error2").remove();
                    if (prestashopUrl.length < 1) {
                        $("#prestashop_url").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (prestashopApikey.length < 1) {
                        $("#prestashop_key").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else {
                        $('#credentialCheck').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        $.ajax({
                            url: "input-validation.php?type=validatePrestashopToken",
                            method: "POST",
                            data: {
                                prestashopUrl: prestashopUrl,
                                prestashopApikey: prestashopApikey
                            }
                        }).done(function (result) {
                            if (result === 'true') {
                                $('#credentialCheck').html('Credential 1 Submit');
                                swal({
                                    title: "You've selected PRESTASHOP In App 1",
                                    text: "Your provided credentials are correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed To next Credential!",
                                    showLoaderOnConfirm: true,
                                    closeOnConfirm: true
                                }, function () {

                                    $.ajax({
                                        url: "crm-choosen.php",
                                        type: "get", //send it through get method
                                        data: {
                                            insertedId: insertedId,
                                            crmType: currentCrmType,
                                            prestashopUrl: prestashopUrl,
                                            prestashopApikey: prestashopApikey,
                                            credential: 'credential',
                                            function_name: 'No'
                                        },
                                        success: function (response) {
                                            if ($.isNumeric(response)) {
                                                //inserted id stored in hidden field
                                                $('#subscription_id').val(response);

                                                $('#credentialCheck').css('display', 'none');
                                                $('#credential2Check').css('display', 'block');
                                                $('#sfdcInput :input').prop("disabled", true);
                                            }
                                            else {
                                                swal("OOPS...", "Unable  To Process Credential", "error");
                                            }
                                        },
                                        error: function (xhr) {
                                            swal("OOPS...", "Unable  To Insert Credential", "error");
                                        }
                                    });
                                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&vtiger_user_name=" + vtigerUsername + "&vtiger_password=" + vtigerEndpoint + "&vtiger_security_password=" + vtigerAccessKey + "&credential=" + credential + "&function_name=" + function_name;
                                });
                            }
                            else {
                                swal("OOPS...", "Wrong credentials In App 2!", "error");
                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Wrong credentials In App 2!", "error");
                        });
                    }
                }
                else {
                    swal("Please select any Cloud App 1 to proceed further!");
                }
            }

        });

        $('body').on('click', '#credential2Check', function (e) {

            e.preventDefault();

            //for functionname insert in db
            var currentCrmType = $('#crmtype').val();
            if (currentCrmType === 'MAGENTO') {
                var function_name = 'magentoTo';
            }
            else if (currentCrmType === 'BIGCOMMERCE') {
                var function_name = 'bigcommerceTo';
            }
            else {
                var function_name = 'shopifyTo';
            }

            //Current Crm type 2 Credential Checking
            var currentCrmType2 = $('#crmtype2').val();

            if (currentCrmType2 === "NOCRM") {
                swal("Please Select  Credential 2 to proceed further!");
            }
            else {
                if (currentCrmType2 === 'ZOHO') {
                    var credential = $('#subscription_id').val();
                    var zohoAuthToken = '';
                    var zohoUserName = '';
                    var zohoPassword = '';
                    var type = '';
                    if ($('#box-2').is(':checked')) {
                        zohoAuthToken = $("#zohoAuthToken2").val();
                        $(".error2").remove();
                        if (zohoAuthToken.length < 1) {
                            $("#zohoAuthToken2").after('<label class="error2">This field is required.</label>');
                            return false;
                        } else {
                            num = 1;
                            type = 'api';
                        }


                    }
                    else {
                        zohoUserName = $("#zohoUserName").val();
                        zohoPassword = $("#zohoPassword").val();
                        //zohoInvenOrgID = $("#zohoInvenOrgId").val();
                        $(".error2").remove();
                        if (zohoUserName.length < 1) {
                            $("#zohoUserName").after('<label class="error2">This field is required.</label>');
                            return false;
                        } else if (zohoPassword.length < 1) {
                            $("#zohoPassword").after('<label class="error2">This field is required.</label>');
                            return false;
                        } else {
                            num = 1;
                            type = 'login';
                        }
                    }

                    if (type === 'api') {

                        $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        function_name = function_name + 'zoho';

                        $.ajax({
                            url: "input-validation.php?type=validateZohoZuthToken",
                            method: "POST",
                            data: {zohoAuthToken: zohoAuthToken, authType: type},
                            dataType: "text"
                        }).done(function (result) {
                            $('#credential2Check').html('Credential 2 Submit');
                            if (result === 'true') {
                                swal({
                                    title: "You've selected ZOHO CRM In App 2",
                                    text: "Your provided credential is correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed next!",
                                    showLoaderOnConfirm: true,
                                    closeOnConfirm: false
                                }, function () {

                                    $.ajax({
                                        url: "crm-choosen.php",
                                        type: "get", //send it through get method
                                        data: {
                                            insertedId: insertedId,
                                            crmType: currentCrmType2,
                                            zohoAuthToken: zohoAuthToken,
                                            credential: credential,
                                            function_name: function_name
                                        },
                                        success: function (response) {
                                            if ($.isNumeric(response)) {
                                                swal("Success", "App 2 Credential Update Successfull", "success");
                                                $('#credential2Check').css('display', 'none');
                                                $('.app1help').css('display', 'none');
                                                $('.app2help').css('display', 'none');
                                                $('.app3help').css('display', 'block');
                                                $('#sfdcInput2 :input').prop("disabled", true);
                                            }
                                            else {
                                                swal("OOPS...", "Unable  To Process Credential", "error");
                                            }
                                        },
                                        error: function (xhr) {
                                            swal("OOPS...", "Unable  To Insert Credential", "error");
                                        }
                                    });

                                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&zohoAuthToken=" + zohoAuthToken + "&credential=" + credential + "&function_name=" + function_name;
                                });
                            } else {
                                swal("OOPS...", "Wrong credentials in App 2 !", "error");
                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something went wrong in App 2!", "error");
                        });

                    }
                    else {

                        $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        function_name = function_name + 'zoho';

                        $.ajax({
                            url: "input-validation.php?type=validateZohoZuthToken",
                            method: "POST",
                            data: {zohoUserName: zohoUserName, zohoPassword: zohoPassword, authType: type},
                            dataType: "text"
                        }).done(function (result) {
                            $('#credential2Check').html('Credential 2 Submit');
                            if (result !== 'false') {
                                swal({
                                    title: "You've selected ZOHO CRM In App 2",
                                    text: "Your provided credential is correct!",
                                    type: "success",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, proceed next!",
                                    showLoaderOnConfirm: true,
                                    closeOnConfirm: false
                                }, function () {

                                    $.ajax({
                                        url: "crm-choosen.php",
                                        type: "get", //send it through get method
                                        data: {
                                            insertedId: insertedId,
                                            crmType: currentCrmType2,
                                            zohoAuthToken: result,
                                            credential: credential,
                                            function_name: function_name
                                        },
                                        success: function (response) {
                                            if ($.isNumeric(response)) {
                                                swal("Success", "App 2 Credential Update Successfull", "success");
                                                $('#credential2Check').css('display', 'none');
                                                $('.app1help').css('display', 'none');
                                                $('.app2help').css('display', 'none');
                                                $('.app3help').css('display', 'block');
                                                $('#sfdcInput2 :input').prop("disabled", true);
                                            }
                                            else {
                                                swal("OOPS...", "Unable  To Process Credential", "error");
                                            }
                                        },
                                        error: function (xhr) {
                                            swal("OOPS...", "Unable  To Insert Credential", "error");
                                        }
                                    });

                                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&zohoAuthToken=" + zohoAuthToken + "&credential=" + credential + "&function_name=" + function_name;
                                });
                            } else {
                                swal("OOPS...", "Wrong credentials in App 2 !", "error");
                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something went wrong in App 2!", "error");
                        });

                    }
                }
                else if (currentCrmType2 === 'SFDC') {
                    var credential = $('#subscription_id').val();
                    var sfdc_user_name = $("#sfdc_user_name2").val();
                    var sfdc_password = $("#sfdc_password2").val();
                    var sfdc_security_password = $("#sfdc_security_password2").val();
                    $(".error2").remove();
                    if (sfdc_user_name.length < 1) {
                        $("#sfdc_user_name2").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (sfdc_password.length < 1) {
                        $("#sfdc_password2").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (sfdc_security_password.length < 1) {
                        $("#sfdc_security_password2").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else {
                        $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        function_name = function_name + 'sfdc';
                        $.ajax({
                            url: "input-validation.php?type=validateSfdcInfo",
                            method: "POST",
                            data: {
                                sfdc_user_name: sfdc_user_name,
                                sfdc_password: sfdc_password,
                                sfdc_security_password: sfdc_security_password
                            },
                            dataType: "json"
                        }).done(function (result) {
                            $('#credential2Check').html('Credential 2 Submit');
                            swal({
                                title: "You've selected SALSEFORCE CRM In App 2",
                                text: "Your provided credentials are correct!",
                                type: "success",
                                showCancelButton: true,
                                confirmButtonText: "Yes, proceed next!",
                                showLoaderOnConfirm: true,
                                closeOnConfirm: false
                            }, function () {
                                $.ajax({
                                    url: "crm-choosen.php",
                                    type: "get", //send it through get method
                                    data: {
                                        insertedId: insertedId,
                                        crmType: currentCrmType2,
                                        sfdc_user_name: sfdc_user_name,
                                        sfdc_password: sfdc_password,
                                        sfdc_security_password: sfdc_security_password,
                                        credential: credential,
                                        function_name: function_name
                                    },
                                    success: function (response) {
                                        if ($.isNumeric(response)) {
                                            //inserted id stored in hidden field
                                            swal("Success", "App 2 Credential Update Successfull", "success");
                                            $('#credential2Check').css('display', 'none');
                                            $('.app1help').css('display', 'none');
                                            $('.app2help').css('display', 'none');
                                            $('.app3help').css('display', 'block');
                                            $('#sfdcInput2 :input').prop("disabled", true);
                                        }
                                        else {
                                            swal("OOPS...", "Unable  To Process Credential", "error");
                                        }
                                    },
                                    error: function (xhr) {
                                        swal("OOPS...", "Unable  To Insert Credential", "error");
                                    }
                                });
                                // window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&sfdc_user_name=" + sfdc_user_name + "&sfdc_password=" + sfdc_password + "&sfdc_security_password=" + sfdc_security_password + "&credential=" + credential + "&function_name=" + function_name;
                            });
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Wrong credentials In App 2!", "error");
                        });
                    }
                }
                else if (currentCrmType2 === 'VTIGER') {
                    var credential = $('#subscription_id').val();
                    var vtigerEndpoint = $("#vtigerEndpoint2").val();
                    var vtigerUsername = $("#vtigerUsername2").val();
                    var vtigerAccessKey = $("#vtigerAccessKey2").val();
                    $(".error2").remove();
                    if (vtigerEndpoint.length < 1) {
                        $("#vtigerEndpoint2").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (vtigerUsername.length < 1) {
                        $("#vtigerUsername2").after('<label class="error2">This field is required.</label>');
                        return false;
                    } else if (vtigerAccessKey.length < 1) {
                        $("#vtigerAccessKey2").after('<label class="error">This field is required.</label>');
                        return false;
                    } else {
                        $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        function_name = function_name + 'vtiger';
                        $.ajax({
                            url: "input-validation.php?type=validateVtigerToken",
                            method: "POST",
                            data: {
                                vtigerEndpoint: vtigerEndpoint,
                                vtigerUsername: vtigerUsername,
                                vtigerAccessKey: vtigerAccessKey
                            },
                            dataType: "json"
                        }).done(function (result) {
                            $('#credential2Check').html('Credential 2 Submit');
                            swal({
                                title: "You've selected VTIGER CRM In App 2",
                                text: "Your provided credentials are correct!",
                                type: "success",
                                showCancelButton: true,
                                confirmButtonText: "Yes, proceed next!",
                                showLoaderOnConfirm: true,
                                closeOnConfirm: false
                            }, function () {

                                $.ajax({
                                    url: "crm-choosen.php",
                                    type: "get", //send it through get method
                                    data: {
                                        insertedId: insertedId,
                                        crmType: currentCrmType2,
                                        vtiger_user_name: vtigerUsername,
                                        vtiger_password: vtigerEndpoint,
                                        vtiger_security_password: vtigerAccessKey,
                                        credential: credential,
                                        function_name: function_name
                                    },
                                    success: function (response) {
                                        if ($.isNumeric(response)) {
                                            //inserted id stored in hidden field
                                            swal("Success", "App 2 Credential Update Successfull", "success");
                                            $('#credential2Check').css('display', 'none');
                                            $('.app1help').css('display', 'none');
                                            $('.app2help').css('display', 'none');
                                            $('.app3help').css('display', 'block');
                                            $('#sfdcInput2 :input').prop("disabled", true);

                                        }
                                        else {
                                            swal("OOPS...", "Unable  To Process Credential", "error");
                                        }
                                    },
                                    error: function (xhr) {
                                        swal("OOPS...", "Unable  To Insert Credential", "error");
                                    }
                                });
                                //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&vtiger_user_name=" + vtigerUsername + "&vtiger_password=" + vtigerEndpoint + "&vtiger_security_password=" + vtigerAccessKey + "&credential=" + credential + "&function_name=" + function_name;
                            });
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Wrong credentials In App 2!", "error");
                        });
                    }
                }
                else if (currentCrmType2 === 'HUBSPOT') {
                    var credential = $('#subscription_id').val();
                    $(".error2").remove();
                    $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i> Loading..');
                    //Popup Start For Access in Hubspot CRM..........
                    var w = 620;
                    var h = 360;
                    var height = 400;
                    var width = 650;
                    var left = (window.screen.width / 2) - ((w / 2) + 10);
                    var top = (window.screen.height / 2) - ((h / 2) + 50);
                    window.open('hubspot/app-login.php', 'Access to Hubspot',
                        "status=no,height=" + height + ",width=" + width + ",resizable=yes,left="
                        + left + ",top=" + top + ",screenX=" + left + ",screenY="
                        + top + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no");
                    //..........End of Popup..
                }
                else if (currentCrmType2 === 'ZOHO_INVENTORY') {
                    var credential = $('#subscription_id').val();
                    var zohoInvenAuthToken = '';
                    var zohoInvenUserName = '';
                    var zohoInvenPassword = '';
                    var zohoInvenOrgID = '';
                    var type = '';
                    if ($('#box-1').is(':checked')) {
                        zohoInvenAuthToken = $("#zohoInvenAuthToken2").val();
                        $(".error2").remove();
                        if (zohoInvenAuthToken.length < 1) {
                            $("#zohoInvenAuthToken2").after('<label class="error2">This field is required.</label>');
                            return false;
                        } else {
                            num = 1;
                            type = 'api';
                        }


                    }
                    else {
                        zohoInvenUserName = $("#zohoInvenUserName").val();
                        zohoInvenPassword = $("#zohoInvenPassword").val();
                        //zohoInvenOrgID = $("#zohoInvenOrgId").val();
                        $(".error2").remove();
                        if (zohoInvenUserName.length < 1) {
                            $("#zohoInvenUserName").after('<label class="error2">This field is required.</label>');
                            return false;
                        } else if (zohoInvenPassword.length < 1) {
                            $("#zohoInvenPassword").after('<label class="error2">This field is required.</label>');
                            return false;
                        } else {
                            num = 1;
                            type = 'login';
                        }
                    }

                    if (type === 'api') {
                        $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                        function_name = function_name + 'zohoinventory';
                        $.ajax({
                            url: "input-validation.php?type=validateZohoInvenZuthToken",
                            method: "POST",
                            data: {zohoInvenAuthToken: zohoInvenAuthToken, authType: type},
                            dataType: "text"
                        }).done(function (result) {
                            $('#credential2Check').html('Credential 2 Submit');
                            if (result === 'false') {
                                swal("OOPS...", "Wrong credentials In App 2 ! ", "error");
                            }
                            else {
                                swal("Ok", "Connected... Now Choose Your Organization Id", "success");
                                $('.zohoInventoryOrgDivApi').html(result);
                                $('#credential2Check').attr('id', 'zohoInventoryLoginSubmit');
                                //For Credential Only for Zoho Inventory Submit we will change the id name of submit 2 and call it after populated value field checking

                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something went wrong In App 2 !", "error");
                        });
                    }
                    else {
                        $('#credential2Check').html('<i class="fa fa-spinner fa-spin"></i>');
                        function_name = function_name + 'zohoinventory';
                        $.ajax({
                            url: "input-validation.php?type=validateZohoInvenZuthToken",
                            method: "POST",
                            data: {
                                zohoInvenUserName: zohoInvenUserName,
                                zohoInvenPassword: zohoInvenPassword,
                                authType: type
                            },
                            dataType: "text"
                        }).done(function (result) {
                            $('#credential2Check').html('Credential 2 Submit');
                            if (result === 'false') {
                                swal("OOPS...", "Wrong credentials In App 2 ! ", "error");
                            }
                            else {
                                swal("Ok", "Connected... Now Choose Your Organization Id", "success");
                                $('.zohoInventoryOrgDivLogin').html(result);
                                $('#credential2Check').attr('id', 'zohoInventoryLoginSubmit');
                                //For Credential Only for Zoho Inventory Submit we will change the id name of submit 2 and call it after populated value field checking

                            }
                        }).fail(function (jqXHR, textStatus) {
                            swal("OOPS...", "Something went wrong In App 2 !", "error");
                        });
                    }


                }
                else {
                    swal("Please select any Cloud App 2 to proceed further!");
                }

            }

        });

        $('body').on('click', '#try_sync', function (e) {


            e.preventDefault();
            //for loading


            var id = $('#subscription_id').val();
            var now = new Date(),
                time_to = now.getHours() + ':' + now.getMinutes();
            time_from = now.getHours() - 1 + ':' + now.getMinutes();


            swal({
                title: "Are you sure?",
                text: "From " + time_from + " To " + time_to + " Data will Synchronize !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Do it!"
            }, function () {
                $('#try_sync').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                $.ajax({
                    url: "../subscription/TrySync.php",
                    type: "get", //send it through get method
                    data: {
                        id: id,
                        page: 'crm-type'
                    },
                    success: function (response) {
                        $('#try_sync').html('Try Now');
                        if ($.isNumeric(response)) {
                            $total = "Total No of Order is " + response;

                            swal({
                                title: "Done",
                                text: $total,
                                type: "success",
                                showCancelButton: true,
                                confirmButtonText: "Yes, proceed for subscription!",
                                closeOnConfirm: false
                            }, function () {
                                window.location = "../subscription/select-plan.php?access_id=" + id;
                            });
                        }
                        else {
                            swal("OOPS...", "Unable  To Process Credential", "error");
                        }
                    },
                    error: function (xhr) {
                        swal("OOPS...", "Unable  To Process", "error");
                    }
                });
                //window.location = "../subscription/TrySync.php?id=" + id + "&page=crm-type";

            });

        });

        //if Clicking Skip_next
        $('body').on('click', '#skip_next', function (e) {
            e.preventDefault();
            var id = $('#subscription_id').val();
            window.location = "../subscription/select-plan.php?access_id=" + id;
        });

        //check box checking For Zoho Inventory If Login Type Selecting or Auth Type Selecting
        $('body').on('click', '#box-1', function (e) {
            if ($(this).is(':checked')) {
                $('#login_type_inventory').css('display', 'none');
                $('#token_type_inventory').css('display', 'block');
                $('.login-type-help').css('display', 'none');
                $('.token-type-help').css('display', 'block');
                $('#zohoInvenUserName').val('');
                $('#zohoInvenPassword').val('');
                $('#zohoInvenAuthToken2').val('');
                $('#zohoInvenOrgID2').val('');
                $('#credential2Check').html('Credential 2 Submit');
            }
            else {

                $('#login_type_inventory').css('display', 'block');
                $('#token_type_inventory').css('display', 'none');
                $('.login-type-help').css('display', 'block');
                $('.token-type-help').css('display', 'none');
                $('#zohoInvenUserName').val('');
                $('#zohoInvenPassword').val('');
                $('#zohoInvenAuthToken2').val('');
                $('#zohoInvenOrgID2').val('');
                $('#credential2Check').html('Check Credential 2');
            }
        });

        //check box checking For Zoho Inventory If Login Type Selecting or Auth Type Selecting
        $('body').on('click', '#box-2', function (e) {
            if ($(this).is(':checked')) {
                $('#login_type_crm').css('display', 'none');
                $('#token_type_crm').css('display', 'block');
                $('.login-type-crm').css('display', 'none');
                $('.token-type-crm').css('display', 'block');
                $('#zohoUserName').val('');
                $('#zohoPassword').val('');
                $('#zohoAuthToken2').val('');
            }
            else {

                $('#login_type_crm').css('display', 'block');
                $('#token_type_crm').css('display', 'none');
                $('.login-type-crm').css('display', 'block');
                $('.token-type-crm').css('display', 'none');
                $('#zohoUserName').val('');
                $('#zohoPassword').val('');
                $('#zohoAuthToken2').val('');
            }
        });

        //This function Call When Zoho Inventory Credential Only Username And Password Provided Not Auth Token and Organization Id
        $('body').on('click', '#zohoInventoryLoginSubmit', function (e) {
            var currentCrmType = $('#crmtype').val(); //First Crm
            var credential = $('#subscription_id').val();
            var zohoInvenAuthToken = $('#zohoInventoryAuthToken').val();
            var zohoInvenOrgId = $('#zohoInventoryOrganizationId').val();
            if (currentCrmType === 'MAGENTO') {
                var function_name = 'magentoTo';
            }
            else if (currentCrmType === 'BIGCOMMERCE') {
                var function_name = 'bigcommerceTo';
            }
            else {
                var function_name = 'shopifyTo';
            }

            //Current Crm type 2 Credential Checking
            var currentCrmType2 = $('#crmtype2').val(); //Second Crm
            function_name = function_name + 'zohoinventory';

            if (zohoInvenOrgId === '') {
                $("#zohoInventoryOrganizationId").after('<label class="error2">This field is required.</label>');
                return false;
            }
            else {
                $(".error2").remove();
                swal({
                    title: "You've selected ZOHO INVENTORY In App 2",
                    text: "Your provided credential are correct!",
                    type: "success",
                    showCancelButton: true,
                    confirmButtonText: "Yes, proceed next!",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                }, function () {
                    $.ajax({
                        url: "crm-choosen.php",
                        type: "get", //send it through get method
                        data: {
                            insertedId: insertedId,
                            crmType: currentCrmType2,
                            zohoInvenAuthToken: zohoInvenAuthToken,
                            zohoInvenOrgID: zohoInvenOrgId,
                            credential: credential,
                            function_name: function_name
                        },
                        success: function (response) {
                            if ($.isNumeric(response)) {
                                //inserted id stored in hidden field
                                swal("Success", "App 2 Credential Update Successfull", "success");
                                $('#zohoInventoryLoginSubmit').css('display', 'none');
                                $('.app1help').css('display', 'none');
                                $('.app2help').css('display', 'none');
                                $('.app3help').css('display', 'block');
                                $('.boxes').css('display', 'none');
                                $('#sfdcInput2 :input').prop("disabled", true);

                            }
                            else {
                                swal("OOPS...", "Unable  To Process Credential", "error");
                            }
                        },
                        error: function (xhr) {
                            swal("OOPS...", "Unable  To Insert Credential", "error");
                        }
                    });
                    //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&zohoInvenAuthToken=" + zohoInvenAuthToken + "&zohoInvenOrgID=" + zohoInvenOrgID + "&credential=" + credential + "&function_name=" + function_name;
                });
            }
        });

    });

    // Function For Getting Hubspot Access Token From Popup Window.
    function GetToken(access, refresh) {
        var insertedId = "<?php echo $insertDataId; ?>";
        var app1 = $('#crmtype').val().toLowerCase();
        var app2 = $('#crmtype2').val().toLowerCase();
        var crmType = app2.toUpperCase();
        var function_name = app1 + 'To' + app2;
        var credential = $('#subscription_id').val();
        $(".error2").remove();

        $('#credential2Check').html('Credential 2 Submit');
        swal({
            title: "You've selected HUBSPOT CRM In App 2",
            text: "Your provided credentials are correct!",
            type: "success",
            showCancelButton: true,
            confirmButtonText: "Yes, proceed next!",
            showLoaderOnConfirm: true,
            closeOnConfirm: false
        }, function () {

            $.ajax({
                url: "crm-choosen.php",
                type: "get", //send it through get method
                data: {
                    insertedId: insertedId,
                    crmType: crmType,
                    refresh_token: refresh,
                    credential: credential,
                    function_name: function_name
                },
                success: function (response) {
                    if ($.isNumeric(response)) {
                        $.ajax({
                            url: "hubspot/create-form.php?type=" + app1,
                            type: "get", //send it through get method
                            data: {
                                access_token: access,
                                credential: credential
                            },
                            success: function (response) {
                                //Operation On field
                                if (response === '1') {
                                    swal("Done", "Proceed For Next..", "success");
                                    $('#credential2Check').css('display', 'none');
                                    $('.app1help').css('display', 'none');
                                    $('.app2help').css('display', 'none');
                                    $('.app3help').css('display', 'block');
                                    $('#sfdcInput2 :input').prop("disabled", true);
                                } else {
                                    swal("OOPS...", response, "error");
                                }
                            },
                            error: function (xhr) {
                                swal("OOPS...", "Unable  To Create Form", "error");
                            }
                        });
                    }
                    else {
                        swal("OOPS...", "Unable  To Process Credential", "error");
                    }
                },
                error: function (xhr) {
                    swal("OOPS...", "Unable  To Insert Credential", "error");
                }
            });
            //window.location = "crm-choosen.php?insertedId=" + insertedId + "&crmType=" + currentCrmType2 + "&vtiger_user_name=" + vtigerUsername + "&vtiger_password=" + vtigerEndpoint + "&vtiger_security_password=" + vtigerAccessKey + "&credential=" + credential + "&function_name=" + function_name;
        });
    }
    //End of Function................
</script>
</body>
</html>

