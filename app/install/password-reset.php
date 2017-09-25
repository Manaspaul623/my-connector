<?php

include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
$user_temp_pass='';
$password_changed=0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['change'])) {

        $oldpassword = $_POST['temp_password'];
        $newpassword = $_POST['retypenewpassword'];

        $cond = " AND user_password='$oldpassword'";
        $usrDetails = count_row($userLogin, $cond);
        if ($usrDetails == 0) {
            $login_error = 1;
        } else {

            //Checking Any Subscription Previously  Created by This User
            $update_arr= array(
                'user_password' => md5($newpassword)
            );
            $cond = " AND user_password='$oldpassword'";
            $password_update = update($userLogin,$update_arr,$cond);

            if ($password_update) {
                $password_changed = 1;
            } else {
                $update_error = 1;
            }
        }

    }
}

if(isset($_REQUEST['code'])){
    $user_temp_pass=base64_decode(urldecode($_REQUEST['code']));
    $cond = " AND user_password='$user_temp_pass'";
    $usr_count = count_row($userLogin, $cond);

}
else
{
    $usr_count=0;
}




?>


<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/png" href="../images/fav.png"/>
    <link rel="canonical" href="www.aquaapi.io/index.html"/>
    <title>Aquaapi :: Change Password</title>
    <link rel="stylesheet" type="text/css" href="/app/style/before-installation/style.css">
    <link rel="stylesheet" type="text/css" href="/app/style/sweetalert.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <script src="/app/js/sweetalert.min.js"></script>

    <style type="text/css">
        label.error2 {
            color: #ff0000;
            font-size: 12px;
            margin-top: -8px;
        }
        label.success {
            color: limegreen;
            font-size: 12px;
            margin-top: -8px;
        }
        label.warning {
            color: darkorange;
            font-size: 12px;
            margin-top: -8px;
        }

    </style>

</head>
<body>
<center>
    <?php include_once("$_SERVER[DOCUMENT_ROOT]/app/analyticstracking.php") ?>
    <div class="page-wrap">
        <div class="grid-wrap">
            <img class="logo pad" src="/app/images/logo.png" alt="logo">
            <div class="row">
                <div id="login_class" class="grid-12">
                    <div class="pad">
                        <div class="box">
                            <h3> Change Password</h3>
                            <div class="login_error">
                                <?php
                                if(isset($login_error))
                                {
                                    echo '<label class="error">Something Went Wrong</label>';
                                }
                                if(isset($update_error))
                                {
                                    echo '<label class="error">Password Not Changed</label>';
                                }
                                if($password_changed != 0)
                                {
                                    echo '<label class="success">Password Update Successfull.</label>';
                                }
                                ?>
                            </div>
                            <div id="web_main">
                                <div id="main" class="edit_dv">
                                    <div id="left-main">
                                        <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post'>
                                            <div class="left-main inner">
                                                <div class="panel-section">
                                                    <label style="text-align:left;">New Password:</label>
                                                    <input type='password' id='newpassword' name='newpassword' required/>
                                                    <br>
                                                    <label style="text-align:left;">Retype New Password:</label>
                                                    <input type='password' id='retypenewpassword' name='retypenewpassword' required/>
                                                    <br>
                                                </div>
                                                <!-- hidden Temp password -->
                                                <input type="hidden" name="temp_password" value="<?php echo $user_temp_pass;?>">

                                                <input type='submit' name="change" class="primary-btn" value='Change Password'/>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="clear-block"></div>
                            </div>



                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</center>
<div class="footer">
    <p>Copyright (c) 2016-2017 aquaAPI LLC</p>
    <a target="_blank" href="http://aquaapi.com/termsofservice.html">Terms And Conditions</a> | <a target="_blank" href="http://aquaapi.com/privacy.html">Privacy Policy</a> | <a target="_blank" href="http://aquaapi.com/contact.html">Contact Us</a>
</div>
</body>
</html>
<script type="text/javascript">

    $(document).ready(function () {
        $user = '<?php echo $usr_count; ?>';
        $changed_password='<?php echo $password_changed; ?>';


        if($user == 0)
        {
            //swal("OOPS...", "Unknown Link Provided", "error");


            swal({
                title: "OOPS...",
                text: "Unknown Link Provided",
                type: "error",
                showCancelButton: false,
                confirmButtonText: "Take me to Login"

            }, function(){
                window.location = "/app/install/login.php";
            });

        }
        if($changed_password == 1)
        {
            swal({
                title: "Success",
                text: "Your Password Changed",
                type: "success",
                showCancelButton: false,
                confirmButtonText: "Take me to Login"

            }, function(){
                window.location = "/app/install/login.php";
            });
        }
    });
</script>