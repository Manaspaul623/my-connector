<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="/bigcommerce-app-management/style/before-installation/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    </head>
    <body>
        <div class="grid-wrap">
            <img class="logo" src="/bigcommerce-app-management/images/logo.png" alt="logo">
            <div class="row">
                <div class="grid-7">
                    <h1>Zoho Information</h1>
                    <form class="cnterBox" method="post" id="frmZohoAuthToken" name="frmZohoAuthToken" action="basic-data.php">
                        <span id="zMessage"></span>
                        <input type="hidden" name="crm_type" id ="crm_type" value="<?php echo $_POST['crmtype']; ?>">
                        <input type="hidden" name="code" id ="code" value="<?php echo $_POST['c_code']; ?>">
                        <input type="hidden" name="scope" id ="scope" value="<?php echo $_POST['c_scope']; ?>">
                        <input type="hidden" name="context" id ="context" value="<?php echo $_POST['c_context']; ?>">
                        <div class="box">
                            <label>Zoho auth token</label>
                            <input type="text" id="zohoAuthToken" name="zohoAuthToken" placeholder="Zoho auth token" required>
                            <input class="primary-btn" type="submit" name="zohoAuthInfoSub" id="zohoAuthInfoSub" value="Submit">
                        </div>
                    </form>
                </div>
                <div class="grid-5 help">
                    <h2>Instructions</h2>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                $('body').on('click', '#zohoAuthInfoSub', function () {
                    $("#frmZohoAuthToken").validate({
                        submitHandler: function (form) {
                            $("#zohoAuthInfoSub").val('Loading.....');
                            $("#zohoAuthInfoSub").attr('disabled', 'disabled');
                            form.submit();
                        },
                        rules: {
                            zohoAuthToken: {
                                required: true,
                                remote: "input-validation.php?type=validateZohoZuthToken"
                            }
                        },
                        messages: {
                            zohoAuthToken: {
                                required: "Please enter your zoho auth token",
                                remote: "Please enter a valid token."
                            }
                        }
                    });
                });
            });
        </script>
        <script type="text/javascript">
var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || 
{widgetcode:"ef65a35676541685f18b32d11ac194752e5c4d06a654cfd6d2c912381758911d", values:{},ready:function(){}};
var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;
s.src="https://salesiq.zoho.com/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);d.write("<div id='zsiqwidget'></div>");
</script>
    </body>
</html>