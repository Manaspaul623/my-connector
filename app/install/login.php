<?php
session_start();
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
if(isset($_SESSION['user_id']))
{
    header("Location:https://" . APP_DOMAIN . "/app/install/crm-type.php");
}
include "$_SERVER[DOCUMENT_ROOT]/app/install/login-process.php";
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="google-signin-client_id" content="<?php echo GOOGLE_CLIENT_ID; ?>">
    <link rel="shortcut icon" type="image/png" href="../images/fav.png"/>
    <link rel="canonical" href="www.aquaapi.io/app/install/login.php"/>
    <title>Aquaapi :: Login</title>
    <link rel="stylesheet" type="text/css" href="/app/style/before-installation/style.css">
    <link rel="stylesheet" type="text/css" href="/app/style/sweetalert.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="/app/js/sweetalert.min.js"></script>

    <!-- for Zoho or Google anyliss  -->
    <script src="https://<?php echo APP_DOMAIN; ?>/js/page_analytics.js"></script>

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
        .abcRioButton abcRioButtonLightBlue{
            width: 200px !important;
        }
        .abcRioButtonLightBlue{
            background-color: #007282 !important;
            color: white !important;
            border-radius: 20px !important;
        }

    </style>

</head>
<body>


<center>
    <?php include_once("$_SERVER[DOCUMENT_ROOT]/app/analyticstracking.php") ?>
    <div class="page-wrap">
        <div class="grid-wrap">
            <a href="/"> <img class="logo pad" src="/app/images/logo.png" alt="logo"></a>
            <div class="row">
                <h1>Cloud Connector Configuration</h1>
                <div id="login_class" class="grid-12">
                    <div class="pad">


                        <div class="box">

                            <h3> Please Login</h3>
                            <div class="login_error">
                                <?php
                                if (isset($login_error)) {
                                    echo '<label class="error">Username and Password do not match</label>';
                                }
                                if (isset($register_success)) {
                                    echo '<label class="success">Registration Successful. Please Login...</label>';
                                }
                                if (isset($mail_send)) {
                                    echo '<label class="success">Reset Link sent successfully. Please check your Inbox (and your spam foldar, just in case)</label>';
                                }
                                if (isset($mail_not_send)) {
                                    echo '<label class="error">Mail not Sent..</label>';
                                }
                                if (isset($user_exist)) {
                                    echo '<label class="warning">User already exists...</label>';
                                }
                                if (isset($user_not_found)) {
                                    echo '<label class="error">User not found ?</label>';
                                }
                                ?>
                            </div>
                            <div id="web_main">
                                <div id="main" class="edit_dv">
                                    <div id="left-main">
                                        <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post'>
                                            <div class="left-main inner">
                                                <div class="panel-section">

                                                    <label style="text-align:left;">Email Address:</label>
                                                    <input type='email' id='username' name='username' required/>
                                                    <br />
                                                    <label style="text-align:left;">Password:</label>
                                                    <input type='password' id='passwd' name='passwd' required/>
                                                    <br>
                                                </div>
                                                <input type='submit' name="login" class="primary-btn" value='Log in'/> <br>OR
                                                <div>
                                                    <!--fb:login-button size="large" scope="public_profile,email" onclick="checkLoginState();" login_text="Login with Facebook">
                                                    </fb:login-button--></div><br>
                                                <div class="g-signin2" data-onsuccess="Google_signIn"></div>

                                                <br />
                                                <h5> Forgot Password ?  <a class="btn_submit" id="forget" href="#forget">Click Here</a></h5>
                                                <h4> Do not have an Account ?  <a class="btn_submit" id="register" href="#register">Register as a New User</a></h4>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="clear-block"></div>
                            </div>



                        </div>

                    </div>

                </div>
                <!-- For Registration -->
                <div id="register_class" class="grid-12" style="display:none;">
                    <div class="pad">
                        <div class="box">

                            <h3>Register New User</h3>
                            <?php
                            if(isset($register_error))
                            {
                                echo '<label class="error">Registration Not Successfull</label>';
                            }
                            ?>
                            <div id="web_main">
                                <div id="main" class="edit_dv">
                                    <div id="left-main">
                                        <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post'>
                                            <div class="left-main inner">
                                                <div class="panel-section">

                                                    </span>
                                                    <label style="text-align:left;">Name:</label>
                                                    <input type='text' id='username' name='username' required />
                                                    <br />
                                                    <label style="text-align:left;">Email Address:</label>
                                                    <input type='email' id='email' name='email' required />
                                                    <br />
                                                    <label style="text-align:left;">Password:</label>
                                                    <input type='password' id='passwd' name='passwd' required/>
                                                    <br>
                                                    <label style="text-align:left;">Confirm Password:</label>
                                                    <input type='password' id='confirm_passwd' name='confirm_passwd' required/>

                                                </div>

                                                <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_RECAPTCHA_ID; ?>"></div><br><br>
                                                <input type='submit'  name="register" class="primary-btn" value='Register'/>
                                                <br />
                                                <h4> Already a user? <a class="btn_submit" id="login" href="#login">Log In </a> here</h4>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="clear-block"></div>
                            </div>



                        </div>

                    </div>

                </div>
                <!-- For Forgot Password -->
                <div id="forget_class" class="grid-12" style="display:none;">
                    <div class="pad">
                        <div class="box">
                            <h3> Forgot Password</h3>
                            <div id="web_main">
                                <div id="main" class="edit_dv">
                                    <div id="left-main">
                                        <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post'>
                                            <div class="left-main inner">
                                                <div class="panel-section">

                                                    <label style="text-align:left;">Enter Registered Email Address:</label>
                                                    <input type='email' id='reset_email' name='reset_email' required/>
                                                </div>
                                                <input type='submit' name="forget" class="primary-btn" value='Send Reset Link'/>

                                                <h5>Already a registered user ? <a class="btn_submit" id="login1" href="#login">Log In </a> here</h5>

                                                <h4>Do not have an Account ?  <a class="btn_submit" id="register1" href="#register">Register as a New User</a></h4>
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

