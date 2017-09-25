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
                    <h1>Salesforce Information</h1>
                    <form class="cnterBox"   name="frmSubmit" id="sfdcForm" method="post" action="basic-data.php">               
                        <input type="hidden" name="crm_type" id ="crm_type" value="<?php echo $_POST['crmtype']; ?>">
                        <input type="hidden" name="code" id ="code" value="<?php echo $_POST['c_code']; ?>">
                        <input type="hidden" name="scope" id ="scope" value="<?php echo $_POST['c_scope']; ?>">
                        <input type="hidden" name="context" id ="crm-type" value="<?php echo $_POST['c_context']; ?>">

                        <div class="box">
                            <label>Sfdc user name</label>
                            <input type="text" id="sfdc_user_name" name="sfdc_user_name" required>
                            <label>Sfdc password</label>
                            <input type="password" id="sfdc_password" name="sfdc_password" required>
                            <label>Sfdc security password</label>
                            <input type="password" id="sfdc_security_password" name="sfdc_security_password" required>
                            <input class="primary-btn" type="submit" id="sfdcSubmit" name="sfdcSubmit" value="Submit">
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
                $('body').on('click', '#sfdcSubmit', function () {
                    $("#sfdcForm").validate({
                        submitHandler: function (form) {
                            var sfdc_user_name = $("#sfdc_user_name").val();
                            var sfdc_password = $("#sfdc_password").val();
                            var sfdc_security_password = $("#sfdc_security_password").val();
                            $.ajax({
                                url: "input-validation.php?type=validateSfdcInfo",
                                method: "POST",
                                data: {sfdc_user_name: sfdc_user_name, sfdc_password: sfdc_password, sfdc_security_password: sfdc_security_password},
                                dataType: "json"
                            }).done(function (result) {
                                console.log(result);
                                alert("Credentials correct");
                                form.submit();
                            }).fail(function (jqXHR, textStatus) {
                                alert("Wrong credentials ");
                            });
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