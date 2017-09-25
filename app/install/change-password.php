<?php

session_start();
//For Logout The Session
if(isset($_GET['logout']))
{   unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    session_destroy();
}
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if (!isset($_SESSION['user_id'])) {
    header("Location:https://" . APP_DOMAIN . "/app/install/login.php");
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['change'])) {
        $user_id=$_SESSION['user_id'];
        $oldPassword = md5($_POST['oldpassword']);
        $newPassword = $_POST['retypenewpassword'];

        $cond = " AND user_id='$user_id' AND user_password='$oldPassword'";
        $usrDetails = count_row($userLogin, $cond);
        if ($usrDetails == 0) {
            $login_error = 1;
        } else {

            //Checking Any Subscription Previously  Created by This User
            $update_arr= array(
                'user_password' => md5($newPassword)
            );
            $cond = " AND user_id='$user_id'";
            $password_update = update($userLogin,$update_arr,$cond);

            if ($password_update) {
                $password_changed = 1;
            } else {
                $update_error = 1;
            }
        }

    }
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
        <?php include "$_SERVER[DOCUMENT_ROOT]/app/header.php"; ?>
        <div class="grid-wrap">
            <div class="row">
                <div id="login_class" class="grid-12">
                    <div class="pad">
                        <div class="box">
                            <h3> Change Password</h3>
                            <div class="login_error">
                                <?php
                                if(isset($login_error))
                                {
                                    echo '<label class="error">Old Password Not Match</label>';
                                }
                                if(isset($update_error))
                                {
                                    echo '<label class="error">Password Not Changed</label>';
                                }
                                if(isset($password_changed))
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

                                                    <label style="text-align:left;">Old Password:</label>
                                                    <input type='password' id='oldpassword' name='oldpassword' required/>
                                                    <br />
                                                    <label style="text-align:left;">New Password:</label>
                                                    <input type='password' id='newpassword' name='newpassword' required/>
                                                    <br>
                                                    <label style="text-align:left;">Retype New Password:</label>
                                                    <input type='password' id='retypenewpassword' name='retypenewpassword' required/>
                                                    <br>
                                                </div>
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