<script type="text/javascript">
    $(document).ready(function () {

        $('#register,#register1').click(function (event) {
            $('#forget_class').css('display', 'none');
            $('#login_class').css('display', 'none');
            $('#register_class').css('display', 'block');
        });

        $('#login,#login1').click(function (event) {
            $('#register_class').css('display', 'none');
            $('#login_class').css('display', 'block');
            $('#forget_class').css('display', 'none');
        });

        $('#forget').click(function (event) {
            $('#register_class').css('display', 'none');
            $('#login_class').css('display', 'none');
            $('#forget_class').css('display', 'block');
        });


    });

</script>

<script type="text/javascript"> // Facebook Sign In

    function statusChangeCallback(response) {
        console.log(response);
        if (response.status === 'connected') {
            // Logged into your app and Facebook.
            testAPI();
        }
    }

    function checkLoginState() {
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });
    }

    window.fbAsyncInit = function () {
        FB.init({
            appId: '<?php echo FACEBOOK_APP_ID; ?>',
            cookie: true,  // enable cookies to allow the server to access
                           // the session
            xfbml: true,  // parse social plugins on this page
            version: 'v2.8' // use graph api version 2.8
        });

        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });

    };

    // Load the SDK asynchronously
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));


    function testAPI() {
        FB.api('/me', function (response) {
            console.log(response);
            var id = response.id;
            var name  = response.name;
            var type = 'FACEBOOK';
            update_facebook_data(id,name,type);
        },{scope:'email'});

    }

</script>
<script type="text/javascript">
    //Google Sign In
    function Google_signIn(googleUser) {
        var profile = googleUser.getBasicProfile();
        var id = profile.getId();
        var name = profile.getName();
        var email = profile.getEmail();
        update_google_data(id,name,email);
    }

    function update_google_data(id,name,email) {
        $.ajax({
            type: "POST",
            data: { id: id, name: name, email: email},
            url: 'login_user_api.php',
            success: function(msg) {
                if(msg === '1')
                {
                    location.reload();
                }
            }
        });
    }

    function update_facebook_data(id,name,type) {
        $.ajax({
            type: "POST",
            data: { id: id, name: name, type: type},
            url: 'login_user_api.php',
            success: function(msg) {
                if(msg === '1')
                {
                    location.reload();
                }
            }
        });
    }
</script>
</body>
</html>

